const Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

const publicPath = process.env.PUBLIC_PATH || '/build';

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath(publicPath)
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.jsx)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/js/app.jsx')
    .addEntry('page', './assets/js/page.js')

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    //.enableStimulusBridge('./assets/controllers.json')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    .configureBabel(
        (babelConfig) => {
            if (!Encore.isProduction()) {
                babelConfig.plugins.push('react-hot-loader/babel');
            }
            babelConfig.plugins.push('babel-plugin-styled-components');
            //babelConfig.plugins.push('@babel/plugin-proposal-class-properties');
            babelConfig.plugins.push('@babel/plugin-transform-runtime');
        }
    )

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()
    .enableBabelTypeScriptPreset({
        isTSX: true,
        allExtensions: true,
    })

    // uncomment if you use React
    .enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    .enableIntegrityHashes(Encore.isProduction())

    .configureDefinePlugin((options) => {
        options.IS_DEV = JSON.stringify(!Encore.isProduction());
    })

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
    .configureDevServerOptions((options) => {
        options.https = {
            key: '../docker/build/webserver/app.key',
            cert: '../docker/build/webserver/app.crt'
        }
    })

    .copyFiles({
        from: './assets/images'
    })
;

const config = Encore.getWebpackConfig();
if (!Encore.isProduction()) {
    config.devtool = 'cheap-module-source-map';
    config.resolve.alias['react-dom'] = '@hot-loader/react-dom';
}

// required because content-disposition requires these node modules
config.resolve.fallback = {
    path: require.resolve('path-browserify'),
    buffer: require.resolve('buffer/'),
};

module.exports = config;
