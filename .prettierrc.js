const defaultConfig = require('@wordpress/prettier-config');

/** @type {typeof defaultConfig} */
module.exports = {
    ...defaultConfig,
    parenSpacing: false,
    arrowParens: 'avoid',
    useTabs: false,
    tabWidth: 4,
    trailingComma: 'all',
    semi: true,
    singleQuote: true,
}
