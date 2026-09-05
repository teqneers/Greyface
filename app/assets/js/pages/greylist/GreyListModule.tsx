import {keepPreviousData, useQuery} from '@tanstack/react-query';
import type {ColumnDef, RowSelectionState} from '@tanstack/react-table';
import {ChevronDown, Clock, Inbox, ShieldCheck, Trash2} from 'lucide-react';
import React, {useEffect, useMemo, useState} from 'react';
import {useTranslation} from 'react-i18next';

import {useApplication} from '@/application/ApplicationContext';
import ApplicationModuleContainer from '@/application/ApplicationModuleContainer';
import {usePermissions} from '@/application/usePermissions';
import {DataTable, DataTableBulkBar, DataTablePagination, DataTableToolbar} from '@/components/data-table';
import {ConfirmDialog} from '@/components/dialogs';
import {EmptyState} from '@/components/EmptyState';
import {FormattedDate} from '@/components/FormattedDate';
import {PageHeader} from '@/components/PageHeader';
import {Button} from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {UserSelect} from '@/components/UserSelect';
import {useListState} from '@/hooks/useListState';
import {apiFetch, listUrl} from '@/lib/api';
import type {ListResponse} from '@/lib/api';
import type {Greylist} from '@/types/greylist';

import {rowId} from './api';
import {DeleteByDateDialog} from './DeleteByDateDialog';
import {GreylistHelpButton, GreylistHelpCallout} from './GreylistHelp';
import {needsConfirmation} from './api';
import type {ListTarget} from './api';
import {GreylistRowActions, useGreylistMutations} from './GreylistActions';
import {GreylistStats} from './GreylistStats';

