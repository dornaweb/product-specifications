# ADR: Specification Definition and Identity

## Status

Approved. Amended 2026-11-13: the slug is chosen at creation instead of generated with a
random suffix, and deleting a specification purges its values from every product.

## Context

A specification is the reusable definition that a set composes and a product carries a
value for. ADR 0001 settled sets, ADR 0002 settled values, ADR 0003 settled types —
nothing settled the definition itself.

v1 stores it as a term in the `spec-attr` taxonomy, registered `public: false`,
`show_ui: false`, and with **no `show_in_rest`**, so specifications are unreachable by
the REST API entirely. The term carries its name, description and a user-editable slug —
`Specification\SpecificationTaxonomy`, `EntityUpdater\AttributeController`, and
`templates/admin/entity-management/edit-attribute-form.php`, where the slug field is
literally marked `placeholder="Optional"`.

Four term meta keys hang off it:

```txt
attr_type       the type id
attr_values     the option list, for select and radio
attr_default    the default value
attr_group      the one group it belongs to — required
```

`attr_group` is required. `AttributeController::validate()` throws *"Please select a
group"* when it is empty.

### v1 composition is spread across three places

A specification does not name the sets it belongs to. It is in a set *indirectly*:

| Meta | On | Holds |
| --- | --- | --- |
| `_groups` | the `specs-table` post | ordered group term ids |
| `attributes` | the `spec-group` term | ordered specification term ids |
| `attr_group` | the `spec-attr` term | the one group it belongs to |

`Repository\AttributeFieldRepository::findGroupedCollection()` walks exactly that: read
`_groups`, and for each group collect every specification whose `attr_group` matches,
sorted by the group term's `attributes` list.

Three consequences follow from that shape, and all three are limitations:

- A specification belongs to exactly **one** group, in every set at once. It cannot be
  "Display" in one table and "Screen" in another.
- Ordering lives on the **group**, so every set using that group gets the same order.
- Adding a specification to a group adds it to **every set** containing that group,
  silently.

ADR 0001 collapses all three into one structure document per set, which turns each
limitation into a capability.

## Decision

### A specification is a `spec-attr` term

This is forced, not chosen. `CLAUDE.md` fixes it: *"Storage keys (`spec-attr`,
`spec-group`, `specs-table`, meta keys) never change, whatever the symbols around them
are renamed to."* Moving specifications to a custom post type would be a migration of
every specification, every value key and every set structure, for no user-visible gain.

The costs are real and are recorded rather than argued away:

- Terms have no revisions, so a specification definition cannot be rolled back.
- Term meta is not exposed to REST by default and has to be registered or projected.
- Term slugs are unique across `wp_terms`, not per taxonomy, so a specification and a
  group can share a slug — harmless here, because a specification slug is only ever
  resolved within `spec-attr`.
- A term has no intrinsic order, which v1 works around with an ordering list on the
  group. v2 does not need the workaround: order is set-scoped.

### The slug is the identity, it is chosen once, and it is frozen

This rule is stated here because it belongs to the definition. ADR 0002 depends on it and
points at it.

```txt
sanitize_title( the key the merchant typed, or the name )
screen-size
```

Chosen at creation, never editable afterward, unique within `spec-attr`, and the only
stored identity in the model. The value map is keyed by it (ADR 0002), the set structure
references it (ADR 0001), and no term id is stored anywhere. The editor calls it the
**key**; REST, the export package and WordPress itself call the same string the slug.

The create form offers a key derived from the name and follows the name until the merchant
edits it. A key another specification already holds is refused before the save, naming
that specification and offering the next free numeric form, `screen-size-2`. The server
enforces the same rule for every client: an explicit slug that collides is refused with a
conflict carrying the suggestion, and a request that sends no slug receives the derived
key with a numeric suffix only when the plain one is taken. A key that is a canonical
integer string, such as `2024`, is refused, because PHP casts such strings to integer
array keys and that breaks both `json_decode` round trips and the variation merge in
ADR 0006.

Uniqueness within the taxonomy is WordPress's own guarantee. The plugin adds only two
things: it turns core's silent renaming on collision into an explicit answer, and it never
lets a slug change after the first save, neither through REST nor through
`wp_update_term()`.

