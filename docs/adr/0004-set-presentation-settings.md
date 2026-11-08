# ADR: Set Presentation Settings

## Status

Approved

## Context

A Specification Set decides two separate things: which specifications appear and how
they are grouped and ordered, and how that composition is presented. ADR 0001 settled
the first, in one versioned post meta document:

```txt
specification_set_structure
```

Nothing settles the second. v1 has no per-set presentation options at all — its only
per-set option is `_groups`, an ordered list of `spec-group` term ids, which ADR 0001
already replaced with the structure.

v1 does have presentation settings, but they are global, in `Settings\SettingsRegistrar`:

| Option | Decides |
| --- | --- |
| `dwps_tab_title` | The WooCommerce product tab's title |
| `dwps_wc_default_specs` | Whether Woo's "Additional information" tab is removed |
| `dwps_disable_default_styles` | Whether the plugin's stylesheet is enqueued |

These are plugin integration behavior — how the plugin sits inside WooCommerce and the
theme. They are not about how a set presents, and they stay where they are.

Two v1 rendering behaviors are hardcoded, and both become choices in v2.

**Empty rows are always hidden.** Not by a rendering rule, but as a side effect of
storage: `ProductSpecificationsMetabox.php:145` discards empty values at save, so the
row never exists to render.

**Empty groups are always hidden, by a different mechanism.**
`Shortcode\SpecificationsTable::groupsWithSpecifications()` filters out any group with
zero attributes before rendering. This is a second, independent behavior, and it is the
one that stops a set from rendering a heading over nothing.

## Decision

Presentation lives in a second versioned post meta document on the Specification Set,
beside the structure rather than inside it:

```txt
specification_set_presentation
```

```json
{
  "schema_version": 1,
  "empty_rows": "hide",
  "empty_groups": "hide"
}
```

### Every member defaults to the v1 behavior

| Member | Default | Reproduces |
| --- | --- | --- |
| `empty_rows` | `"hide"` | v1 discarding empty values at save |
| `empty_groups` | `"hide"` | `groupsWithSpecifications()` dropping empty groups |

A set migrated from v1 renders exactly as it did before, without the migration having
to inspect anything. The migration writes this document with its defaults and nothing
else.

### Rows and groups are separate members

v1 hides both, by two unrelated mechanisms, and they come apart the moment `empty_rows`
can be `"show"`. Showing every row means a group with no values at all renders as a
heading over a table of blanks, which v1 never did and no merchant asked for. One
member cannot express "show the blanks, but drop a group that is entirely empty", which
is the case stores actually want.

`empty_rows` is `"hide"` or `"show"`. `"show"` renders a placeholder rather than an
empty cell, filterable, defaulting to an em dash. A genuinely blank `<td>` reads as a
rendering bug, so "show, but empty" is not a state worth offering.

### There is no global default for a presentation setting

The v1 globals are a different category: whether the plugin's stylesheet loads is a
page-level fact, and whether Woo's tab is removed is a product-level fact. Neither is
about a set. No setting exists at two levels, so there is no global-to-set precedence
rule to write or to explain.

## Document versioning

`specification_set_presentation` carries a `schema_version`, on the policy stated in
[ADR 0009](0009-stored-document-versioning.md), which was settled while writing this ADR
and moved out because it governs every stored document rather than this one.

## Rationale

Composition and presentation change for different reasons and on different schedules.
The structure is what the migration writes and what the set editor manipulates.
Presentation is what new display options extend, after the migration has shipped.

Keeping them apart means a new presentation option never moves the structure's schema
version, never revalidates a structure write path, and never touches the migration. The
cost is one more meta key on one post type, which the client reads through the same
entity record it already uses for the structure.

Putting presentation inside the structure would couple every future presentation
option to the document the migration depends on being stable — which is exactly the
second migration this ADR exists to avoid.

## Consequences

### Positive

- New presentation options arrive as members of this document, with no structure
  schema change and no migration.
- A migrated set renders identically to v1 with no per-set decision required.
- The structure document stays about composition, which keeps its schema small enough
  to validate strictly.
- Row and group emptiness are independently controllable, which is what merchants ask
  for and what v1 accidentally provided.

### Negative

- Two documents to read when rendering a set, and two to write when saving one.
- A store-wide change is one edit per set. There is no global default, by decision.

## Alternatives Considered

### One document holding both structure and presentation

Rejected. One write and one version is genuinely simpler, but it ties every future
presentation change to the document the migration writes, and forces a schema version
bump plus revalidation of every structure write for changes that have nothing to do
with composition.

### Presentation as individual post meta keys, one per setting

Rejected. It gives up versioning, spreads the defaults across the codebase, and turns
"read a set's presentation" into a variable number of lookups.

### A global default with per-set override

Rejected. It needs a three-level precedence rule that has to be explained in the UI and
implemented in every reader. The v1 globals are not the same kind of setting and do not
establish a precedent for it.

### Presentation in the plugin settings, global only

Rejected. The whole point of a set is that different product ranges present
differently. A global setting cannot express that.

### One `empty_rows` member covering groups as well

Rejected. It cannot express "show the blank rows, drop the dead group", and v1 already
implements the two behaviors separately.

## Final Decision

```txt
specification_set_presentation   versioned document, beside the structure
```

Members default to the v1 behavior, so a migrated set is unchanged. Rows and groups
have independent emptiness settings. The v1 global settings are a separate category
and are untouched.
