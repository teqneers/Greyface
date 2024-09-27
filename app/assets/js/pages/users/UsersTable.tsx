import React, {useMemo} from 'react';
import {useTranslation} from 'react-i18next';
import {useHistory} from 'react-router-dom';
import {CellProps, Column, TableState} from 'react-table';

import {usePermissions} from '../../application/usePermissions';
import DefaultButton from '../../controllers/Buttons/DefaultButton';
import DeleteButton from '../../controllers/Buttons/DeleteButton';
import LoadingIndicator from '../../controllers/LoadingIndicator';
import Table from '../../controllers/Table/Table';
import {User, UserAlias} from '../../types/user';

interface UsersTableProps {
    data: User[],
    pageCount: number,
    isFetching: boolean,
    initialState?: Partial<TableState<UserAlias>>,
    onStateChange?: (state: TableState<UserAlias>) => void,
}

const UsersTable: React.FC<UsersTableProps> = (
    {
        data,
        isFetching,
        pageCount,
        initialState,
        onStateChange
    }) => {

    const {t} = useTranslation();
    const history = useHistory();

    const {isCurrentUser} = usePermissions();

    const columns = useMemo<Column<User>[]>(() => [{
        Header: t('user.username'),
        id: 'username',
        accessor: (originalRow) => originalRow.username,
        canSort: true,
        disableResizing: true
    }, {
        Header: t('user.email'),
        id: 'email',
        accessor: (originalRow) => originalRow.email,
        canSort: true,
        disableResizing: true
    }, {
        Header: t('user.role'),
        id: 'role',
        accessor: (originalRow) => t(`user.roles.${originalRow.role}`),
        canSort: true,
        disableResizing: true
    }, {
        Header: '',
        id: 'actions',
        disableSortBy: true,
        disableResizing: true,
        Cell: ({row: {original: row}}: CellProps<User, string>) => {
            return <>
                <DefaultButton label="button.changePassword"
                        onClick={() => history.push(`/users/${row.id}/password`)}/>
                <DefaultButton label="button.edit"
                        onClick={() => history.push(`/users/${row.id}/edit`)}/>
                {!isCurrentUser(row) && <DeleteButton
                        onClick={() => history.push(`/users/${row.id}/delete`)}/>}
            </>;
        }
    }], [t, history, isCurrentUser]);

    if (isFetching) {
        return <LoadingIndicator/>;
    }

    return (
            <Table<User>
                idColumn="id"
                data={data}
                pageCount={pageCount}
                columns={columns}
                disableSortRemove={true}
                onStateChange={onStateChange}
                initialState={initialState}/>
    );
};

export default UsersTable;
