# Custom specification types

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

## Values that refer to the site

A type whose stored value refers to something local to the site, such as an attachment
id, implements `Specification\Type\PortableValue` as well:

| Method | Does |
| --- | --- |
| `exportValue($stored, $options)` | Turns the stored value into a form that means the same on another store |
| `importValue($exported, $options)` | Turns that form back into what this store stores, or `null` when nothing on this store matches |

An export carries the portable form, and an import turns it back.
