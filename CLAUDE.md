# Product Specifications

WooCommerce plugin. v2 is a refactor tracked in `.claude/pm/`; read
`.claude/pm/README.md` before touching a ticket.

## Priority

Work in this order. When a ticket does not obviously sit in one of these tiers,
ask before picking it up.

1. **Refactor the plugin and make its basic features work.** Everything in the
   v1 feature set, running on the v2 model.
2. **New features.** Comparison, WooCommerce built-in details, table layouts,
   the Gutenberg block.
3. **Meta work.** Test harnesses, tooling, documentation. Last, and only when
   asked.

## Testing

Write a test when it would catch a real defect. Do not write one to cover a line.

A test earns its place when it pins **behavior that can break silently**:

- Branching or normalization logic — parsing, mapping, sanitizing, fallbacks
- A contract other code relies on — a stored data shape, a public return type,
  wiring that only fails at runtime
- A bug that was actually hit, so it cannot come back

A test does not earn its place when it only restates the code:

- A getter that returns what the constructor was handed
- A `const` or a static array of configuration
- A factory that news up one object with no logic in between
- A mock asserting that the thing it mocked was called

Coverage is not a goal. A suite of low-value tests costs more to maintain than
it ever catches, and it hides the tests that matter.

**No integration tests.** Postponed until the refactor is finished — see PS-011.
Do not add one, and do not add a test that needs a booted WordPress. Unit tests
only; anything else is verified by hand and written down in the ticket's test plan.

## Code

- PHP 8.1. PHPStan at `level: max` must stay green — run `composer phpstan`,
  which sets the memory limit the analysis needs.
- No explanatory comments. Comment a genuine ambiguity, nothing else. A comment
  that restates the line below it is deleted, not reworded.
- **A comment describes the code as it stands, to a reader who has no history.**
  It never names a ticket, an ADR, a version of this plugin, a migration, or a
  decision — no `PS-###`, no "v1"/"v2", no "legacy", no "was", "used to", "now",
  or "instead of". Those are scaffolding for building the release and are
  meaningless to whoever opens the file afterward. Write the rule the code obeys,
  not the story of how it got there. That story belongs in the ticket and the ADR.

  ```php
  // v1 wrote attr_values as a bare list; v2 writes a map. PS-059 reads both.   ✗
  // Options are stored as a bare list of choices or as a map keyed by option    ✓
  // name. Both are read.
  ```

  This applies to test names and data-provider labels too, which are read the
  same way and printed in failure output.
- Ask, don't tell. Behavior belongs on the object that owns the data.
- YAGNI. Write what the ticket needs.
- Storage keys (`spec-attr`, `spec-group`, `specs-table`, meta keys) never change,
  whatever the symbols around them are renamed to.

## Checks

```bash
composer phpstan
vendor/bin/phpcs
vendor/bin/phpunit
npm run lint:js
npm run build
```

## Language

American English, in code, comments, tickets, and commit messages.
