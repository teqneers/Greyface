import {useMutation, useQuery, useQueryClient} from '@tanstack/react-query';
import {format, subDays} from 'date-fns';
import {CalendarX2} from 'lucide-react';
import React, {useMemo, useState} from 'react';
import {useTranslation} from 'react-i18next';
import {toast} from 'sonner';

import {useApplication} from '@/application/ApplicationContext';
import {ConfirmDialog} from '@/components/dialogs';
import {Button} from '@/components/ui/button';
import {Input} from '@/components/ui/input';
import {Label} from '@/components/ui/label';
import {apiFetch, listUrl} from '@/lib/api';
import type {ListResponse} from '@/lib/api';
import {cn} from '@/lib/utils';

import {greylistApi} from './api';

const PRESETS = [7, 30, 90] as const;
type Preset = typeof PRESETS[number] | 'custom';

const isoDay = (date: Date) => format(date, 'yyyy-MM-dd');

/**
 * Deletes every entry first seen on or before a date. Presets cover the
 * usual "clear the backlog" cases; the count shown comes from the list
 * endpoint's "before" filter, so the admin sees what will go before confirming.
 */
export function DeleteByDateDialog(): React.ReactElement {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const queryClient = useQueryClient();
    const api = useMemo(() => greylistApi(apiUrl), [apiUrl]);

    const [open, setOpen] = useState(false);
    const [preset, setPreset] = useState<Preset>(30);
    const [custom, setCustom] = useState(() => isoDay(subDays(new Date(), 30)));

    const date = preset === 'custom' ? custom : isoDay(subDays(new Date(), preset));

    const {data: preview, isFetching} = useQuery({
        queryKey: ['greylist', 'count-before', date],
        queryFn: () => apiFetch<ListResponse<unknown>>(
            listUrl(`${apiUrl}/greylist`, {page: 0, pageSize: 1, sort: null, query: ''}, {before: date})
        ),
        enabled: open && date !== '',
    });

    const remove = useMutation({
        mutationFn: () => api.removeBefore(date),
        onSuccess: ({deleted}) => {
            toast.success(t('greylist.deleteByDate.done', {count: deleted}));
            queryClient.invalidateQueries({queryKey: ['greylist']});
            setOpen(false);
        },
        onError: (error: Error) => toast.error(error.message),
    });

    const count = preview?.count;

    return (
        <>
            <Button variant="outline" onClick={() => setOpen(true)}>
                <CalendarX2 aria-hidden="true"/>
                {t('button.deleteByDate')}
            </Button>
            <ConfirmDialog
                open={open}
                onOpenChange={setOpen}
                title={t('greylist.deleteByDate.title')}
                description={t('greylist.deleteByDate.description')}
                destructive
                pending={remove.isPending}
                confirmLabel={count === undefined ? t('button.delete') : t('greylist.deleteByDate.confirm', {count})}
                onConfirm={() => remove.mutate()}>
                <fieldset className="space-y-3">
                    <legend className="text-sm font-medium">{t('greylist.deleteByDate.olderThan')}</legend>
                    <div className="flex flex-wrap gap-2" role="radiogroup" aria-label={t('greylist.deleteByDate.olderThan')}>
                        {[...PRESETS, 'custom' as const].map((option) => (
                            <button
                                key={option}
                                type="button"
                                role="radio"
                                aria-checked={preset === option}
                                onClick={() => setPreset(option)}
                                className={cn(
                                    'rounded-md border px-3 py-1.5 text-sm transition-colors',
                                    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                                    preset === option ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-accent',
                                )}>
                                {option === 'custom' ? t('greylist.deleteByDate.custom') : t('greylist.deleteByDate.days', {count: option})}
                            </button>
                        ))}
                    </div>
                    {preset === 'custom' && (
                        <div className="space-y-1.5">
                            <Label htmlFor="delete-before">{t('greylist.deleteByDate.upTo')}</Label>
                            <Input id="delete-before" type="date" value={custom} max={isoDay(new Date())}
                                   onChange={(event) => setCustom(event.target.value)} className="w-48"/>
                        </div>
                    )}
                    <p className="text-sm text-muted-foreground" aria-live="polite">
                        {isFetching || count === undefined
                            ? t('greylist.deleteByDate.counting')
                            : t('greylist.deleteByDate.preview', {count, date})}
                    </p>
                </fieldset>
            </ConfirmDialog>
        </>
    );
}
