# ADR: Store Product Specification Values as a Versioned Value Map

## Status

Proposed

## Context

A product carries the values for the specifications its assigned Specification Set
composes. v1 stores those values as a denormalized snapshot across two product meta
keys, written by `ProductSpecifications\ProductSpecificationsMetabox::action()`:

```txt
_dwps_table                  the assigned specs-table post id
_dwps_specification_table    an ordered array of group snapshots
```

The snapshot copies every label into the product:

```php
[
    [
        'group_id'   => '12',
        'group_name' => 'Display',
        'group_slug' => 'display',
        'group_desc' => '',
        'attributes' => [
            [
                'attr_id'   => '456',
                'attr_name' => 'Screen size',
                'attr_slug' => 'screen_size',
                'attr_desc' => '',
                'value'     => '6.1"',
            ],
        ],
    ],
]
```

Three properties of that shape matter here.

Labels are copied into every product, so renaming a specification leaves every product
showing the old name until it is saved again.

Empty values are discarded at save time, so a missing entry already means "no value".
There is no stored empty in v1.

`true_false` is stored as the literal strings `'yes'` and `'no'`, and the frontend
template branches on those literals directly.

ADR 0001 settled the set side: composition lives in one versioned post meta document
on the set, and each set item carries `term_id` for runtime lookup beside `slug` for
portability and repair.

## Decision

A product stores its values in one post meta key:

```txt
product_specifications_values
```

The value is a versioned document whose `values` member maps specification term id to
value:

```json
{
  "schema_version": 1,
  "values": {
    "456": "6.1",
    "457": "yes",
    "458": ["usb-c", "hdmi"]
  }
}
```

The assigned Specification Set is stored separately:

```txt
product_specifications_set    the Specification Set post id, an integer
```

### Values are keyed by term id

Runtime lookup resolves a set item to its value by id. Portability is already solved
one level up: the set structure carries the slug beside the id, so a broken reference
is repaired from the structure rather than from every product.

### The document is versioned

`schema_version` mirrors the structure document from ADR 0001, so the plugin has one
versioning pattern rather than two. It versions this document only. It is not the
plugin version and not the plugin data version, which tracks whether a site has
migrated at all.

### A value is stored as its own type

The type's sanitizer decides what a stored value is: a string, an integer, a float, a
boolean, or an array for a composite type such as a number with a unit. Values are not
stringified on the way in. A type that needs structure gets structure without inventing
a second convention, and a numeric specification stays comparable without parsing.

### An absent key is read against its context

Absence carries three different meanings, and each is resolved by something other than
the absence itself:

| Where | Absent key means | Resolved by |
| --- | --- | --- |
| On a product | No value was entered | The product is not a variation |
| On a variation | Inherit from the parent product | The product is a variation |
| For a derived specification | Nothing is ever stored | The specification's type declares itself derived |

The three never overlap. A variation is distinguishable from a product by its post
type, and a derived specification is distinguishable by its type, so no sentinel value
is needed to tell them apart.

Clearing a value removes its key rather than storing an empty string. One way to mean
nothing is enough, and storing empties would grow every product's meta with entries
that render as blank rows.

## Rationale

The map is the single source of truth. Nothing else stores a value, and anything that
needs values arranged differently derives that arrangement and can rebuild it from the
map.

Labels are not copied. A specification's name, description, type and options live on
its term, so renaming one changes every product at once and no product ever holds a
stale label.

Keeping the assigned set in its own meta key keeps it queryable. A member of a
serialized array cannot be reached by `meta_query`, and "which products use this set"
is a question the migration asks and the admin will ask again.

## Consequences

### Positive

- Renaming a specification no longer leaves stale copies on products.
- A product's meta is a fraction of the v1 size: ids and values, no labels.
- One shape serves products and variations, so variation support needs no new storage
  concept.
- The migration has an unambiguous target.

### Negative

- Reading a product's table requires the set structure as well as the value map. The
  value map alone does not describe an order or a grouping.
- Term ids are not portable across environments, so an export must resolve them
  through the set structure's slugs.
- A value left behind when a specification changes type becomes an orphan. Cleanup
  removes orphans; they are inert until then.

## Alternatives Considered

### Keep the v1 denormalized snapshot

Rejected. It is the cause of stale labels, it grows without bound, and every rename
requires touching every product.

### Key the map by slug instead of term id

Rejected. Renaming a slug would orphan every value, and runtime lookup would cost a
term query per row. The structure already carries slugs for the portability case.

### One meta key per specification, `_spec_456`

Rejected. A product with forty specifications would carry forty meta rows, and reading
a table would be forty lookups or one query joined by a `LIKE` on the key.

### A custom database table

Rejected, consistent with ADR 0001. Core storage is sufficient, and a custom table
would need its own migration, its own backup story, and its own multisite handling.

### A bare map with no version envelope

Rejected. The structure document is versioned, and having one versioned document and
one unversioned one in the same plugin is a trap for whoever writes the next migration.

### Store an explicit empty rather than removing the key

Rejected for products. It creates two ways to mean nothing and grows meta with blank
rows. This has one open consequence, recorded below.

## Consequences for the ADRs that follow

- **Derived values.** A derived specification has no key in the map. This ADR
  makes that unambiguous by resolving absence against the specification's type.
- **Variation overrides.** A variation stores a sparse document in this same
  shape, and an absent key inherits. The one question this ADR does not answer is how
  a variation overrides a parent value *to empty*, since removing the key is what
  inheritance means. The variation work must decide that, and it is the only storage
  question left open here.
- **Defaults.** A default is written into the map the first time the editor
  opens the field, so it becomes an ordinary stored value with no special marker.
- **Save lifecycle.** Sanitize, write the map, run the type's save action,
  fire the public hooks. Anything else written is a projection and is rebuildable from
  the map.

## Migration target

The migration reads the v1 keys and writes the new ones:

| v1 | v2 |
| --- | --- |
| `_dwps_table` | `product_specifications_set` |
| `_dwps_specification_table[].attributes[].attr_id` | a key in `values` |
| `_dwps_specification_table[].attributes[].value` | that key's value |
| `group_*` and `attr_name`, `attr_slug`, `attr_desc` | dropped; they live on the term and in the set structure |

Two details the migration must not lose:

- `true_false` values are the strings `'yes'` and `'no'`. They convert to booleans, and
  the renderer stops branching on the literals.
- v1 already discarded empty values, so an absent v1 entry migrates to an absent key,
  which means the same thing.

## Final Decision

```txt
product_specifications_values   versioned document, term id => value
product_specifications_set      the assigned Specification Set post id
```

The map is the single source of truth. Labels live on the specification term.
Order and grouping live in the set structure. Anything else is a projection.
