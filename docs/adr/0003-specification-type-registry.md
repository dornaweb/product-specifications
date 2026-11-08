# ADR: Specification Type Registry

## Status

Approved

## Context

A specification has a type, which decides how its value is entered, sanitized, stored
and rendered. In v1 the set of types is closed and hardcoded in
`Attribute\AttributeFieldFactory::createFromWpTerm()`:

```php
switch ($type) {
    case 'text':       return new AttributeFieldText(...);
    case 'textarea':   return new AttributeFieldTextArea(...);
    case 'select':
    case 'radio':      return new AttributeFieldSelect(...);
    case 'true_false': return new AttributeFieldTrueFalse(...);
    default:           return null;
}
```

Adding a type means editing that method, adding a class implementing
`Attribute\AttributeField`, and adding a template under
`templates/attribute-field-controls/`. `select` and `radio` share one class and differ
only in presentation.

The v1 `AttributeField` interface has no `sanitize()` at all. Sanitization is a single
`FILTER_SANITIZE_SPECIAL_CHARS` applied to the whole POST array in
`ProductSpecificationsMetabox::action()`, plus one `textarea` special case that restores
newlines.

The type is stored per specification as term meta:

```txt
attr_type      the type id
attr_values    the option list, for select and radio
attr_default   the default value
```

v2 renders the admin in React, so a type now needs a client half as well as a server
half. v2 also adds types that v1's shape cannot express: a number with a unit, and a
value read from a WooCommerce product field that must never be stored.

The hardcoded strings were replaced with a native backed enum,
`Specification\SpecificationType`. An enum is a closed set by construction, so it
cannot survive a registry that third parties extend.

## Decision

A type is an object registered in a registry under a string id. Core registers its own
types the same way a third party does, through the same public mechanism.

### What a type declares on the server

```php
interface SpecificationType
{
    public function id(): string;

    public function label(): string;

    /** @return array<string, mixed> */
    public function optionsSchema(): array;

    public function sanitize(mixed $input): mixed;

    public function value(WC_Product $product, mixed $stored): mixed;

    public function plainValue(mixed $value, SpecificationOptions $options): string;

    public function render(mixed $value, SpecificationOptions $options): string;
}
```

| Method | Used by |
| --- | --- |
| `id()`, `label()` | Everything. The id is what `attr_type` already stores |
| `optionsSchema()` | The REST endpoint, to validate a specification's configuration payload |
| `sanitize()` | The save lifecycle, on the way into the value map. Returning `null` means an empty value |
| `value()` | Every read. Returns the value to present. The default implementation returns `$stored` |
| `plainValue()` | Anything that cannot take markup: exports, structured data, search. Unescaped text, such as "Yes" for a yes-or-no value shown as an icon. Added 2026-11-08 (PS-098) |
| `render()` | Frontend rendering, to format a value. It does not emit markup around the value |

### What a type declares on the client

A matching registry keyed by the same ids, registered through `@wordpress/hooks` so a
third-party script needs no build step against this plugin:

```js
addFilter(
    'product_specifications.types',
    'my-plugin/rating',
    ( types ) => ( {
        ...types,
        rating: {
            options: RatingOptionsPanel,  // configures the specification
            edit:    RatingField,          // enters a value on a product
            format:  formatRating,         // displays a value in the admin
        },
    } )
);
```

The two halves are separate registries keyed by a shared id rather than one declaration
crossing the PHP/JS boundary. A type that only renders on the frontend needs no client
half, and a type that ships its own script needs no PHP change to be editable.

### A type builds its own configuration form

Most types need configuration: `select` takes a list of choices, a unit type takes a
list of units and a default among them, a WooCommerce detail type takes the product
field to read. The type's `options` component renders that form. Core builds no generic
form from the schema.

`optionsSchema()` therefore describes validity, not presentation. It is what the REST
endpoint validates the configuration payload against, in the same JSON schema
vocabulary `StructureMetaDefinition` already uses. It is not read by the editor.

This is the deliberate trade. A generic schema-driven form builder would let a
PHP-only plugin ship a fully editable type, at the cost of inventing a UI vocabulary
that JSON Schema does not have — "a sortable list of strings with an add button", "a
choice whose options come from the field above it". Two explicit places to edit per
type is more work and less cleverness, and it is obvious to read.

### There is no derived flag

A specification whose value comes from somewhere else — the product's weight, its SKU —
is not a special kind of type. It is a type that draws no input and computes its read:

```php
final class WeightType implements SpecificationType
{
    public function value(WC_Product $product, mixed $stored): mixed
    {
        return $product->get_weight();
    }
}
```

Two ordinary behaviors replace a flag and a second method:

| Question | Answered by |
| --- | --- |
| Where does the value come from? | `value()`, which the reader calls for every type |
| Does the editor draw an input? | The client registry has no `edit` component for this type |

Nothing is stored because nothing is ever submitted. With no `edit` component the
specification's key never appears in the payload, so `sanitize()` is never called for
it. `sanitize()` is not how a type declines to store, and `null` returned from it means
an empty value — see ADR 0006, where a stored `null` on a variation means that variation
has no value.

A value posted for such a specification through a hand-crafted REST call would be
stored and then ignored, because `value()` does not read `$stored`. That is the same
inert orphan ADR 0005 keeps on purpose, so it needs no guard.

`isDerived()` and `resolve()` are not part of the interface. Every type implementing a
`resolve()` it never uses, so that two types can use it, is the kind of dead method the
project's ask-don't-tell rule exists to avoid.

This matches ADR 0002, which resolves an absent key against the specification's type
rather than against a marker in the value map.

### Registration on the server

An action carrying the registry, not a filter over an array:

