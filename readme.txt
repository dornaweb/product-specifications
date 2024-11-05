=== Product Specifications for Woocommerce ===

Contributors: dornaweb, pelentak, desperadohouse, mehdiraized
Tags: specifications, product specifications,specs,specifications table,product attributes
Requires at least: 5.9
Tested up to: 6.6.2
WC tested up to: 9.3.2
WC requires at least: 8.0.0
Stable tag: 0.8.4
Requires PHP: 7.4
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
Donate link: https://www.paypal.com/donate/?hosted_button_id=W7GUT6CFS5PVA

This plugin adds a product specifications table to your woocommerce single-product page.

== Description ==

With Product Specifications plugin you can easily create spec. tables for your products. you can create multiple tables so you can use them for different types of products.
Product Specifications is very light-weight and easy to customize.

= Translation =
To contribute in translating this plugin please visit: [Wordpress Translation Repository](https://translate.wordpress.org/projects/wp-plugins/product-specifications/)

= Development =
Product specifications development takes place under its [Github Repository](https://github.com/dornaweb/product-specifications/ "Product specifications plugin"), Contributions are more than welcome.

== Installation ==

1. Upload plugin to  "wp-content/plugins/" directory
2. Activate it in plugins section.

== Frequently Asked Questions ==

= How do I use the plugin? =
* Install and activate the plugin
* Go to specification tables -> groups and define some Groups
* Go to specification tables -> attributes and define some attributes associated with groups defined in previous step
* Go to specification tables -> add new to add a new table for a specific type of product ( eg. for mobile phones )
* Go to "Products -> add new" under the specifications tables section you see a dropdown which you can select which table should load.
* Select the table and fill product data in it. That's it.

= How can i Customize styles of tables =
By default, The plugin adds its own styles to the tables, you can however disable plugin's stylesheets in the settings section and style the tables on your own.
The specification table is under a div with `.dwspecs-product-table` CSS class and you can customize it in your CSS files.

= Is there a shortcode i can use to display the table =
Yes, You can use `[specs-table]` shortcode.

== Screenshots ==

1. Product specs table on product single page
2. Add new product page, where you fill information about the product
3. Add new product page, where you fill information about the product
4. Table attribute groups page
5. Product specs with tabs on the left
6. Right-to-left languages support


== Changelog ==

= 0.8.5 - 2024-11-05 =
- Fix possible fatal error in import/export tool.
- Fix issue with multiline textarea field.
- Maintenance: Add Github issue and Pull request templates.

= 0.8.4 - 2024-09-24 =
- Fix broken "select" and "radio" field types.

= 0.8.3 - 2024-09-22 =
- Fix fatal error in order edit page.
- Fix wrong minimum PHP version requirement (PHP 7.4+ is supported).
- Ensure compatibility with latest WordPress and WooCommerce versions.

= 0.8.2 - 2024-08-29 =
- Minor security improvements.
- Include composer.json in releases.

[See changelog for all versions](https://raw.githubusercontent.com/dornaweb/product-specifications/main/changelog.txt).
