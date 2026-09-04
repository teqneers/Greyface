import {keepPreviousData, useMutation, useQuery, useQueryClient} from '@tanstack/react-query';
import type {ColumnDef, RowSelectionState} from '@tanstack/react-table';
import {Inbox, Pencil, Plus, Trash2} from 'lucide-react';
import React, {useEffect, useMemo, useState} from 'react';
import {useTranslation} from 'react-i18next';
import {useNavigate, useLocation} from 'react-router-dom';
import {toast} from 'sonner';

import {useApplication} from '@/application/ApplicationContext';
import {DataTable, DataTableBulkBar, DataTablePagination, DataTableToolbar} from '@/components/data-table';
import {ConfirmDialog} from '@/components/dialogs';
import {EmptyState} from '@/components/EmptyState';
import {Button} from '@/components/ui/button';
import {Tooltip, TooltipContent, TooltipTrigger} from '@/components/ui/tooltip';
import {useListState} from '@/hooks/useListState';
import {apiFetch, apiJson, listUrl} from '@/lib/api';
import type {ListResponse} from '@/lib/api';

import type {EntryConfig} from './config';
import {EntryFormDialog} from './EntryFormDialog';

export interface EntryListProps<T> {
    config: EntryConfig<T, any>,
    i18n: string,
    /** Whether the URL currently ends in /create. */
    creating: boolean,
}

/**
 * One tab of a list screen: table, toolbar, bulk delete, and the create,
 * edit and delete dialogs. Create is a route (…/create) so it can be linked;
 * edit and delete are local because the rows have composite keys.
 */
