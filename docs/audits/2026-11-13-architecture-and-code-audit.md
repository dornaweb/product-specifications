# Architecture and Code Audit, 2026-11-13

Read-only review of branch `refactor/release-2` at commit f196058. The findings are
kept as written on the day; the section below records what was decided afterward, so
a reader knows which recommendations still stand.

## Decisions taken after this audit

- Recommendation 1 is settled in favor of the slug as the single identity, with two
  changes to the identity rule itself: the slug is chosen at creation instead of
  generated with a random suffix, and deleting a specification purges its values from
  every product so a freed key can never adopt leftovers. ADR 0010, ADR 0002, ADR 0001
  and ADR 0008 were amended on 2026-11-13. PS-112 implements it.
- Deviations D1, D2 and D3 (term ids in set structures, the pinned `schema_version`,
  per-row term loading) are fixed together by PS-113, sequenced after PS-112.
- Recommendation 3 (reject unknown type ids), 4 (surface write failures), 6 (gate the
  stack trace) and 7 (lossy status mapping) have no ticket yet.
- The rest of Priority 1 and Priority 2 is open.

---

# Product Specifications 2.0: Architecture and Code Audit

Read-only audit of branch `refactor/release-2` at commit f196058, 2026-11-13. Scope: PHP under `src/` and `inc/`, TypeScript under `client/` and `src/Block/`, measured against the thirteen ADRs in `docs/adr/`.

## 1. Executive Summary

Verdict: the relaunch is structurally sound and close to beta quality. The domain model is the right one for this plugin's scale, the module boundaries are real, and the codebase passes every static gate it declares. The problems that matter are few and specific, and almost all of them sit at the storage boundary, which is where they must be fixed before 2.0.0 ships, because stored shapes are frozen afterward.

Checks run during the audit:

| Check | Result |
| --- | --- |
| PHPUnit unit suite | 451 tests, 653 assertions, green |
| PHPStan level max | 0 errors |
| PHPCS (PSR-12 + WordPress-VIP-Go) | 0 errors, 33 warnings, all under `tests/` |
| TypeScript `tsc --noEmit`, strict | clean |
| ESLint, `@wordpress/eslint-plugin` recommended | clean |

Three findings outrank everything else:

1. Set items store `term_id` beside `slug`. ADR 0001 as amended and ADR 0002 make the slug the only stored identity. The implementation requires a term id in the schema, drops items without one, and resolves rows by term id on the storefront. This reintroduces the dual-key problem two ADRs spent a decision removing and silently breaks the native export and import promise.
2. `schema_version` is pinned to the current value in the structure REST schema. ADR 0009 names this exact code as the hazard it was written to remove. The first schema bump makes every stored set fail REST validation until it is rewritten.
3. The storefront reader resolves specifications one term at a time inside the render loop, which ADR 0002 forbids, and three modules query product-value storage keys directly instead of asking the repository.

Everything else is ordinary technical debt: a validation gap on one REST field, swallowed write errors, a settings subsystem built for nine field kinds when three are used, three oversized React components, and contracts duplicated between PHP and TypeScript.

## 2. Domain Model Assessment

### 2.1 Entities and storage

| Entity | Storage | Identity | Module |
| --- | --- | --- | --- |
| Specification | `spec-attr` term plus `attr_type`, `attr_values` (map), `attr_default` term meta | Frozen slug, generated once by `SpecificationSlug`, enforced at REST by `SlugGuard` | `Specification` |
| Specification Set | `specs-table` post plus `specification_set_structure` post meta, a versioned document | Post slug, assigned to drafts too by `DraftSlug` | `SpecificationSet` |
| Product Values | `product_specifications_values` post meta, a versioned map keyed by slug, plus `product_specifications_set` holding the set slug | The product or variation post | `ProductValues` |
| Specification Type | In-memory registry; the string id is what `attr_type` stores | Type id | `Specification\Type` |
| Value usage index | One non-autoloaded option per suggesting specification | Term id, as a cache key | `Suggestion` |

Relationships:

- Set to Specification: the structure document lists groups, each listing items `{term_id, slug, visible}`. Composition, grouping and order live only here.
- Product to Set: one slug in its own meta key, so it stays queryable.
- Product to Specification: the value map is keyed by specification slug. Labels are never copied.
- Type to everything: `SpecificationType` owns sanitize, validate, read (`value()`), plain text and HTML output. `PortableValue` adds export and import mapping for site-local values such as attachments.

### 2.2 Strengths

