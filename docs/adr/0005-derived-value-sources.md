# ADR: Derived Value Sources

## Status

Approved

## Context

A product already carries most of what a merchant retypes into a specification table.
WooCommerce knows its weight, its dimensions, its SKU, its categories, its tags and its
attributes, and renders them in its own "Additional information" tab.

v1 has no way to use any of it. Every value is typed into the metabox by hand and copied
into the product snapshot. The plugin's entire relationship with WooCommerce's own data
is one global option, `dwps_wc_default_specs`, which decides whether Woo's tab is
removed — `Integration\WooCommerce\ProductTabs::addProductSpecificationsTab()`.

So a merchant who wants weight in the specification table types it twice, and the second
copy goes stale the moment the real weight changes. That is the problem this ADR exists
for.

ADR 0003 removed the mechanism this ADR was originally going to define. There is no
`isDerived()` flag and no `resolve()` method. A type computes its value in `value()` and
draws no input, because it registers no `edit` component on the client. Nothing is
stored because nothing is ever submitted: with no input, the specification's key never
reaches the payload. Nothing further is needed to express "derived".

What remains open is the contract around such a type: what its sources are, how its
output is formatted, what it does when the underlying field is empty, and what happens
to values stored before a specification became derived.

## Decision

### One registered type per source

Each source is its own type, registered through the ADR 0003 registry:

```txt
woocommerce_weight
woocommerce_dimensions
woocommerce_sku
woocommerce_categories
woocommerce_tags
woocommerce_attribute
```

Each owns its own `value()`, its own formatting and its own cost. A specification of
type `woocommerce_weight` needs no configuration at all; only `woocommerce_attribute`
carries an option, naming which attribute to read.

The alternative shapes were rejected. A single `woocommerce_detail` type carrying the
source in its options turns `value()` into a switch over sources — the exact shape the
registry was built to delete. A generic field name resolved dynamically against the
product exposes every getter on `WC_Product`, including `get_id()`, `get_meta()` and
`get_changes()`, none of which is a specification.

A third party adds a source the same way it adds any other type, because it *is* any
other type.

### Formatting is delegated to WooCommerce

The plugin introduces no unit, currency, decimal or term-list formatting of its own:

| Type | Delegates to |
| --- | --- |
| `woocommerce_weight` | `wc_format_weight()` |
| `woocommerce_dimensions` | `wc_format_dimensions()` |
| `woocommerce_categories` | `wc_get_product_category_list()` |
| `woocommerce_tags` | `wc_get_product_tag_list()` |
| `woocommerce_attribute` | `WC_Product::get_attribute()` |

The specification table then shows byte-identical output to the rest of the store: the
same decimal separator, the same unit, the same linked terms. A store that switches its
weight unit from kilograms to pounds updates every specification table at once, with
nothing stored and nothing migrated.

This also keeps PS-060's unit type independent. That type exists for hand-entered
numbers with a unit. A derived weight never touches it.

### The type tests emptiness itself

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

A type that delegated blindly would never produce an empty value, so a row would never
be empty, and ADR 0004's `empty_rows` setting would silently stop working for every
derived specification.

The type therefore checks the underlying field first and returns nothing when it is
unset. WooCommerce's formatter is called only for a value that exists. Whether an empty
row is hidden, or shown with a placeholder, stays a per-set presentation decision where
ADR 0004 put it.

### `render()` returns output-ready HTML, and the type escapes it

`wc_get_product_category_list()` returns anchor tags. v1's `true_false` presentation is
an inline SVG. Both are legitimate output, so the render contract ADR 0003 left implicit
is settled here:

```txt
render()  returns output-ready HTML
the type  is responsible for escaping everything inside it
the layout template outputs it unescaped
```

A template that escaped the return value would destroy both cases. A type that returns
unescaped user input is a vulnerability, and the responsibility sits with the type
because only the type knows which parts of its output are markup.

### Nothing is stored, and nothing is removed

A derived specification has no key in the value map, on a product or on a variation.

A specification switched from a storing type to a derived one leaves its old values on
every product. Nothing removes them. This is deliberate: ADR 0002 guarantees that a
value the current type cannot read survives untouched, so switching a specification back
restores every product's value. Deleting them on the type change would make that switch
a data-loss event.

