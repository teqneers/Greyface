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

/**
 * Where an entry can be sent besides the auto-whitelist. The values are the
 * server's; App\Domain\Connect\ListTarget is the other half of this contract.
 */
export type ListTarget =
    | 'auto-whitelist-domain'
    | 'whitelist-email'
    | 'whitelist-domain'
    | 'blacklist-email'
    | 'blacklist-domain';

/** Anything acting on the sender's whole domain is worth confirming first. */
export const DOMAIN_WIDE_TARGETS: ReadonlySet<ListTarget> = new Set<ListTarget>([
    'auto-whitelist-domain',
    'whitelist-domain',
    'blacklist-domain',
]);

/**
 * Which destinations ask before acting. Whitelisting one sender is the common
 * case and is reversible from the toast, so it does not. Anything covering a
 * whole domain does, and so does blacklisting, which starts delaying mail that
 * was arriving fine.
 */
export function needsConfirmation(target: ListTarget): boolean {
    return DOMAIN_WIDE_TARGETS.has(target) || target.startsWith('blacklist');
}

export interface ListMoveResult {
    moved: number,
    target: ListTarget,
    /** `created` says whether the list row was new, which is what undo needs. */
    entries: { entry: MovedEntry, created: boolean }[],
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
    moveToList: (entries: GreylistKey[], target: ListTarget) =>
        apiJson<ListMoveResult>(`${apiUrl}/greylist/toList`, 'POST', {entries, target}),
    undoMoveToList: (result: ListMoveResult) =>
        apiJson(`${apiUrl}/greylist/undoToList`, 'POST', {target: result.target, entries: result.entries}),
    removeBefore: (date: string) =>
        apiJson<{ deleted: number }>(`${apiUrl}/greylist/delete-to-date`, 'DELETE', {date}),
});