**The name is freely editable and the slug never follows it.** Renaming `Screen size` to
`Display size` changes every product's rendered label at once and touches no stored key.

**The key is shown in the editor**, editable before the first save and read-only after it.
A developer writing a template, reading an export or debugging a value map can find it
without a database query, and a merchant can see that it is not something to change.

**Deleting a specification removes its values from every product.** A freed key may be
chosen again, so nothing may be left for a later specification to adopt. Small purges run
inside the deletion; larger ones continue in the background, and until one finishes its
key counts as taken. ADR 0002 and ADR 0008 record the consequences.

Non-Latin keys are allowed. WordPress stores every term slug percent-encoded, so such a
key is legible in the editor and the list, which decode it, and opaque in code and files,
which carry the stored form. A merchant who wants a key that reads well in both chooses a
Latin one.

Slugs preserved from v1 keep their v1 form, because v1 exposes
`dwspecs_attr_value_by( $postId, 'slug', … )` as a public lookup and rewriting them would
break any theme calling it. A preserved slug that is a canonical integer string gets a
numeric suffix, `2024-2`, for the reason above.

### A specification belongs to no group

`attr_group` is dropped. Grouping is set-scoped and lives in the structure document
(ADR 0001), so one specification can sit in different groups, in different positions, in
different sets.

The migration reads `attr_group` to build structures and then never reads it again;
the cleanup step removes it with the rest of the v1 data.

A specification also carries no intrinsic order. The `attributes` ordering list on the
group term goes the same way.

### Term meta after this decision

```txt
attr_type       unchanged
attr_values     unchanged as a key; its shape becomes an arbitrary map per ADR 0003
attr_default    unchanged, and it is a prefill only, per ADR 0007
```

### REST: core endpoints, one computed field

`spec-attr` gains `show_in_rest`, and one computed field carries the definition:

```php
register_rest_field( 'spec-attr', 'specification', [
    'get_callback' => …,   // { type, options, default, usage_count }
    'schema'       => …,
] );
```

```json
{
  "id": 456,
  "name": "Screen size",
  "slug": "screen-size",
  "description": "",
  "specification": {
    "type": "select",
    "options": { "values": ["5.5\"", "6.1\"", "6.7\""] },
    "default": null,
    "usage_count": 412
  }
}
```

Four admin screens consume this and nothing on the frontend does, because the shortcode
and the product tab render server-side in PHP: the specifications list, the
specification editor, the set editor's picker and inline creation, and the product value
panel, which needs each specification's type and options to draw the right input.

Two properties matter more than the shape:

**The term meta keys are not registered for REST.** `attr_type`, `attr_values` and
`attr_default` stay internal storage. What is public is the curated `specification`
object, which can change shape without the storage changing and vice versa. Registering
the raw meta would make v1's key names a public API forever.

**The field carries the definition only, never a product's value.** The response is
identical for every product, so it caches, and the value panel fetches specifications
once and reads values from the product's own payload, keyed by slug. Merging a product's
values into a term response would put product data on a taxonomy endpoint and make it
vary per product.

Core's term controller supplies list, pagination, search and `_fields` for free, and
`@wordpress/core-data` treats `spec-attr` as an entity — `getEntityRecords( 'taxonomy', 'spec-attr' )` —
so no custom client data store is needed.

Two things to verify by hand rather than assume: `spec-attr` is `public: false`, so
WordPress's own permission rules for a non-public taxonomy apply once `show_in_rest` is
on, and they must line up with the plugin's capability model. And `usage_count`
is the expensive member of that payload — `_fields` trims the response but does not stop
the callback running, so it needs its own guard.

## Rationale

The slug rule is the load-bearing decision in the whole storage model, and it was living
inside the ADR about *product values* because that is where it was first needed. Anyone
asking how a specification is identified would not look there. It belongs to the
definition, which is what this ADR is.

Dropping `attr_group` is what makes ADR 0001's structure the single description of
composition. Leaving it, even unused, would leave two places that answer "which group is
this in" and guarantee that they eventually disagree.

