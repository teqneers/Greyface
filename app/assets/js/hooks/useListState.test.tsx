import {act, renderHook} from '@testing-library/react';
import React from 'react';
import type {ReactNode} from 'react';
import {MemoryRouter, useLocation} from 'react-router-dom';
import {beforeEach, describe, expect, it} from 'vitest';

import {useListState} from './useListState';

function wrapperAt(route: string) {
    return ({children}: { children?: ReactNode }) => <MemoryRouter initialEntries={[route]}>{children}</MemoryRouter>;
}

function useHarness() {
    const list = useListState({key: 'test', defaultSort: {id: 'name', desc: false}, filterKeys: ['user']});
    const location = useLocation();
    return {...list, search: location.search};
}

describe('useListState', () => {
    beforeEach(() => {
        window.localStorage.clear();
        window.sessionStorage.clear();
    });

    it('starts from the defaults and writes changes into the URL', () => {
        const {result} = renderHook(useHarness, {wrapper: wrapperAt('/greylist')});

        expect(result.current.state).toMatchObject({page: 0, pageSize: 10, sort: {id: 'name', desc: false}, query: ''});

        act(() => result.current.setSort({id: 'domain', desc: true}));
        act(() => result.current.setPage(2));

        expect(result.current.state.sort).toEqual({id: 'domain', desc: true});
        expect(result.current.state.page).toBe(2);
        expect(result.current.search).toBe('?sort=domain%3Adesc&page=3');
    });

    it('remembers sort, size, search and filters across visits and the page for the session', () => {
        const first = renderHook(useHarness, {wrapper: wrapperAt('/greylist')});
        act(() => first.result.current.setPageSize(50));
        act(() => first.result.current.setQuery('spam'));
        act(() => first.result.current.setFilter('user', 'u-1'));
        act(() => first.result.current.setPage(1));
        first.unmount();

        const second = renderHook(useHarness, {wrapper: wrapperAt('/greylist')});
        expect(second.result.current.state).toMatchObject({pageSize: 50, query: 'spam', filters: {user: 'u-1'}, page: 1});

        // A new browser session forgets the page but keeps the rest.
        window.sessionStorage.clear();
        const third = renderHook(useHarness, {wrapper: wrapperAt('/greylist')});
        expect(third.result.current.state).toMatchObject({pageSize: 50, query: 'spam', page: 0});
    });

    it('lets an explicit URL win over what was remembered', () => {
        const first = renderHook(useHarness, {wrapper: wrapperAt('/greylist')});
        act(() => first.result.current.setQuery('spam'));
        first.unmount();

        const shared = renderHook(useHarness, {wrapper: wrapperAt('/greylist?q=ham&size=25')});
        expect(shared.result.current.state).toMatchObject({query: 'ham', pageSize: 25});
    });

    it('resets to the first page when the search changes and clamps stale pages', () => {
        const {result} = renderHook(useHarness, {wrapper: wrapperAt('/greylist?page=4')});
        expect(result.current.state.page).toBe(3);

        act(() => result.current.clampPage(12));
        expect(result.current.state.page).toBe(1);

        act(() => result.current.setQuery('x'));
        expect(result.current.state.page).toBe(0);
    });
});
