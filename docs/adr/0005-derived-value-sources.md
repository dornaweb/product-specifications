# ADR: Derived Value Sources

## Status

Approved. Amended 2026-11-08: one `woocommerce` type with the source in its options
replaces one registered type per source, and taxonomies are listed from the site rather
than fixed to categories and tags.

## Context

A product already carries most of what a merchant retypes into a specification table.
WooCommerce knows its weight, its dimensions, its SKU, its global unique ID, its
categories, tags and brands, and its attributes, and renders some of them in its own
"Additional information" tab.

v1 has no way to use any of it. Every value is typed into the metabox by hand and copied
into the product snapshot. The plugin's entire relationship with WooCommerce's own data
is one global option, `dwps_wc_default_specs`, which decides whether Woo's tab is
removed — `Integration\WooCommerce\ProductTabs::addProductSpecificationsTab()`.

So a merchant who wants weight in the specification table types it twice, and the second
copy goes stale the moment the real weight changes. That is the problem this ADR exists
for.

ADR 0003 removed the mechanism this ADR was originally going to define. There is no
`isDerived()` flag and no `resolve()` method. A type computes its value in `value()` and
draws no input. Nothing is stored because nothing is ever submitted: with no input, the
specification's key never reaches the payload.

What remains open is the contract around such a type: what its sources are, how its
output is formatted, what it does when the underlying field is empty, and what happens
to values stored before a specification became derived.

## Decision

### One `woocommerce` type, with the source in its options

One type is registered through the ADR 0003 registry, id `woocommerce`, labeled
**WooCommerce product data**. Its options name the source:

```json
{ "source": "weight" }
{ "source": "taxonomy", "name": "product_brand" }
{ "source": "attribute", "name": "pa_material" }
```

The type picker then separates the two things a merchant decides: *typed in* — text,
paragraph, select, yes or no, number with unit — or *read from the product*. Which field
is read is a choice inside that type, so moving a specification from weight to
dimensions is an options change, not a type change.

Inside the type, each source is its own small class behind one interface, held in a map
keyed by source id. The type looks the source up and delegates `value()`, `plainValue()`
and `render()` to it. There is no `switch`: each source owns its own reading, formatting
and emptiness test, which is what the rejected one-type shape lacked.

### Sources

| Source | Options | Reads | Formats with | Empty when |
| --- | --- | --- | --- | --- |
| `weight` | — | `get_weight()` | `wc_format_weight()` | `! has_weight()` |
| `dimensions` | — | `get_dimensions( false )` | `wc_format_dimensions()` | `! has_dimensions()` |
| `length`, `width`, `height` | — | `get_length()`, … | `wc_format_dimensions()` with one value | the value is empty, or the product is virtual |
| `sku` | — | `get_sku()` | escaped as text | empty string |
| `global_unique_id` | — | `get_global_unique_id()` | escaped as text | empty string |
| `taxonomy` | `name`: the taxonomy | `get_the_terms()` | links, as `get_the_term_list()` | no terms |
| `attribute` | `name`: the attribute | `WC_Product::get_attribute()` | escaped as text | empty string |

`global_unique_id` is WooCommerce's GTIN, UPC, EAN or ISBN field, added in 9.2. The
plugin requires WooCommerce 9.2 for it.

### Taxonomies come from the site

The taxonomy source offers every taxonomy registered for `product` that has an admin
screen (`show_ui`), except attribute taxonomies (`taxonomy_is_product_attribute()`),
which the attribute source covers. Categories, tags and brands appear without being named
in code, and so does a taxonomy another plugin registers for products. WooCommerce's
internal taxonomies, product type, visibility and shipping class, have no admin screen
and drop out.

Terms are read with `get_the_terms()` and rendered linked to their archive, in the markup
`get_the_term_list()` produces. That is what `wc_get_product_category_list()` and
`wc_get_product_tag_list()` call, so categories and tags render exactly as they do
elsewhere in the store. The value itself is the list of term names and links, so a plain
consumer gets the names without parsing markup.

### Attributes ignore WooCommerce's visibility checkbox

The attribute source offers the site's global attributes and accepts the name of a custom
product attribute. `get_attribute()` reads both: it tries the name as given, then with
the `pa_` prefix.

It reads the attribute whether or not "Visible on the product page" is ticked. The
merchant chose this attribute for the table, and unticking that box is how a merchant
keeps it out of WooCommerce's own tab without losing it.

