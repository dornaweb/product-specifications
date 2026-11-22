=== Product Specifications & Comparison for WooCommerce ===

Contributors: dornaweb, pelentak, desperadohouse, mehdiraized
Tags: woocommerce, product specifications, product comparison, compare products, specifications table
Requires at least: 7.0
Tested up to: 7.1
Stable tag: 2.1.0
Requires PHP: 8.1
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
Donate link: https://www.paypal.com/donate/?hosted_button_id=W7GUT6CFS5PVA

Group product specifications into sets for each kind of product, show them in a table, and let customers compare products side by side.

== Description ==

When shoppers choose between similar products, they look at the details: screen size, weight, battery life, warranty. **Product Specifications & Comparison for WooCommerce** puts those details in a clear specifications table on every product page, and lets shoppers compare products side by side.

You define each specification once, group specifications into a set for each kind of product, such as laptops, sofas or running shoes, and fill in the values on the product screen. The table and the comparison read the same values, so there is nothing to keep in sync.

[Try the live demo](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/dornaweb/product-specifications/develop/.wordpress-org/blueprints/blueprint.json). It opens a sample store with products and specifications right in your browser, using WordPress Playground. Nothing to install or sign up for.

= Upgrading from 0.x? Read this first =

**Version 2 stores specifications in a new format. Your specification tables stay hidden from the storefront until you run the migration.** Plan the update before you click it:

1. Check that your site runs WordPress 7.0, WooCommerce 9.2 and PHP 8.1 or newer.
2. Take a backup of your database. The migration changes data in place.
3. Update the plugin at a quiet time.
4. Go to WooCommerce > Product Specifications > Migration and click Start migration.
5. The migration runs in the background. You can leave the screen and come back later to see what it converted.

