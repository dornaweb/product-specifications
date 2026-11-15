# Hooks

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

## Product values

| Hook | Type | Arguments |
| --- | --- | --- |
| `product_specifications_values_saved` | action | `int $postId`, `array $newValues`, `array $oldValues` |
| `product_specifications_rebuild` | action | — |

`values_saved` fires after one product's values are written, from any entry point.
Bulk writes, such as an import or a data upgrade, do not fire it per product; they fire
`rebuild` once at the end. Anything derived from values listens to both.

Deleting a specification removes its key from every product's stored map, inside the
deletion for small counts and in the background above that. That removal is a storage
operation, not a save: it fires neither `values_saved` per product nor `rebuild`, because
no other specification's values change. Anything that holds data for a specification
removes it on WordPress's `delete_term` action for the `spec-attr` taxonomy. Anything
derived from values still ignores a key that has no specification.
