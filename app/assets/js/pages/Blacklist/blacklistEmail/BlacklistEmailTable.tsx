import React, {useMemo} from 'react';
import {useTranslation} from 'react-i18next';
import {CellProps, Column, TableState} from 'react-table';

import LoadingIndicator from '../../../controllers/LoadingIndicator';
import Table from '../../../controllers/Table/Table';
import {BlackListEmail} from '../../../types/greylist';
import DeleteEmail from './DeleteEmail';
import EditEmail from './EditEmail';

interface BlacklistEmailTableProps {
    data: BlackListEmail[],
    refetch: () => void,
    pageCount: number,
    isFetching: boolean,
    initialState?: Partial<TableState<BlackListEmail>>,
    onStateChange?: (state: TableState<BlackListEmail>) => void,
}

const BlacklistEmailTable: React.VFC<BlacklistEmailTableProps> = (
    {
        data,
        refetch,
        isFetching,
        pageCount,
        initialState,
        onStateChange
    }) => {

    const {t} = useTranslation();

    const columns = useMemo<Column<BlackListEmail>[]>(() => [{
        Header: t('blacklist.email.email'),
        id: 'email',
        accessor: (originalRow) => originalRow.email,
        canSort: true,
        disableResizing: true
    }, {
        Header: '',
        id: 'actions',
        width: 100,
        minWidth: 100,
        maxWidth: 100,
        disableSortBy: true,
        disableResizing: true,
        Cell: ({row: {original: row}}: CellProps<BlackListEmail, string>) => {
            return <>
                <EditEmail onUpdate={refetch} data={row}/>
                <DeleteEmail onDelete={refetch} data={row}/>
            </>;
        }
    }], [t, refetch]);

    if (isFetching) {
        return <LoadingIndicator/>;
    }

    return (
        <Table<BlackListEmail>
            idColumn="email"
            data={data}
            pageCount={pageCount}
            columns={columns}
            disableSortRemove={true}
            onStateChange={onStateChange}
            initialState={initialState}/>
    );
};

export default BlacklistEmailTable;
