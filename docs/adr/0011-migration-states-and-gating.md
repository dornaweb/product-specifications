# ADR: Migration States and Gating

## Status

Approved

## Context

A site running v1 does not become a v2 site when the plugin files are replaced. It
becomes a v1 site with v2 code on it, and stays that way until a migration runs. Seven
tickets each decide a piece of what happens in between — PS-038, PS-039, PS-044, PS-045,
PS-046, PS-047 and PS-050 — and nothing states the model they are all pieces of.

What exists today is one option and a boolean:

```php
final class DataVersion
{
    public const CURRENT_VERSION = 2;
    public const LEGACY_VERSION = 1;

    public function isMigrated(): bool
    {
        return $this->version >= self::CURRENT_VERSION;
    }
}
```

`Upgrade\Installer` runs on `init` at priority 5 and stores a version only when none is
stored, using `Upgrade\LegacyDataProbe` to tell a fresh install from a legacy one.
`Upgrade\MigrationState` wraps the option with `isMigrated()` and `requiresMigration()`.

Two states. The lifecycle has six, and the two it cannot express — a run in progress and
a run that stopped — are exactly the two PS-039 requires, since it is specified as
"chunked, resumable, idempotent… stores its own progress and can be retried".

### The keys do not collide

This is the fact the rest of the ADR rests on. v1 and v2 store values under different
meta keys:

```txt
v1   _dwps_table                  _dwps_specification_table
v2   product_specifications_set   product_specifications_values
```

A product edited through the v2 panel before migration writes the v2 keys and never
touches the v1 ones. Nothing is overwritten and nothing is destroyed.

## Decision

### Six states

| State | Data version | v1 data on disk | Frontend | Admin |
| --- | --- | --- | --- | --- |
| `fresh` — no legacy data | 2 | none | v2 | normal |
| `pending` — not started | 1 | yes | nothing | warning |
| `running` | 1 | yes, plus partial v2 | nothing | warning |
| `failed` — stopped partway | 1 | yes, plus partial v2 | nothing | warning, resumable |
| `migrated` — not cleaned | 2 | yes | v2 | normal |
| `cleaned` | 2 | none | v2 | normal |

### The data version answers one question, and a run record answers the rest

`product_specifications_data_version` keeps meaning exactly what it means now: which
shape the site's data is in. It is `1` or `2` and it changes once.

A separate migration run record holds what the runner is doing:

```txt
status         idle | running | failed | complete
processed      chunk position
total          products to process
started_at
errors         what failed, and where
skipped        products that already held v2 data
```

PS-039 needs this for resumability and PS-044 needs it for the progress screen, so it is
not a structure invented for this ADR. Keeping it out of the data version stops one value
from conflating "what shape is the data" with "what is the runner doing", and gives
progress somewhere to live.

The record's exact shape is PS-039's to design.

### The frontend renders nothing until the data version reports migrated

There is no v1 fallback. Between the plugin update and a completed migration, no
specification table renders. The site keeps working; the table is absent.

The alternative — keeping the v1 renderer serving tables from `_dwps_specification_table`
until the switch — was considered and rejected. It would have removed the visible gap
entirely, and it would mean two renderers live at once, a second code path exercised on
every store that upgrades, and a v2 codebase whose behavior depends on legacy data still
being present.

The consequence is real and is accepted: **shoppers see no specification tables for as
long as the merchant leaves the migration unrun.** That makes PS-050's update warning
essential rather than a nicety.

### Nothing in the admin is blocked

An admin-wide warning banner while the state is `pending`, `running` or `failed`
(PS-046), linking to the migration screen. That is the entire mechanism. No screen is
replaced, no panel is disabled, no save is refused.

This changes PS-038, which is written as "replace the panel with a clear notice until the
data version reports migrated". The panel is shown, with a warning.

The reasoning is that the risk PS-038 guards against does not exist as stated. Its
problem says "saving would risk data loss" — but the keys do not collide, so a product
saved early keeps its v1 values untouched. What actually happens is that the migration
skips that product and lists it in the report, and its v1 values remain on disk until
cleanup.

The migration is a tool that moves data. It is not a supervisor of the store, and the
plugin does not take a site hostage to protect a merchant from an action it has already
warned them about.