- One source of truth per concern. Composition lives on the set, values on the product, the definition on the term. The ADR reasoning is visible in code, not only in documents.
- Immutable domain objects with guarded operations. `SpecificationSetStructure`, `SpecificationGroup` and `SpecificationSetItem` are readonly, every mutation returns a new instance, and an invalid operation throws a named exception. `ProductValueDocument`, `MigrationRun`, `ImportRun` and `NumberValue` follow the same pattern.
- Per-type sanitization. `ProductValueWriter` routes each submitted value through its type. Booleans stay booleans, composite numbers keep their shape, and the fallback `TextType` refuses to flatten values it does not understand.
- An open type system. Nine core types register through the same action a third party would use. The WooCommerce type delegates to small `ProductDataSource` classes with no switch statement, as ADR 0005 specified.
- Versioned documents and no custom tables. Migration, import and value storage all read and write through `fromStorage()` and `toStorage()` pairs, so a shape change has one place to land.
- Migration and import are resumable, chunked and background-safe. `BackgroundWorker` is shared by both, queues the next event before working, and a CLI path exists for sites without cron.

### 2.3 Scalability and extensibility risks

R1. Dual identity on set items (high). `StructureMetaDefinition::itemSchema()` requires `term_id`, `StructureSanitizer::sanitizeItems()` drops any item whose term id is below 1, `SpecificationGroup` keys every operation on an integer term id, and `SpecificationsTableReader::rows()` resolves each row through `find($item->termId())`. The client mirrors this: the set editor sends `term_id`, and the product panel fetches definitions by term id. After a native export and import, term ids change, so every set structure points at missing terms while the value maps, keyed by slug, survive. The import package already works around this: `SetsSection::export()` writes only the slug and `importEntry()` re-resolves the term id. That is the model telling you which key is real.

R2. Unindexed value scans (medium, grows with the catalog). `WordPressProductValueRepository::storedMapsMentioning()` runs a `LIKE` over the serialized form of the key across the whole `postmeta` table and loads every matching map into PHP. It backs `valueMapsWith()` for suggestion index builds and `countUsing()` for import planning notes. `ValueUsageProjection::rebuildAll()` runs it once per suggesting specification after every migration and every import that writes. ADR 0013 accepted this; it still deserves a stated ceiling.

R3. Per-row term loading on the storefront (medium). Each visible row costs a `get_term()` plus three `get_term_meta()` reads through `SpecificationDefinitionMapper::fromTerm()`. With a persistent object cache this is mostly free; without one, a forty-row table on a cold cache is forty-plus term-meta queries per product page. ADR 0002 describes the batched `get_terms()` call by slug with `update_term_meta_cache`. No batched lookup exists anywhere under `src/`.

R4. Whole-document writes, last write wins (accepted). Documented in ADR 0002 and ADR 0008 and unchanged. Bulk value entry is where a guard is due.

R5. Repositories swallow failures. `SpecificationDefinitionRepository::save()` and `WordPressSpecificationSetRepository::save()` turn a `WP_Error` into `0`. `SpecificationRestField::write()` ignores the return value, so a failed term update answers HTTP 200. The deletion service and the import sections check for `< 1`; the REST field does not.

R6. The settings schema cannot grow without touching three places. `SettingsSchema::hydrate()` hardcodes the three keys and throws when they disagree with the attributes that `fields()` discovers by reflection. Adding a setting means a new constructor parameter, a new attribute and a new line in `hydrate()`. The reflection buys the REST field list and nothing else.

R7. Variation semantics are half-present. `ProductValueWriter::sanitizedReplacement()` stores `null` for a variation as ADR 0006 specifies, but nothing reads a variation against its parent and the editing ticket is deferred. Harmless today; a reader that implements inheritance later must remember that `null` already carries meaning in stored data.

R8. No uninstall path. There is no `uninstall.php` and no deactivation hook. The plugin leaves its data version, migration run, import run, per-specification usage options and the private imports directory behind. Acceptable for a data plugin, but undocumented.

## 3. Architectural Deviations

### 3.1 Drift from the ADRs