They cost meta space until the merchant deletes the specification, at which point
PS-024's mandatory value purge removes them.

`Upgrade`'s cleanup step (PS-048) does not do this. It is the migration's second phase —
one explicit action after a successful migration — not an ongoing collector, and it runs
once.

### A derived specification has no default

There is no input, so there is nothing to prefill. The specification editor hides the
default value field for a type that registers no `edit` component.

### Variations need nothing

`WC_Product_Variation::get_weight()` already falls back to the parent's weight when the
variation's own is unset, and the same is true of dimensions. A derived specification
therefore follows WooCommerce's own inheritance rules for free, without ADR 0006's
variation override model applying to it at all.

## Rationale

The mechanism question answered itself once ADR 0003 removed the flag. Everything left
in this ADR is about not reinventing what WooCommerce already does correctly.

Delegating formatting is the load-bearing decision. A plugin that formats a weight
itself will format it differently from the store around it, in some locale, eventually.
Calling the same function WooCommerce calls means that class of bug cannot occur.

Keeping orphaned values follows from ADR 0002 rather than being a new position. The two
decisions together give one property worth stating plainly: **changing a specification's
type is always reversible.**

## Consequences

### Positive

- No duplicate data entry, and no stale copies of data WooCommerce already owns.
- Derived output is indistinguishable from WooCommerce's own.
- Changing a specification's type is reversible in both directions.
- Adding a source is adding a type. It needs no new concept and no core change.
- Variation inheritance for derived values is free and already correct.

### Negative

- **A taxonomy source costs a term query per product.** Free inside a normal product
  loop, where `update_post_term_cache` has primed the terms. Not free in a comparison
  table (PS-065), which renders many products at once and must prime term caches across
  its whole product set explicitly.
- Orphaned values from a type change persist indefinitely and are invisible in the
  admin. They are inert, and they are the price of reversibility.
- Each source is a class. Six sources are six small classes rather than one with a
  switch, which is more files and less to read in each.
- A type owning its own escaping means a badly written third-party type can introduce an
  XSS. The contract has to be documented for third parties, not just followed by core.

## Constraints recorded for later tickets

- **PS-077's invariant does not cover derived values.** "Any projection must be
  rebuildable from the map" is false for a derived specification, whose value is
  rebuildable from the *product*. Filtering on a derived specification needs a different
  mechanism from filtering on a stored one, and PS-077 must account for both.
- **PS-065 comparison** across a derived specification means loading every product
  object, not reading a meta value. Its cost profile differs from comparison across
  stored values.
- **PS-061** implements the types this ADR describes. Reading a variation's own
  WooCommerce attribute stays out of scope there and here.

## Alternatives Considered

### One `woocommerce_detail` type with the source in its options

Rejected. It keeps the registry small at the cost of a `switch` inside `value()`,
`render()` and the options panel — the closed-set shape ADR 0003 exists to remove. It
also forces every source to share one options schema and one client component.

### A generic field name resolved against the product

Rejected. It reaches every WooCommerce field with no core change, and it exposes every
public getter on `WC_Product` as a candidate specification. There is no safe list to
derive from a naming convention.

### The plugin formats derived values itself

Rejected. It would duplicate WooCommerce's unit, decimal and currency handling, and
diverge from it in some locale eventually. The store's own output is the correct output
by definition.

### Compose derived weights with the unit type from PS-060

Rejected. It gives the plugin one rendering path for every unit, at the cost of coupling
a tier-one type to a tier-two ticket and reimplementing what `wc_format_weight()` already
does.

### Purge stored values when a specification becomes derived

Rejected. It is the tidiest database and it makes changing a type irreversible, which
contradicts the guarantee ADR 0002 makes.

### A maintenance action that collects orphans

Rejected for now. It is a screen nobody visits to recover space nobody is short of. If
orphan volume ever becomes real, it is additive and can be built then.

## Final Decision

A derived specification is an ordinary type that draws no input and computes its read. Sources are individually registered types, one per source. Formatting is
WooCommerce's. Emptiness is tested by the type so that per-set presentation settings
keep working. `render()` returns output-ready HTML and the type escapes it. Nothing is
stored, and nothing stored is removed, so a type change is always reversible.
