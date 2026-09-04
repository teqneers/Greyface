import js from '@eslint/js';
import react from 'eslint-plugin-react';
import reactHooks from 'eslint-plugin-react-hooks';
import globals from 'globals';
import tseslint from 'typescript-eslint';

// ESLint 9 flat config, replacing .eslintrc.js. Ignores, env and extends all
// moved into this single exported array.
export default tseslint.config(
    {
        ignores: ['node_modules/**', 'public/build/**', 'vite.config.ts', 'vitest.config.mts'],
    },

    js.configs.recommended,
    ...tseslint.configs.recommended,
    react.configs.flat.recommended,

    {
        files: ['assets/js/**/*.{js,jsx,ts,tsx}'],
        plugins: {'react-hooks': reactHooks},
        languageOptions: {
            ecmaVersion: 2020,
            sourceType: 'module',
            globals: {...globals.browser},
            parserOptions: {ecmaFeatures: {jsx: true}},
        },
        settings: {react: {version: 'detect'}},
        rules: {
            ...reactHooks.configs.recommended.rules,

            'linebreak-style': ['error', 'unix'],
            quotes: ['error', 'single'],
            semi: ['error', 'always'],

            'react/display-name': 'off',
            'react/prop-types': 'off',
            'react/react-in-jsx-scope': 'off',

            '@typescript-eslint/no-explicit-any': 'off',
            '@typescript-eslint/ban-ts-comment': 'off',
            '@typescript-eslint/explicit-module-boundary-types': 'off',
            // Replaces the ban-types rule removed in typescript-eslint 8; the
            // codebase intentionally uses bare `object` in a few generic spots.
            '@typescript-eslint/no-empty-object-type': 'off',

            // eslint-plugin-react-hooks 7 adds rules that flag long-standing
            // patterns in the page modules (setState inside an effect to reset
            // pagination when the search query changes). They are worth seeing
            // and worth fixing, but not worth a risky refactor in the middle of
            // a library migration — so they warn rather than fail the build.
            'react-hooks/exhaustive-deps': 'warn',
            'react-hooks/set-state-in-effect': 'warn',
            'react-hooks/use-memo': 'warn',
        },
    },

    {
        files: ['assets/js/**/*.test.{ts,tsx}', 'assets/js/test/**/*.{ts,tsx}'],
        languageOptions: {globals: {...globals.node, ...globals.vitest}},
    },
);
