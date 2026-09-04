import {apiJson} from '@/lib/api';
import type {Greylist} from '@/types/greylist';

export interface GreylistKey {
    name: string,
    domain: string,
    source: string,
    rcpt: string,
}

export interface MovedEntry extends GreylistKey {
    firstSeen: string,
}

export interface MoveResult {
    message: string,
    entry: MovedEntry,
    awlCreated: boolean,
}

export function keyOf(row: Greylist): GreylistKey {
    const {name, domain, source, rcpt} = row.connect;
    return {name, domain, source, rcpt};
}

export function rowId(row: Greylist): string {
    const {name, domain, source, rcpt} = row.connect;
    return [name, domain, source, rcpt].join('|');
}

export const greylistApi = (apiUrl: string) => ({
    moveToWhiteList: (key: GreylistKey) => apiJson<MoveResult>(`${apiUrl}/greylist/toWhiteList`, 'POST', key),
    undoMove: (entry: MovedEntry, removeAwl: boolean) =>
        apiJson(`${apiUrl}/greylist/undoToWhiteList`, 'POST', {entry, removeAwl}),
    bulkMove: (entries: GreylistKey[]) =>
        apiJson<{ moved: number }>(`${apiUrl}/greylist/bulk/toWhiteList`, 'POST', {entries}),
    remove: (key: GreylistKey) => apiJson(`${apiUrl}/greylist/delete`, 'DELETE', key),
    bulkRemove: (entries: GreylistKey[]) =>
        apiJson<{ deleted: number }>(`${apiUrl}/greylist/bulk/delete`, 'DELETE', {entries}),
    removeBefore: (date: string) =>
        apiJson<{ deleted: number }>(`${apiUrl}/greylist/delete-to-date`, 'DELETE', {date}),
});
