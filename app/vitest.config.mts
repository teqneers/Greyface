import {defineConfig, mergeConfig} from 'vitest/config';

import viteConfig from './vite.config';

export default mergeConfig(viteConfig, defineConfig({
    test: {
        environment: 'jsdom',
        globals: true,
        setupFiles: ['./assets/js/test/setup.ts'],
        include: ['assets/js/**/*.test.{ts,tsx}'],
        // Unit tests of the React layer only; styling is not under test.
        css: false,
        restoreMocks: true,
        clearMocks: true,
        unstubEnvs: true,
        unstubGlobals: true,
    },
}));
