let mix = require('laravel-mix');

require('dotenv').config();

mix.webpackConfig(webpack => {
    return {
        resolve: {
            extensions: ['.ts', '.tsx'],
        },
        module: {
            rules: [
                {
                    test: /\.tsx?$/,
                    loader: 'ts-loader',
                    options: {
                        appendTsSuffixTo: [/\.vue$/]
                    }
                }
            ]
        },
        plugins: [
            new webpack.DefinePlugin({
                ENV: JSON.stringify(process.env.APP_ENV),
                SOCKET_ORIGIN: JSON.stringify(process.env.SOCKET_ORIGIN),
                DEBUG: process.env.NODE_ENV === 'development',
                BROADCAST_DRIVER: JSON.stringify(process.env.BROADCAST_DRIVER)
            })
        ]
    }
});

Mix.listen('configReady', (webpackConfig) => {
    if (Mix.isUsing('hmr')) {
        // Remove leading '/' from entry keys
        webpackConfig.entry = Object.keys(webpackConfig.entry).reduce((entries, entry) => {
            entries[entry.replace(/^\//, '')] = webpackConfig.entry[entry];
            return entries;
        }, {});

        // Remove leading '/' from ExtractTextPlugin instances
        webpackConfig.plugins.forEach((plugin) => {
            if (plugin.constructor.name === 'ExtractTextPlugin') {
                plugin.filename = plugin.filename.replace(/^\//, '');
            }
        });
    }
});

mix.autoload({
    jquery: ['$', 'window.jQuery']
});

mix.disableSuccessNotifications();

mix.js('resources/assets/js/admin/admin-app.js', 'public/js');
mix.js('resources/assets/js/dealer/dealer-app.ts', 'public/js');
mix.sass('resources/assets/sass/app.scss', 'public/css');
mix.copyDirectory('resources/assets/images', 'public/img');
mix.copyDirectory('resources/assets/fonts', 'public/fonts');
mix.copyDirectory('resources/assets/sounds', 'public/sounds');