| # | ADR says | Code does | Where |
| --- | --- | --- | --- |
| D1 | Set items carry `{slug, visible}`; term ids are never stored (ADR 0001 amendment, ADR 0002) | Items carry and require `term_id`; identity operations key on it | `src/SpecificationSet/StructureMetaDefinition.php:96-102`, `StructureSanitizer.php:85-89`, `SpecificationGroup.php`, `SpecificationSetItem.php`, `client/admin/SpecificationSetEditor/mapping/specificationSetStructure.ts` |
| D2 | `schema_version` is declared as a plain integer with no bounds (ADR 0009) | Pinned to `CURRENT_VERSION` on both bounds | `src/SpecificationSet/StructureMetaDefinition.php:36-40` |
| D3 | No per-item lookups inside a render loop; batch by slug (ADR 0002) | `find($termId)` per row, three meta reads per term | `src/Storefront/SpecificationsTableReader.php:72`, `src/Specification/SpecificationDefinitionMapper.php:12-23` |
| D4 | An unknown type id falls back to text without writing (ADR 0003) | A REST write with any `specification.type` string validates options against the fallback schema and persists the unknown id | `src/Specification/Rest/SpecificationRestField.php:75-122`, `src/Specification/Type/OptionsChecker.php:27` |
| D5 | A stale document is upgraded by a dedicated upgrader on read (ADR 0009) | No upgrader exists for any of the three documents | `SpecificationSetMapper::structureFromArray()`, `ProductValueDocument::fromStorage()` |

D5 is not a defect while every document is at version 1, but D2 guarantees it becomes one at version 2. D4 is inconsistent with the importer, which checks the registry in `SpecificationsSection::incoming()`.

Deliberately unbuilt rather than drifted: the presentation document of ADR 0004, variation editing of ADR 0006, the cleanup step and template overrides. The code contains no half-finished stubs for them.

### 3.2 Boundary leaks

B1. Three modules query product-value storage keys directly. `SpecificationSet\Rest\ProductUsageCounter` joins `postmeta` on `WordPressProductValueRepository::SET_META_KEY` with raw SQL; `Migration\Step\ProductValuesStep::productIds()` and `ImportExport\Section\ValuesSection::productIds()` build `meta_query` clauses on both value keys. The repository interface exists so that no other module knows these keys. Each should be a repository method, and the `$GLOBALS['wpdb']` reads in two classes plus `global $wpdb` in `ProductMatcher` should become an injected dependency, which also retires the `FakeWpdb` global in tests.

B2. The migration gate is checked in five places: `SpecificationsTableReader::read()`, `ProductSpecifications::value()`, `ProductTabs::addProductSpecificationsTab()`, `SpecificationsUpdater::save()` and `MigrationRequired::unlessMigrated()`. The reader and the updater are the two that matter; the rest are defensive copies of the same rule.

B3. The `spec-attr` taxonomy is registered against the `specs-table` post type in `Specification\Module::run()`. No code creates or reads a term relationship between a set and a specification; the structure document is the relationship. The registration suggests a model that does not exist.

### 3.3 Over-engineering and scatter

O1. Three classes for one write path: `SpecificationsUpdate` (builder), `SpecificationsUpdater` (a twenty-line gate that delegates) and `ProductValueWriter` (the work). The names are near-identical and the gate belongs in the writer. Inside the writer, `replace()` and `merge()` repeat the resolve-set, read-old, write, fire-action sequence with small variations.

O2. The settings subsystem. `SettingType` declares nine control kinds; `Settings` uses three. `SettingField::sanitize()` carries branches for six unused kinds, `SettingControl.tsx` renders all nine, and four `phpcs:ignore` lines exist because labels declared in attributes cannot pass through `__()` at declaration time. This is a generic settings framework for a three-field page.

O3. A global redux store for one screen. `SpecificationSetEditor/state/store.ts` calls `register()` at import time, and `actions.ts` and `selectors.ts` are pass-through boilerplate around a reducer that one hook reads. A `useReducer` scoped to the editor removes a global singleton, two files and the store casts.

O4. Three ways to talk to REST on the client: the core-data entity store (specifications, sets, set editor), `@wordpress/api-fetch` (product values, migration, import), and raw `window.fetch` with a hand-built nonce header in `client/admin/Settings/api.ts`. The last forgoes nonce refresh and root-URL middleware for no stated reason.

## 4. Code-Level Audit

### 4.1 PHP

Overall: PSR-12 clean, `declare(strict_types=1)` everywhere, constructor promotion, readonly properties, enums where introduced, and PHPStan max with no baseline. The exceptions follow.

Correctness and robustness:

