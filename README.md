# Product Specifications for Woocommerce

With Product Specifications plugin you can easily create spec. tables for your products. you can create multiple tables so you can use them for different types of products.
Product Specifications is very light-weight and easy to customize.

Plugin is available at [Wordpress Repository](https://wordpress.org/plugins/product-specifications/) , There's also a live demo, you can [check it out](http://demos.dornaweb.com/specs/shop/)

### Translation

To contribute in translating this plugin please visit: [Wordpress Translation Repository](https://translate.wordpress.org/projects/wp-plugins/product-specifications/)

## Usage

### Basic uasge

- Install and activate the plugin
- Go to specification tables -> groups and define some Groups
- Go to specification tables -> attributes and define some attributes associated with groups defined in previous step
- Go to specification tables -> add new to add a new table for a specific type of product ( eg. for mobile phones )
- Go to "Products -> add new" under the specifications tables section you see a dropdown which you can select which table should load.
- Select the table and fill product data in it. That's it.

### CSS Customizations

By default, The plugin adds its own styles to the tables, you can however disable plugin's stylesheets in the settings section and style the tables on your own.
The specification table is under a div with `.dwspecs-product-table` CSS class and you can customize it in your CSS files.

### Shortcode

You can use `[specs-table]` shortcode to display specs table.

### How to fully customize the HTML markup of the table

Copy the file `/inc/views/shortcode-table-view.php` file somewhere in your theme and add the below code to your functions.php file

```
add_filter('dw_specs_table_shortcode_output', 'my_custom_spec_table_markup', 10, 2 );
function my_custom_spec_table_markup( $output, $args ){
	ob_start();
	extract( $args );
	include( 'PATH_TO_COPIED_FILE');
	return ob_get_clean();
}
```

## Donation

If this plugin has been handy to you, you can keep up the work by donating to below address:

`0x90B0f45e24C6942594Ad0a6cfcf23860F83623bA` (Any Ethereum, BSC, FTM based tokens are welcome)
`TA6NCxJ97WsCDmUVEEvsVz1PW4kAVza295` (Any TRC20 (Tron network) tokens)

## Changelog
### 0.5.3
* Fixed incompability with WordPress 6.1

### 0.5.2

- Fixed a php 8.0 incompability

### 0.5.1

- Selectbox Saving issue fixed
- Selectbox disabled when Alternative option is set

### 0.5.0

- Added ability to choose title of specs tab
- Translation string fixed

- Appearance issue fixed
