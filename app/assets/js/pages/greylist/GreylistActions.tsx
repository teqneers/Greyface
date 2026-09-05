import {useMutation, useQueryClient} from '@tanstack/react-query';
import {ChevronDown, ShieldCheck, Trash2} from 'lucide-react';
import React, {useMemo, useState} from 'react';
import {useTranslation} from 'react-i18next';
import {toast} from 'sonner';

import {useApplication} from '@/application/ApplicationContext';
import {usePermissions} from '@/application/usePermissions';
import {ConfirmDialog} from '@/components/dialogs';
import {Button} from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {Tooltip, TooltipContent, TooltipTrigger} from '@/components/ui/tooltip';
import type {Greylist} from '@/types/greylist';

import {greylistApi, keyOf, needsConfirmation} from './api';
import type {ListMoveResult, ListTarget, MoveResult} from './api';

/**
 * Whitelisting is reversible (an undo in the toast puts the entry back), so
 * it acts immediately. Deleting is not, so it asks first.
 */
export function useGreylistMutations() {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const queryClient = useQueryClient();
    const api = useMemo(() => greylistApi(apiUrl), [apiUrl]);
    const invalidate = () => queryClient.invalidateQueries({queryKey: ['greylist']});

    const undo = useMutation({
        mutationFn: (result: MoveResult) => api.undoMove(result.entry, result.awlCreated),
        onSuccess: () => {
            toast.success(t('greylist.undone'));
            invalidate();
        },
        onError: (error: Error) => toast.error(error.message),
    });

    const move = useMutation({
        mutationFn: (row: Greylist) => api.moveToWhiteList(keyOf(row)),
        onSuccess: (result, row) => {
            invalidate();
            toast.success(t('greylist.moved', {sender: `${row.connect.name}@${row.connect.domain}`}), {
                action: {label: t('button.undo'), onClick: () => undo.mutate(result)},
                duration: 8000,
            });
        },
        onError: (error: Error) => toast.error(error.message),
    });

    const remove = useMutation({
        mutationFn: (row: Greylist) => api.remove(keyOf(row)),
        onSuccess: () => {
            toast.success(t('greylist.deleted'));
            invalidate();
        },
        onError: (error: Error) => toast.error(error.message),
    });

    const bulkMove = useMutation({
        mutationFn: (rows: Greylist[]) => api.bulkMove(rows.map(keyOf)),
        onSuccess: ({moved}) => {
            toast.success(t('greylist.bulkMoved', {count: moved}));
            invalidate();
        },
        onError: (error: Error) => toast.error(error.message),
    });

    const bulkRemove = useMutation({
        mutationFn: (rows: Greylist[]) => api.bulkRemove(rows.map(keyOf)),
        onSuccess: ({deleted}) => {
            toast.success(t('greylist.bulkDeleted', {count: deleted}));
            invalidate();
        },
        onError: (error: Error) => toast.error(error.message),
    });

    const undoList = useMutation({
        mutationFn: (result: ListMoveResult) => api.undoMoveToList(result),
        onSuccess: () => {
            toast.success(t('greylist.undone'));
            invalidate();
        },
        onError: (error: Error) => toast.error(error.message),
    });

    /**
     * The other list destinations. One mutation for a single row and for a
     * selection, because the server takes the same shape either way.
     */
    const moveToList = useMutation({
        mutationFn: ({rows, target}: { rows: Greylist[], target: ListTarget }) =>
            api.moveToList(rows.map(keyOf), target),
        onSuccess: (result, {target}) => {
            invalidate();
            toast.success(t(`greylist.sentTo.${target}`, {count: result.moved}), {
                action: {label: t('button.undo'), onClick: () => undoList.mutate(result)},
                duration: 8000,
            });
        },
        onError: (error: Error) => toast.error(error.message),
    });

    return {move, remove, bulkMove, bulkRemove, moveToList};
}

