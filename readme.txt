=== Product Specifications for Woocommerce ===

Contributors: dornaweb, pelentak
Tags: product specifications,product specs,product specifications table,product attributes table,product attributes, woocommerce, woocommerce product specifications, product details, product information, product table
Requires at least: 5.1
Tested up to: 6.2
WC tested up to: 7.5.1
Stable tag: 0.7.1
Requires PHP: 5.6
License: GNU GPL V2. or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html

This plugin adds a product specifications table to your woocommerce single-product page.

== Description ==

With Product Specifications plugin you can easily create spec. tables for your products. you can create multiple tables so you can use them for different types of products.
Product Specifications is very light-weight and easy to customize.

Translation :
To contribute in translating this plugin please visit: [Wordpress Translation Repository](https://translate.wordpress.org/projects/wp-plugins/product-specifications/)

= Development =
Product specifications is also available at [github](https://github.com/dornaweb/product-specifications/ "Product specifications plugin"), Feel free to contribute.

== Donate this plugin: ==
If this plugin has been handy to you, you can keep up the work by donating to below address:

(Any Ethereum, BSC, FTM based tokens are welcome)
`0x90B0f45e24C6942594Ad0a6cfcf23860F83623bA`

(Any TRC20 or Tron network tokens)
`TA6NCxJ97WsCDmUVEEvsVz1PW4kAVza295`


== Installation ==

1. Upload plugin to  "wp-content/plugins/" directory
2. Activate it in plugins section.

== Frequently Asked Questions ==

= How do i use the plugin? =
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


= How can i fully customize the HTML markup of the table =
Copy the file `/src/views/shortcode-table-view.php` file somewhere in your theme and add the below code to your functions.php file

`add_filter('dw_specs_table_shortcode_output', 'my_custom_spec_table_markup', 10, 2 );
function my_custom_spec_table_markup( $output, $args ){
	ob_start();
	extract( $args );
	include( 'PATH_TO_COPIED_FILE');
	return ob_get_clean();
}`

== Screenshots ==

1. Product specs table on product single page
2. Add new product page, where you fill information about the product
3. Add new product page, where you fill information about the product
4. Table attribute groups page
5. Product specs with tabs on the left
6. Right-to-left languages support


== Changelog ==

== 0.7.4 2024-03-24 ==
* Fix wrong filepath.

== 0.7.3 2024-02-21 ==
* Fix wrong filepath.

== 0.7.2 2023-06-20 ==
* Fix wrong filepath.

== 0.7.1 2023-04-05 ==
* Add full changelog.
* Tested with latest WP and WC versions.

== 0.7.0 2023-04-05 ==
* Santize/Escape all outputs in the plugin to prevent possible security issues. [#20](https://github.com/dornaweb/product-specifications/pull/20)

== 0.6.0 ==
* Fixed issue with deselecting tables
* Migrate translation to translate.wordpress.org

== 0.5.3 ==
* Fixed incompability with WordPress 6.1

[See changelog for all versions](https://raw.githubusercontent.com/dornaweb/product-specifications/main/changelog.txt).
