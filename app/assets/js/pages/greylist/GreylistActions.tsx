import {useMutation, useQueryClient} from '@tanstack/react-query';
import {ShieldCheck, Trash2} from 'lucide-react';
import React, {useMemo, useState} from 'react';
import {useTranslation} from 'react-i18next';
import {toast} from 'sonner';

import {useApplication} from '@/application/ApplicationContext';
import {ConfirmDialog} from '@/components/dialogs';
import {Button} from '@/components/ui/button';
import {Tooltip, TooltipContent, TooltipTrigger} from '@/components/ui/tooltip';
import type {Greylist} from '@/types/greylist';

import {greylistApi, keyOf} from './api';
import type {MoveResult} from './api';

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

    return {move, remove, bulkMove, bulkRemove};
}

export function GreylistRowActions({row}: { row: Greylist }): React.ReactElement {
    const {t} = useTranslation();
    const {move, remove} = useGreylistMutations();
    const [confirm, setConfirm] = useState(false);
    const sender = `${row.connect.name}@${row.connect.domain}`;

    return (
        <div className="flex items-center justify-end gap-1">
            <Button variant="outline" size="sm" disabled={move.isPending} onClick={() => move.mutate(row)}>
                <ShieldCheck aria-hidden="true"/>
                {t('button.moveToWhitelist')}
            </Button>
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
        </div>
    );
}
