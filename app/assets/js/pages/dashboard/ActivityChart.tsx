import {useQuery} from '@tanstack/react-query';
import {Table2} from 'lucide-react';
import React, {useMemo, useState} from 'react';
import {useTranslation} from 'react-i18next';
import {Bar, BarChart, CartesianGrid, XAxis, YAxis} from 'recharts';

import {useApplication} from '@/application/ApplicationContext';
import {useLocalizedDate} from '@/application/i18n';
import {toDate} from '@/components/FormattedDate';
import {Button} from '@/components/ui/button';
import {
    ChartContainer,
    ChartLegend,
    ChartLegendContent,
    ChartTooltip,
    ChartTooltipContent,
} from '@/components/ui/chart';
import type {ChartConfig} from '@/components/ui/chart';
import {Skeleton} from '@/components/ui/skeleton';
import {Table, TableBody, TableCell, TableHead, TableHeader, TableRow} from '@/components/ui/table';
import {apiFetch} from '@/lib/api';
import {cn} from '@/lib/utils';

import {ACTIVITY_RANGES} from './api';
import type {Activity, ActivityRange} from './api';

/**
 * Two series, one axis: entries still pending by the day they were first
 * seen, and senders SQLGrey accepted (auto-whitelist last seen) per day.
 * Colours come from the validated chart tokens; a table twin is one click away.
 */
export function ActivityChart(): React.ReactElement {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const {format} = useLocalizedDate();
    const [days, setDays] = useState<ActivityRange>(14);
    const [asTable, setAsTable] = useState(false);

    const {data, isLoading, isFetching} = useQuery({
        queryKey: ['dashboard', 'activity', days],
        queryFn: () => apiFetch<Activity>(`${apiUrl}/dashboard/activity?days=${days}`),
    });

    const config = useMemo<ChartConfig>(() => ({
        greylisted: {label: t('dashboard.activity.greylisted'), color: 'var(--chart-1)'},
        autoWhitelisted: {label: t('dashboard.activity.autoWhitelisted'), color: 'var(--chart-2)'},
    }), [t]);

    const rows = useMemo(() => (data?.buckets ?? []).map((bucket) => {
        const date = toDate(bucket.date) ?? new Date(bucket.date);
        return {...bucket, short: format(date, 'd.M.'), long: format(date, 'PPP')};
    }), [data, format]);

    const total = rows.reduce((sum, row) => sum + row.greylisted + row.autoWhitelisted, 0);

    return (
        <section className="rounded-lg border bg-card p-4 text-card-foreground sm:p-5" aria-labelledby="activity-title">
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 id="activity-title" className="text-sm font-semibold">{t('dashboard.activity.title')}</h2>
                    <p className="text-xs text-muted-foreground">{t('dashboard.activity.subtitle')}</p>
                </div>
                <div className="flex items-center gap-2">
                    <div role="radiogroup" aria-label={t('dashboard.activity.range')} className="flex rounded-md border p-0.5">
                        {ACTIVITY_RANGES.map((range) => (
                            <button
                                key={range}
                                type="button"
                                role="radio"
                                aria-checked={days === range}
                                onClick={() => setDays(range)}
                                className={cn(
                                    'rounded px-2.5 py-1 text-xs font-medium transition-colors',
                                    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                                    days === range ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground',
                                )}>
                                {t('dashboard.activity.days', {count: range})}
                            </button>
                        ))}
                    </div>
                    <Button variant="ghost" size="sm" aria-pressed={asTable} onClick={() => setAsTable((value) => !value)}>
                        <Table2 aria-hidden="true"/>
                        {t('dashboard.activity.table')}
                    </Button>
                </div>
            </div>

            {isLoading ? (
                <Skeleton className="h-64 w-full"/>
            ) : asTable ? (
                <div className="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{t('dashboard.activity.date')}</TableHead>
                                <TableHead className="text-right">{config.greylisted.label}</TableHead>
                                <TableHead className="text-right">{config.autoWhitelisted.label}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody className="tabular-nums">
                            {rows.map((row) => (
                                <TableRow key={row.date}>
                                    <TableCell>{row.long}</TableCell>
                                    <TableCell className="text-right">{row.greylisted}</TableCell>
                                    <TableCell className="text-right">{row.autoWhitelisted}</TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            ) : total === 0 ? (
                <div className="flex h-64 items-center justify-center text-sm text-muted-foreground">
                    {t('dashboard.activity.empty', {count: days})}
                </div>
            ) : (
                <ChartContainer config={config} className={cn('h-64 w-full', isFetching && 'opacity-60 transition-opacity')}>
                    <BarChart data={rows} barGap={2} barCategoryGap="30%" margin={{top: 8, right: 8, left: 0, bottom: 0}}>
                        <CartesianGrid vertical={false} strokeDasharray="0" stroke="var(--border)"/>
                        <XAxis dataKey="short" tickLine={false} axisLine={false} tickMargin={8} minTickGap={16}
                               className="text-xs"/>
                        <YAxis allowDecimals={false} tickLine={false} axisLine={false} width={32} className="text-xs"/>
                        <ChartTooltip cursor={{fill: 'var(--accent)'}}
                                      content={<ChartTooltipContent labelKey="long" labelFormatter={(_, payload) => payload?.[0]?.payload.long}/>}/>
                        <ChartLegend content={<ChartLegendContent/>}/>
                        <Bar dataKey="greylisted" fill="var(--color-greylisted)" radius={[4, 4, 0, 0]} maxBarSize={24}/>
                        <Bar dataKey="autoWhitelisted" fill="var(--color-autoWhitelisted)" radius={[4, 4, 0, 0]} maxBarSize={24}/>
                    </BarChart>
                </ChartContainer>
            )}
        </section>
    );
}