### Product saves during a run are unchecked

Products save normally while the migration runs. The runner does not re-check them, lock
them, or queue them.

A product edited after its chunk has been processed keeps whatever the merchant entered,
because the migration will not come back to it. A product edited before its chunk is
processed is skipped by the conflict rule below. Either way, no v1 data is lost and the
report says what happened.

### The migration never overwrites existing v2 data

When the runner meets a product, a set or a specification that already holds v2 data, it
skips it and lists it in the result. This settles PS-047's stated rule and is what makes
an unblocked admin safe: early v2 edits survive the migration rather than being
overwritten by it.

### Rollback and downgrade

v1 data is untouched until PS-048's cleanup step, which is a separate, explicit action.
Until it is run:

- **Rollback** is setting the data version back to `1`. The v2 renderer stops, the
  warning banner returns, and the migration can be run again.
- **Downgrade** to v1 plugin files works, because v1 reads keys that are still there.

Cleanup is the point of no return, and it should say so.

## Rationale

The six states are not a new mechanism; they are the states the seven tickets already
imply, written down once so they stop being decided seven times.

Splitting the run record from the data version follows from what each is for. The data
version is a fact about stored data that changes once in a site's life. The run record is
a fact about a process, changes constantly, and is discarded when the process ends.

Not blocking the admin is the decision most likely to be questioned later, so the reason
is worth stating plainly: the guard would protect against a data loss that cannot happen,
because v1 and v2 keys do not collide. What is at stake is a merchant redoing work on a
handful of products they edited before migrating, which the migration report tells them
about. That is not worth disabling a screen for.

## Consequences

### Positive

- One model, stated once, that seven tickets implement rather than each inventing.
- `DataVersion` keeps its single, stable meaning.
- Rollback and downgrade both work until cleanup, so a failed upgrade is recoverable.
- No screen is disabled, so a merchant can explore v2 before committing to a migration.
- The conflict rule makes early v2 edits safe rather than merely tolerated.

### Negative

- **Specification tables are absent from the shop** between the update and the
  migration. This is the largest cost in the decision and it is deliberate.
- **The value panel shows an empty table for every unmigrated product**, even though the
  product has v1 values. Without the warning banner explaining it, this reads as data
  loss.
- A merchant who edits products early gets those products silently skipped. The migration
  report is the only place that surfaces it.
- Two records must be read to know what is going on, and they can disagree if a run
  record is left behind by a crashed process.

## Alternatives Considered

### Render from v1 until migrated

Rejected. It removes the visible gap entirely and costs nothing that is not already being
kept, since legacy removal is epic I and both renderers coexist through the transition
anyway. It was rejected because it keeps a second render path live on every upgrading
store and makes v2 behavior depend on legacy data still being present, which is the
coupling the refactor exists to remove.

### Render per product during the run

Rejected. A migrated product renders from v2 and an unmigrated one from v1, so there is
no gap and no big-bang switch. It requires a per-product check on every render and keeps
both renderers active simultaneously, for a window that a merchant controls the length of.

### Block the value panel until migration completes

Rejected. It is PS-038 as originally written, and it guards against a data loss that
cannot occur because the meta keys do not collide.

### Block the whole v2 admin until migration completes

Rejected. No conflict would be possible, because no v2 data could exist before the
migration wrote it. It also stops a merchant from looking at what they have upgraded to
before committing to migrating.

### Block product saves for the duration of the run

Rejected. It removes every race, and on a large catalogue with background processing it
means hours during which nobody can edit a product on a live store.

### Extend `DataVersion` to hold the whole lifecycle

Rejected. One option is fewer moving parts, and it conflates the shape of the data with
the state of a process, and has nowhere to put progress, errors or a chunk position.

## Final Decision

```txt
product_specifications_data_version    1 or 2 — which shape the data is in
migration run record                   status, progress, errors, skipped
```

Six states. The frontend renders nothing until the version reports migrated. The admin
blocks nothing and warns everywhere. Product saves are unchecked. The migration never
overwrites existing v2 data and reports what it skipped. v1 data survives until cleanup,
so rollback and downgrade both work until then.
