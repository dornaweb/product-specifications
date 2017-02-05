=== Product Specifications for Woocommerce ===
Contributors: dornaweb
Tags: product specifications,product specs,product specifications table,product attributes table,product attributes
Requires at least: 4
Tested up to: 4.7
Stable tag: 0.2
License: GNU GPL V2. or later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html

This plugin adds a product specifications table to your woocommerce single-product page.

== Description ==
With Product Specifications plugin you can easily create spec. tables for your products. you can create multiple tables so you can use them for different types of products.
Product Specifications is very light-weight and easy to customize.

Available languages :
English
Persian

To contribute in translating this plugin contact me : info@dornaweb.com

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
The table is under a div with `.dwspecs-product-table` CSS class and you can customize it in your CSS files.

= Is there a shortcode i can use to display the table =
Yes, You can use `[specs-table]` shortcode.


= How can i fully customize the HTML markup of the table =
Copy the file `/inc/views/shortcode-table-view.php` file somewhere in your theme and add the below code to your functions.php file

`add_filter('dw_specs_table_shortcode_output', 'my_custom_spec_table_markup', 10, 2 );
function my_custom_spec_table_markup( $output, $args ){
     ob_start();
     extract( $args );
     include( 'PATH_TO_COPIED_FILE');
     return ob_get_clean();
}`


== Changelog ==

= 0.2 =
Some bugs fixed