# Developers

These pages list everything the plugin supports for themes, site code and add-ons. They
are the whole public surface. Classes, services, container ids, REST routes and markup not
listed here are internal and can change in any release.

| Page | Covers |
| --- | --- |
| [PHP API](./php-api) | Reading and writing a product's specifications |
| [Hooks](./hooks) | Changing what the storefront shows, and reacting to saved values |
| [Custom specification types](./types) | Adding a type of your own |
| [Import and export sections](./import-export) | Carrying your own data in export packages |

## Booting

| Hook | Type | Arguments |
| --- | --- | --- |
| `product_specifications_loaded` | action | — |

Fires once the plugin has booted successfully. It does not fire when the plugin failed
to start, so code that depends on the plugin hooks here rather than on `plugins_loaded`.

The running version is `\Amiut\ProductSpecs\plugin()->properties()->version()`. An
add-on that needs a minimum version checks it on this action.

## Access

| Hook | Type | Arguments |
| --- | --- | --- |
| `product_specifications_capability` | filter | `string $capability` |

The capability that manages specifications and sets. Defaults to `manage_woocommerce`.

## Storing your own data

Keep it in your own post meta, term meta or options. Do not add keys to the plugin's
stored documents: each is validated against its own schema, and a key it does not
define can be dropped on the next save.
