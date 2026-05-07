const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );
const WooDependencyExtractionWebpackPlugin = require( '@woocommerce/dependency-extraction-webpack-plugin' );

const NODE_ENV = process.env.NODE_ENV || 'development';
const isHot = process.argv.includes( '--hot' );
const isProduction = NODE_ENV === 'production';

/** @type {typeof defaultConfig} */
const webpackConfig = {
	...defaultConfig,
	entry: {
		admin: './client/admin/index.js',
		admininit: './client/admin/init.ts',
		frontend: './client/frontend/index.js',
	},
	plugins: [
		...defaultConfig.plugins,
		new WooDependencyExtractionWebpackPlugin(),
	],
	output: {
		filename: '[name].js',
		path: path.resolve( process.cwd(), 'assets' ),
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

module.exports = webpackConfig;
