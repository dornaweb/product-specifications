# Product Specifications

A WooCommerce plugin that adds specification tables to products. Merchants define each
specification once, arrange specifications into sets with groups, and fill in the values
on each product. The table shows in a product tab, a block and the `[specs-table]`
shortcode. Published on WordPress.org as `product-specifications`.

## Model

| Concept | Storage |
| --- | --- |
| Specification | A `spec-attr` term. Its slug is its key: chosen at creation, never changed afterward |
| Specification set | A `specs-table` post. Groups and order live in the `specification_set_structure` meta |
| Product values | Product meta `product_specifications_values` (a map keyed by specification slug) and `product_specifications_set` |
| Settings | The `dwps_settings` option |

Stored documents carry a `schema_version`. The decisions behind the model are in
`docs/adr/`. Read the relevant ADR before changing a stored shape.

Storage keys (`spec-attr`, `spec-group`, `specs-table`, meta and option keys) never change,
whatever the symbols around them are renamed to. `spec-group` is the retired group taxonomy,
read only by the migration from 0.x.

## Layout

- `product-specifications.php` boots an Inpsyde Modularity package; each folder under
  `src/` is a module with its own `Module.php`.
- `inc/api.php` is the public PHP API.
- `client/` is the React and TypeScript admin, mounted in the WooCommerce admin shell.
  Each screen is a lazily loaded chunk.
- `src/Block/<Name>/` holds a block: `block.json`, its scripts and a view-only `render.php`.
- `assets/` is build output and is not committed.
- `docs/site/` is the user and developer documentation, readable on GitHub and buildable
  with VitePress.
- `.claude/pm/` holds tickets; read `.claude/pm/README.md` before touching one.

## Development

```bash
npx @wordpress/env start     # http://localhost:8888, multisite, WooCommerce installed
npm run start                # watch the admin and block assets
```

Translations come from the community on translate.wordpress.org, which extracts the strings
from each release itself. The template is not committed. To audit strings after changing
them, generate it locally:

```bash
npx @wordpress/env run cli --env-cwd=wp-content/plugins/product-specifications -- \
  wp i18n make-pot . languages/product-specifications.pot \
  --exclude=docs,e2e-artifacts,client,build,tests \
  --headers='{"Report-Msgid-Bugs-To":"https://github.com/dornaweb/product-specifications"}'
```

Run it after `npm run build`; it reads the admin strings from the built chunks. A string is
extracted only when it is a literal argument of `__()` and its relatives.

## Releases

`develop` is the default branch. Publishing a GitHub release, not a pre-release, runs
`.github/workflows/release.yml`. It builds the assets, scopes the Composer dependencies and
deploys to WordPress.org. `.distignore` decides what ships, and `.gitattributes` mirrors it
for `git archive`. WordPress.org serves the version named by `Stable tag` in `readme.txt`.
Record each release in `changelog.txt` and the latest entries in `readme.txt`.

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

PHP tests are unit tests only; there is no PHP integration harness. Behavior that needs a
booted WordPress is covered by the Playwright suite in `tests/e2e/`, run against wp-env with
`npm run test:e2e`. Verify anything else by hand and write it down in the ticket's test plan.

## Code

- PHP 8.1. PHPStan at `level: max` must stay green — run `composer phpstan`,
  which sets the memory limit the analysis needs.
- No explanatory comments. Comment a genuine ambiguity, nothing else. A comment
  that restates the line below it is deleted, not reworded.
- **A comment describes the code as it stands, to a reader who has no history.**
  It never names a ticket, an ADR, a version of this plugin, a migration, or a
  decision — no `PS-###`, no "v1"/"v2", no "legacy", no "was", "used to", "now",
  or "instead of". Write the rule the code obeys, not the story of how it got
  there. That story belongs in the ticket and the ADR.

  ```php
  // v1 wrote attr_values as a bare list; v2 writes a map. PS-059 reads both.   ✗
  // Options are stored as a bare list of choices or as a map keyed by option    ✓
  // name. Both are read.
  ```

  This applies to test names and data-provider labels too, which are read the
  same way and printed in failure output.
- `inc/api.php` and the classes it documents get full docblocks for every parameter,
  return and throw. Internal code stays comment-free.
- Ask, don't tell. Behavior belongs on the object that owns the data.
- YAGNI. Write what the ticket needs.

## Checks

```bash
composer phpstan
vendor/bin/phpcs
vendor/bin/phpunit
npm run lint:js
npm run typecheck
npm run test:js
npm run build
```

## Language

American English, in code, comments, tickets, and commit messages.
