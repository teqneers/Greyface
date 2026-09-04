import type {ColumnDef} from '@tanstack/react-table';
import type {TFunction} from 'i18next';
import React from 'react';
import {z} from 'zod';

import {FormattedDate} from '@/components/FormattedDate';
import type {AutoWhiteListDomain, AutoWhiteListEmail, BlackListDomain, BlackListEmail, WhiteListDomain, WhiteListEmail} from '@/types/greylist';

import type {EntryConfig, ListConfig} from './config';
import {domainValue, emailValue, manyOf, textValue} from './schemas';
import type {ValueItem} from './schemas';

/* ---------- opt-in / opt-out: one value per row ---------- */

type SingleValues = { value: ValueItem[] };
type SingleEdit = { value: string };

function single<T extends object>(
    t: TFunction,
    kind: 'emails' | 'domains',
    field: 'email' | 'domain',
    apiPath: string,
    storageKey: string,
    i18n: string,
): EntryConfig<T, SingleValues | SingleEdit> {
    const value = field === 'email' ? emailValue(t) : domainValue(t);
    const read = (row: T) => String(row[field as keyof T] ?? '');
    const columns: ColumnDef<T, any>[] = [{
        id: field,
        header: t(`${i18n}.${kind === 'emails' ? 'email' : 'domain'}.${field}`),
        accessorFn: read,
        cell: ({getValue}) => <span className="font-medium break-all">{getValue<string>()}</span>,
    }];
    return {
        kind,
        apiPath,
        storageKey,
        defaultSort: {id: field, desc: false},
        columns,
        getRowId: read,
        label: read,
        form: {
            // Create takes a list, edit a single value; the dialog switches shape.
            schema: z.union([
                z.object({value: manyOf(t, value)}),
                z.object({value}),
            ]) as z.ZodType<SingleValues | SingleEdit>,
            multiField: 'value',
            fields: [{name: 'value', label: `${i18n}.${kind === 'emails' ? 'email' : 'domain'}.${field}`, autoComplete: 'off'}],
            empty: () => ({value: [{v: ''}]}),
            fromRow: (row) => ({value: read(row)}),
        },
        createBody: (values) => ({[field]: (values as SingleValues).value.map((item) => item.v)}),
        updateBody: (row, values) => ({dynamicID: {[field]: read(row)}, [field]: (values as SingleEdit).value}),
        deleteBody: (row) => ({[field]: read(row)}),
    };
}

/* ---------- auto-whitelist: SQLGrey's own rows ---------- */

type AwlEmailValues = { name: string, domain: string, source: string };
type AwlDomainValues = { domain: string, source: string };

function seenColumns<T extends { firstSeen: unknown, lastSeen: unknown }>(t: TFunction): ColumnDef<T, any>[] {
    return [
        {
            id: 'firstSeen',
            header: t('autoWhitelist.email.firstSeen'),
            accessorFn: (row) => row.firstSeen,
            cell: ({row}) => <FormattedDate value={row.original.firstSeen as any}/>,
            meta: {nowrap: true},
        },
        {
            id: 'lastSeen',
            header: t('autoWhitelist.email.lastSeen'),
            accessorFn: (row) => row.lastSeen,
            cell: ({row}) => <FormattedDate value={row.original.lastSeen as any}/>,
            meta: {nowrap: true},
        },
    ];
}

const source = <T extends { source: string }>(t: TFunction): ColumnDef<T, any> => ({
    id: 'source',
    header: t('autoWhitelist.email.source'),
    accessorFn: (row) => row.source,
    cell: ({getValue}) => <code className="text-xs text-foreground/80">{getValue<string>()}</code>,
    meta: {nowrap: true},
});

