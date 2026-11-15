=== Product Specifications for Woocommerce ===

Contributors: dornaweb, pelentak, desperadohouse, mehdiraized
Tags: specifications, product specifications,specs,specifications table,product attributes
Requires at least: 7.0
Tested up to: 7.1
Stable tag: 0.8.11
Requires PHP: 8.1
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
Donate link: https://www.paypal.com/donate/?hosted_button_id=W7GUT6CFS5PVA

Add a specifications table to your WooCommerce products: define specifications once, group them into sets, and fill in each product.

== Description ==

Product Specifications adds a specifications table to your WooCommerce product pages. Define each specification once, arrange specifications into sets for each kind of product, and fill in the values on the product screen.

= Upgrading from 0.x =
Version 2.0.0 stores specifications differently. Your tables are hidden from the storefront until you run the migration.

1. Check that your site runs WordPress 7.0, WooCommerce 9.2 and PHP 8.1 or newer.
2. Take a backup of your database. The migration changes data in place.
3. Update the plugin at a quiet time.
4. Go to WooCommerce > Product Specifications > Migration and click Start migration.
5. The migration runs in the background. You can leave the screen and come back later to see what it converted.

The full [migration guide](https://github.com/dornaweb/product-specifications/blob/2.0.0/docs/site/guide/upgrading.md) explains what changes and how to update custom code.

= Features =

* A reusable library of specifications, each usable in any number of sets.
* Specification types: text, number with units and ranges, single select, document, link, date, and data WooCommerce already holds, such as weight, dimensions, SKU, categories or attributes.
* A drag-and-drop set editor with groups.
* A product panel that enters each value with the control its type needs.
* The table in a product tab, in a block, or anywhere with the `[specs-table]` shortcode.
* Import and export of specifications, sets and product values between stores, also from WP-CLI.
* A PHP API, hooks and custom specification types for developers.

= Documentation =
The user guide and developer documentation are in the [GitHub repository](https://github.com/dornaweb/product-specifications/blob/2.0.0/docs/site/README.md).

= Translation =
To contribute in translating this plugin please visit: [WordPress Translation Repository](https://translate.wordpress.org/projects/wp-plugins/product-specifications/)

= Development =
Product specifications development takes place under its [GitHub Repository](https://github.com/dornaweb/product-specifications/ "Product specifications plugin"), Contributions are more than welcome.

== Installation ==

1. Install and activate the plugin from Plugins > Add New Plugin.
2. Go to WooCommerce > Product Specifications.

== Frequently Asked Questions ==

= How do I use the plugin? =
* Go to WooCommerce > Product Specifications > Specifications and add your specifications.
* Go to Specification sets, add a set, and drag specifications into groups.
* Edit a product, choose the set in the Product specifications panel, fill in the values and update the product.

= I updated from 0.x and my tables are gone. =
Run the migration under WooCommerce > Product Specifications. Tables are hidden until it finishes. See the [migration guide](https://github.com/dornaweb/product-specifications/blob/2.0.0/docs/site/guide/upgrading.md).

= How can I customize the styles of the table? =
Turn off the plugin stylesheet under WooCommerce > Product Specifications > Settings and style the table in your theme. The table's classes start with `ps-product-specifications`.

= Is there a shortcode I can use to display the table? =
Yes, use `[specs-table]`, or `[specs-table post_id="123"]` for another product. There is also a Product Specifications block.

== Screenshots ==

1. Product specs table on product single page
2. Add new product page, where you fill information about the product
3. Add new product page, where you fill information about the product
4. Table attribute groups page
5. Product specs with tabs on the left
6. Right-to-left languages support


== Upgrade Notice ==

= 2.0.0 =
2.0.0 changes how specifications are stored. Tables are hidden until you run the migration under WooCommerce > Product Specifications. Take a backup and read the migration guide in the plugin description first.

== Changelog ==

= 2.0.0 =
- Rebuilt plugin: a specification library, specification sets with groups, and a new product panel.
- New specification types: number, document, link, date and WooCommerce product data.
- New Product Specifications block, import and export, WP-CLI commands and a PHP API.
- Requires PHP 8.1. Existing data is converted by a migration you start after updating.

= 0.8.11 - 2026-11-15 =
- Maintenance: Warn on the plugin update row that version 2.0.0 changes how specifications are stored.
- Maintenance: Test and bump compatibility versions with latest WooCommerce and WordPress versions.

= 0.8.10 - 2026-06-20 =
- Fix: security issue in attribute/group CRUD.
- Fix: Possible PHP notice.
- Maintenance: Add wp-env configuration to plugin's VCS.
- Maintenance: Test and bump compatibility versions with latest WooCommerce and WordPress versions.

= 0.8.9 - 2026-05-12 =
- Fix: issue in php scoper.

= 0.8.8 - 2026-05-12 =
- Test and bump compatibility versions with latest WooCommerce and WordPress versions.

= 0.8.7 - 2024-11-12 =
- Add compatibility with HPOS order storage.

= 0.8.6 - 2024-11-06 =
- Fix: Group names are displayed as encoded strings.
- Fix: PHP notice.
- Maintenance: Ensure compatibility with latest WordPress and WooCommerce versions.

= 0.8.5 - 2024-11-05 =
- Fix possible fatal error in import/export tool.
- Fix issue with multiline textarea field.
- Maintenance: Add Github issue and Pull request templates.

= 0.8.4 - 2024-09-24 =
- Fix broken "select" and "radio" field types.

[See changelog for all versions](https://raw.githubusercontent.com/dornaweb/product-specifications/main/changelog.txt).
