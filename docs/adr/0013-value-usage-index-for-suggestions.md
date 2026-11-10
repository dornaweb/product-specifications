# ADR: Value Usage Index for Suggestions

## Status

Approved

## Context

A Text specification can suggest values while an editor types. The merchant
types a starting list, and with **Learn new values** on, values that products carry join
it. The autocomplete shows the most used values first.

That needs two facts the value map cannot answer cheaply: which distinct values a
specification has across products, and how many products carry each. Values live in one
serialized document per product (ADR 0002), so neither can be read with a `GROUP BY`.
`WordPressProductValueRepository::countUsing()` answers one question of that kind today
by scanning every product's document that mentions the specification's slug. It is
acceptable for a delete warning. It is not acceptable on every product screen load.

Two more constraints shape where the answer is kept:

- The storefront reads each specification through `SpecificationDefinitionRepository::find()`,
  and reading any term meta primes the cache with **all** of that term's meta. Anything
  stored in term meta is loaded on every storefront page that shows the specification.
- ADR 0008: the value map is the truth, and anything else is a projection that must be
  rebuildable from it.

## Decision

### One usage index per suggesting specification

An option, not autoloaded, holds one document per specification whose suggestions are on:

```txt
product_specifications_value_usage_<term id>
```

```json
{
  "schema_version": 1,
  "values": {
    "blue": { "Blue": 10, "blue ": 2 },
    "usb-c": { "USB-C": 7 }
  }
}
```

`values` maps a value's **normalized form** to the spellings products carry and how many
products carry each. A product is counted once per value, however often it lists it.
Products and variations count; trashed ones do not.

The term id in the option name is a cache key, not a stored reference (ADR 0002): the
index is rebuilt from the value maps, so it never needs to survive a term id changing.
The option is deleted with its specification and is not part of the export package.

`schema_version` follows ADR 0009.

### Normalization

A value's identity is the string trimmed, with each run of whitespace collapsed to one
space, and lowercased (`mb_strtolower` on the server, `toLowerCase` in the editor).
"Blue", "blue " and "BLUE" are one value. Accents are kept: "Café" and "Cafe" are two.

The spelling shown for a value is the merchant's typed spelling when the typed list has
it, otherwise the spelling the most products carry, ties broken by byte order. Counting
spellings, rather than remembering the first one seen, is what makes a rebuild produce
the same spelling every time.

### Learned values are derived

A learned value is a value in the index that is not in the typed list. Nothing else
stores it. Promoting one adds it to the typed list, which is what stops it being learned.

A learned value leaves the suggestions when the last product carrying it is changed.
Until then it ranks by its count, so a typo on one product sits below a value forty
products share.

### Kept current by the save lifecycle

The index is a projection, maintained the way ADR 0008 prescribes:

| Event | Effect |
| --- | --- |
| `product_specifications_values_saved` | For each changed key of a suggesting specification, subtract the old value's items and add the new ones |
| A product or variation is trashed, or deleted without being trashed first | Subtract its stored values |
| A product or variation is restored from the trash | Add its stored values |
| `product_specifications_rebuild` | Rebuild every suggesting specification's index from the value maps |
| A specification's type or options change | Build its index when it suggests and has none; delete it when it no longer suggests |

An index that is missing when it is read or updated is built from the value maps first,
so suggestions turned on for a specification, or a specification an import created,
never needs a separate step.

### Read with the specification

A `suggestions` field on the specification's REST response returns them ranked, so the
product screen gets them from the request it already makes. It is a field of its own,
next to `specification`, so the index depends on specifications and products and
neither depends on it. Ranking is by product count, the typed list first
on a tie, then typed order, then alphabetical. Learned values are included only while
**Learn new values** is on, and at most 500 of them.

## Rationale

- **No scan on the hot path.** A product save does one option read and one write per
  changed suggesting specification. The product screen reads one small option per
  specification. The full scan runs only when the index has to be built.
- **The storefront pays nothing.** The index is outside term meta and not autoloaded.
- **Rebuildable.** Everything in the index is derived, so drift is repaired by a rebuild,
  and an import or migration seeds suggestions from the values it wrote.
- **The suggestions are what products carry.** There is no merchant state beside the
  typed list, so the suggestions never disagree with the products.
- **Fits the audience.** Most sites run a few hundred to a few thousand products, and a
  specification with suggestions on has tens to hundreds of distinct values.

## Consequences

### Positive

- Existing values become suggestions the moment suggestions are turned on.
- The merchant sees how many products carry each learned value, which is what makes
  promoting it an informed decision.
- No new table, no schema migration, nothing to uninstall but options.

### Negative

- Two saves that finish at the same moment can lose one update to the counts, last write
  winning. Counts only rank suggestions, and the next rebuild corrects them.
- A write that bypasses the value writer and fires no rebuild leaves the index stale until
  the next rebuild.
- The server does not snap a typed variant to the known spelling. `sanitize()` receives
  the specification's options, not its index, so "blue" imported where "Blue" exists is
  stored as "blue". The editor snaps; imports do not.
- A typo stays suggested until the products carrying it are corrected.

## Alternatives Considered

### Append learned values to the typed list

ACF's `save_options`. Rejected. Typos become part of the merchant's list, there are no
counts to judge them by, and a settings save made while a product was being saved
overwrites the value that product just added.

### Store the index in term meta

Rejected. It would be loaded on every storefront page that shows the specification.

### A hidden taxonomy, one term per value

What WordPress does for tags, with counts for free. Rejected. It is a full projection of
the value map into terms and relationships, which is the projection filtering would
need, and far more than ranking suggestions needs.

### Count on demand and cache the result

Invalidate on save and rescan on the next read. Rejected. An editor working through
products would pay a full scan per specification on every product screen.

### A custom table with atomic increments

Rejected for now. It removes the lost update, at the cost of an installer, upgrades and
uninstall for a count that only ranks suggestions.

## Final Decision

One non-autoloaded option per suggesting specification holds value counts by normalized
form and spelling. It is maintained from the save lifecycle and post lifecycle hooks,
rebuilt on `product_specifications_rebuild`, built whenever it is missing, and read
through a `suggestions` field on the specification's REST response. Learned values are derived from it, not stored, and
leave the suggestions only when no product carries them.
