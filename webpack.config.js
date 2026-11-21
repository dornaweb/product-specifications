const defaultConfigs = require( '@wordpress/scripts/config/webpack.config' );
const { getWebpackEntryPoints } = require( '@wordpress/scripts/utils' );
const path = require( 'path' );
const MiniCSSExtractPlugin = require( 'mini-css-extract-plugin' );
const WooDependencyExtractionWebpackPlugin = require( '@woocommerce/dependency-extraction-webpack-plugin' );
const WpDependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );

const NODE_ENV = process.env.NODE_ENV || 'development';
const isHot = process.argv.includes( '--hot' );
const isProduction = NODE_ENV === 'production';
const outputPath = path.resolve( process.cwd(), 'assets' );

// With --experimental-modules, wp-scripts builds scripts and script modules as
// two separate configurations.
const [ defaultConfig, defaultModuleConfig ] = Array.isArray( defaultConfigs )
	? defaultConfigs
	: [ defaultConfigs ];

/** @type {typeof defaultConfig} */
const webpackConfig = {
	...defaultConfig,
	entry: {
		// Every src/**/block.json lists its scripts; wp-scripts copies the
		// block.json and render.php next to them under the same relative path.
		...getWebpackEntryPoints( 'script' )(),
		admininit: './client/admin/init.ts',
		'product-values': './client/productValues/index.tsx',
	},
	plugins: [
		// The WooCommerce plugin extends the WordPress one; running both leaves
		// each with half the externals and one overwriting the other's asset file.
		...defaultConfig.plugins.flatMap( ( plugin ) => {
			if ( plugin instanceof WpDependencyExtractionWebpackPlugin ) {
				return [];
			}

			if ( plugin instanceof MiniCSSExtractPlugin ) {
				return [
					new MiniCSSExtractPlugin( {
						filename: '[name].css',
						chunkFilename: '[name].css?ver=[contenthash]',
					} ),
				];
			}

			return [ plugin ];
		} ),
		new WooDependencyExtractionWebpackPlugin(),
	],
	output: {
		filename: '[name].js',
		path: outputPath,
		chunkFilename: `chunks/[name].js?ver=[contenthash]`,
	},
	optimization: {
		...defaultConfig.optimization,
		runtimeChunk: false,
	},
};

if ( ! isProduction ) {
	webpackConfig.devtool = webpackConfig.devtool || 'source-map';

	if ( isHot ) {
		webpackConfig.devServer = {
			...webpackConfig.devServer,
			devMiddleware: {
				...webpackConfig.devServer?.devMiddleware,
				writeToDisk: true,
			},
			allowedHosts: 'auto',
			host: 'localhost',
			port: 8887,
			client: {
				webSocketURL: 'ws://localhost:8887/ws',
			},
			proxy: {
				...webpackConfig.devServer?.proxy,
				'/build': {
					pathRewrite: {
						'^/build': '',
					},
				},
			},
		};
	}
}

module.exports = defaultModuleConfig
	? [
			webpackConfig,
			{
				...defaultModuleConfig,
				output: { ...defaultModuleConfig.output, path: outputPath },
			},
	  ]
	: webpackConfig;
