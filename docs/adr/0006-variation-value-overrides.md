# ADR: Variation Value Overrides

## Status

Approved

## Context

A variable product sells one thing in several configurations. A laptop offered with
16GB and 32GB of RAM has one specification table today, and it can only be right for one
of them.

v1 does not support this at all, and the reason is one line.
`ProductSpecifications\ProductSpecificationsMetabox::enabled()` returns
`$post->post_type === 'product'`. Variations are `product_variation` posts, so they never
receive the metabox, never hold values, and nothing on the frontend looks for any —
`Shortcode\SpecificationsTable::render()` resolves `get_the_ID()`, which on a product
page is the parent.

Magento solves this by copying a full value set onto every child, which does not scale
and makes a parent edit meaningless. WooCommerce core solves the same problem for weight
and dimensions with empty-means-inherit, which scales but cannot express "this variation
has none."

ADR 0002 settled the storage shape and deferred exactly one question to this ADR:

> The one question this ADR does not answer is how a variation overrides a parent value
> *to empty*, since removing the key is what inheritance means.

## Decision

### A variation stores a sparse document in the same shape

Post meta on the `product_variation` post, the same key, the same versioned document:

```txt
product_specifications_values
```

Only what the variation overrides is stored. Nothing is copied from the parent.

### Three states, because a sparse map natively has two

| In the variation's `values` | Means |
| --- | --- |
| key absent | Inherit the parent's value |
| key present, with a value | Override with this value |
| key present, `null` | This variation has no value for this specification |

The third state is what ADR 0002 left open. A refurbished laptop with no warranty cannot
simply omit the key, because omitting means inherit and the parent says "2 years".

`null` is the token because a stored value is not always a string. A `true_false`
specification stores a boolean and a multi-select stores an array; an empty string
appearing in either is a type mismatch every sanitizer and reader would have to
special-case. `null` means "no value" identically for all of them.

A `null` override is an empty value like any other. Whether the row is hidden or shown
with a placeholder is ADR 0004's `empty_rows` setting for that set. This ADR introduces
no rendering rule.

### `null` means one thing everywhere

`null` is an empty value, resolved against context exactly as an absent key is:

| Context | `null` means | Result |
| --- | --- | --- |
| Returned by `sanitize()` on a product | The field was cleared | The key is removed |
| Returned by `sanitize()` on a variation | The override was cleared | `null` is stored |
| Stored on a variation | This variation has no value | Renders empty |

A specification whose value comes from elsewhere is not part of this at all. It stores
nothing because its client `edit` component is read-only, so its key never reaches
the payload and `sanitize()` is never called. Nothing about `null` identifies such a
type; `value()` ignoring `$stored` does.

### Merging uses the union operator, not `array_merge()`

```php
$effective = $variationValues + $parentValues;
```

The union operator keeps the left operand's keys and fills the rest from the right, so
the variation wins and the parent supplies everything it did not override.

`array_merge()` must not be used here. It renumbers **integer** keys rather than
overwriting them, so a numerically-keyed specification would append instead of override
and inheritance would break silently. ADR 0002 already requires the migration to suffix
any preserved v1 slug that is a bare number — a rule written for a `json_decode` key-cast
hazard, which turns out to be load-bearing here too.

Readers use `array_key_exists()`, never `isset()`. `isset()` returns false for a stored
`null`, which would collapse the override-to-empty state back into inheritance.

### Every specification can be overridden

There is no `variable` flag on the structure's set item. A merchant does not decide at
set-definition time which specifications may vary.

The scale problem this creates — a forty-row set on a product with twenty variations —
is solved in the interface rather than in the schema. The editor renders the parent's
table grayed out per variation, and the merchant clicks a row to override it and edits in
place. Overriding is opt-in per row per variation, at the moment of editing, and the
sparse document is the natural record of what was clicked.

### The set assignment stays on the parent

`product_specifications_set` never appears on a variation. A variation inherits its
parent's set and cannot choose another, so the structure is the same for every variation
of a product and only the values differ.

### Products keep their rule

On a product, clearing a value still removes its key. The asymmetry with variations is
semantic rather than stylistic: on a product an absent key already means "no value", so
removing it is unambiguous. On a variation an absent key means inherit, so empty needs a
token of its own.

## Rationale

The sparse document is the whole decision. It gives variation support with no new
storage concept, no migration, and no cost for the majority of variations that override
nothing.

`null` as the third state costs one convention — `array_key_exists()` over `isset()` —
and buys the one case the WooCommerce convention cannot express. That case is real: a
Yes/No specification that does not apply to one variant has no textual workaround.

Keeping the schema free of a `variable` flag means the decision about which
specifications vary is made where it is actually known — while editing a variation, not
while composing a set months earlier.

## Consequences

### Positive

- Variation support with no new storage concept and no migration.
- A variation that overrides nothing stores nothing.
- A parent edit propagates to every variation that has not overridden that row.
- The structure schema is unchanged.
- Derived specifications need nothing: `WC_Product_Variation::get_weight()` already
  falls back to the parent, so WooCommerce's own inheritance applies (ADR 0005).

### Negative

- `isset()` on a value map is now a bug. Every reader must use `array_key_exists()`, and
  nothing in the language enforces it.
- Two documents must be read to render one variation's table, and three counting the set
  structure.
- Storage permits overriding any specification, so the override interface is the only thing
  standing between a merchant and eight hundred input fields. If that interface is not
  built well, the feature is unusable.
- A `null` stored against a specification later changed to a type that cannot represent
  emptiness is inert, like any other orphan ADR 0002 keeps.

## Constraints recorded for later work

- **Variation-aware frontend table.** The table renders once, server-side, for
  the parent. Showing variation-specific values means either rendering every variation's
  table into the DOM and switching on WooCommerce's `found_variation` event, or fetching
  on variation change. This ADR forces neither. Rendering all of them costs N variations
  × M rows in the page.
- **Variation override editing.** The grayed-out inherited table with per-row
  opt-in is the agreed interaction, and the storage supports it with no schema flag.
- A variation may hold a key its parent lacks; the merge handles it and it renders.
- A variation key naming a specification not in the assigned set is ignored, exactly as
  on a product.

## Alternatives Considered

### Copy the full value set to every variation

Rejected. It is Magento's model. It multiplies stored data by the variation count, and
it makes editing the parent meaningless because nothing inherits.

### Empty means inherit, with no third state

Rejected. It is what WooCommerce core does for weight and dimensions, and it is the
simplest possible model. It cannot express a Yes/No specification that does not apply to
one variant, and the textual workaround — typing "None" — has no equivalent for a
boolean or an array.

### An empty string as the empty token

Rejected. It keeps `isset()` safe, which is its only advantage, and the inherit check
requires `array_key_exists()` regardless. It puts a string into boolean and array
specifications, which every sanitizer and every reader would have to special-case.

### A separate list of cleared keys

Rejected. `{ "values": {…}, "cleared": […] }` is self-documenting, and it creates two
structures that can disagree. A key present in both has no defined meaning, and nothing
prevents it.

### A `variable` flag on the structure's set item

Rejected. It moves a decision to set-composition time that is only knowable at
variation-editing time, adds a schema member, and asks every merchant to answer a
question about every specification in order to solve an interface problem.

## Final Decision

```txt
product_specifications_values   on the product_variation post, sparse
```

Key absent inherits. A value overrides. `null` means this variation has no value.
Merging is `$variation + $parent`. Readers use `array_key_exists()`. Every specification
is overridable, and the interface, not the schema, keeps that manageable.
