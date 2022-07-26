import React, {useMemo} from 'react';
import {useTranslation} from 'react-i18next';
import {useHistory} from 'react-router-dom';
import {CellProps, Column, TableState} from 'react-table';
import LoadingIndicator from '../../controllers/LoadingIndicator';
import Table from '../../controllers/Table/Table';
import {UserAlias} from '../../types/user';


interface UserAliasTableProps {
    data: UserAlias[],
    isFetching: boolean,
    initialState?: Partial<TableState<UserAlias>>,
    onStateChange?: (state: TableState<UserAlias>) => void,
}

const UserAliasTable: React.VFC<UserAliasTableProps> = (
    {
        data,
        isFetching,
        initialState,
        onStateChange
    }) => {

    const {t} = useTranslation();
    const history = useHistory();

    if (isFetching) {
        return <LoadingIndicator/>;
    }

    const columns = useMemo<Column<UserAlias>[]>(() => [{
        Header: t('alias.aliasName'),
        id: 'aliasName',
        width: 300,
        accessor: (originalRow) => originalRow.alias_name,
        disableSortBy: true,
        disableResizing: true
    }, {
        Header: t('user.username'),
        id: 'username',
        accessor: (originalRow) => originalRow.user.username,
        width: 700,
        disableSortBy: true,
        disableResizing: true
    }], [history, t]);

    console.log(data);
    return (
        <div>
            <Table<UserAlias>
                idColumn="aliasName"
                data={data}
                columns={columns}
                disableSortRemove={true}
                onStateChange={onStateChange}
                initialState={initialState}/>
        </div>
    );
};

export default UserAliasTable;