export function EntryList<T>({config, i18n, creating}: EntryListProps<T>): React.ReactElement {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const navigate = useNavigate();
    const location = useLocation();
    const queryClient = useQueryClient();

    const {state, setPage, setPageSize, setSort, setQuery, clampPage} = useListState({
        key: config.storageKey,
        defaultSort: config.defaultSort,
    });
    const [selection, setSelection] = useState<RowSelectionState>({});
    const [editing, setEditing] = useState<T | undefined>();
    const [deleting, setDeleting] = useState<T | undefined>();
    const [bulkDelete, setBulkDelete] = useState(false);

    const queryKey = ['list', config.apiPath, state];
    const {data, isLoading, isFetching, isError, error} = useQuery({
        queryKey,
        queryFn: () => apiFetch<ListResponse<T>>(listUrl(`${apiUrl}${config.apiPath}`, state)),
        placeholderData: keepPreviousData,
    });

    useEffect(() => {
        if (data) {
            clampPage(data.count);
        }
    }, [data, clampPage]);

    useEffect(() => {
        setSelection({});
    }, [state]);

    const invalidate = () => queryClient.invalidateQueries({queryKey: ['list', config.apiPath]});

    const remove = useMutation({
        mutationFn: (row: T) => apiJson(`${apiUrl}${config.apiPath}/delete`, 'DELETE', config.deleteBody(row)),
        onSuccess: () => {
            toast.success(t('lists.deleted'));
            invalidate();
            setDeleting(undefined);
        },
        onError: (failure: Error) => toast.error(failure.message),
    });

    // No bulk endpoint on these lists; the page is at most 100 rows.
    const bulkRemove = useMutation({
        mutationFn: async (rows: T[]) => {
            const results = await Promise.allSettled(
                rows.map((row) => apiJson(`${apiUrl}${config.apiPath}/delete`, 'DELETE', config.deleteBody(row)))
            );
            return results.filter((result) => result.status === 'fulfilled').length;
        },
        onSuccess: (count, rows) => {
            if (count < rows.length) {
                toast.warning(t('lists.bulkDeletedPartial', {count, total: rows.length}));
            } else {
                toast.success(t('lists.bulkDeleted', {count}));
            }
            invalidate();
            setSelection({});
            setBulkDelete(false);
        },
    });

    const rows = useMemo(() => data?.results ?? [], [data]);
    const selectedRows = useMemo(() => rows.filter((row) => selection[config.getRowId(row)]), [rows, selection, config]);

    const columns = useMemo<ColumnDef<T, any>[]>(() => [
        ...config.columns,
        {
            id: 'actions',
            header: () => <span className="sr-only">{t('table.actions')}</span>,
            enableSorting: false,
            cell: ({row}) => (
                <div className="flex items-center justify-end gap-1">
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <Button variant="ghost" size="icon-sm" onClick={() => setEditing(row.original)}
                                    aria-label={t('lists.editEntry', {label: config.label(row.original)})}>
                                <Pencil aria-hidden="true"/>
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>{t('button.edit')}</TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <Button variant="ghost" size="icon-sm" onClick={() => setDeleting(row.original)}
                                    aria-label={t('lists.deleteEntry', {label: config.label(row.original)})}>
                                <Trash2 aria-hidden="true"/>
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>{t('button.delete')}</TooltipContent>
                    </Tooltip>
                </div>
            ),
            meta: {align: 'right', nowrap: true},
        },
    ], [config, t]);

    const kindKey = config.kind === 'emails' ? 'email' : 'domain';
    const basePath = location.pathname.replace(/\/create$/, '');

    return (
        <div className="space-y-3">
            <DataTableToolbar
                query={state.query}
                onQueryChange={setQuery}
                actions={
                    <Button onClick={() => navigate(`${basePath}/create`)}>
                        <Plus aria-hidden="true"/>
                        {t(`lists.add.${config.kind}`)}
                    </Button>
                }
            />

            <DataTableBulkBar count={selectedRows.length} onClear={() => setSelection({})}>
                <Button size="sm" variant="outline" className="text-destructive" onClick={() => setBulkDelete(true)}>
                    <Trash2 aria-hidden="true"/>
                    {t('lists.bulkDelete')}
                </Button>
            </DataTableBulkBar>

            {isError ? (
                <EmptyState title={t('errors.loadFailed')} description={(error as Error).message}/>
            ) : (
                <DataTable
                    columns={columns}
                    data={rows}
                    getRowId={config.getRowId}
                    sort={state.sort}
                    onSortChange={setSort}
                    isLoading={isLoading}
                    isFetching={isFetching}
                    selection={selection}
                    onSelectionChange={setSelection}
                    emptyState={
                        <EmptyState
                            icon={Inbox}
                            title={t('lists.empty.title')}
                            description={state.query ? t('lists.empty.filtered') : t(`${i18n}.${kindKey}.emptyDescription`)}
                            action={!state.query && (
                                <Button variant="outline" onClick={() => navigate(`${basePath}/create`)}>
                                    <Plus aria-hidden="true"/>
                                    {t(`lists.add.${config.kind}`)}
                                </Button>
                            )}
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

            <EntryFormDialog
                config={config}
                i18n={i18n}
                open={creating}
                onOpenChange={(open) => !open && navigate(basePath + location.search, {replace: true})}
            />
            <EntryFormDialog
                config={config}
                i18n={i18n}
                open={editing !== undefined}
                onOpenChange={(open) => !open && setEditing(undefined)}
                row={editing}
            />
            <ConfirmDialog
                open={deleting !== undefined}
                onOpenChange={(open) => !open && setDeleting(undefined)}
                title={t(`${i18n}.${kindKey}.deleteHeader`)}
                description={deleting ? t('lists.deleteMessage', {label: config.label(deleting)}) : undefined}
                destructive
                pending={remove.isPending}
                onConfirm={() => deleting && remove.mutate(deleting)}
            />
            <ConfirmDialog
                open={bulkDelete}
                onOpenChange={setBulkDelete}
                title={t('lists.bulkDeleteTitle')}
                description={t('lists.bulkDeleteMessage', {count: selectedRows.length})}
                destructive
                pending={bulkRemove.isPending}
                confirmLabel={t('lists.bulkDeleteConfirm', {count: selectedRows.length})}
                onConfirm={() => bulkRemove.mutate(selectedRows)}
            />
        </div>
    );
}
