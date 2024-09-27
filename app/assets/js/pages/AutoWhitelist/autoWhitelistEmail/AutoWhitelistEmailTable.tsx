import React, {useMemo} from 'react';
import {useTranslation} from 'react-i18next';
import {CellProps, Column, TableState} from 'react-table';
import DisplayDate from '../../../controllers/DisplayDate';

import LoadingIndicator from '../../../controllers/LoadingIndicator';
import Table from '../../../controllers/Table/Table';
import {DATE_TIME_SECONDS_FORMAT} from '../../../types/common';
import {AutoWhiteListEmail} from '../../../types/greylist';
import DeleteEmail from './DeleteEmail';
import EditEmail from './EditEmail';

interface AutoEmailTableProps {
    data: AutoWhiteListEmail[],
    refetch: () => void,
    pageCount: number,
    isFetching: boolean,
    initialState?: Partial<TableState<AutoWhiteListEmail>>,
    onStateChange?: (state: TableState<AutoWhiteListEmail>) => void,
}

const AutoEmailTable: React.FC<AutoEmailTableProps> = (
    {
        data,
        refetch,
        isFetching,
        pageCount,
        initialState,
        onStateChange
    }) => {

    const {t} = useTranslation();

    const columns = useMemo<Column<AutoWhiteListEmail>[]>(() => [{
        Header: t('autoWhitelist.email.name'),
        id: 'name',
        minWidth: 300,
        maxWidth: 500,
        accessor: (originalRow) => originalRow.name,
        canSort: true,
        disableResizing: true
    }, {
        Header: t('autoWhitelist.email.domain'),
        id: 'domain',
        accessor: (originalRow) => originalRow.domain,
        canSort: true,
        disableResizing: true
    }, {
        Header: t('autoWhitelist.email.source'),
        id: 'source',
        accessor: (originalRow) => originalRow.source,
        canSort: true,
        disableResizing: true
    }, {
        Header: t('autoWhitelist.email.firstSeen'),
        id: 'firstSeen',
        accessor: (originalRow) => <DisplayDate date={originalRow.firstSeen}
                                                format={DATE_TIME_SECONDS_FORMAT}/>,
        canSort: true,
        disableResizing: true
    }, {
        Header: t('autoWhitelist.email.lastSeen'),
        id: 'lastSeen',
        accessor: (originalRow) => <DisplayDate date={originalRow.lastSeen}
                                                format={DATE_TIME_SECONDS_FORMAT}/>,
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
        Cell: ({row: {original: row}}: CellProps<AutoWhiteListEmail, string>) => {
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
        <Table<AutoWhiteListEmail>
            idColumn="email,source,domain"
            data={data}
            pageCount={pageCount}
            columns={columns}
            disableSortRemove={true}
            onStateChange={onStateChange}
            initialState={initialState}/>
    );
};

export default AutoEmailTable;
