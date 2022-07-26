import React, {useMemo} from 'react';
import {useTranslation} from 'react-i18next';
import {useHistory} from 'react-router-dom';
import {Column, TableState} from 'react-table';
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

const UserAliasTable: React.VFC<UserAliasTableProps> = (
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
        width: 300,
        accessor: (originalRow) => originalRow.alias_name,
        canSort: true,
        disableResizing: true
    }, {
        Header: t('user.username'),
        id: 'username',
        accessor: (originalRow) => originalRow.user.username,
        width: 700,
        canSort: true,
        disableResizing: true
    }], [t]);

    if (isFetching) {
        return <LoadingIndicator/>;
    }

    return (
        <div>
            <Table<UserAlias>
                idColumn="aliasName"
                data={data}
                pageCount={pageCount}
                columns={columns}
                disableSortRemove={true}
                onStateChange={onStateChange}
                initialState={initialState}/>
        </div>
    );
};

export default UserAliasTable;