function awlEmails(t: TFunction): EntryConfig<AutoWhiteListEmail, AwlEmailValues> {
    return {
        kind: 'emails',
        apiPath: '/awl/emails',
        storageKey: 'autoWhitelist.emails',
        defaultSort: {id: 'lastSeen', desc: true},
        columns: [
            {
                id: 'name',
                header: t('autoWhitelist.email.name'),
                accessorFn: (row) => row.name,
                cell: ({getValue}) => <span className="font-medium break-all">{getValue<string>()}</span>,
            },
            {id: 'domain', header: t('autoWhitelist.email.domain'), accessorFn: (row) => row.domain},
            source<AutoWhiteListEmail>(t),
            ...seenColumns<AutoWhiteListEmail>(t),
        ],
        getRowId: (row) => [row.name, row.domain, row.source].join('|'),
        label: (row) => `${row.name}@${row.domain}`,
        form: {
            schema: z.object({name: textValue(t), domain: domainValue(t), source: textValue(t)}),
            fields: [
                {name: 'name', label: 'autoWhitelist.email.name', autoComplete: 'off'},
                {name: 'domain', label: 'autoWhitelist.email.domain', autoComplete: 'off'},
                {name: 'source', label: 'autoWhitelist.email.source', placeholder: '192.0.2', autoComplete: 'off'},
            ],
            empty: () => ({name: '', domain: '', source: ''}),
            fromRow: ({name, domain, source}) => ({name, domain, source}),
        },
        createBody: (values) => values,
        updateBody: (row, values) => ({dynamicID: {name: row.name, domain: row.domain, source: row.source}, ...values}),
        deleteBody: ({name, domain, source}) => ({name, domain, source}),
    };
}

function awlDomains(t: TFunction): EntryConfig<AutoWhiteListDomain, AwlDomainValues> {
    return {
        kind: 'domains',
        apiPath: '/awl/domains',
        storageKey: 'autoWhitelist.domains',
        defaultSort: {id: 'lastSeen', desc: true},
        columns: [
            {
                id: 'domain',
                header: t('autoWhitelist.domain.domain'),
                accessorFn: (row) => row.domain,
                cell: ({getValue}) => <span className="font-medium break-all">{getValue<string>()}</span>,
            },
            source<AutoWhiteListDomain>(t),
            ...seenColumns<AutoWhiteListDomain>(t),
        ],
        getRowId: (row) => [row.domain, row.source].join('|'),
        label: (row) => row.domain,
        form: {
            schema: z.object({domain: domainValue(t), source: textValue(t)}),
            fields: [
                {name: 'domain', label: 'autoWhitelist.domain.domain', autoComplete: 'off'},
                {name: 'source', label: 'autoWhitelist.domain.source', placeholder: '192.0.2', autoComplete: 'off'},
            ],
            empty: () => ({domain: '', source: ''}),
            fromRow: ({domain, source}) => ({domain, source}),
        },
        createBody: (values) => values,
        updateBody: (row, values) => ({dynamicID: {domain: row.domain, source: row.source}, ...values}),
        deleteBody: ({domain, source}) => ({domain, source}),
    };
}

/* ---------- the three screens ---------- */

export const whitelist = (t: TFunction): ListConfig => ({
    slug: 'whitelist',
    i18n: 'whitelist',
    legacyPrefix: '/opt-out',
    emails: single<WhiteListEmail>(t, 'emails', 'email', '/opt-out/emails', 'whitelist.emails', 'whitelist'),
    domains: single<WhiteListDomain>(t, 'domains', 'domain', '/opt-out/domains', 'whitelist.domains', 'whitelist'),
});

export const blacklist = (t: TFunction): ListConfig => ({
    slug: 'blacklist',
    i18n: 'blacklist',
    legacyPrefix: '/opt-in',
    emails: single<BlackListEmail>(t, 'emails', 'email', '/opt-in/emails', 'blacklist.emails', 'blacklist'),
    domains: single<BlackListDomain>(t, 'domains', 'domain', '/opt-in/domains', 'blacklist.domains', 'blacklist'),
});

export const autoWhitelist = (t: TFunction): ListConfig => ({
    slug: 'auto-whitelist',
    i18n: 'autoWhitelist',
    legacyPrefix: '/awl',
    emails: awlEmails(t),
    domains: awlDomains(t),
});
