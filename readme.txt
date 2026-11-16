=== Product Specifications for WooCommerce ===

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

1. The specifications table on a product page, with text, number, yes or no, date and SKU values.
2. The Product specifications panel on the product screen. The SKU is read from the product.
3. The Specifications screen: every specification, its type and the sets that use it.
4. A WooCommerce product data specification shows a value the product already has, such as its SKU.
5. A Number specification with units, decimal places and bounds.
6. The set editor: drag specifications from the library into groups.
7. The Specification sets screen.
8. The Settings screen.

== Upgrade Notice ==

= 2.0.1 =
Updating from 0.x? 2.0 changes how specifications are stored. Tables are hidden until you run the migration under WooCommerce > Product Specifications. Take a backup and read the migration guide in the plugin description first.

= 2.0.0 =
2.0.0 changes how specifications are stored. Tables are hidden until you run the migration under WooCommerce > Product Specifications. Take a backup and read the migration guide in the plugin description first.

== Changelog ==

= 2.0.1 - Unreleased =
- Translators: The 0.x strings are gone, and about 560 of the 574 strings are new. "Attribute" is now "specification" and "table" is now "specification set". "Attribute" remains only for WooCommerce product attributes.

= 2.0.0 - 2026-11-15 =
Version 2.0.0 is a rewrite. Existing data is converted by a migration you start after updating. See "Upgrading from 0.x" above.

- BREAKING: Requires PHP 8.1 or newer (was 7.4).
- BREAKING: Requires WordPress 7.0 or newer (was 5.9).
- BREAKING: Requires WooCommerce 9.2 or newer (was 8.0).
- New: A reusable library of specifications, each usable in any number of sets, with a readable key.
- New: Specification types Number, Document, Link, Date and WooCommerce product data. Text specifications can suggest values and hold several values.
- New: A drag-and-drop editor for specification sets and their groups.
- New: A Product specifications panel on the product screen, with values checked before the product is saved.
- New: A Product Specifications block.
- New: Import and export of specifications, sets and product values between stores.
- New: A guided migration from 0.x that runs in the background.
- New: A PHP API, hooks, custom specification types, REST endpoints and WP-CLI commands for developers.
- Change: Tables are now specification sets, each with its own groups. Radio and Select are one Single select type.
- Removal: The old admin screens, the shared group taxonomy, and settings and templates that are no longer read.

= 0.8.11 - 2026-11-15 =
- Maintenance: Warn on the plugin update row that version 2.0.0 changes how specifications are stored.
- Maintenance: Test and bump compatibility versions with latest WooCommerce and WordPress versions.

= 0.8.10 - 2026-06-20 =
- Fix: security issue in attribute/group CRUD.
- Fix: Possible PHP notice.
- Maintenance: Add wp-env configuration to plugin's VCS.
- Maintenance: Test and bump compatibility versions with latest WooCommerce and WordPress versions.

[See the changelog for all versions](https://raw.githubusercontent.com/dornaweb/product-specifications/develop/changelog.txt).
