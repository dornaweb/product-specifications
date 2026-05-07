import wordpress from '@wordpress/eslint-plugin';

export default [
	...wordpress.configs.recommended,
	{
		ignores: [
			'assets/**',
			'build/**',
			'dist/**',
			'node_modules/**',
			'vendor/**',
			'coverage/**'
		],
	},
	{
		files: [ 'client/**/*.{js,jsx,ts,tsx}' ],
		rules: {
			// Project-specific overrides
		},
	},
];
