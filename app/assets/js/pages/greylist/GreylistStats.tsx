import {useQuery} from '@tanstack/react-query';
import {Hourglass, History} from 'lucide-react';
import React from 'react';
import {useTranslation} from 'react-i18next';

import {useApplication} from '@/application/ApplicationContext';
import {FormattedDate} from '@/components/FormattedDate';
import {Skeleton} from '@/components/ui/skeleton';
import {apiFetch, listUrl} from '@/lib/api';
import type {ListResponse} from '@/lib/api';
import type {Greylist} from '@/types/greylist';

function Stat({icon: Icon, label, children}: { icon: React.ElementType, label: string, children: React.ReactNode }) {
    return (
        <div className="flex items-center gap-3 rounded-lg border bg-card px-4 py-3">
            <div className="flex size-9 items-center justify-center rounded-md bg-muted text-muted-foreground">
                <Icon className="size-4" aria-hidden="true"/>
            </div>
            <div className="min-w-0">
                <div className="text-xs text-muted-foreground">{label}</div>
                <div className="text-lg font-semibold leading-tight">{children}</div>
            </div>
        </div>
    );
}

/** Two numbers an end user cares about: how much is waiting, and since when. */
export function GreylistStats(): React.ReactElement {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();

    const {data, isLoading} = useQuery({
        queryKey: ['greylist', 'stats'],
        queryFn: () => apiFetch<ListResponse<Greylist>>(
            listUrl(`${apiUrl}/greylist`, {page: 0, pageSize: 1, sort: {id: 'firstSeen', desc: false}, query: ''})
        ),
    });

    const oldest = data?.results[0]?.connect.firstSeen;

    return (
        <div className="mb-5 grid gap-3 sm:grid-cols-2 lg:max-w-xl">
            <Stat icon={Hourglass} label={t('greylist.stats.pending')}>
                {isLoading ? <Skeleton className="h-6 w-12"/> : data?.count ?? 0}
            </Stat>
            <Stat icon={History} label={t('greylist.stats.oldest')}>
                {isLoading ? <Skeleton className="h-6 w-32"/> : oldest
                    ? <span className="text-base"><FormattedDate value={oldest} style="date"/></span>
                    : <span className="text-base text-muted-foreground">–</span>}
            </Stat>
        </div>
    );
}
