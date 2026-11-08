# ADR: Value Save Lifecycle and Hooks

## Status

Approved

## Context

Saving a product's specification values is where every other decision in this epic meets:
the type registry sanitizes, the value map is written, variations resolve differently
from products, and anything that needs values arranged differently has to learn that they
changed.

v1 does all of it in one method, `ProductSpecifications\ProductSpecificationsMetabox::action()`:

1. Read `specs_table` from `$_POST`. If absent, delete `_dwps_specification_table` and
   `_dwps_table`, and return.
2. `update_post_meta( '_dwps_table', $tableId )`.
3. `filter_input( INPUT_POST, 'dw-attr', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_REQUIRE_ARRAY )`
   — one blanket sanitizer for every value of every type.
4. Build the denormalized snapshot, resolving each group and attribute term, skipping
   empty values and the magic string `dwspecs_chk_none`, and special-casing textarea
   newlines.
5. `update_post_meta( '_dwps_specification_table', … )`.

**There is no `do_action` anywhere in that path.** The plugin's only extension points are
on output — `dwspecs_table_value_output` and `dw_specs_table_shortcode_output`. A third
party that wants to know a value changed has nothing to subscribe to, and a type that
needs to act on save has nowhere to do it.

Two v1 details matter beyond the structure. `dwspecs_chk_none` is v1's "no selection"
marker for select and radio, and it is discarded exactly as an empty value is. And step 1
means that unassigning a table from a product **deletes every value on it**.

This ticket was written proposing "sanitize, write to the map, run the type's save
action, fire the public hooks". ADR 0003 removed the type's save action: the interface is
`sanitize()`, `value()` and `render()`, with no `save()`.

## Decision

### The lifecycle is a service, not a controller

v2's product panel is React over REST, and a save can also arrive from the classic
product screen, from WP-CLI, from the migration, or from import. Every one of those calls
the same service, which is what makes the hooks fire consistently regardless of entry
point.

```txt
1. Resolve the target            product or variation
2. For each submitted key        specification by slug → its type → sanitize()
3. Apply the context rule        null removes the key on a product,
                                 stores null on a variation
4. Write                         one update_post_meta for the whole document
5. Announce                      fire the public actions
```

Step 2 replaces v1's single blanket `FILTER_SANITIZE_SPECIAL_CHARS`. Each value is
sanitized by the type that owns it, which is what makes a boolean stay a boolean and a
composite value keep its shape.

Step 3 is ADR 0002 and ADR 0006 applied, not a new rule. A specification whose value
comes from elsewhere never reaches step 2, because the client draws no input for it and
its key never appears in the payload.

### A write is a full replace

The client loads the **entire** stored value map and sends the whole thing back. It
renders only what the assigned set composes, but it carries the rest untouched.

This keeps three things true at once. Deletion is expressible, because an absent key in
the payload means an absent key in storage — which a partial merge cannot say now that
ADR 0006 has given an absent key a meaning. Reads and writes have the same shape. And
values outside the assigned set — orphans from a type change, or values from a set that
was previously assigned — survive a round trip instead of being silently dropped when a
merchant switches sets.

Two concurrent saves still overwrite each other, last write winning. ADR 0002 records
this as carried over from v1 and unchanged; PS-075 is where a guard gets decided, because
bulk value entry is what makes it routine rather than rare.

### Public actions, and types are ordinary subscribers

```php
do_action(
    'product_specifications_values_saved',
    int   $postId,
    array $newValues,
    array $oldValues
);
```

Both maps are passed. A listener that maintains a projection needs the old one to know
what to remove, not only the new one to know what to add.

```php
do_action( 'product_specifications_rebuild' );
```

Fired once after a bulk path finishes, so a projection can rebuild in one pass.

**A type has no `save()` method.** A type that must act when a value is saved — writing a
term for filtering, for example — subscribes to these actions the same way a third party
would:

```php
final class RatingType implements SpecificationType
{
    public function __construct()
    {
        add_action(
            'product_specifications_values_saved',
            $this->syncTerms(...),
            10,
            3
        );
    }
}
```

This is ADR 0003's principle applied: core registers its types through the same mechanism
third parties use, so core's types get no extension point third parties lack. It also
avoids a seventh interface method that most types would no-op — the shape ADR 0003
already removed twice.

There is no pre-save filter over the value map. `sanitize()` is the per-type seam and it
is enough. A filter can be added later without breaking anything that exists.

### The map is the truth, and anything else is a projection

Nothing but `product_specifications_values` stores a value. A listener that writes
somewhere else — a term for filtering, a lookup table, a search index — is maintaining a
projection, and a projection must be fully rebuildable from the map. That is what makes
`product_specifications_rebuild` sufficient after a migration, and what keeps a
projection's loss recoverable.

PS-077 is where a projection actually gets designed. Note that ADR 0005 already records
a limit on the invariant: a specification whose value comes from elsewhere has no key in
the map, so a projection covering it rebuilds from the product, not from the map.

