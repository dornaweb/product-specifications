# Product Specifications for Woocommerce

With Product Specifications plugin you can easily create spec. tables for your products. you can create multiple tables so you can use them for different types of products.
Product Specifications is very light-weight and easy to customize.

Plugin is available at [Wordpress Repository](https://wordpress.org/plugins/product-specifications/) , There's also a live demo, you can [check it out](http://demos.dornaweb.com/specs/shop/)

### Available languages

English
Persian
Russian ( Thanks to Илья Китаев )

To contribute in translating this plugin contact me : info@dornaweb.com

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

### 0.5.0

- Added ability to choose title of specs tab
- Translation string fixed

- Appearance issue fixed

### 0.4.3

- Appearance issue fixed

### 0.4.2

- Bug: when selecting "OR" value would not be saved -> fixed

### 0.4.1

- Bug: Attributes not being loaded in product table fixed

### 0.4.0

- Changed Modal Library
- Faster modal Experience
- Added Ability to import/export tables data in JSON format
- Plugin Classes are now loaded with PHP Autoloaders
- Ability to export and import product specs table data
- Ability to Override Specs Table template from your active theme
- Performance Improvement
- Other bug fixes

### 0.3.2

- Fixed Some CSS problems in admin area

### 0.3.1

- Added ability to disable Woocommerce's default information table
- Added default CSS stylesheet and ability to disable it
- Added ability to remove `true/false` fields from specific products
- Bug fix in group re-ordering
- Bug fix when product spec. table is empty and it's still shown on product page
- Some minor bug fixes
- Updated Persian translation
- Added Russian language ( Thanks to Илья Китаев )
