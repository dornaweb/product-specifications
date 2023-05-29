const baseConfig = require( '@wordpress/prettier-config' );

module.exports = {
    ...baseConfig,
    useTabs: false,
    parenSpacing: false,
    overrides: [
        {
            files: '*.{css,sass,scss}',
            options: {
                singleQuote: false,
                tabWidth: 2,
            },
        },
    ],
};
