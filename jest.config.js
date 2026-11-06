const defaults = require( '@wordpress/scripts/config/jest-unit.config.js' );

module.exports = {
	...defaults,
	rootDir: __dirname,
	testMatch: [ '<rootDir>/client/**/*.test.[jt]s?(x)' ],
	moduleNameMapper: {
		...defaults.moduleNameMapper,
		'^@woocommerce/navigation$':
			'<rootDir>/tests/js/mocks/woocommerceNavigation.ts',
	},
};
