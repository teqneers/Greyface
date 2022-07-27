import React, {useMemo} from 'react';
import {Button} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useHistory} from 'react-router-dom';
import {CellProps, Column, TableState} from 'react-table';
import {usePermissions} from '../../application/usePermissions';
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

const UsersTable: React.VFC<UsersTableProps> = (
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
                <Button className="m-1" variant="outline-primary" size="sm"
                        onClick={() => history.push(`/users/${row.id}/edit`)}>Edit</Button>
                {!isCurrentUser(row) && <Button size="sm" variant="outline-danger"
                        onClick={() => history.push(`/users/${row.id}/delete`)}>Delete</Button>}
            </>;
        }
    }], [t, history, isCurrentUser]);

    if (isFetching) {
        return <LoadingIndicator/>;
    }
    console.log(data);
    return (

        <div>
            <Table<User>
                idColumn="id"
                data={data}
                pageCount={pageCount}
                columns={columns}
                disableSortRemove={true}
                onStateChange={onStateChange}
                initialState={initialState}/>
        </div>
    );
};

export default UsersTable;
