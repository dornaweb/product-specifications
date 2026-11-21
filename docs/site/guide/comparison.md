# Product comparison

Shoppers compare products of one specification set side by side. They start on a product
page, click **Compare**, and add more products from the compare page.

## How it works for shoppers

1. On a product page, the shopper clicks **Compare**.
2. The compare page opens with that product. The link holds the comparison, for example
   `/compare/?compare=12`.
3. **Add product**, above the table, searches the other products of the same set. Picking
   one adds it to the link.
4. Each product's **×** removes it from the comparison.

The link is the whole comparison, so a shopper can bookmark or share it. Nothing is stored
for the shopper.

Only products of the first product's set can be compared with each other, so every column
shows the same specifications in the same groups. Products of another set are left out, and
so are hidden, password-protected and unpublished products, variations, and out-of-stock
products when the store hides them.

## Turn comparison on for a set

Open the set under **WooCommerce → Product Specifications → Specification sets** and use the
**Comparison** tab. **Enable comparison** decides whether the set's products get a compare
button and can be compared.

A new set offers comparison. A set created before comparison existed doesn't until you turn
it on.

## The compare page

The plugin creates a **Compare** page holding the **Product Comparison** block and selects
it in the settings. You can edit the page like any other, or use another page: add the
**Product Comparison** block, or the shortcode in the classic editor:

```text
[specs-comparison]
```

then select that page under **Compare page** in the settings.

The compare page asks search engines not to index it and stays out of the WordPress
sitemap, because every link to it is a different comparison.

## The compare button

By default the button appears on the product page after the add-to-cart button. Choose
another place, or turn it off, with **Compare button on product pages** in the settings.

The button shows only when the product can be compared, its set is published and offers
comparison, and a published compare page is selected. A product that is out of stock or can't be bought has
no add-to-cart form, so in that position it has no button.

To place the button yourself, for example on product cards in a block theme, use the
**Compare Button** block. Inside a product loop it uses each product; elsewhere, choose a
product in the block's settings. In the classic editor, use the shortcode:

```text
[specs-compare-button]
```

Without `id` it uses the product being viewed. To show another product's button, pass its
ID:

```text
[specs-compare-button id="123"]
```

## Settings

Under **WooCommerce → Product Specifications → Settings → Comparison**:

| Setting | What it does |
| --- | --- |
| **Compare page** | The page that shows the comparison. |
| **Compare button on product pages** | After the add-to-cart button (the default), after the product meta, or not at all. |
| **Compare button label** | The button's text. The default label is *Compare*. |
| **Maximum comparable products** | The maximum number of products a shopper can compare, from 2 to 10. The default is 3. |

## Styling the comparison

The comparison is wrapped in `.ps-comparison`. Its parts:

| Class | Element |
| --- | --- |
| `.ps-comparison__add` | The **Add product** area above the table |
| `.ps-comparison__table` | The table |
| `.ps-comparison__product` | One product's column heading |
| `.ps-comparison__remove` | A product's **×** |
| `.ps-comparison__heading` | A group's heading row |
| `.ps-comparison__name` | A specification's name |
| `.ps-comparison__value` | One product's value |
| `.ps-compare-button` | The compare button |

On narrow screens each specification's name moves above its values, so the products share
the full width. Developers can change each product's heading; see
[Hooks](../developers/hooks.md).