- `SpecificationRestField::write()` never checks the return of `save()`. A `WP_Error` from `wp_update_term()` becomes a 200 response. `src/Specification/Rest/SpecificationRestField.php:119`
- `SpecificationRestField::write()` accepts any string as `type` (see D4). The fix is one `has()` check and a 400.
- `SpecificationDefinitionRepository::delete()` treats the `0` that `wp_delete_term()` returns for a missing term as success, because `is_bool(0)` is false and `0` is not a `WP_Error`. `src/Specification/SpecificationDefinitionRepository.php:87-90`
- `ProductValueWriter::rejection()` skips validation when the submitted value is identical to the raw stored default. Booleans are stored as `'1'` and `'0'` by `storableDefault()`, so the shortcut never fires for yes/no, and for other types it compares against an unsanitized string. Harmless today, confusing to read, and a bypass waiting to be widened. `src/ProductValues/ProductValueWriter.php:150-157`
- `ProductValueWriter::merge()` calls `productType()` purely for its exception and discards the result. The intent is invisible. `src/ProductValues/ProductValueWriter.php:58`
- `handleFailure()` in the main plugin file prints the stack trace into an admin notice for every admin user, which exposes filesystem paths. Gate it on `WP_DEBUG` or log it. `product-specifications.php:30-47`
- The set list and the set editor both map any status other than `publish` to `draft`. `SetsSection` imports `pending` and `private` sets and `DraftSlug` handles `pending`, so a private set opened in the editor is silently saved back as a draft. `client/admin/SpecificationSets/mapRecord.ts:28`, `client/admin/SpecificationSetEditor/mapping/specificationSetRecord.ts:77`

Dead code, leftovers and stale suppressions:

- `WooCommerceNotInstalledNoticeHandler` is unreachable. The header declares `Requires Plugins: woocommerce` and WordPress 7.0, and the class docblock says it applies only below WordPress 6.5. It is still wired in `Integration\Module`.
- `Assets\Asset` is an interface with two constants and no implementor, kept to namespace `TYPE_SCRIPT` and `TYPE_STYLE` for one private method.
- `ProductValues\RequestsRouteId` is a one-method trait with one consumer.
- `src/SpecificationSet/SpecificationSetPostType.php:86` suppresses an Inpsyde sniff that `phpcs.xml.dist` does not load.
- `Specification\Module` builds the registry with one `TextType` as fallback and registers a second instance in the action; the `?? $this->fallback` branch in `SpecificationTypeRegistry::get()` can never run because the constructor registers the fallback.
- `AssetHelper` is the one file that still reads pre-refactor: a `@psalm-type` annotation in a PHPStan project, a comment restating the line below it, and `array_merge` where a spread would do.

Naming and consistency:

- `SpecificationSetPostType`, `FeaturesCompatibilityDeclarations` and `WooCommerceNotInstalledNoticeHandler` are the only non-final classes; the latter two use explicit property assignment where every other class promotes.
- `MigrationRun`, `ImportRun` and `EntryOutcome` model status as string constants while `NumberRejection`, `DatePrecision`, `Dimension` and `SettingType` are enums. With PHP 8.1 the enum should win.
- `ImportRun` stores its cursor as an array although `ImportCursor` exists, so `ImportRunner::runChunk()` converts in both directions on every entry.
- `SettingType::BOOLEAN = 'toggle'` and `STRING_LIST = 'checkbox_group'`: case names describe data, values describe a control.
- `SpecificationCollectionQuery` hardcodes the `rest_spec-attr_collection_params` hook name instead of composing it from `SpecificationTaxonomy::KEY`.
- `ValuesSection` (496 lines) accumulates notes through `array &$notes` reference parameters in `plannedSet()` and `plannedValues()`. A small planned-entry result object removes the references and the two boolean returns.

Comments: the project rule (explain an ambiguity, never history) is followed with unusual discipline. A search for ticket ids, version names and "legacy" under `src/` outside `Migration/` and `Upgrade/` returns nothing. The remaining comments are explanatory.

### 4.2 TypeScript and React

Overall: functional components only, hooks throughout, `strict: true`, zero `any`, zero `@ts-ignore`, one justified `eslint-disable` for `no-alert`, every string through `@wordpress/i18n`, lazy-loaded page chunks, and 47 Jest cases on pure logic. The exceptions follow.

Size and responsibility:

| File | Lines | Why it is a bottleneck |
| --- | --- | --- |
| `client/productValues/ProductValuesPanel.tsx` | 627 | 15 `useState` hooks, a capture-phase `submit` listener that hijacks the classic `#post` form and replays it with `requestSubmit`, plus an inline `ValueField` component |
| `client/admin/Specifications/components/SpecificationForm.tsx` | 486 | Type picker, type-change confirmation, duplicate warning, keyboard shortcuts and every field in one component |
| `client/admin/SpecificationSetEditor/hooks/useSpecificationSetEditor.ts` | 414 | An `initializedRouteKey` ref juggles route changes against effect re-runs; its own comment admits the unsaved-changes guard "is bypassed rather than answered" |
| `client/admin/SpecificationSetEditor/components/StructureTab.tsx` | 389 | Drag state machine, pending-group dialog and library wiring inline |

