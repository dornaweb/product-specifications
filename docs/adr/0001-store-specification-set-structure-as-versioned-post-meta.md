# ADR: Store Specification Set Structure as Versioned Post Meta

## Status

Approved

## Context

The Product Specifications plugin uses **Specification Sets** to define the structure of product specification tables.

A Specification Set controls:

- which specifications are included
- how specifications are grouped
- the order of groups
- the order of specifications inside each group

Specification Sets are stored as a custom post type (same as legacy "specifications tables" for backward compatibility):

```txt
post_type: specs-table
```

Specifications are reusable definitions stored as WordPress terms. A specification term owns the canonical definition of that specification, such as label, type, value source, unit configuration, validation, and display behavior.

Groups are only presentational. They are scoped to a Specification Set and are not stored on products.

Products only store specification values.

## Decision

Each Specification Set will store its structure in a single post meta field:

```txt
specification_set_structure
```

The `specification_set_structure` meta value will be a versioned JSON document.

The structure stores:

- `schema_version`
- groups
- group order
- specification set items
- specification order inside each group

The structure does not duplicate full specification definitions.

Each specification set item stores:

- `term_id` for runtime lookup
- `slug` for portability, import/export, and repair
- `visible` for set-specific display control
- `comparable` for set-specific comparison control

Example:

```json
{
  "schema_version": 1,
  "groups": [
    {
      "key": "display",
      "label": "Display",
      "description": "",
      "items": [
        {
          "term_id": 123,
          "slug": "screen_size",
          "visible": true,
          "comparable": true
        },
        {
          "term_id": 124,
          "slug": "resolution",
          "visible": true,
          "comparable": true
        }
      ]
    },
    {
      "key": "battery",
      "label": "Battery",
      "description": "",
      "items": [
        {
          "term_id": 201,
          "slug": "battery_capacity",
          "visible": true,
          "comparable": false
        }
      ]
    }
  ]
}
```

## Rationale

Specification definitions should not be copied into every Specification Set.

If definitions were duplicated inside set structures, updating a specification would require updating every set that contains it.

Instead, the Specification Set stores only layout and composition:

```txt
Which specifications are shown?
In which groups?
In which order?
```

The canonical specification definition remains on the specification term.

Using set item objects instead of bare term IDs is slightly more verbose, but it keeps the structure explicit:

```json
{
  "term_id": 123,
  "slug": "screen_size",
  "visible": true,
  "comparable": true
}
```

This allows set-specific presentation options without duplicating the full specification definition.

The `schema_version` field exists only to version the internal JSON structure. It is not the plugin version or the Specification Set revision.

## Consequences

### Positive

- Specification Sets stay lightweight.
- Specification definitions remain reusable.
- Updating a specification term affects all sets that reference it.
- Group and specification ordering is simple.
- Groups remain presentational and scoped to the set.
- The model uses only WordPress core storage.

### Negative

- Term IDs are not portable across environments.
- Broken references are possible if a specification term is deleted.
- Import/export needs to resolve references by stable key.

## Alternatives Considered

### Store full specification definitions in the set structure

Rejected because it duplicates definitions and creates synchronization problems.

### Store only bare term IDs

Rejected because it is less future-proof and does not include stable keys for portability.

### Store groups as taxonomy terms

Rejected because groups are only presentational and scoped to a single Specification Set.

### Use custom database tables

Rejected. WordPress core storage is sufficient.

## Final Decision

A Specification Set is stored as a custom post type.

Its structure is stored in one post meta field:

```txt
specification_set_structure
```

The structure is a versioned JSON document that stores groups, ordering, and references to specification terms.

Specification definitions live on specification terms.

Product values live on products.

```txt
Specification Set = grouped layout/composition
Specification Term = reusable specification definition
Product Meta = product-specific values
```
