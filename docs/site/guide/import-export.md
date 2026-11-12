# Import and export

Move specifications, sets and product values between stores, or keep them as a backup.
Go to **WooCommerce → Product Specifications → Import / Export**.

![The Import / Export screen](/images/admin-import-export.png)

## Export

Click **Download export** to download every specification and specification set as one
JSON file.

Turn on **Include product values** to add each product's set and values. Each product is
identified by its SKU and its slug, so its values can be matched on another store.

## Import

1. Choose a file made by the export and click **Preview import**.
2. The preview lists, for specifications, sets and product values, what the import will
   do. Nothing has been changed at this point.
3. Click **Start import**. The import runs in the background, so you can leave the page
   and come back. If it stops, **Resume import** continues where it left off.
4. When it finishes, the report lists every entry that needs a look. **Download report**
   saves it.

### How entries are matched

| Entry | Matched by | When it exists |
| --- | --- | --- |
| Specification | Its key | Updated from the file |
| Specification set | Its slug | Updated from the file |
| Product values | The product's SKU, then its slug | The file's values are laid over the product's, and its set is assigned |

Variations are not matched.

### Outcomes

| Outcome | Meaning |
| --- | --- |
| **New** | Created on this store. |
| **Updated** | Changed to match the file. |
| **Unchanged** | Already the same. |
| **Type conflict** | A specification with the same key has a different type on this store. It is left as it is, and the file's set items and values for it are skipped. |
| **Skipped** | Left out, with the reason, such as *No product on this store matches it*. |
| **Failed** | Could not be saved. |

::: info
Import and export are available once the [migration](./upgrading) is complete.
:::

## From the command line

With [WP-CLI](https://wp-cli.org/):

```sh
# Export everything, with product values, to a dated file in the current directory
wp product-specifications export

# Export without product values, to a file of your choice
wp product-specifications export --without-values --file=specifications.json

# Show what an import would do, without changing anything
wp product-specifications import specifications.json --dry-run

# Import
wp product-specifications import specifications.json
```