```php
add_action(
    'product_specifications_register_types',
    static function (SpecificationTypeRegistry $registry): void {
        $registry->register(new RatingType());
    }
);
```

The registry is told what to add rather than handing out an array to be rebuilt. This
follows the ask-don't-tell rule the project already applies to the structure domain
objects, and it means a third party never has to understand the registry's internal
shape.

### The type formats the value, the template owns the markup

`render()` returns the presentable value. The template owns the row, the cell and
the table around it, and stays theme-overridable, which is what PS-054 needs to have
anything left to override.

In v1 this boundary does not exist: `templates/shortcodes/specifications-table.php`
branches on the `'yes'` and `'no'` literals and emits the check and cross SVGs inline.
Under this decision the `true_false` type returns that markup from `render()` and the
template emits it into a cell it owns. The v1 `dwspecs_table_value_output` filter maps
onto the formatted value and keeps working.

### The v1 type ids do not change

`text`, `textarea`, `select` and `true_false` keep their ids, so `attr_type` needs no
mapping table and the registry itself causes no data migration.

`radio` is the exception, and it is a merge rather than a rename. It and `select` store
the same thing — one string from a configured list — and differ only in the control that
collects it. They become one type, `select`, labeled **Single select**. That id is the one
that survives because export files carry it and the importer writes `attr_type` from the
file, so a new id would need mapping for as long as any such file exists.

A specification still stored as `radio` falls back to text until it is mapped to `select`.
The mapping belongs to the migration, which normalizes the type meta anyway, and to the
importer for a file written before the merge.

### An unknown type id falls back to text, without writing

The registry returns the text type for an id it does not recognize, so a specification
whose type was provided by a deactivated plugin still renders and still appears in the
editor rather than disappearing.

The fallback does not sanitize what it does not understand. A stored value that is not
a scalar is passed through untouched on save. A plain string behaves exactly as text.
Without that guard the text sanitizer would stringify a composite value — `["6.1", "in"]`
from a unit type — the first time the merchant saved the product, and reactivating the
plugin would find the data gone. ADR 0002 promises that a value the current type cannot
read survives so that restoring the type restores the value; this is what keeps that
promise.

### `SpecificationType` is replaced

The enum is removed when the registry lands. Its `fromValue()` fallback-to-text
behavior moves to the registry.

`SpecificationOptions` is currently a `list<string>`, which fits only the choice type. It becomes an arbitrary map validated against the type's `optionsSchema()`,
so a unit type can carry `{ units: [...], default_unit: "mm" }`.

## Rationale

The closed switch is the reason every new type touches core. Replacing it with a
registry moves the cost of a new type from "edit four files in core" to "register an
object and register a component".

Not building a generic form system is the other half of that. The registry's job is to
make a type addable; it is not to make the admin able to draw a form it has never seen.
Conflating the two is what turned this ADR's first draft into a schema-driven form
builder disguised as a footnote.

Removing the derived flag follows from ADR 0002. Absence in the value map is already
resolved against the type, so the type only needs to behave — decline the write,
compute the read — not to announce a category it belongs to.

## Consequences

### Positive

- A new type is a registration, in this plugin or another.
- Derived types are expressible with no storage concept and no flag.
- v1 types migrate with no value change.
- The type interface has one read path, `value()`, used identically by every type.
- `optionsSchema()` stays small, because it only has to describe validity.

### Negative

- **A third-party type needs a JS build to be configurable.** A PHP-only plugin can
  register a type that stores and renders, but not one a merchant can set up in the
  editor. This is the accepted cost of not building a generic form system.
- Two registries must be kept in step by convention, since nothing enforces that a PHP
  id has a client counterpart. The editor renders an explicit "no editor registered for
  type `rating`" placeholder rather than a blank space, so the failure is visible.
- The same convention now covers derived-ness. A type that registers an `edit`
  component and also declines in `sanitize()` shows an input that silently discards
  what is typed into it. It is a developer error, and it surfaces on the first test.
- A type provided by a plugin that is later deactivated leaves specifications rendering
  as text with their configuration inert until the plugin returns.

## Alternatives Considered

### Keep the enum and extend it per release

Rejected. It makes every new type a core release, and third parties cannot add one at
all, which is the stated goal.

### A filter returning an array of type definitions

Rejected. It hands the internal shape to every consumer, invites array surgery, and
gives no type safety. An action carrying the registry gives both.

### A generic schema-driven configuration form

Rejected. It is the only option under which a PHP-only plugin ships a fully editable
type, and it is by a distance the largest build in the epic: JSON Schema describes
validity and has no vocabulary for presentation, so it would need a second UI schema
beside it. Two explicit halves per type are more work per type and far less machinery
overall.

### `isDerived()` and `resolve()` on the interface

Rejected. It puts a method on every type so that two types can use it, and it asks the
reader to branch on a category when it could just call one method. Declining in
`sanitize()` and computing in `value()` expresses the same thing with no additions.

### No fallback for an unknown type id

Rejected. Deactivating a plugin would empty rows from live product tables with no
indication why.

### One declaration shared between PHP and JS, generated from the server

Rejected. It couples the two halves, forces a PHP round trip for a purely client
concern, and makes a frontend-only type carry an editor it does not need.

### Types as classes discovered by convention, without registration

Rejected. Implicit discovery is hard to debug, hard to order, and gives a third party
no way to replace a core type.

## Final Decision

```txt
SpecificationTypeRegistry               server-side, keyed by string id
product_specifications_register_types   the action that carries it
product_specifications.types            the client filter, same ids
```

A type declares its id, label, options schema, sanitizer, value reader and renderer,
and registers an options panel, an edit field and a formatter on the client. Core types
are registered through the same mechanism third parties use. There is no derived flag.
The five v1 type ids are preserved.
