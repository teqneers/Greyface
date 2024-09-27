import React, {useMemo} from 'react';
import {useTranslation} from 'react-i18next';
import {CellProps, Column, TableState} from 'react-table';

import LoadingIndicator from '../../../controllers/LoadingIndicator';
import Table from '../../../controllers/Table/Table';
import {WhiteListEmail} from '../../../types/greylist';
import DeleteEmail from './DeleteEmail';
import EditEmail from './EditEmail';

interface WhitelistEmailTableProps {
    data: WhiteListEmail[],
    refetch: () => void,
    pageCount: number,
    isFetching: boolean,
    initialState?: Partial<TableState<WhiteListEmail>>,
    onStateChange?: (state: TableState<WhiteListEmail>) => void,
}

const WhitelistEmailTable: React.FC<WhitelistEmailTableProps> = (
    {
        data,
        refetch,
        isFetching,
        pageCount,
        initialState,
        onStateChange
    }) => {

    const {t} = useTranslation();

    const columns = useMemo<Column<WhiteListEmail>[]>(() => [{
        Header: t('whitelist.email.email'),
        id: 'email',
        accessor: (originalRow) => originalRow.email,
        canSort: true,
        disableResizing: true
    }, {
        Header: '',
        id: 'actions',
        disableSortBy: true,
        disableResizing: true,
        Cell: ({row: {original: row}}: CellProps<WhiteListEmail, string>) => {
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
        <Table<WhiteListEmail>
            idColumn="email"
            data={data}
            pageCount={pageCount}
            columns={columns}
            disableSortRemove={true}
            onStateChange={onStateChange}
            initialState={initialState}/>
    );
};

export default WhitelistEmailTable;
