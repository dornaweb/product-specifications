# Extending Product Specifications

This page lists everything the plugin supports for themes, site code and add-ons. It is
the whole public surface. Classes, services, container ids, REST routes and markup not
listed here are internal and can change in any release.

## Booting

| Hook | Type | Arguments |
| --- | --- | --- |
| `product_specifications_loaded` | action | — |

Fires once the plugin has booted successfully. It does not fire when the plugin failed
to start, so code that depends on the plugin hooks here rather than on `plugins_loaded`.

The running version is `\Amiut\ProductSpecs\plugin()->properties()->version()`. An
add-on that needs a minimum version checks it on this action.

## Reading specifications

Functions in the `Amiut\ProductSpecs` namespace. Each takes a product id or a
`WC_Product`.

| Function | Returns |
| --- | --- |
| `specificationsTable($product)` | The product's `SpecificationsTable`, as the storefront shows it |
| `hasSpecifications($product)` | Whether that table has anything in it |
| `specificationValue($product, $key)` | One specification's value as its type reads it, or `null` |
| `renderSpecificationsTable($product)` | The table's HTML |

`SpecificationsTable` holds `TableGroup`s, each holding `TableRow`s. Groups and rows
carry a `key()` that does not change when a merchant renames them; `label()` and
`name()` are the display text.

## Changing what the storefront shows

| Hook | Type | Arguments |
| --- | --- | --- |
| `product_specifications_table` | filter | `SpecificationsTable $table`, `WC_Product $product` |
| `render_block_product-specifications/product-specifications` | filter | WordPress core block filter |

`product_specifications_table` runs before anything renders: the product tab, the
block, the `[specs-table]` shortcode and `specificationsTable()` all see its result. Use
it to hide, reorder or add rows and groups. A row's `value()` is output as is, so a row
you build must hold escaped HTML. Returning anything other than a `SpecificationsTable`
leaves the table unchanged.

Every entry point renders through the block, so WordPress's `render_block_{name}`
filter is the place to wrap or replace the final HTML. There are no template overrides.

| Hook | Type | Arguments |
| --- | --- | --- |
| `product_specifications_load_styles` | filter | `bool $load` |

Whether the plugin's stylesheet loads. Defaults to the store's setting.

## Specification types

| Hook | Type | Arguments |
| --- | --- | --- |
| `product_specifications_register_types` | action | `SpecificationTypeRegistry $registry` |
| `product_specifications.types` | JS filter, `@wordpress/hooks` | the map of client type definitions |

A type implements `Specification\Type\SpecificationType` and is registered with
`$registry->register()`. Core registers its own types the same way. The JS filter adds
the type's admin controls; a type without them can be stored and rendered but not
configured in the admin.

`render()` returns output-ready HTML, and the type escapes everything inside it.

`value($product, $stored, $options)` is the value to present, computed on every read. A type
that reads it from the product ignores `$stored`, and registers a read-only value control on
the client so nothing is submitted for it.

`sanitize($input, $options)` turns input into the stored value, and `null` means empty.
`validate($input, $options)` returns the message for input the type rejects, or `null`.
Both receive the specification's options. `validate()` sees raw input, and only for a
value that differs from the stored one, so changing a specification's options never
blocks saving a product that keeps an old value.

A rejected value is never written. Saving a product in the admin fails as a whole and
shows each message under its field, and saving a specification refuses a rejected
default. A product value equal to its specification's default is always accepted.
Imports and data upgrades are not validated: they sanitize what they carry.

## Product values

| Hook | Type | Arguments |
| --- | --- | --- |
| `product_specifications_values_saved` | action | `int $postId`, `array $newValues`, `array $oldValues` |
| `product_specifications_rebuild` | action | — |

`values_saved` fires after one product's values are written, from any entry point.
Bulk writes, such as an import or a data upgrade, do not fire it per product; they fire
`rebuild` once at the end. Anything derived from values listens to both.

Deleting a specification does not touch products. Its values stay in each product's
stored map until that product is next saved. The storefront, the functions above and
exports never show them. Anything derived from values ignores a key that has no
specification.

## Import and export

| Hook | Type | Arguments |
| --- | --- | --- |
| `product_specifications_register_package_sections` | action | `SectionRegistry $registry` |

A section implements `ImportExport\Section` and carries its own data in the package.
`ImportPlan` and `EntryOutcome` are part of that contract.

## Access

| Hook | Type | Arguments |
| --- | --- | --- |
| `product_specifications_capability` | filter | `string $capability` |

The capability that manages specifications and sets. Defaults to `manage_woocommerce`.

## Storing your own data

Keep it in your own post meta, term meta or options. Do not add keys to the plugin's
stored documents: each is validated against its own schema, and a key it does not
define can be dropped on the next save.