Duplicated contracts. The structure document shape is declared four times (`SpecificationSetEditor/mapping/specificationSetStructure.ts`, `productValues/types.ts`, `Specifications/hooks/useSetUsage.ts`, `SpecificationSets/mapRecord.ts`), and `SetRecord` and `SpecificationRecord` twice each. Option keys (`values`, `suggest`, `multiple`, `learn`, the number keys, `source` and `name`) are string literals maintained independently in PHP and TS. Nothing checks them against each other, and `types/product-specifications-admin.d.ts` is a hand-maintained mirror of what `Admin\Assets::load()` localizes.

Typing escape hatches. The `as unknown as ResolutionSelectors` cast and its dispatch twin appear ten times across six hooks. One `useCoreDataResolution()` helper would own the single cast. `tsconfig.json` sets `noImplicitAny: false` and `noUncheckedIndexedAccess: false` under `strict: true`; the code already guards most index reads, so both can be turned on.

Minor:

- `SpecificationSets/index.tsx` and `SpecificationSetEditor/index.tsx` reference the global `React.MouseEvent` and `React.FormEvent` namespace without importing React; `SpecificationSets/index.tsx` and `shared/ui/EmptyState.tsx` use the deprecated global `JSX.Element`.
- `useSpecifications.ts` and `useSpecificationSets.ts` repeat the same try, refresh, notice wrapper; `useSettings.ts` repeats it a third way.
- `client/admin/constants.ts` carries two comments naming the PHP files that define the same constants. They are the only history-style comments in the client and will go stale.

## 5. Actionable Recommendations

Ordered by the cost of not doing them. The first group changes stored shapes or public contracts and should land before the 2.0.0 beta freezes them.

### Priority 0: before the beta

1. Resolve the set-item identity. Either drop `term_id` from the stored structure as the ADRs say (schema, sanitizer, mapper, domain objects keyed by slug, client types) and resolve a set's slugs in one `get_terms()` call, or amend ADR 0001 and ADR 0002 to state that the slug is authoritative and the term id is a cache readers must fall back from. Shipping schema 1 with the current ambiguity makes it permanent.
2. Unpin `schema_version` in `StructureMetaDefinition::schema()` to a plain integer.
3. Reject unknown type ids in `SpecificationRestField::write()` with a 400, matching `SpecificationsSection::incoming()`.
4. Surface write failures. Make the repositories return the `WP_Error` or throw instead of returning `0`, and have `SpecificationRestField::write()` propagate it.
5. Batch definition loading on the storefront: add `findByKeys()` to `SpecificationDefinitionRepository` using `get_terms()` with `update_term_meta_cache`, and read the whole set through it in `SpecificationsTableReader`.
6. Gate the stack trace in `handleFailure()` on `WP_DEBUG`.
7. Fix the lossy status mapping in the set list and editor so `pending` and `private` sets survive a save.

### Priority 1: debt that compounds

8. Move storage-key knowledge back behind `ProductValueRepository` with `countBySet()` and `productIdsHolding()`, inject `wpdb`, and delete the three direct queries.
9. Fold `SpecificationsUpdater` into `ProductValueWriter` and extract the shared write sequence from `replace()` and `merge()`.
10. Cut the settings subsystem to the three field kinds in use, replace the reflection with an explicit `fields()` list, and let labels go through `__()` so the four `phpcs:ignore` lines disappear.
11. Create one `client/shared/contracts/` module for the structure document, the specification and set REST records, and the option keys. Import it from every screen and from the block.
12. Split `ProductValuesPanel` into a set-assignment component, a group-tab list, a field list and a `useClassicFormSubmitBridge()` hook. Split `SpecificationForm` along the type-chooser boundary. Replace the global editor store with a screen-scoped reducer.
13. Make `MigrationRun`, `ImportRun` and `EntryOutcome` statuses backed enums, and let `ImportRun` hold an `ImportCursor`.
14. Move `Settings/api.ts` to `@wordpress/api-fetch`, and add `useCoreDataResolution()` to retire the ten casts.
15. Enable `noUncheckedIndexedAccess` and `noImplicitAny`.

### Priority 2: hygiene

16. Delete `WooCommerceNotInstalledNoticeHandler`, the `Asset` constants interface, the `RequestsRouteId` trait, the stale Inpsyde suppression, the duplicate `TextType` instance and the taxonomy-to-post-type registration link; make the three remaining classes `final`.
17. Decide and document the uninstall policy, or add `uninstall.php` for the options and the imports directory.
18. Write the ceiling of the serialized `LIKE` scan into ADR 0013 and name the catalog size at which the usage index moves to a projection table.
19. Replace the reference parameters in `ValuesSection` with a planned-entry result object.
20. Import React event types and `ReactElement` explicitly where the global namespaces are used.
