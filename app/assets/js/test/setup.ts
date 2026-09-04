import '@testing-library/jest-dom/vitest';

import {cleanup} from '@testing-library/react';
import {afterEach, beforeEach} from 'vitest';

// The settings module persists to localStorage on import and on every change.
// Starting each test from an empty store keeps table state from leaking between
// tests, which is otherwise very hard to see.
beforeEach(() => {
    window.localStorage.clear();
});

afterEach(() => {
    cleanup();
});