### Formatting is delegated

The plugin introduces no unit, decimal or term-list formatting of its own. The table
shows byte-identical output to the rest of the store: the same decimal separator, the
same unit, the same linked terms. A store that switches its weight unit from kilograms to
pounds updates every specification table at once, with nothing stored and nothing
migrated.

This also keeps the unit type independent. That type exists for hand-entered
numbers with a unit. A derived weight never touches it.

### The source tests emptiness itself

WooCommerce's formatters do not return an empty value for an empty field. Both
`wc_format_weight()` and `wc_format_dimensions()` return the localized string `N/A`:

```php
function wc_format_weight( $weight ) {
    $weight_string = wc_format_localized_decimal( $weight );

    if ( ! empty( $weight_string ) ) {
        $weight_label  = I18nUtil::get_weight_unit_label( get_option( 'woocommerce_weight_unit' ) );
        $weight_string = sprintf( _x( '%1$s %2$s', 'formatted weight', 'woocommerce' ), $weight_string, $weight_label );
    } else {
        $weight_string = __( 'N/A', 'woocommerce' );
    }

    return $weight_string;
}
```

A source that delegated blindly would never produce an empty value, so a row would never
be empty, and ADR 0004's `empty_rows` setting would silently stop working for every
derived specification.

Each source therefore checks the underlying field first and returns `null` when it is
unset, using WooCommerce's own predicate where one exists, so a row is empty exactly when
WooCommerce's own tab would leave it out. The formatter is called only for a value that
exists. Whether an empty row is hidden, or shown with a placeholder, stays a per-set
presentation decision where ADR 0004 put it.

### An unavailable source renders nothing and keeps its options

A taxonomy whose plugin is deactivated, a deleted attribute, or a source id this version
does not know reads as empty, so the row follows `empty_rows`. The options are kept, so
reactivating the plugin restores the row.

### `value()` receives the specification's options

A source with a `name` cannot read without knowing it, so `value()` takes the
specification's options alongside the product and the stored value. ADR 0003 records the
signature.

### `render()` returns output-ready HTML, and the type escapes it

`get_the_term_list()` returns anchor tags. The yes-or-no type's icon presentation is an
inline SVG. Both are legitimate output, so the render contract ADR 0003 left implicit
is settled here:

```txt
render()  returns output-ready HTML
the type  is responsible for escaping everything inside it
the template outputs it unescaped
```

A template that escaped the return value would destroy both cases. A type that returns
unescaped user input is a vulnerability, and the responsibility sits with the type
because only the type knows which parts of its output are markup.

### The product screen shows where the value comes from

The type's client half registers a read-only value control naming the source, such as
"Read from the product's weight". It never calls `onChange`, so the specification's key
never enters the payload. Without a control the product screen would show the
unavailable-type warning and a raw value box.

### Nothing is stored, and nothing is removed

A derived specification has no key in the value map, on a product or on a variation.

A specification switched from a storing type to a derived one leaves its old values on
every product. Nothing removes them. This is deliberate: ADR 0002 guarantees that a
value the current type cannot read survives untouched, so switching a specification back
restores every product's value. Deleting them on the type change would make that switch
a data-loss event.

They cost meta space until the merchant deletes the specification, at which point
the mandatory value purge removes them.

`Upgrade`'s cleanup step does not do this. It is the migration's second phase —
one explicit action after a successful migration — not an ongoing collector, and it runs
once.

### A derived specification has no default

There is no input, so there is nothing to prefill. The type registers no default control,
and the specification editor hides the default value field.

### Variations

`WC_Product_Variation::get_weight()` already falls back to the parent's weight when the
variation's own is unset, and the same is true of dimensions and SKU. Those sources
follow WooCommerce's own inheritance rules for free, without ADR 0006's variation
override model applying to them at all.

Terms belong to the parent product, not the variation, and a variation's
`get_attribute()` returns its own selected value. Given a variation, the taxonomy and
attribute sources read the parent.

## Rationale

The mechanism question answered itself once ADR 0003 removed the flag. Everything left
in this ADR is about not reinventing what WooCommerce already does correctly.

Delegating formatting is the load-bearing decision. A plugin that formats a weight
itself will format it differently from the store around it, in some locale, eventually.
Calling the same function WooCommerce calls means that class of bug cannot occur.