export function GreylistRowActions({row}: { row: Greylist }): React.ReactElement {
    const {t} = useTranslation();
    const {isAdministrator} = usePermissions();
    const admin = isAdministrator();
    const {move, remove, moveToList} = useGreylistMutations();
    const [confirm, setConfirm] = useState(false);
    const [pending, setPending] = useState<ListTarget | null>(null);
    const sender = `${row.connect.name}@${row.connect.domain}`;
    const domain = row.connect.domain;

    const send = (target: ListTarget): void => {
        if (needsConfirmation(target)) {
            setPending(target);
            return;
        }
        moveToList.mutate({rows: [row], target});
    };

    return (
        <div className="flex items-center justify-end gap-1">
            {/* A split button: the everyday action keeps its single click, and the
                caret carries the rest. Only administrators may write to the other
                lists, so only they see it. */}
            <div className="flex items-center">
                <Button variant="outline" size="sm" disabled={move.isPending}
                        className={admin ? 'rounded-r-none' : undefined}
                        onClick={() => move.mutate(row)}>
                    <ShieldCheck aria-hidden="true"/>
                    {t('button.moveToWhitelist')}
                </Button>
                {admin && (
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="outline" size="sm"
                                    className="rounded-l-none border-l-0 px-1.5"
                                    aria-label={t('greylist.otherDestinations', {sender})}>
                                <ChevronDown aria-hidden="true"/>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" className="w-72">
                            <DropdownMenuLabel className="text-muted-foreground text-xs font-normal">
                                {t('greylist.scope.sender', {sender})}
                            </DropdownMenuLabel>
                            {/* The visible text stays short, but the two scopes
                                would otherwise read identically to a screen
                                reader: the group heading above is not part of an
                                item's accessible name. */}
                            <DropdownMenuItem onSelect={() => send('whitelist-email')}
                                              aria-label={t('greylist.target.neverGreylistSender', {sender})}>
                                {t('greylist.target.neverGreylist')}
                            </DropdownMenuItem>
                            <DropdownMenuItem variant="destructive" onSelect={() => send('blacklist-email')}
                                              aria-label={t('greylist.target.alwaysGreylistSender', {sender})}>
                                {t('greylist.target.alwaysGreylist')}
                            </DropdownMenuItem>
                            <DropdownMenuSeparator/>
                            <DropdownMenuLabel className="text-muted-foreground text-xs font-normal">
                                {t('greylist.scope.domain', {domain})}
                            </DropdownMenuLabel>
                            <DropdownMenuItem onSelect={() => send('auto-whitelist-domain')}
                                              aria-label={t('greylist.target.trustDomainFromSource', {domain, source: row.connect.source})}>
                                {t('greylist.target.trustFromSource', {source: row.connect.source})}
                            </DropdownMenuItem>
                            <DropdownMenuItem onSelect={() => send('whitelist-domain')}
                                              aria-label={t('greylist.target.neverGreylistDomain', {domain})}>
                                {t('greylist.target.neverGreylist')}
                            </DropdownMenuItem>
                            <DropdownMenuItem variant="destructive" onSelect={() => send('blacklist-domain')}
                                              aria-label={t('greylist.target.alwaysGreylistDomain', {domain})}>
                                {t('greylist.target.alwaysGreylist')}
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                )}
            </div>
            <Tooltip>
                <TooltipTrigger asChild>
                    <Button variant="ghost" size="icon-sm" onClick={() => setConfirm(true)}
                            aria-label={t('greylist.deleteEntry', {sender})}>
                        <Trash2 aria-hidden="true"/>
                    </Button>
                </TooltipTrigger>
                <TooltipContent>{t('button.delete')}</TooltipContent>
            </Tooltip>
            <ConfirmDialog
                open={confirm}
                onOpenChange={setConfirm}
                title={t('greylist.deleteHeader')}
                description={t('greylist.deleteMessage', {sender})}
                destructive
                pending={remove.isPending}
                onConfirm={() => remove.mutate(row, {onSuccess: () => setConfirm(false)})}
            />
            <ConfirmDialog
                open={pending !== null}
                onOpenChange={(open) => !open && setPending(null)}
                title={pending ? t(`greylist.confirm.${pending}.title`) : ''}
                description={pending ? t(`greylist.confirm.${pending}.message`, {sender, domain}) : ''}
                confirmLabel={pending ? t(`greylist.confirm.${pending}.action`) : undefined}
                destructive={pending?.startsWith('blacklist')}
                pending={moveToList.isPending}
                onConfirm={() => pending && moveToList.mutate(
                    {rows: [row], target: pending},
                    {onSuccess: () => setPending(null)}
                )}
            />
        </div>
    );
}
