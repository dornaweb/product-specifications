=== Product Specifications for WooCommerce ===

Contributors: dornaweb, pelentak, desperadohouse, mehdiraized
Tags: woocommerce, specifications, product attributes, product tabs, product data
Requires at least: 7.0
Tested up to: 7.1
Stable tag: 2.0.1
Requires PHP: 8.1
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
Donate link: https://www.paypal.com/donate/?hosted_button_id=W7GUT6CFS5PVA

Add a product specifications table to WooCommerce: technical specs with units, groups and reusable sets, in a product tab, block or shortcode.

== Description ==

**Product Specifications for WooCommerce** adds a clear, grouped specifications table to your product pages. Shoppers compare technical specs at a glance, and you enter each value once, with the right input for its type.

Define a specification once, such as *Screen size*, *Weight* or *Warranty*, and reuse it across products. Arrange specifications into a specification set for each kind of product, such as laptops, shoes or furniture, with groups like *Display*, *Dimensions* or *Materials*. On the product screen, pick the set and fill in the values. The table appears in a Specifications tab on the product page.

Built for stores whose shoppers decide on details: electronics, hardware and tools, auto parts, appliances, furniture, fashion, cosmetics, books and music.

= Why use specifications instead of product attributes? =

WooCommerce attributes are built for variations and filters. They hold plain text, sit in one flat list, and every value is a term. A specification has a type: a number with a unit such as 15.6 in or 2.1 kg, a range such as 100–240 V, a date, a datasheet download, a link or a yes or no. Specifications are grouped, ordered per set, and checked when the product is saved. Values WooCommerce already has, such as SKU, weight, dimensions, brand and attributes, can be shown in the same table without typing them twice.

= Features =

* **Reusable specification library.** Define a specification once and use it in any number of sets. Search, filter by type, and see which sets use each one.
* **Specification types.** Text, paragraph, single select, yes or no, number, document, link, date and WooCommerce product data.
* **Numbers with units.** 17 unit kinds, such as length, weight, data size, voltage and power, or units you define. Ranges, decimal places, thousands separators, minimum and maximum.
* **WooCommerce product data.** Show weight, dimensions, SKU, GTIN, categories, tags, brands or any product attribute, read from the product each time, so it never goes stale.
* **Documents.** Attach datasheets, manuals, safety sheets and certificates from the media library. Shoppers see a download link with the file type and size.
* **Drag-and-drop set editor.** Build groups by dragging specifications from the library, create new ones inline, and hide items per set.
* **Fast product entry.** Each value gets the control its type needs, text suggests values already used on your products, and defaults are offered as a starting value.
* **Display anywhere.** A product tab, a Product Specifications block for block themes, and the `[specs-table]` shortcode for page builders and classic themes.
* **Replace the Additional information tab.** Hide WooCommerce's own tab when a product has specifications, or always.
* **Theme friendly.** Lightweight markup with clear CSS classes, or turn off the plugin stylesheet and style the table in your theme.
* **Import and export.** Move specifications, sets and product values between stores as one file, with a preview before anything changes. Products are matched by SKU or slug. WP-CLI commands included.
* **For developers.** A documented PHP API to read and write values, hooks to change the table, and custom specification types.
* **Compatible with** WooCommerce High-Performance Order Storage (HPOS) and translation-ready.

= Upgrading from 0.x =
Version 2.0.0 stores specifications differently. Your tables are hidden from the storefront until you run the migration.

1. Check that your site runs WordPress 7.0, WooCommerce 9.2 and PHP 8.1 or newer.
2. Take a backup of your database. The migration changes data in place.
3. Update the plugin at a quiet time.
4. Go to WooCommerce > Product Specifications > Migration and click Start migration.
5. The migration runs in the background. You can leave the screen and come back later to see what it converted.

