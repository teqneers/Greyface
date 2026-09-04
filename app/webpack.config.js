// Encore 7 ships as ESM, so the CommonJS require() hands back the module
// namespace rather than the Encore instance.
const Encore = require('@symfony/webpack-encore').default;
const ReactRefreshWebpackPlugin = require('@pmmmwh/react-refresh-webpack-plugin');

// Manually configure the runtime environment if the "encore" command has not
// done it already — useful for tools that load this file directly. Every other
// Encore method throws until this has run.
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
    .setManifestKeyPrefix('build/')

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
                babelConfig.plugins.push('react-refresh/babel');
            }
            babelConfig.plugins.push('babel-plugin-styled-components');
            babelConfig.plugins.push('@babel/plugin-transform-runtime');

            // Babel 8 removed useBuiltIns/corejs from @babel/preset-env, and
            // Encore 7 refuses to start if they are still set. This plugin is
            // the replacement and injects the same usage-based core-js imports.
            babelConfig.plugins.push(['polyfill-corejs3', {
                method: 'usage-global',
                version: '3.38',
            }]);
        }
    )

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()
    // Babel 8 removed the isTSX/allExtensions options from
    // @babel/preset-typescript; .tsx is now detected from the file extension.
    .enableBabelTypeScriptPreset()

    // Babel 8 switched @babel/preset-react to the automatic JSX runtime, and
    // Encore 7 no longer sets `development` for you. Left at its default, the
    // production bundle called jsxDEV() from react/jsx-dev-runtime, which is not
    // in a production React build — the SPA rendered a blank page with
    // "(0 , b.jsxDEV) is not a function" in the console.
    .enableReactPreset((options) => {
        options.development = !Encore.isProduction();
    })

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    .enableIntegrityHashes(Encore.isProduction())

    .configureDefinePlugin((options) => {
        options.IS_DEV = JSON.stringify(!Encore.isProduction());
    })

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
    .configureDevServerOptions((options) => {
        options.server = {
            type: 'https',
            options: {
                key: '../docker/build/webserver/app.key',
                cert: '../docker/build/webserver/app.crt'
            }
        };
    })

    .copyFiles({
        from: './assets/images'
    })
;

const config = Encore.getWebpackConfig();
if (!Encore.isProduction()) {
    config.devtool = 'cheap-module-source-map';
    config.plugins.push(new ReactRefreshWebpackPlugin({overlay: false}));
}

// required because content-disposition requires these node modules.
// Encore 7 no longer always emits a `resolve` key, so create it if absent
// instead of assuming it is there.
config.resolve = config.resolve || {};
config.resolve.fallback = {
    ...config.resolve.fallback,
    path: require.resolve('path-browserify'),
    buffer: require.resolve('buffer/'),
};

module.exports = config;
