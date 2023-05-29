const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
    ...defaultConfig,
    entry: {
        // backend: './resources/standalones/backend.ts',
        backend_style: './resources/standalones/backend.css',
    },
    output: {
        path: __dirname + '/assets',
        filename: '[name].js',
    },
    module: {
        ...defaultConfig.module,
    },
};
