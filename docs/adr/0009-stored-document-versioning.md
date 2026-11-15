# ADR: Stored Document Versioning

## Status

Approved

## Context

The plugin stores four things in post meta:

```txt
specification_set_structure      a document   ADR 0001
specification_set_presentation   a document   ADR 0004
product_specifications_values    a document   ADR 0002, ADR 0006
product_specifications_set       one string   ADR 0002
```

Three of them are structured, and each was given a `schema_version` member independently
as it was designed. That produced three documents with three unstated policies about when
the version moves, what the schema should accept, and who writes it — which is a trap for
whoever writes the next migration, and the kind of ambiguity that costs far more than the
field itself.

It also produced one concrete hazard. `StructureMetaDefinition::schema()` pinned the
version to exactly the current value:

```php
'schema_version' => [
    'type'    => 'integer',
    'minimum' => self::CURRENT_VERSION,
    'maximum' => self::CURRENT_VERSION,
],
```

This ADR states the policy once. It was settled while writing ADR 0004 and lives here
because it governs all of them.

## Decision

This rule covers every stored document in the plugin.

**Structured data carries a `schema_version`. A bare scalar does not.** A document is
versioned; `product_specifications_set`, which holds one `post_name`, is not. Wrapping a
single value in a version envelope buys nothing and costs a shape to unwrap.

**The version is declared in the schema, not constrained by it.**

```php
'schema_version' => [ 'type' => 'integer' ],
```

No `minimum`, no `maximum`. It has to be declared because `additionalProperties: false`
rejects any member that is not, but REST validates shape, not vintage. A schema pinned
to exactly the current version rejects every document written by an earlier release,
which is the entire population a lazy upgrade exists to read — the two cannot both hold.
Pinning also creates an ordering hazard: WordPress runs no plugin code at update time,
so between a file update and the upgrade router running, every stored document would be
out of spec and every REST read and write of it would fail with an error naming
`schema_version` and explaining nothing.

**The version is written by the server, never read from the request.** The sanitizer
stamps `CURRENT_VERSION` on every write and discards whatever the payload claimed. That
is what makes leaving it unvalidated safe.

**A member added with a default does not bump the version.** A document written before
the member existed is read with that member's default, which is the behavior the member
replaced. The version moves only when an existing member's meaning or shape changes.

**A stale document is upgraded by a dedicated upgrader service**, one per document,
called by the repository after loading and before the domain object is built. Nothing
downstream ever sees an old shape, and the upgraded document is written back only when
the object is next saved. Version upgrades are branching logic over stored shapes, which
is exactly what the project's testing rule says to pin with unit tests.

## Rationale

The version earns its place on `product_specifications_values` and is insurance on the
other two. A store holds one value map per product, so a shape change there can never be
a wholesale rewrite — reading an old document and upgrading it in place is the only
affordable strategy, and that is what a version is for. A store holds a handful of sets,
which the upgrade router rewrites in one pass, so their version will most likely never be
read.

Keeping the rule uniform anyway is the cheaper choice. Three lines of JSON cost nothing;
a per-document judgment call about which documents are versioned costs a paragraph of
explanation forever, and invites the question "why is this one different?" at every
future change.

Unpinning the version is not a relaxation, it is a correction. Validating vintage in the
REST schema contradicts lazy upgrade by construction: the schema would reject exactly the
population an upgrade-on-read exists to serve. It also created an ordering hazard that
nothing wrote down. WordPress runs no plugin code at update time, so between a file update
and the upgrade router running on the next request, every stored document would have been
out of spec — and every REST read and write of a set would have failed with an error
naming `schema_version` and explaining nothing. That risk grows with a backgrounded
upgrade, and it disappears entirely once REST stops having an opinion about vintage.

## Consequences

### Positive

- One rule, applied to every stored document, stated in one place.
- Lazy upgrade of product values is possible, which is what makes a future value-shape
  change affordable on a large catalogue.
- No ordering dependency between the upgrade router and the REST layer.
- Version upgrade logic is in a named service with no WordPress plumbing around it, so it
  is unit-testable — and branching over stored shapes is exactly the kind of logic the
  project's testing rule says to pin.

### Negative

- The version is unvalidated by the schema, so a malformed stored value is caught by the
  upgrader rather than at the boundary. The server stamping it on write is what keeps
  that safe.
- Two of the three documents carry a member that will probably never be read.
- An upgrader service per document is three small classes for a change that may never
  come.

## Alternatives Considered

### Version only the product value map

Rejected. It is the only document where the version does real work, and versioning it
alone means a reader has to remember which documents are versioned and which are not.
Uniformity is worth more than two unread integers.

### Version nothing, and let the plugin data version drive every upgrade

Rejected. It is the simplest possible model and it makes every future value-shape change
a rewrite of every product, which is precisely what background processing exists to avoid
and what a lazy upgrade avoids for free.

### Keep the exact-version pin and require the upgrade router to run first

Rejected. It preserves the strictest validation, at the cost of making lazy upgrade
impossible and introducing an ordering rule between two subsystems that nothing enforces.

### Upgrade inside the registered `sanitize_callback`

Rejected. WordPress would then apply it on every path automatically, which is fewer call
sites to remember. It also buries upgrade logic inside WordPress's meta plumbing, where
it is hard to see, hard to test, and runs on writes as well as reads.

## Final Decision

```php
'schema_version' => [ 'type' => 'integer' ],
```

Structured data is versioned; a bare scalar is not. The schema declares the member and
constrains nothing. The server stamps `CURRENT_VERSION` on every write and ignores what
the request claimed. Adding a member with a default does not bump the version. A stale
document is upgraded by a dedicated upgrader service, called by the repository on read.