### Bulk paths write silently

The migration and import write through the repository without firing
`product_specifications_values_saved`, then fire `product_specifications_rebuild` once
when they finish.

Firing per product would run every listener eighty thousand times during a migration,
making the migration as slow as its slowest subscriber and putting third-party code in
the path of the one operation that must not fail. Rebuilding once at the end reaches the
same state.

### Unassigning a set deletes its values, on save

This preserves v1's behavior, which deletes both meta keys when no table is selected.

Two things make it safe. The editor **warns before the save** — naming how many values
will be deleted — so it is never a silent loss. And nothing is persisted by the interface
action alone: unassigning or switching a set in the editor changes nothing on disk until
the product is saved, so a merchant who unassigns and navigates away loses nothing.

Switching from one set to another is not an unassignment. The client sends back the full
map it loaded, so values for specifications the new set does not compose are kept as
inert orphans and return if the original set is reassigned.

### Revisions are out of scope

WordPress has supported revisioned post meta since 6.4, through `revisions_enabled` on
`register_post_meta()` and the `wp_post_revision_meta_keys` filter. It cannot be used
here, and the reason is upstream of this plugin:

```php
// WC_Post_Types::register_post_types()
$supports = array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'publicize', 'wpcom-markdown' );
```

`product` does not support `revisions`, so there are no revisions for meta to attach to.
`product_variation` is registered with `'supports' => false` and supports nothing at all.

Reaching revisioned values would mean calling `add_post_type_support( 'product', 'revisions' )`
from this plugin — a store-wide change that adds a `wp_posts` row on every product save
across the whole catalogue and alters behavior for every other plugin on the site. A
specification plugin should not make that decision for a store.

PS-074 owns this, in the deferred epic. These findings are recorded so it does not have
to rediscover them.

## Rationale

Every part of this ADR is a seam rather than a feature. The plugin has none today, which
is why filtering and projections both currently have nowhere to attach.

Passing both maps to the action is the load-bearing detail. An action that carries only
the new values lets a listener add but never remove, so every projection built on it
would leak.

Not giving types a `save()` method is a consistency decision more than a technical one.
The moment core's types can do something a third party's cannot, the registry stops being
a real extension point and becomes a core convenience with a public façade.

## Consequences

### Positive

- One save path, whatever the entry point, so hooks fire consistently.
- Per-type sanitization replaces one blanket filter that flattened every type.
- Filtering and any future projection have somewhere to attach, without any
  of them being designed yet.
- A migration does not run third-party code eighty thousand times.
- Unassigning a set is warned about and is not persisted by a UI action alone.
- Switching sets no longer loses the values of the set being switched away from.

### Negative

- Full replace means concurrent saves lose each other's changes. Unchanged from v1,
  recorded in ADR 0002, decided in PS-075.
- The whole value map crosses the wire on every product save, including keys the editor
  does not render. On a product with many orphaned values this is larger than it needs
  to be.
- A projection is stale between a bulk write and the rebuild that follows it.
- A listener that throws during a save affects the save. There is no isolation, because
  WordPress actions provide none.
- Specification values do not appear in revisions and cannot be restored with one. This
  is a real gap, and it is upstream of the plugin.

## Alternatives Considered

### A `save()` method on the type interface

Rejected. It is more discoverable and hands a type only its own value, and it puts a
seventh method on an interface most implementations would no-op. It also gives core's
types a capability third parties do not have, which contradicts ADR 0003.

### Partial merge with explicit delete instructions

Rejected. It narrows the concurrency window to genuinely concurrent edits of the same
field, which is a real benefit. It also creates two ways to say "no value" — a delete
instruction in the payload and a stored `null` — after ADR 0006 spent a decision getting
to one.

### Full replace with an optimistic version guard

Rejected for now. It closes the concurrency race properly, at the cost of a read on every
save and a conflict-resolution path in the editor. PS-075 is where bulk entry makes that
worth building, and it can be added without changing anything decided here.

### Firing the save hooks from the migration and import

Rejected. It is one rule with no silent mode to forget, and it makes a migration as slow
as its slowest subscriber while putting third-party code in the path of the operation
that must not fail.

### A pre-save filter over the sanitized map

Rejected for now. `sanitize()` is already the per-type seam, nothing needs a second one,
and it is additive whenever something does.

### Enabling post revisions on `product` to get value revisions

Rejected. It would add a `wp_posts` row per product save across an entire catalogue and
change behavior for every other plugin on the site. Not a decision a specification plugin
makes on a store's behalf.

## Final Decision

```txt
product_specifications_values_saved( $postId, $newValues, $oldValues )
product_specifications_rebuild()
```

One service, called by every entry point. Per-type sanitization. A full-replace write
carrying the whole map. Two public actions, which core's types subscribe to on the same
terms as anyone else. The map is the truth; everything else is a rebuildable projection.
Bulk paths write silently and rebuild once. Unassigning a set deletes its values on save,
after a warning, and never on a UI action alone.
