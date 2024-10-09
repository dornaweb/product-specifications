const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

/** @type {typeof defaultConfig} */
module.exports = {
    ...defaultConfig,
    entry: {
        admin: './client/admin/index.js',
        frontend: './client/frontend/index.js',
    },
    output: {
        filename: '[name].js',
        path: path.resolve(process.cwd(), 'assets'),
    },
};
