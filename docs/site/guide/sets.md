# Specification sets

A specification set holds the specifications for one kind of product, arranged into the
groups customers see on the product page. A laptop store might have a *Laptops* set with
*Performance*, *Display* and *Design* groups.

Sets live under **WooCommerce → Product Specifications → Specification sets**.

![The Specification sets screen](/images/admin-sets.png)

The list shows each set's status, how many specifications and groups it has, and how
many products use it.

## Create a set

Click **Add set**. The **General** tab holds:

| Field | What it does |
| --- | --- |
| **Title** | The set's name in the admin. |
| **Key / slug** | A permanent identifier, used by imports to match sets between stores. |
| **Description** | Optional, for your own reference. |
| **Status** | *Publish* to show the set's table on your storefront. A *Draft* set can be assigned and filled in, but its table stays hidden until it is published. |

## Arrange specifications

Open the **Specifications** tab.

![The set editor](/images/admin-set-editor.png)

- The **Specification library** on the left lists every specification. Search it,
  filter by type, and hide the ones already in the set.
- Drag a specification into a group, or use its menu to move it. Drop it below the last
  group to create a new group, or click **Add group**.
- Click a group's name to rename it, and drag groups and specifications to reorder them.
- **New specification** creates a specification without leaving the editor.
- A specification can be in a set once.

Click **Save set** when you are done. If you leave with unsaved changes, the editor
warns you first.

## Item options

Each specification in a set has **Options**:

- **Visible on the storefront** hides a specification on the product page while keeping
  it on the product screen. It applies to this set only: the same specification can be
  visible in one set and hidden in another.

A specification's default value lives on the specification itself, so it is the same in
every set.

## The same specification in several sets

Because specifications are shared, a value entered for *Warranty* is the same value
whichever set the product uses. Switching a product to another set keeps the values of
specifications the new set does not have, and they come back if you switch back.
