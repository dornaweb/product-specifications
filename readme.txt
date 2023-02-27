=== Product Specifications for Woocommerce ===

Contributors: dornaweb
Tags: product specifications,product specs,product specifications table,product attributes table,product attributes, woocommerce, woocommerce product specifications, product details, product information, product table
Requires at least: 5.1
Tested up to: 6.1
Stable tag: 0.5.3
Requires PHP: 5.6
License: GNU GPL V2. or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html

This plugin adds a product specifications table to your woocommerce single-product page.

== Description ==

With Product Specifications plugin you can easily create spec. tables for your products. you can create multiple tables so you can use them for different types of products.
Product Specifications is very light-weight and easy to customize.

Available languages :
English
Persian
Russian ( Thanks to Илья Китаев )

To contribute in translating this plugin contact me : info@dornaweb.com

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

== 0.5.3 ==
* Fixed incompability with WordPress 6.1

== 0.5.2 ==
* Fixed incompability with php 8.0

== 0.5.1 ==
* Selectbox Saving issue fixed
* Selectbox disabled when Alternative option is set

== 0.5.0 ==
* Added ability to choose title of specs tab
* Translation string fixed

== 0.4.3 ==
* Bug: Appearance issue fixed

== 0.4.2 ==
* Bug: when selecting "OR" value would not be saved -> fixed

== 0.4.1 ==
* Bug: Attributes not being loaded in product table fixed

== 0.4.0 ==
* Changed Modal Library
* Faster modal Experience
* Added Ability to import/export tables data in JSON format
* Plugin Classes are now loaded with PHP Autoloaders
* Ability to export and import product specs table data
* Ability to Override Specs Table template from your active theme
* Performance Improvement
* Other bug fixes

= 0.3.2 =
* Fixed Some CSS problems in admin area

= 0.3.1 =
* Added ability to disable Woocommerce's default information table
* Added default CSS stylesheet and ability to disable it
* Added ability to remove `true/false` fields from specific products
* Bug fix in group re-ordering
* Bug fix when product spec. table is empty and it's still shown on product page
* Some minor bug fixes
* Updated Persian translation
* Added Russian language ( Thanks to Илья Китаев )
