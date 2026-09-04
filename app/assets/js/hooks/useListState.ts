import {useCallback, useEffect, useMemo} from 'react';
import {useSearchParams} from 'react-router-dom';

import type {SortState} from '@/lib/api';

/**
 * State of a list screen: what the URL carries, and what is remembered.
 *
 * The URL is the source of truth while on the screen (sharable, back button
 * works). Sort, page size, search and filters are also remembered across
 * logins in localStorage; the page number only for the browser session. When a
 * screen is opened without any query parameters, the remembered state is put
 * into the URL. Parameters in the URL always win.
 */
export interface ListState {
    page: number,
    pageSize: number,
    sort: SortState | null,
    query: string,
    filters: Record<string, string>,
}

export interface ListStateOptions {
    /** Storage key; one per screen. */
    key: string,
    defaultSort?: SortState | null,
    defaultPageSize?: number,
    /** Names of extra filter parameters, e.g. ['user']. */
    filterKeys?: string[],
}

export const PAGE_SIZES = [10, 25, 50, 100];

const STORAGE_KEY = 'teqneers.greyface.lists';
const SESSION_KEY = 'teqneers.greyface.pages';

type Remembered = Omit<ListState, 'page'>;

function read<T>(storage: Storage, key: string): Record<string, T> {
    try {
        return JSON.parse(storage.getItem(key) || '{}') as Record<string, T>;
    } catch {
        return {};
    }
}

function write<T>(storage: Storage, key: string, id: string, value: T): void {
    try {
        storage.setItem(key, JSON.stringify({...read<T>(storage, key), [id]: value}));
    } catch {
        // Storage may be unavailable (private mode); the URL still works.
    }
}

export function readRemembered(key: string): Partial<ListState> {
    const remembered = read<Remembered>(window.localStorage, STORAGE_KEY)[key] ?? {};
    const page = read<number>(window.sessionStorage, SESSION_KEY)[key];
    return page !== undefined ? {...remembered, page} : remembered;
}

function parseSort(value: string | null): SortState | null | undefined {
    if (value === null) {
        return undefined;
    }
    if (value === '') {
        return null;
    }
    const [id, direction] = value.split(':');
    return {id, desc: direction === 'desc'};
}

function serializeSort(sort: SortState | null): string {
    return sort ? `${sort.id}:${sort.desc ? 'desc' : 'asc'}` : '';
}

function parseInteger(value: string | null, fallback: number, min: number): number {
    const parsed = value === null ? NaN : Number.parseInt(value, 10);
    return Number.isFinite(parsed) && parsed >= min ? parsed : fallback;
}

export interface UseListState {
    state: ListState,
    setPage: (page: number) => void,
    setPageSize: (pageSize: number) => void,
    setSort: (sort: SortState | null) => void,
    setQuery: (query: string) => void,
    setFilter: (name: string, value: string) => void,
    /** Clamps the page once the row count is known and a stale page is out of range. */
    clampPage: (rowCount: number) => void,
}

export function useListState(options: ListStateOptions): UseListState {
    const {key, defaultSort = null, defaultPageSize = PAGE_SIZES[0], filterKeys = []} = options;
    const [searchParams, setSearchParams] = useSearchParams();
    // Callers pass fresh object/array literals each render; compare by value so
    // the state object stays referentially stable between renders.
    const defaultSortKey = serializeSort(defaultSort);
    const filterKeysKey = filterKeys.join(',');
    const hasParams = ['page', 'size', 'sort', 'q', ...filterKeys].some((name) => searchParams.has(name));

    // Nothing in the URL: seed it from what was remembered, replacing the
    // history entry so the back button does not loop through the seed.
    useEffect(() => {
        if (hasParams) {
            return;
        }
        const remembered = readRemembered(key);
        const next = new URLSearchParams();
        if (remembered.page) {
            next.set('page', String(remembered.page + 1));
        }
        if (remembered.pageSize) {
            next.set('size', String(remembered.pageSize));
        }
        if (remembered.sort !== undefined) {
            next.set('sort', serializeSort(remembered.sort));
        }
        if (remembered.query) {
            next.set('q', remembered.query);
        }
        for (const [name, value] of Object.entries(remembered.filters ?? {})) {
            if (value) {
                next.set(name, value);
            }
        }
        if ([...next.keys()].length > 0) {
            setSearchParams(next, {replace: true});
        }
    }, [hasParams, key, setSearchParams]);

    const state = useMemo<ListState>(() => {
        const filters: Record<string, string> = {};
        for (const name of filterKeysKey ? filterKeysKey.split(',') : []) {
            filters[name] = searchParams.get(name) ?? '';
        }
        const sort = parseSort(searchParams.get('sort'));
        return {
            // The URL is 1-based for humans; the state is 0-based like the API.
            page: parseInteger(searchParams.get('page'), 1, 1) - 1,
            pageSize: parseInteger(searchParams.get('size'), defaultPageSize, 1),
            sort: sort === undefined ? (parseSort(defaultSortKey) ?? null) : sort,
            query: searchParams.get('q') ?? '',
            filters,
        };
    }, [searchParams, defaultPageSize, defaultSortKey, filterKeysKey]);

    // Remember whatever the URL currently says.
    useEffect(() => {
        if (!hasParams) {
            return;
        }
        const {page, ...rest} = state;
        write<Remembered>(window.localStorage, STORAGE_KEY, key, rest);
        write<number>(window.sessionStorage, SESSION_KEY, key, page);
    }, [state, hasParams, key]);

    const update = useCallback((changes: Partial<Record<'page' | 'size' | 'sort' | 'q', string | null>> & { filters?: Record<string, string> }) => {
        setSearchParams((previous) => {
            const next = new URLSearchParams(previous);
            const apply = (name: string, value: string | null | undefined) => {
                if (value === undefined) {
                    return;
                }
                if (value === null || value === '') {
                    next.delete(name);
                } else {
                    next.set(name, value);
                }
            };
            apply('page', changes.page);
            apply('size', changes.size);
            if (changes.sort !== undefined) {
                // An explicit empty sort must survive as "" to override the default.
                next.set('sort', changes.sort ?? '');
            }
            apply('q', changes.q);
            for (const [name, value] of Object.entries(changes.filters ?? {})) {
                apply(name, value);
            }
            // Keep the URL minimal: page 1 is the default.
            if (next.get('page') === '1') {
                next.delete('page');
            }
            // Make sure a seeded URL is recognised as explicit even if everything
            // was reset to defaults.
            if ([...next.keys()].length === 0) {
                next.set('size', String(state.pageSize));
            }
            return next;
        });
    }, [setSearchParams, state.pageSize]);

    const setPage = useCallback((page: number) => update({page: String(page + 1)}), [update]);
    const setPageSize = useCallback((pageSize: number) => update({size: String(pageSize), page: null}), [update]);
    const setSort = useCallback((sort: SortState | null) => update({sort: serializeSort(sort), page: null}), [update]);
    const setQuery = useCallback((query: string) => update({q: query, page: null}), [update]);
    const setFilter = useCallback((name: string, value: string) => update({filters: {[name]: value}, page: null}), [update]);
    const clampPage = useCallback((rowCount: number) => {
        const lastPage = Math.max(0, Math.ceil(rowCount / state.pageSize) - 1);
        if (state.page > lastPage) {
            update({page: lastPage === 0 ? null : String(lastPage + 1)});
        }
    }, [state.page, state.pageSize, update]);

    return {state, setPage, setPageSize, setSort, setQuery, setFilter, clampPage};
}
