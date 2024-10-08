# Product Specifications for Woocommerce

With Product Specifications plugin you can easily create spec. tables for your products. you can create multiple tables so you can use them for different types of products.
Product Specifications is very light-weight and easy to customize.

Plugin is available at [Wordpress Repository](https://wordpress.org/plugins/product-specifications/) , There's also a live demo, you can [check it out](https://tastewp.com/plugins/product-specifications/)

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

Copy the file `/src/views/shortcode-table-view.php` file somewhere in your theme and add the below code to your functions.php file

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

Donations are happily appreciated, if you find this work useful, you can donate any amount via one of the following ways:

- [Donate via Paypal](https://www.paypal.com/donate/?hosted_button_id=W7GUT6CFS5PVA)
- Cryptocurrency: `0x90B0f45e24C6942594Ad0a6cfcf23860F83623bA` (Ethereum network)

<a href="https://www.buymeacoffee.com/amiut" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" style="height: 60px !important;width: 217px !important;" ></a>

## Changelog
[See changelog for all versions](https://raw.githubusercontent.com/dornaweb/product-specifications/main/changelog.txt).
