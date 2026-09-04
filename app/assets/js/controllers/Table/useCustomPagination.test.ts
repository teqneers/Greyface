import {renderHook} from '@testing-library/react';
import {describe, expect, it} from 'vitest';

import {DOTS, useCustomPagination} from './useCustomPagination';

/**
 * Pure pagination arithmetic. No router, no query client — this also serves as
 * the harness smoke test.
 */
describe('useCustomPagination', () => {
    const pages = (totalPageCount: number, currentPage: number, siblingCount = 1) =>
        renderHook(() => useCustomPagination({totalPageCount, currentPage, siblingCount})).result.current;

    it('lists every page when they all fit', () => {
        expect(pages(5, 1)).toEqual([1, 2, 3, 4, 5]);
        expect(pages(6, 1)).toEqual([1, 2, 3, 4, 5, 6]);
    });

    it('truncates on the right while near the start', () => {
        expect(pages(10, 1)).toEqual([1, 2, 3, 4, 5, DOTS, 10]);
    });

    it('truncates on the left while near the end', () => {
        expect(pages(10, 10)).toEqual([1, DOTS, 6, 7, 8, 9, 10]);
    });

    it('truncates on both sides in the middle', () => {
        expect(pages(20, 10)).toEqual([1, DOTS, 9, 10, 11, DOTS, 20]);
    });

    it('widens the window with a larger sibling count', () => {
        expect(pages(20, 10, 2)).toEqual([1, DOTS, 8, 9, 10, 11, 12, DOTS, 20]);
    });

    it('handles a single page', () => {
        expect(pages(1, 1)).toEqual([1]);
    });
});
