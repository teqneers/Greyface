import {defineConfig} from 'vitest/config';

export default defineConfig({
    define: {
        // Webpack's DefinePlugin supplies this in a real build (see webpack.config.js);
        // i18n.tsx reads it to pick the fallback language.
        IS_DEV: JSON.stringify(false),
    },
    test: {
        environment: 'jsdom',
        globals: true,
        setupFiles: ['./assets/js/test/setup.ts'],
        include: ['assets/js/**/*.test.{ts,tsx}'],
        // Encore owns the real build; these are unit tests of the React layer only.
        css: false,
        restoreMocks: true,
        clearMocks: true,
        unstubEnvs: true,
        unstubGlobals: true,
    },
});
