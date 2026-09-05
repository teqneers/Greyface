import '@testing-library/jest-dom/vitest';

import {cleanup} from '@testing-library/react';
import {afterEach, beforeEach, vi} from 'vitest';

// Radix builds its menus and popovers on the Pointer Events API, which jsdom
// does not implement. Without these a trigger renders fine and simply never
// opens, so a test that clicks one fails looking as though the component were
// broken. Only tests that open a menu from its trigger need them; the dialog
// tests control `open` themselves and never noticed.
Element.prototype.hasPointerCapture ??= vi.fn(() => false);
Element.prototype.setPointerCapture ??= vi.fn();
Element.prototype.releasePointerCapture ??= vi.fn();
Element.prototype.scrollIntoView ??= vi.fn();

// The settings module persists to localStorage on import and on every change.
// Starting each test from an empty store keeps table state from leaking between
// tests, which is otherwise very hard to see.
beforeEach(() => {
    window.localStorage.clear();
});

afterEach(() => {
    cleanup();
});
