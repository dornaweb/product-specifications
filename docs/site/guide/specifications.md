# Specifications

A specification is one thing you describe about a product, such as *Processor*,
*Screen size* or *Warranty*. You define it once and use it in as many
[sets](./sets) as you like.

Specifications live under **WooCommerce → Product Specifications → Specifications**.

![The Specifications screen](/images/admin-specifications.png)

The list shows each specification's type and how many sets use it. Search by name, and
filter by type.

## Add a specification

Click **Add specification**, or press <kbd>N</kbd>.

![Adding a Number specification](/images/admin-specification-form.png)

| Field | What it does |
| --- | --- |
| **Type** | What kind of answer this records. It decides the input on the product screen and how the value renders. See [Specification types](./types). |
| **Name** | The label shown in the admin and on your storefront. Renaming later changes the label everywhere and touches no stored data. |
| **Default** | Offered as a suggestion when a product has no value yet. It is never stored until the product is saved, and never shown on your storefront on its own. |
| **Description** | Optional, for your own reference. |

Other fields depend on the type, such as the units of a Number or the options of a
Single select.

### Adding many in a row

**Save and add another** (<kbd>⌘</kbd>/<kbd>Ctrl</kbd> + <kbd>Enter</kbd>) saves and
opens a new, empty form, so you can enter a whole list from the keyboard. **Save and
close** (<kbd>⌘</kbd>/<kbd>Ctrl</kbd> + <kbd>S</kbd>) saves and closes the form, and
<kbd>Esc</kbd> cancels.

## The specification key

Every specification gets a key when it is created, shown under its name in the list,
such as `screen-size`. The key never changes, even when you rename the specification.
Developers use it to read values, and imports use it to match specifications between
stores.

## Changing a specification

Click **Edit** on a specification. A new name or default applies everywhere at once. A
value a product already has is kept when you change options such as the minimum of a
Number: the new rules apply the next time that value is changed.

Changing the **type** can make existing values unreadable, for example when a Text
specification becomes a Number. Those values stay stored and show as unreadable on the
product screen. Nothing is deleted, and changing the type back restores them.

## Deleting a specification

Before you delete a specification, the plugin shows which sets use it. Deleting removes
it from those sets. Its values are no longer shown anywhere, and they are removed from
each product the next time that product is saved. Deleting cannot be undone.
