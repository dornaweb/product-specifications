# Getting started

Product Specifications adds a specifications table to your WooCommerce products. You
define your specifications once, arrange them into sets, and fill in the values on each
product.

![The specifications table on a product page](/images/storefront-table.png)

## Requirements

- WordPress 7.0 or newer
- WooCommerce 9.2 or newer
- PHP 8.1 or newer

## Install

1. In your WordPress admin, go to **Plugins → Add New Plugin** and search for
   "Product Specifications".
2. Install and activate it.

Everything the plugin adds lives under **WooCommerce → Product Specifications**.

::: tip Upgrading from version 0.x?
Version 2.0.0 stores specifications differently, and your tables are hidden until you
migrate them. Read [Upgrading from 0.x](./upgrading) before you update.
:::

## Your first table

The model has three parts:

| Part | What it is |
| --- | --- |
| **Specification** | One thing you describe, such as *Processor* or *Weight*, with a type that decides how it is entered and shown. |
| **Specification set** | The specifications for one kind of product, arranged into groups, such as *Laptops*. |
| **Product values** | What a product has for each specification in its set, such as *Intel Core Ultra 7*. |

### 1. Create specifications

Go to **WooCommerce → Product Specifications → Specifications** and click **Add
specification**. Choose a type, give it a name, and save. Use **Save and add another**
to add several in a row. See [Specifications](./specifications) and
[Specification types](./types).

### 2. Build a set

Go to **Specification sets** and click **Add set**. Give it a title, then open the
**Specifications** tab and drag specifications from the library into groups. Save the
set. See [Specification sets](./sets).

### 3. Fill in a product

Edit a product. In the **Product specifications** panel, choose the set, enter the values
and click the product's **Update** button. See [Product values](./products).

### 4. See it on your store

Open the product page. The table appears in a **Specifications** tab. You can also place
it anywhere with the block or the shortcode. See [On your storefront](./storefront).