Exposing a curated field rather than raw term meta keeps v1's storage key names out of
the public API. `attr_type` is a name the plugin is stuck with in the database; it does
not have to be a name third parties are stuck with in their integrations.

## Consequences

### Positive

- One document owns how a specification is identified.
- A specification can appear in different groups, in different orders, in different sets.
- Renaming a specification is free and affects nothing stored.
- The public REST shape is decoupled from the storage keys.
- Core's term endpoints supply list, search and pagination without a custom controller.

### Negative

- A key is frozen once saved, so a typo in it is corrected only by deleting the
  specification and creating it again, which re-enters its values. Renaming a key in place
  would rewrite every product holding it, the same loop the purge runs, and is deferred
  until a merchant asks for it.
- Deleting a specification rewrites every product holding its value. Below a threshold it
  happens inside the deletion; above it the work continues in the background and the key
  is unavailable until it finishes.
- Specifications have no revisions, because terms do not.
- `usage_count` on a list of specifications is a query per specification unless it is
  batched or guarded.
- Two things a merchant could do in v1 are gone: editing a slug after creation, and
  setting a specification's group once for every set at once.

## Alternatives Considered

### A custom post type instead of a taxonomy

Rejected, and not really available. It would give revisions, first-class REST and post
meta, and it would break the storage-key rule in `CLAUDE.md`, requiring a migration of
every specification, every value key and every set structure for no user-visible gain.

### Keep `attr_group` as an optional default group

Rejected. It saves clicks when composing a set, and it reintroduces the link between
definition and composition that ADR 0001 spent a decision removing. Two places answering
the same question is one too many.

### Register the term meta keys for REST directly

Rejected. It is less code than a computed field, and it makes `attr_type`, `attr_values`
and `attr_default` a public API — v1 key names that the plugin is stuck with in storage
but need not be stuck with in its contract.

### Custom REST routes for specifications

Rejected. Full control of the shape and one payload per request, at the cost of
reimplementing list, pagination, search and permissions that core's term controller
already provides, and a second API shape beside the core post meta already used for sets.

### Merge a product's values into the specification response

Rejected. It saves the value panel one request, and it puts product data on a taxonomy
endpoint, makes the response vary per product, and destroys its cacheability.

### Hide the slug entirely in the editor

Rejected. A merchant never needs it, but a developer writing a template or reading an
export does, and a read-only field is cheaper than a database query. The list screen shows
it under the name for the same reason, so the editor field is the second place it can be
read rather than the only one.

### Generate the slug with a random suffix

Rejected on 2026-11-13, after being the decision from 2026-08-30 until then. The slug was
`sanitize_title( name ) + '-' + 8 hex characters`, generated on save and never shown
before it. It made every key a developer, an export file or a CSV column has to name look
like `weight-c3dd218b`, which nobody types from memory, and it made the public API read
as if it were keyed by ids. The two jobs the suffix did are done elsewhere: uniqueness
within the taxonomy is WordPress's own rule, and never landing a new specification on a
deleted one's leftover values is the purge's job. The suffix was never released, so no
stored slug changes.

### A readable key in term meta beside an opaque slug

Rejected. One generated slug as the stored identity and a second, editable, unique key
resolved to it at the API and import boundaries is how a field framework separates an
internal id from a developer-facing name. It needs no purge and lets a key be renamed for
free. It also puts two strings on every specification, one of them hidden and leaking into
REST responses, the database, exports and `wp term list`, and it enforces uniqueness in
code rather than through the taxonomy. The right design when identity must survive
renames at zero cost; for this plugin's size and audience, not worth a second concept.

## Final Decision

```txt
spec-attr term          name, description, frozen slug
attr_type               the type id
attr_values             the type's configuration
attr_default            the editor prefill
```

`attr_group` is dropped; grouping and ordering are set-scoped. The slug is chosen once,
from the name unless the merchant changes it, is unique within the taxonomy, is refused
with a suggestion when taken, is never editable afterward, and is the only identity the
model stores. Deleting a specification purges its values from every product.
Specifications reach the client through core's term endpoints plus one computed
`specification` field carrying the definition and nothing else.
