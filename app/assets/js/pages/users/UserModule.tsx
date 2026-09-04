import {keepPreviousData, useMutation, useQuery, useQueryClient} from '@tanstack/react-query';
import type {ColumnDef} from '@tanstack/react-table';
import {KeyRound, Pencil, Plus, Trash2, Users} from 'lucide-react';
import React, {useEffect, useMemo, useState} from 'react';
import {useTranslation} from 'react-i18next';
import {Route, Routes, useNavigate, useParams} from 'react-router-dom';
import {toast} from 'sonner';

import {useApplication} from '@/application/ApplicationContext';
import ApplicationModuleContainer from '@/application/ApplicationModuleContainer';
import {usePermissions} from '@/application/usePermissions';
import {DataTable, DataTablePagination, DataTableToolbar} from '@/components/data-table';
import {ConfirmDialog} from '@/components/dialogs';
import {EmptyState} from '@/components/EmptyState';
import {FormattedDate} from '@/components/FormattedDate';
import {PageHeader} from '@/components/PageHeader';
import {Badge} from '@/components/ui/badge';
import {Button} from '@/components/ui/button';
import {Tooltip, TooltipContent, TooltipTrigger} from '@/components/ui/tooltip';
import {useListState} from '@/hooks/useListState';
import {apiFetch, listUrl} from '@/lib/api';
import type {ListResponse} from '@/lib/api';
import type {User} from '@/types/user';

import {usersApi} from './api';
import {SetPasswordDialog} from './SetPasswordDialog';
import {UserFormDialog} from './UserFormDialog';

type DialogKind = 'create' | 'edit' | 'password';

/** Reads the dialog to show from the nested route and renders it. */
function UserDialogs({kind, users}: { kind: DialogKind, users: User[] }): React.ReactElement {
    const {id} = useParams();
    const navigate = useNavigate();
    const close = () => navigate('/users' + window.location.search, {replace: true});
    const username = users.find((user) => user.id === id)?.username;

    if (kind === 'password') {
        return <SetPasswordDialog open onOpenChange={(open) => !open && close()} userId={id} username={username}/>;
    }
    return <UserFormDialog open onOpenChange={(open) => !open && close()} userId={kind === 'edit' ? id : undefined}/>;
}

const UserModule: React.FC = () => {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const {isCurrentUser} = usePermissions();
    const navigate = useNavigate();
    const queryClient = useQueryClient();
    const api = useMemo(() => usersApi(apiUrl), [apiUrl]);

    const {state, setPage, setPageSize, setSort, setQuery, clampPage} = useListState({
        key: 'users',
        defaultSort: {id: 'username', desc: false},
    });
    const [deleting, setDeleting] = useState<User | undefined>();

    const {data, isLoading, isFetching, isError, error} = useQuery({
        queryKey: ['users', 'list', state],
        queryFn: () => apiFetch<ListResponse<User>>(listUrl(`${apiUrl}/users`, state)),
        placeholderData: keepPreviousData,
    });

    useEffect(() => {
        if (data) {
            clampPage(data.count);
        }
    }, [data, clampPage]);

    const remove = useMutation({
        mutationFn: (user: User) => api.remove(user.id),
        onSuccess: () => {
            toast.success(t('user.deleted'));
            queryClient.invalidateQueries({queryKey: ['users']});
            setDeleting(undefined);
        },
        onError: (failure: Error) => toast.error(failure.message),
    });

    const rows = useMemo(() => data?.results ?? [], [data]);

    const columns = useMemo<ColumnDef<User>[]>(() => [
        {
            id: 'username',
            header: t('user.username'),
            accessorKey: 'username',
            cell: ({getValue}) => <span className="font-medium">{getValue<string>()}</span>,
        },
        {id: 'email', header: t('user.email'), accessorKey: 'email'},
        {
            id: 'role',
            header: t('user.role'),
            accessorKey: 'role',
            cell: ({row}) => (
                <Badge variant={row.original.is_administrator ? 'default' : 'secondary'}>
                    {t(`user.roles.${row.original.role}`)}
                </Badge>
            ),
        },
        {
            id: 'created_at',
            header: t('user.createdAt'),
            accessorKey: 'created_at',
            enableSorting: false,
            cell: ({getValue}) => <FormattedDate value={getValue<string>()} style="date"/>,
            meta: {nowrap: true},
        },
        {
            id: 'actions',
            header: () => <span className="sr-only">{t('table.actions')}</span>,
            enableSorting: false,
            cell: ({row}) => {
                const user = row.original;
                return (
                    <div className="flex items-center justify-end gap-1">
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button variant="ghost" size="icon-sm" onClick={() => navigate(`/users/${user.id}/edit${window.location.search}`)}
                                        aria-label={t('user.editUser', {username: user.username})}>
                                    <Pencil aria-hidden="true"/>
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>{t('button.edit')}</TooltipContent>
                        </Tooltip>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button variant="ghost" size="icon-sm" onClick={() => navigate(`/users/${user.id}/password${window.location.search}`)}
                                        aria-label={t('user.setPasswordFor', {username: user.username})}>
                                    <KeyRound aria-hidden="true"/>
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>{t('user.setPassword')}</TooltipContent>
                        </Tooltip>
                        {!isCurrentUser(user) && (
                            <Tooltip>
                                <TooltipTrigger asChild>
                                    <Button variant="ghost" size="icon-sm" onClick={() => setDeleting(user)}
                                            aria-label={t('user.deleteUser', {username: user.username})}>
                                        <Trash2 aria-hidden="true"/>
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>{t('button.delete')}</TooltipContent>
                            </Tooltip>
                        )}
                    </div>
                );
            },
            meta: {align: 'right', nowrap: true},
        },
    ], [t, navigate, isCurrentUser]);

    return (
        <ApplicationModuleContainer title="user.header">
            <PageHeader title={t('user.header')} description={t('user.description')}/>

            <div className="space-y-3">
                <DataTableToolbar
                    query={state.query}
                    onQueryChange={setQuery}
                    actions={
                        <Button onClick={() => navigate(`/users/create${window.location.search}`)}>
                            <Plus aria-hidden="true"/>
                            {t('button.createUser')}
                        </Button>
                    }
                />

                {isError ? (
                    <EmptyState title={t('errors.loadFailed')} description={(error as Error).message}/>
                ) : (
                    <DataTable
                        columns={columns}
                        data={rows}
                        getRowId={(user) => user.id}
                        sort={state.sort}
                        onSortChange={setSort}
                        isLoading={isLoading}
                        isFetching={isFetching}
                        emptyState={<EmptyState icon={Users} title={t('placeholder.noData')} description={t('user.emptyDescription')}/>}
                    />
                )}

                <DataTablePagination page={state.page} pageSize={state.pageSize} rowCount={data?.count ?? 0}
                                     onPageChange={setPage} onPageSizeChange={setPageSize}/>
            </div>

            <Routes>
                <Route path="create" element={<UserDialogs kind="create" users={rows}/>}/>
                <Route path=":id/edit" element={<UserDialogs kind="edit" users={rows}/>}/>
                <Route path=":id/password" element={<UserDialogs kind="password" users={rows}/>}/>
            </Routes>

            <ConfirmDialog
                open={deleting !== undefined}
                onOpenChange={(open) => !open && setDeleting(undefined)}
                title={t('user.deleteHeader')}
                description={deleting ? t('user.deleteMessage', {username: deleting.username}) : undefined}
                destructive
                pending={remove.isPending}
                onConfirm={() => deleting && remove.mutate(deleting)}
            />
        </ApplicationModuleContainer>
    );
};

export default UserModule;
