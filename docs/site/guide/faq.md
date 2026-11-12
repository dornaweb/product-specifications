# FAQ

## Where is the plugin in the admin?

Under **WooCommerce → Product Specifications**: Specification sets, Specifications,
Import / Export and Settings.

## Why does a product show no table?

Check, in order:

- The product has a specification set assigned, in its **Product specifications** panel.
- The set is published, not a draft.
- At least one specification in the set has a value for this product. Rows without a
  value are left out, and a table with no rows is not shown.
- On a store upgraded from 0.x, the [migration](./upgrading) has completed.

## Can one specification be used in several sets?

Yes. A specification is defined once and can be added to any number of sets. A
product's value for it is the same whichever of those sets the product uses.

## Do values from WooCommerce, such as weight, need to be typed again?

No. Use the [WooCommerce product data](./types#woocommerce-product-data) type. It reads
the value from the product each time the table is shown.

## Does the plugin work with variable products?

A variable product has one set of specifications, shown on its product page. Values per
variation are not supported.

## Can I show the table somewhere other than the product tab?

Yes, with the **Product Specifications** block or the `[specs-table]` shortcode. See
[On your storefront](./storefront).

## How do I move specifications to another store?

Export them under **Import / Export**, with or without product values, and import the
file on the other store. See [Import and export](./import-export).

## Who can manage specifications?

Users who can manage WooCommerce, such as administrators and shop managers.

## How do I translate the plugin?

The plugin is translated on
[translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/product-specifications/).
