import React, {useMemo} from 'react';
import {useTranslation} from 'react-i18next';
import {CellProps, Column, TableState} from 'react-table';

import LoadingIndicator from '../../controllers/LoadingIndicator';
import Table from '../../controllers/Table/Table';
import DisplayDate from '../../controllers/DisplayDate';
import {DATE_TIME_SECONDS_FORMAT} from '../../types/common';
import {Greylist} from '../../types/greylist';
import DeleteGreyList from './DeleteGreyList';
import MoveToWhiteList from './MoveToWhiteList';

interface GreyListTableProps {
    data: Greylist[],
    refetch: () => void,
    pageCount: number,
    isFetching: boolean,
    initialState?: Partial<TableState<Greylist>>,
    onStateChange?: (state: TableState<Greylist>) => void,
}

const GreyListTable: React.VFC<GreyListTableProps> = (
    {
        data,
        refetch,
        isFetching,
        pageCount,
        initialState,
        onStateChange
    }) => {

    const {t} = useTranslation();

    const columns = useMemo<Column<Greylist>[]>(() => [{
        Header: t('greylist.sender'),
        id: 'name',
        accessor: (originalRow) => originalRow.connect.name,
        canSort: true,
        disableResizing: true
    }, {
        Header: t('greylist.domain'),
        id: 'domain',
        accessor: (originalRow) => originalRow.connect.domain,
        canSort: true,
        disableResizing: true
    }, {
        Header: t('greylist.source'),
        id: 'source',
        accessor: (originalRow) => originalRow.connect.source,
        canSort: true,
        disableResizing: true
    }, {
        Header: t('greylist.recipient'),
        id: 'rcpt',
        accessor: (originalRow) => originalRow.connect.rcpt,
        canSort: true,
        disableResizing: true
    }, {
        Header: t('greylist.firstSeen'),
        id: 'firstSeen',
        accessor: (originalRow) => <DisplayDate date={originalRow.connect.firstSeen}
                                                format={DATE_TIME_SECONDS_FORMAT}/>,
        canSort: true,
        disableResizing: true
    }, {
        Header: t('greylist.username'),
        id: 'username',
        accessor: (originalRow) => originalRow.username,
        canSort: true,
        disableResizing: true
    }, {
        Header: '',
        id: 'actions',
        disableSortBy: true,
        disableResizing: true,
        Cell: ({row: {original: row}}: CellProps<Greylist, string>) => {
            return <>
                <MoveToWhiteList onMove={refetch} data={row}/>
                <DeleteGreyList onDelete={refetch} data={row}/>
            </>;
        }
    }], [t, refetch]);

    if (isFetching) {
        return <LoadingIndicator/>;
    }

    return (
        <Table<Greylist>
            idColumn="connect.domain,username"
            data={data}
            pageCount={pageCount}
            columns={columns}
            disableSortRemove={true}
            onStateChange={onStateChange}
            initialState={initialState}/>
    );
};

export default GreyListTable;