The full [migration guide](https://github.com/dornaweb/product-specifications/blob/develop/docs/site/guide/upgrading.md) explains what changes and how to update custom code.

= Features =

* A reusable library of specifications, each usable in any number of sets.
* Specification types: text, number with units and ranges, single select, document, link, date, and data WooCommerce already holds, such as weight, dimensions, SKU, categories or attributes.
* A drag-and-drop set editor with groups.
* A product panel that enters each value with the control its type needs.
* The table in a product tab, in a block, or anywhere with the `[specs-table]` shortcode.
* Import and export of specifications, sets and product values between stores, also from WP-CLI.
* A PHP API, hooks and custom specification types for developers.

= Documentation =
The [user guide](https://github.com/dornaweb/product-specifications/blob/develop/docs/site/README.md) covers every screen and specification type. The [developer documentation](https://github.com/dornaweb/product-specifications/blob/develop/docs/site/developers/index.md) covers the PHP API, hooks and custom types.

= Translation =
Help translate Product Specifications into your language on [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/product-specifications/).

= Development =
Development happens on [GitHub](https://github.com/dornaweb/product-specifications/). Bug reports and pull requests are welcome.

== Installation ==

1. Go to Plugins > Add New Plugin, search for "Product Specifications", then install and activate it. WooCommerce must be active.
2. Go to WooCommerce > Product Specifications > Specifications and add your specifications.
3. Go to Specification sets, add a set, and drag specifications into groups. Publish the set.
4. Edit a product, choose the set in the Product specifications panel, fill in the values and update the product.

The table now shows in the Specifications tab of the product page.

== Frequently Asked Questions ==

= How do I add a specifications table to a WooCommerce product? =
Create your specifications, add them to a published specification set, then choose the set in the product's Product specifications panel and fill in the values. See Installation above for the steps.

= How is this different from WooCommerce product attributes? =
Attributes are plain text terms meant for variations and filtering. Specifications have types, units and groups, are reused through sets, and are checked when the product is saved. You can still show attribute values in the table with the WooCommerce product data type.

= Can I show the table somewhere other than the product tab? =
Yes. Add the Product Specifications block in the block editor, or use the `[specs-table]` shortcode in any page builder or classic theme. `[specs-table post_id="123"]` shows another product's table.

= Does it work with my theme and page builder? =
The product tab works with any theme that shows WooCommerce product tabs. Block themes can place the block in the single product template, and page builders such as Elementor can use the shortcode.

= Can I hide WooCommerce's Additional information tab? =
Yes. Under Settings, remove it when a product has specifications (the default), always remove it, or always keep it.

= Do I need to type weight, dimensions or SKU again? =
No. Use the WooCommerce product data type. It reads the value from the product each time the table is shown.

= Can I add datasheets, manuals or certificates? =
Yes. The Document type attaches files from the media library, such as PDF datasheets. Shoppers see a download link with the file type and size.

= Can one specification be used in several sets? =
Yes. A specification is defined once and can be added to any number of sets. A product keeps its value for it whichever of those sets it uses.

= Does it work with variable products? =
A variable product has one set of specifications, shown on its product page. Values per variation are not supported.

= Why does a product show no table? =
Check that the product has a set assigned, that the set is published, and that at least one specification has a value. Rows without a value are left out. On a store updated from 0.x, the migration must have finished.

= Can I move specifications to another store? =
Yes. Under Import / Export, export specifications, sets and optionally product values as one file and import it on the other store. You see a preview before anything changes, and products are matched by SKU or slug. WP-CLI commands do the same from the command line.

= How do I change the look of the table? =
Turn off the plugin stylesheet under Settings and style the table in your theme. Its CSS classes start with `ps-product-specifications`.

= Who can manage specifications? =
Users who can manage WooCommerce, such as administrators and shop managers.

= Is it compatible with HPOS? =
Yes. The plugin declares compatibility with WooCommerce High-Performance Order Storage.

= I updated from 0.x and my tables are gone. =
Run the migration under WooCommerce > Product Specifications. Tables are hidden until it finishes. See the [migration guide](https://github.com/dornaweb/product-specifications/blob/develop/docs/site/guide/upgrading.md).

= Can developers read and write specifications in code? =
Yes. The plugin has a documented PHP API, hooks to change the table before it renders, and a way to register custom specification types. See the [developer documentation](https://github.com/dornaweb/product-specifications/blob/develop/docs/site/developers/index.md).

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

= 2.0.1 - 2026-11-18 =
- Translators: The 0.x strings are gone, and about 560 of the 574 strings are new. "Attribute" is now "specification" and "table" is now "specification set". "Attribute" remains only for WooCommerce product attributes.
- Fix: Two error messages on the Settings screen can now be translated.
- Fix: The warning shown when deleting a set that products use translates correctly in every language.
- Change: A rewritten plugin description, FAQ and screenshots.

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
