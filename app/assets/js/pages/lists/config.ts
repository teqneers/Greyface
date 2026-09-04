import type {ColumnDef} from '@tanstack/react-table';
import type {z} from 'zod';

import type {SortState} from '@/lib/api';

/** One tab of a list screen: the emails or the domains of a whitelist, say. */
export interface EntryConfig<T, V extends Record<string, unknown>> {
    /** Route segment and translation key suffix: 'emails' | 'domains'. */
    kind: 'emails' | 'domains',
    /** API path relative to apiUrl, e.g. '/opt-out/emails'. */
    apiPath: string,
    /** Storage key for the remembered list state. */
    storageKey: string,
    defaultSort: SortState,
    columns: ColumnDef<T, any>[],
    getRowId: (row: T) => string,
    /** Human label of a row, for toasts and confirmations. */
    label: (row: T) => string,
    form: EntryForm<T, V>,
    /** Request bodies. */
    createBody: (values: V) => unknown,
    updateBody: (row: T, values: V) => unknown,
    deleteBody: (row: T) => unknown,
}

export interface EntryField {
    name: string,
    /** Translation key. */
    label: string,
    placeholder?: string,
    autoComplete?: string,
}

export interface EntryForm<T, V> {
    schema: z.ZodType<V>,
    /**
     * Single-field lists (whitelist emails) let the user add several values
     * at once: the field is an array named `multiField` on create and a single
     * value on edit. Multi-field entries (auto-whitelist) always add one.
     */
    multiField?: string,
    fields: EntryField[],
    empty: () => V,
    fromRow: (row: T) => V,
}

export interface ListConfig {
    /** Route base, e.g. 'whitelist'. */
    slug: string,
    /** Translation namespace: 'whitelist' | 'blacklist' | 'autoWhitelist'. */
    i18n: string,
    emails: EntryConfig<any, any>,
    domains: EntryConfig<any, any>,
    /** Old URL prefix that redirects here. */
    legacyPrefix: string,
}