One type with sources inside it follows from what the merchant is choosing. The type
picker is a list of kinds of answer; "weight", "SKU" and "brands" are not kinds of answer
but places to read one. Listing taxonomies and attributes from the site makes the source
a choice among things that exist on this store, which a fixed list of registered types
cannot express.

Keeping orphaned values follows from ADR 0002 rather than being a new position. The two
decisions together give one property worth stating plainly: **changing a specification's
type is always reversible.**

## Consequences

### Positive

- No duplicate data entry, and no stale copies of data WooCommerce already owns.
- Derived output is indistinguishable from WooCommerce's own.
- Brands and third-party product taxonomies are available with no code naming them.
- The type picker gains one entry, not one per field.
- Changing a specification's type is reversible in both directions.
- Variation inheritance for derived values is free and already correct.

### Negative

- **A taxonomy source costs a term query per product.** Free inside a normal product
  loop, where `update_post_term_cache` has primed the terms. Not free in a view that
  renders many products at once, which must prime term caches across its whole product
  set explicitly.
- A third party cannot add a source to the `woocommerce` type. It registers its own type
  instead, which is a separate entry in the picker.
- Orphaned values from a type change persist indefinitely and are invisible in the
  admin. They are inert, and they are the price of reversibility.
- A type owning its own escaping means a badly written third-party type can introduce an
  XSS. The contract has to be documented for third parties, not just followed by core.

## Constraints recorded for later work

- **The projection invariant does not cover derived values.** "Any projection must be
  rebuildable from the map" is false for a derived specification, whose value is
  rebuildable from the *product*. Filtering on a derived specification needs a different
  mechanism from filtering on a stored one, and filtering must account for both.
- **Reading a variation's own WooCommerce attribute** stays out of scope.
- **MPN and country of origin** have getters in WooCommerce's development branch,
  `get_mpn()` and `get_customs_country_of_origin()`, and are not in a release as of 11.2.
  Each becomes a source once the WooCommerce floor reaches the release that ships it.

## Alternatives Considered

### One registered type per source

The original decision of this ADR: `woocommerce_weight`, `woocommerce_dimensions`,
`woocommerce_sku`, `woocommerce_categories`, `woocommerce_tags`, `woocommerce_attribute`.
Superseded. It adds an entry to the type picker per field, mixed in with kinds of answer;
it turns a move from weight to dimensions into a type change with its warning; and it
cannot offer taxonomies that exist on the site without falling back to an option anyway.
The `switch` it avoided is avoided equally by sources as classes inside one type.

### A generic field name resolved against the product

Rejected. It reaches every WooCommerce field with no core change, and it exposes every
public getter on `WC_Product` as a candidate specification, including `get_id()`,
`get_meta()` and `get_changes()`. There is no safe list to derive from a naming
convention.

### A price source

Rejected. The price is already shown next to the title on the product page; it carries
sale, tax-display and variation-range logic, and its HTML includes strikethrough markup
built for that spot. A comparison view shows price beside the product image, not as a
specification row, and reads it from the product directly.

### Fixed taxonomy sources for categories and tags

Rejected. It misses brands and every taxonomy another plugin registers for products, and
each new one would need a release.

### The plugin formats derived values itself

Rejected. It would duplicate WooCommerce's unit, decimal and currency handling, and
diverge from it in some locale eventually. The store's own output is the correct output
by definition.

### Compose derived weights with the unit type

Rejected. It gives the plugin one rendering path for every unit, at the cost of coupling
two types and reimplementing what `wc_format_weight()` already does.

### Purge stored values when a specification becomes derived

Rejected. It is the tidiest database and it makes changing a type irreversible, which
contradicts the guarantee ADR 0002 makes.

### A maintenance action that collects orphans

Rejected for now. It is a screen nobody visits to recover space nobody is short of. If
orphan volume ever becomes real, it is additive and can be built then.

## Final Decision

A derived specification is an ordinary type that draws no input and computes its read.
WooCommerce product data is one type, `woocommerce`, whose options name the source; each
source is its own class inside it. Taxonomies and attributes are offered from the site.
Formatting is WooCommerce's. Emptiness is tested by the source so that per-set
presentation settings keep working. `value()` receives the specification's options.
`render()` returns output-ready HTML and the type escapes it. Nothing is stored, and
nothing stored is removed, so a type change is always reversible.
