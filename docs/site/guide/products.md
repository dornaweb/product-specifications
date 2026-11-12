# Product values

You fill in a product's specifications on its edit screen, in the **Product
specifications** panel.

![The Product specifications panel](/images/admin-product-panel.png)

## Assign a set

Click **Assign a specification set** and choose one. The panel shows the set's groups on
the left, with how many specifications of each group have a value, and the inputs for the
selected group on the right.

## Enter values

Each specification gets the input its [type](./types) needs: a text field, a list of
options, a number with a unit, a date picker, a media picker for documents, and so on.

- A specification with a **default** offers it as a suggestion. It is stored only once you
  save the product.
- A [WooCommerce product data](./types#woocommerce-product-data) specification has no
  input. It shows where its value is read from, such as *Read from the product's Weight*.
- Values are checked against the specification's settings. A value that does not fit,
  such as a number above its maximum, stops the product from saving and shows why under
  the field.

Click the product's **Update** button to save. Specification values are saved with the
product, not on their own.

## Change or remove the set

Click **Change** next to the set name.

- **Switching to another set** keeps your values. Values the two sets share stay in place,
  and values used only by the old set are kept and return if you switch back.
- **Removing the set** deletes the product's specification values when the product is
  saved. The panel tells you how many values will be deleted before you save, and nothing
  is deleted if you leave without saving.

## Values that cannot be read

If a specification's type comes from another plugin that is inactive, its value is kept
as it was stored and shown as a raw value until you replace it or reactivate the plugin.