The [migration guide](https://github.com/dornaweb/product-specifications/blob/develop/docs/site/guide/upgrading.md) explains what changes and how to update custom code.

= Product specifications =

A specification here is more than a line of text. A number keeps its unit, such as 15.6 in or 2.1 kg, and can be a range such as 100–240 V. A datasheet becomes a download link, and a yes or no shows as a check mark. Details WooCommerce already knows, like SKU, weight, dimensions and brand, can sit in the same table without being typed twice.

The table shows in a Specifications tab on the product page. You can also place it with the Product Specifications block or the `[specs-table]` shortcode.

It suits stores whose shoppers decide on details: electronics, hardware and tools, auto parts, appliances, furniture, fashion, cosmetics, books and music.

= Product comparison =

A shopper clicks Compare on a product page and lands on a comparison table with that product. From there they add other products of the same kind, and every row lines up: the same specifications, in the same groups and order. A phone and a sofa never end up in the same table.

The whole comparison lives in the link, so shoppers can bookmark it or send it to someone. No account is needed. On a phone, each specification's name moves above its values, so three products still fit side by side.

You choose which kinds of products can be compared, where the Compare button goes, and how many products fit in one comparison. The Product Comparison and Compare Button blocks, and their shortcodes, let you put both anywhere.

= Why use specifications instead of product attributes? =

WooCommerce attributes are built for variations and filters. They hold plain text, sit in one flat list, and every value is a term. A specification has a type: a number with a unit, a range, a date, a datasheet download, a link or a yes or no. Specifications are grouped, ordered per set, and checked when the product is saved. And you can still show attribute values in the table when you need them.

= Features =

* **Specification library.** Define a specification once and use it in any number of sets. Search it, filter by type, and see which sets use it.
* **Specification types.** Text, paragraph, single select, yes or no, number, date, link, document and WooCommerce product data.
* **Numbers with units.** 17 unit kinds, such as length, weight, data size, voltage and power, or units you define. Ranges, decimal places, thousands separators, minimum and maximum.
* **WooCommerce product data.** Show weight, dimensions, SKU, GTIN, categories, tags, brands or any product attribute. They are read from the product each time, so they never go stale.
* **Documents.** Attach datasheets, manuals and certificates from the media library. Shoppers get a download link with the file type and size.
* **Set editor.** Drag specifications into groups, and create new ones without leaving the editor.
* **Quick product entry.** Every value gets the right input, with suggestions from your other products.
* **Product comparison.** A Compare button, a comparison page and a shareable link, turned on per set.
* **Display anywhere.** A product tab, blocks for block themes, and shortcodes for page builders and classic themes.
* **Replace the Additional information tab.** Hide WooCommerce's own tab when a product has specifications, or always.
* **Theme friendly.** Light markup with clear CSS classes. Turn off the plugin stylesheet and style it all in your theme.
* **Import and export.** Move specifications, sets and product values between stores as one file, with a preview before anything changes. Products are matched by SKU or slug, and WP-CLI commands are included.
* **Translation ready.** Every screen can be translated on translate.wordpress.org.
* **For developers.** A documented PHP API to read and write values, hooks to change the table and the comparison, and custom specification types.

= Documentation =

The [user guide](https://github.com/dornaweb/product-specifications/blob/develop/docs/site/README.md) covers every screen and specification type. The [developer documentation](https://github.com/dornaweb/product-specifications/blob/develop/docs/site/developers/index.md) covers the PHP API, hooks and custom types.

== Installation ==

1. Go to Plugins > Add New Plugin, search for "Product Specifications", then install and activate it. WooCommerce must be active.
2. Go to WooCommerce > Product Specifications > Specifications and add your specifications.
3. Go to Specification sets, add a set, and drag specifications into groups. Publish the set.
4. Edit a product, choose the set in the Product specifications panel, fill in the values and update the product.

The table now shows in the Specifications tab of the product page. New sets offer comparison, the plugin creates a Compare page for you, and the Compare button appears on product pages. Change any of that under Settings.

== Frequently Asked Questions ==

= How do I add a specifications table to a WooCommerce product? =
Create your specifications, add them to a published specification set, then choose the set in the product's Product specifications panel and fill in the values. See Installation above for the steps.

= How is this different from WooCommerce product attributes? =
Attributes are plain text terms meant for variations and filtering. Specifications have types, units and groups, are reused through sets, and are checked when the product is saved. You can still show attribute values in the table with the WooCommerce product data type.

= Can I show the table somewhere other than the product tab? =
Yes. Add the Product Specifications block in the block editor, or use the `[specs-table]` shortcode in any page builder or classic theme. `[specs-table post_id="123"]` shows another product's table.

= Can shoppers compare products? =
Yes. A Compare button on the product page opens the compare page, where shoppers add more products of the same kind and see them side by side. Turn comparison on or off for each set on its Comparison tab, and choose the compare page, the button's place and its label under Settings.

= Can shoppers compare products from different categories? =
They can compare any products that use the same specification set, whatever their category. Products from different sets can't share a comparison, because their rows would not line up.

= Does the comparison work on phones? =
Yes. On a narrow screen each specification's name moves above its values, so up to three products fit side by side without scrolling sideways.

= Can I put the Compare button on product cards or somewhere else? =
Yes. Add the Compare Button block anywhere, including product loops in block themes, or use the `[specs-compare-button]` shortcode. To stop the automatic button on product pages, choose "Don't add" under Settings.

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

= How do I change the look of the table and the comparison? =
Turn off the plugin stylesheet under Settings and style them in your theme. The table's CSS classes start with `ps-product-specifications`, and the comparison's with `ps-comparison`.

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

= 2.1.0 =
Updating from 0.x? 2.0 changes how specifications are stored. Tables are hidden until you run the migration under WooCommerce > Product Specifications. Take a backup and read the migration guide in the plugin description first.

= 2.0.1 =
Updating from 0.x? 2.0 changes how specifications are stored. Tables are hidden until you run the migration under WooCommerce > Product Specifications. Take a backup and read the migration guide in the plugin description first.

= 2.0.0 =
Updating from 0.x? 2.0 changes how specifications are stored. Tables are hidden until you run the migration under WooCommerce > Product Specifications. Take a backup and read the migration guide in the plugin description first.

== Changelog ==

= 2.1.0 - 2026-11-22 =
- New: Product comparison. A Compare button on the product page opens a compare page, where shoppers compare products of the same set side by side.
- New: A Comparison tab on each specification set. Existing sets don't offer comparison until you turn it on.
- New: Product Comparison and Compare Button blocks and shortcodes.
- Change: The plugin is now called Product Specifications & Comparison for WooCommerce.

= 2.0.1 - 2026-11-18 =
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