const GreyListModule: React.FC = () => {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const {isAdministrator} = usePermissions();
    const admin = isAdministrator();

    const {state, setPage, setPageSize, setSort, setQuery, setFilter, clampPage} = useListState({
        key: 'greylist',
        defaultSort: {id: 'firstSeen', desc: true},
        filterKeys: admin ? ['user'] : [],
    });
    const [selection, setSelection] = useState<RowSelectionState>({});
    const [bulkDelete, setBulkDelete] = useState(false);
    const [bulkTarget, setBulkTarget] = useState<ListTarget | null>(null);

    const {data, isLoading, isFetching, isError, error} = useQuery({
        queryKey: ['greylist', 'list', state],
        queryFn: () => apiFetch<ListResponse<Greylist>>(listUrl(`${apiUrl}/greylist`, state, {user: state.filters.user})),
        placeholderData: keepPreviousData,
    });

    useEffect(() => {
        if (data) {
            clampPage(data.count);
        }
    }, [data, clampPage]);

    // Selection is per page; anything not on screen any more is dropped. `state`
    // is memoised per URL, so this fires exactly when the listing changes.
    const [selectedIn, setSelectedIn] = useState(state);
    if (state !== selectedIn) {
        setSelectedIn(state);
        setSelection({});
    }

    const {bulkMove, bulkRemove, moveToList} = useGreylistMutations();

    const rows = useMemo(() => data?.results ?? [], [data]);
    const selectedRows = useMemo(() => rows.filter((row) => selection[rowId(row)]), [rows, selection]);
    const sendSelectionTo = (target: ListTarget): void => {
        if (needsConfirmation(target)) {
            setBulkTarget(target);
            return;
        }
        moveToList.mutate({rows: selectedRows, target}, {onSuccess: () => setSelection({})});
    };

    const columns = useMemo<ColumnDef<Greylist>[]>(() => {
        const base: ColumnDef<Greylist>[] = [
            {
                id: 'name',
                header: t('greylist.sender'),
                accessorFn: (row) => row.connect.name,
                cell: ({row}) => <span className="font-medium break-all">{row.original.connect.name}</span>,
            },
            {id: 'domain', header: t('greylist.domain'), accessorFn: (row) => row.connect.domain},
            {
                id: 'source',
                header: t('greylist.source'),
                accessorFn: (row) => row.connect.source,
                cell: ({getValue}) => <code className="text-xs text-foreground/80">{getValue<string>()}</code>,
                meta: {nowrap: true},
            },
            {id: 'rcpt', header: t('greylist.recipient'), accessorFn: (row) => row.connect.rcpt},
            {
                id: 'firstSeen',
                header: t('greylist.firstSeen'),
                accessorFn: (row) => row.connect.firstSeen,
                cell: ({row}) => <FormattedDate value={row.original.connect.firstSeen} style="dateTimeSeconds"/>,
                meta: {nowrap: true},
            },
        ];
        if (admin) {
            base.push({
                id: 'username',
                header: t('greylist.username'),
                accessorFn: (row) => row.username,
                cell: ({getValue}) => getValue<string | null>() ?? <span className="text-muted-foreground">–</span>,
            });
        }
        base.push({
            id: 'actions',
            header: () => <span className="sr-only">{t('table.actions')}</span>,
            enableSorting: false,
            cell: ({row}) => <GreylistRowActions row={row.original}/>,
            meta: {align: 'right', nowrap: true},
        });
        return base;
    }, [t, admin]);

    return (
        <ApplicationModuleContainer title="greylist.header">
            <PageHeader
                title={t('greylist.header')}
                description={admin ? t('greylist.adminIntro') : undefined}
                actions={<>
                    <GreylistHelpButton/>
                    {admin && <DeleteByDateDialog/>}
                </>}
            />

            {!admin && <GreylistHelpCallout/>}
            {!admin && <GreylistStats/>}

            <div className="space-y-3">
                <DataTableToolbar
                    query={state.query}
                    onQueryChange={setQuery}
                    filters={admin && (
                        <UserSelect value={state.filters.user ?? ''} onChange={(value) => setFilter('user', value)} withUnassigned/>
                    )}
                />

                <DataTableBulkBar count={selectedRows.length} onClear={() => setSelection({})}>
                    <Button size="sm" variant="outline" disabled={bulkMove.isPending}
                            onClick={() => bulkMove.mutate(selectedRows, {onSuccess: () => setSelection({})})}>
                        <ShieldCheck aria-hidden="true"/>
                        {t('greylist.bulkWhitelist')}
                    </Button>
                    {/* The same destinations the row menu offers, so a selection
                        can do anything a single row can. Administrators only,
                        like the lists they write to. */}
                    {admin && (
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button size="sm" variant="outline" disabled={moveToList.isPending}>
                                    {t('greylist.bulkSendTo')}
                                    <ChevronDown aria-hidden="true"/>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="start" className="w-72">
                                <DropdownMenuLabel className="text-muted-foreground text-xs font-normal">
                                    {t('greylist.scope.selectedSenders', {count: selectedRows.length})}
                                </DropdownMenuLabel>
                                <DropdownMenuItem onSelect={() => sendSelectionTo('whitelist-email')}>
                                    {t('greylist.target.neverGreylist')}
                                </DropdownMenuItem>
                                <DropdownMenuItem variant="destructive"
                                                  onSelect={() => sendSelectionTo('blacklist-email')}>
                                    {t('greylist.target.alwaysGreylist')}
                                </DropdownMenuItem>
                                <DropdownMenuSeparator/>
                                <DropdownMenuLabel className="text-muted-foreground text-xs font-normal">
                                    {t('greylist.scope.selectedDomains')}
                                </DropdownMenuLabel>
                                <DropdownMenuItem onSelect={() => sendSelectionTo('auto-whitelist-domain')}>
                                    {t('greylist.target.trustFromSourceBulk')}
                                </DropdownMenuItem>
                                <DropdownMenuItem onSelect={() => sendSelectionTo('whitelist-domain')}>
                                    {t('greylist.target.neverGreylist')}
                                </DropdownMenuItem>
                                <DropdownMenuItem variant="destructive"
                                                  onSelect={() => sendSelectionTo('blacklist-domain')}>
                                    {t('greylist.target.alwaysGreylist')}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    )}
                    <Button size="sm" variant="outline" className="text-destructive" onClick={() => setBulkDelete(true)}>
                        <Trash2 aria-hidden="true"/>
                        {t('greylist.bulkDelete')}
                    </Button>
                </DataTableBulkBar>

                {isError ? (
                    <EmptyState icon={Clock} title={t('errors.loadFailed')} description={(error as Error).message}/>
                ) : (
                    <DataTable
                        columns={columns}
                        data={rows}
                        getRowId={rowId}
                        sort={state.sort}
                        onSortChange={setSort}
                        isLoading={isLoading}
                        isFetching={isFetching}
                        selection={selection}
                        onSelectionChange={setSelection}
                        emptyState={
                            <EmptyState
                                icon={Inbox}
                                title={t('greylist.empty.title')}
                                description={state.query || state.filters.user ? t('greylist.empty.filtered') : t('greylist.empty.description')}
                            />
                        }
                    />
                )}

                <DataTablePagination
                    page={state.page}
                    pageSize={state.pageSize}
                    rowCount={data?.count ?? 0}
                    onPageChange={setPage}
                    onPageSizeChange={setPageSize}
                />
            </div>

            <ConfirmDialog
                open={bulkTarget !== null}
                onOpenChange={(open) => !open && setBulkTarget(null)}
                title={bulkTarget ? t(`greylist.confirm.${bulkTarget}.title`) : ''}
                description={bulkTarget
                    ? t(`greylist.confirmBulk.${bulkTarget}`, {count: selectedRows.length})
                    : ''}
                confirmLabel={bulkTarget ? t(`greylist.confirm.${bulkTarget}.action`) : undefined}
                destructive={bulkTarget?.startsWith('blacklist')}
                pending={moveToList.isPending}
                onConfirm={() => bulkTarget && moveToList.mutate(
                    {rows: selectedRows, target: bulkTarget},
                    {onSuccess: () => { setBulkTarget(null); setSelection({}); }}
                )}
            />
            <ConfirmDialog
                open={bulkDelete}
                onOpenChange={setBulkDelete}
                title={t('greylist.bulkDeleteTitle')}
                description={t('greylist.bulkDeleteMessage', {count: selectedRows.length})}
                destructive
                pending={bulkRemove.isPending}
                confirmLabel={t('greylist.bulkDeleteConfirm', {count: selectedRows.length})}
                onConfirm={() => bulkRemove.mutate(selectedRows, {
                    onSuccess: () => {
                        setSelection({});
                        setBulkDelete(false);
                    },
                })}
            />
        </ApplicationModuleContainer>
    );
};

export default GreyListModule;
