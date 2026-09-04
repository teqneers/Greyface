import {keepPreviousData, useMutation, useQuery, useQueryClient} from '@tanstack/react-query';
import type {ColumnDef} from '@tanstack/react-table';
import {AtSign, Pencil, Plus, Trash2} from 'lucide-react';
import React, {useEffect, useMemo, useState} from 'react';
import {useTranslation} from 'react-i18next';
import {Route, Routes, useNavigate, useParams} from 'react-router-dom';
import {toast} from 'sonner';

import {useApplication} from '@/application/ApplicationContext';
import ApplicationModuleContainer from '@/application/ApplicationModuleContainer';
import {DataTable, DataTablePagination, DataTableToolbar} from '@/components/data-table';
import {ConfirmDialog} from '@/components/dialogs';
import {EmptyState} from '@/components/EmptyState';
import {PageHeader} from '@/components/PageHeader';
import {Button} from '@/components/ui/button';
import {Tooltip, TooltipContent, TooltipTrigger} from '@/components/ui/tooltip';
import {UserSelect} from '@/components/UserSelect';
import {useListState} from '@/hooks/useListState';
import {apiFetch, listUrl} from '@/lib/api';
import type {ListResponse} from '@/lib/api';
import type {UserAlias} from '@/types/user';

import {UserAliasFormDialog} from './UserAliasFormDialog';

function AliasDialog({editing}: { editing: boolean }): React.ReactElement {
    const {id} = useParams();
    const navigate = useNavigate();
    const close = () => navigate('/users-aliases' + window.location.search, {replace: true});
    return <UserAliasFormDialog open onOpenChange={(open) => !open && close()} aliasId={editing ? id : undefined}/>;
}

const UserAliasModule: React.FC = () => {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const navigate = useNavigate();
    const queryClient = useQueryClient();

    const {state, setPage, setPageSize, setSort, setQuery, setFilter, clampPage} = useListState({
        key: 'users-aliases',
        defaultSort: {id: 'aliasName', desc: false},
        filterKeys: ['user'],
    });
    const [deleting, setDeleting] = useState<UserAlias | undefined>();

    const {data, isLoading, isFetching, isError, error} = useQuery({
        queryKey: ['users-aliases', 'list', state],
        queryFn: () => apiFetch<ListResponse<UserAlias>>(listUrl(`${apiUrl}/users-aliases`, state, {user: state.filters.user})),
        placeholderData: keepPreviousData,
    });

    useEffect(() => {
        if (data) {
            clampPage(data.count);
        }
    }, [data, clampPage]);

    const remove = useMutation({
        mutationFn: (alias: UserAlias) => apiFetch(`${apiUrl}/users-aliases/${alias.id}`, {method: 'DELETE'}),
        onSuccess: () => {
            toast.success(t('alias.deleted'));
            queryClient.invalidateQueries({queryKey: ['users-aliases']});
            setDeleting(undefined);
        },
        onError: (failure: Error) => toast.error(failure.message),
    });

    const rows = useMemo(() => data?.results ?? [], [data]);

    const columns = useMemo<ColumnDef<UserAlias>[]>(() => [
        {
            id: 'aliasName',
            header: t('alias.aliasName'),
            accessorKey: 'alias_name',
            cell: ({getValue}) => <span className="font-medium break-all">{getValue<string>()}</span>,
        },
        {id: 'username', header: t('alias.user'), accessorFn: (row) => row.user.username},
        {
            id: 'actions',
            header: () => <span className="sr-only">{t('table.actions')}</span>,
            enableSorting: false,
            cell: ({row}) => (
                <div className="flex items-center justify-end gap-1">
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <Button variant="ghost" size="icon-sm" onClick={() => navigate(`/users-aliases/${row.original.id}/edit${window.location.search}`)}
                                    aria-label={t('lists.editEntry', {label: row.original.alias_name})}>
                                <Pencil aria-hidden="true"/>
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>{t('button.edit')}</TooltipContent>
                    </Tooltip>
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <Button variant="ghost" size="icon-sm" onClick={() => setDeleting(row.original)}
                                    aria-label={t('lists.deleteEntry', {label: row.original.alias_name})}>
                                <Trash2 aria-hidden="true"/>
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>{t('button.delete')}</TooltipContent>
                    </Tooltip>
                </div>
            ),
            meta: {align: 'right', nowrap: true},
        },
    ], [t, navigate]);

    return (
        <ApplicationModuleContainer title="alias.header">
            <PageHeader title={t('alias.header')} description={t('alias.description')}/>

            <div className="space-y-3">
                <DataTableToolbar
                    query={state.query}
                    onQueryChange={setQuery}
                    filters={<UserSelect value={state.filters.user ?? ''} onChange={(value) => setFilter('user', value)}/>}
                    actions={
                        <Button onClick={() => navigate(`/users-aliases/create${window.location.search}`)}>
                            <Plus aria-hidden="true"/>
                            {t('button.createUserAlias')}
                        </Button>
                    }
                />

                {isError ? (
                    <EmptyState title={t('errors.loadFailed')} description={(error as Error).message}/>
                ) : (
                    <DataTable
                        columns={columns}
                        data={rows}
                        getRowId={(alias) => alias.id}
                        sort={state.sort}
                        onSortChange={setSort}
                        isLoading={isLoading}
                        isFetching={isFetching}
                        emptyState={
                            <EmptyState icon={AtSign} title={t('placeholder.noData')}
                                        description={state.query || state.filters.user ? t('lists.empty.filtered') : t('alias.emptyDescription')}/>
                        }
                    />
                )}

                <DataTablePagination page={state.page} pageSize={state.pageSize} rowCount={data?.count ?? 0}
                                     onPageChange={setPage} onPageSizeChange={setPageSize}/>
            </div>

            <Routes>
                <Route path="create" element={<AliasDialog editing={false}/>}/>
                <Route path=":id/edit" element={<AliasDialog editing/>}/>
            </Routes>

            <ConfirmDialog
                open={deleting !== undefined}
                onOpenChange={(open) => !open && setDeleting(undefined)}
                title={t('alias.deleteHeader')}
                description={deleting ? t('alias.deleteMessage', {alias: deleting.alias_name}) : undefined}
                destructive
                pending={remove.isPending}
                onConfirm={() => deleting && remove.mutate(deleting)}
            />
        </ApplicationModuleContainer>
    );
};

export default UserAliasModule;
