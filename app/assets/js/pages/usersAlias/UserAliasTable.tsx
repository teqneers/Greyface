import React, {useMemo} from 'react';
import {useTranslation} from 'react-i18next';
import {useHistory} from 'react-router-dom';
import {CellProps, Column, TableState} from 'react-table';

import DefaultButton from '../../controllers/Buttons/DefaultButton';
import DeleteButton from '../../controllers/Buttons/DeleteButton';
import LoadingIndicator from '../../controllers/LoadingIndicator';
import Table from '../../controllers/Table/Table';
import {UserAlias} from '../../types/user';

interface UserAliasTableProps {
    data: UserAlias[],
    pageCount: number,
    isFetching: boolean,
    initialState?: Partial<TableState<UserAlias>>,
    onStateChange?: (state: TableState<UserAlias>) => void,
}

const UserAliasTable: React.FC<UserAliasTableProps> = (
    {
        data,
        pageCount,
        isFetching,
        initialState,
        onStateChange
    }) => {

    const {t} = useTranslation();
    const history = useHistory();

    const columns = useMemo<Column<UserAlias>[]>(() => [{
        Header: t('alias.aliasName'),
        id: 'aliasName',
        accessor: (originalRow) => originalRow.alias_name,
        canSort: true,
        disableResizing: true
    }, {
        Header: t('user.username'),
        id: 'username',
        accessor: (originalRow) => originalRow.user.username,
        canSort: true,
        disableResizing: true
    }, {
        Header: '',
        id: 'actions',
        accessor: (originalRow) => originalRow.user.username,
        disableSortBy: true,
        disableResizing: true,
        Cell: ({row: {original: row}}: CellProps<UserAlias, string>) => {
            return <>
                <DefaultButton label="button.edit"
                        onClick={() => history.push(`/users-aliases/${row.id}/edit`)}/>
                <DeleteButton
                        onClick={() => history.push(`/users-aliases/${row.id}/delete`)}/>
            </>;
        }
    }], [t, history]);

    if (isFetching) {
        return <LoadingIndicator/>;
    }

    return (
        <Table<UserAlias>
            idColumn="aliasName,username"
            data={data}
            pageCount={pageCount}
            columns={columns}
            disableSortRemove={true}
            onStateChange={onStateChange}
            initialState={initialState}/>
    );
};

export default UserAliasTable;
