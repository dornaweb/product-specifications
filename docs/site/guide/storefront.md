# On your storefront

A product with a published set and at least one value shows a specifications table. The
table has one heading per group and one row per specification that has a value. Rows
without a value, and groups or specifications hidden in the set, are left out.

![The specifications table on a product page](/images/storefront-table.png)

The product tab, the block and the shortcode all render the same table.

## The product tab

The table appears in a **Specifications** tab on the product page. Change the tab's title
under **WooCommerce → Product Specifications → Settings**.

## The block

In the block editor, add the **Product Specifications** block from the WooCommerce
category. On a single product template it shows the current product's table. Anywhere
else, choose the product in the block's settings.

## The shortcode

```text
[specs-table]
```

Shows the current product's table. To show another product's table, pass its ID:

```text
[specs-table post_id="123"]
```

## Settings

![The Settings screen](/images/admin-settings.png)

| Setting | What it does |
| --- | --- |
| **Specifications tab title** | The heading of the specifications tab. Leave it empty to use *Specifications*. |
| **WooCommerce's own "Additional information" tab** | WooCommerce shows weight, dimensions and attributes in its own tab. Remove it when the product has specifications (the default), always remove it, or always keep it. |
| **Plugin stylesheet** | The plugin's styles for the table. Turn it off if your theme styles the table itself. |

## Styling the table

The table is wrapped in `.ps-product-specifications`. Its parts:

| Class | Element |
| --- | --- |
| `.ps-product-specifications__table` | The table |
| `.ps-product-specifications__group` | One group's rows |
| `.ps-product-specifications__heading` | A group's heading row |
| `.ps-product-specifications__row` | One specification's row |
| `.ps-product-specifications__name` | A specification's name |
| `.ps-product-specifications__value` | A specification's value |

Developers can change what the table holds before it renders. See
[Hooks](../developers/hooks).
