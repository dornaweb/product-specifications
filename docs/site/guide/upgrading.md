# Upgrading from 0.x

Version 2.0.0 stores specifications in a new format. Your existing specifications, tables
and product values are converted by a migration that you start yourself. **Until the
migration has run, your store shows no specification tables.**

This page explains what to do, in order.

## Before you update

1. **Check the requirements.** Version 2.0.0 needs WordPress 7.0, WooCommerce 9.2 and
   PHP 8.1 or newer. On older PHP, the plugin shows a notice instead of loading.
2. **Take a backup** of your database. The migration changes data in place, and a backup
   is the only way back.
3. **Pick a quiet time.** Tables are hidden from your storefront between the update and
   the end of the migration. On most stores the migration takes minutes.

## Run the migration

1. Update the plugin.
2. A notice appears across the admin: *Product Specifications needs to migrate your
   data.* Click **Start the migration**, or go to **WooCommerce → Product Specifications →
   Migration**.
3. The screen shows what will be converted: specifications, specification tables, and
   products with values. Click **Start migration**.

The migration never starts on its own. It runs in three steps:

1. Converting specifications
2. Converting tables into specification sets
3. Migrating product values

It runs in the background. You can leave the page, and come back any time to see where it
is.

### If it stops

The screen shows why it stopped. Nothing is lost: **Resume migration** continues from
where it stopped.

### When it finishes

Your specification tables are live on your storefront again. The summary lists anything
that could not be migrated; enter those specifications by hand. The summary is shown once,
and the Migration tab is removed after you continue.

The old data is kept in your database, unused.

### From the command line

On large stores, or when WP-Cron does not run reliably, run the migration with
[WP-CLI](https://wp-cli.org/):

```sh
# Run the migration to the end, resuming a run that stopped
wp product-specifications migration run

# Show the current run without changing anything
wp product-specifications migration status
```

## Until the migration has run

- Your storefront shows no specification tables.
- The product screen's specifications panel explains that the migration has not run.
  If you save specifications for a product before then, the migration skips that product
  and keeps what you saved.
- Import and export are unavailable.

## What changes

| In 0.x | In 2.0.0 |
| --- | --- |
| Attributes | **Specifications**, each with a [type](./types) |
| Groups, shared by every table | Groups that live inside each set |
| Tables | **[Specification sets](./sets)** |
| Select and Radio | One type, **Single select** |
| Admin screens under *Specs. tables* | Everything under **WooCommerce → Product Specifications** |
| CSS class `.dwspecs-product-table` | `.ps-product-specifications` (see [Styling the table](./storefront#styling-the-table)) |

The `[specs-table]` shortcode keeps working.

### For developers

The `dwspecs_*` helper functions and the `dwspecs_table_value_output` and
`dw_specs_table_shortcode_output` filters are gone. Read specifications with the
[PHP API](../developers/php-api), and change the table with the
[`product_specifications_table`](../developers/hooks#changing-what-the-storefront-shows)
filter.
