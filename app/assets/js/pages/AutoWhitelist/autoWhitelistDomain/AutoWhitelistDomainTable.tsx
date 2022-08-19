import React, {useMemo} from 'react';
import {useTranslation} from 'react-i18next';
import {CellProps, Column, TableState} from 'react-table';
import DisplayDate from '../../../controllers/DisplayDate';

import LoadingIndicator from '../../../controllers/LoadingIndicator';
import Table from '../../../controllers/Table/Table';
import {DATE_TIME_SECONDS_FORMAT} from '../../../types/common';
import {AutoWhiteListDomain} from '../../../types/greylist';
import DeleteDomain from './DeleteDomain';
import EditDomain from './EditDomain';

interface AutoDomainTableProps {
    data: AutoWhiteListDomain[],
    refetch: () => void,
    pageCount: number,
    isFetching: boolean,
    initialState?: Partial<TableState<AutoWhiteListDomain>>,
    onStateChange?: (state: TableState<AutoWhiteListDomain>) => void,
}

const AutoDomainTable: React.VFC<AutoDomainTableProps> = (
    {
        data,
        refetch,
        isFetching,
        pageCount,
        initialState,
        onStateChange
    }) => {

    const {t} = useTranslation();

    const columns = useMemo<Column<AutoWhiteListDomain>[]>(() => [{
        Header: t('autoWhitelist.domain.domain'),
        id: 'domain',
        accessor: (originalRow) => originalRow.domain,
        canSort: true,
        disableResizing: true
    },{
        Header: t('autoWhitelist.domain.source'),
        id: 'source',
        accessor: (originalRow) => originalRow.source,
        canSort: true,
        disableResizing: true
    },{
        Header: t('autoWhitelist.domain.firstSeen'),
        id: 'firstSeen',
        accessor: (originalRow) => <DisplayDate date={originalRow.firstSeen}
                                                format={DATE_TIME_SECONDS_FORMAT}/>,
        canSort: true,
        disableResizing: true
    }, {
        Header: t('autoWhitelist.domain.lastSeen'),
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
        Cell: ({row: {original: row}}: CellProps<AutoWhiteListDomain, string>) => {
            return <>
                <EditDomain onUpdate={refetch} data={row}/>
                <DeleteDomain onDelete={refetch} data={row}/>
            </>;
        }
    }], [t, refetch]);

    if (isFetching) {
        return <LoadingIndicator/>;
    }

    return (
        <Table<AutoWhiteListDomain>
            idColumn="domain"
            data={data}
            pageCount={pageCount}
            columns={columns}
            disableSortRemove={true}
            onStateChange={onStateChange}
            initialState={initialState}/>
    );
};

export default AutoDomainTable;
