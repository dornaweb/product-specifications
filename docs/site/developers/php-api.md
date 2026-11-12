# PHP API

Functions in the `Amiut\ProductSpecs` namespace. They are defined only once the plugin
has booted, so guard calls with `function_exists()` or call them from
`product_specifications_loaded` onward.

## Reading specifications

Each function takes a product id or a `WC_Product`.

| Function | Returns |
| --- | --- |
| `specificationsTable($product)` | The product's `SpecificationsTable`, as the storefront shows it |
| `hasSpecifications($product)` | Whether that table has anything in it |
| `specificationValue($product, $key)` | One specification's value as its type reads it, or `null` |
| `renderSpecificationsTable($product)` | The table's HTML |

`SpecificationsTable` holds `TableGroup`s, each holding `TableRow`s. Groups and rows
carry a `key()` that does not change when a merchant renames them; `label()` and
`name()` are the display text.

```php
use function Amiut\ProductSpecs\specificationValue;

$touchscreen = specificationValue($product, 'touchscreen'); // true, false or null
```

## Writing specifications

Build a `ProductValues\SpecificationsUpdate`, then save it with `saveSpecifications()`:

```php
use Amiut\ProductSpecs\ProductValues\SpecificationsUpdate;
use function Amiut\ProductSpecs\saveSpecifications;

$saved = saveSpecifications(
    SpecificationsUpdate::forProduct($product)
        ->withSet('laptops')
        ->withValues(['weight' => 1.4, 'wifi' => true])
);
```

| Method | Does |
| --- | --- |
| `forProduct($product)` | Starts an update for a product or variation, by id or `WC_Product` |
| `withSet($slug)` | Assigns that specification set |
| `withoutSet()` | Unassigns the set, which deletes every value of the product |
| `withValues($values)` | Sets values, keyed by specification key, in the form each type sanitizes. A later call overrides a key given earlier |

An update is the product's whole value map, as a save in the product editor is: a
specification it does not carry is removed. Without `withSet()` or `withoutSet()` the
product keeps its assigned set. `saveSpecifications()` returns the stored value map.

Values are validated as in the product editor. Nothing is written when the save fails:

- `ProductValues\RejectedValues` when a type rejects a value. `messages()` holds each
  message, keyed by specification.
- `RuntimeException` when the product is not a product or variation, the set does not
  exist, the update carries values but leaves the product without a set, the migration
  is not complete, or storage fails.

A save fires [`product_specifications_values_saved`](./hooks#product-values), as a save in
the product editor does. The function checks no capability; the calling code decides who
may write.
