import React, {useMemo} from 'react';
import {useTranslation} from 'react-i18next';
import {CellProps, Column, TableState} from 'react-table';
import {usePermissions} from '../../application/usePermissions';

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

const GreyListTable: React.FC<GreyListTableProps> = (
    {
        data,
        refetch,
        isFetching,
        pageCount,
        initialState,
        onStateChange
    }) => {

    const {t} = useTranslation();
    const {isAdministrator} = usePermissions();

    const columns = useMemo<Column<Greylist>[]>(() => {

        const columns = [{
            Header: t('greylist.sender'),
            id: 'name',
            minWidth: 300,
            maxWidth: 500,
            accessor: (originalRow) => originalRow.connect.name,
            canSort: true,
            disableResizing: true
        }, {
            Header: t('greylist.domain'),
            id: 'domain',
            width: 160,
            minWidth: 150,
            maxWidth: 200,
            accessor: (originalRow) => originalRow.connect.domain,
            canSort: true,
            disableResizing: true
        }, {
            Header: t('greylist.source'),
            id: 'source',
            width: 130,
            minWidth: 130,
            maxWidth: 150,
            accessor: (originalRow) => originalRow.connect.source,
            canSort: true,
            disableResizing: true
        }, {
            Header: t('greylist.recipient'),
            id: 'rcpt',
            width: 180,
            minWidth: 180,
            maxWidth: 250,
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
        }] as unknown as Column<Greylist>[];

        if (isAdministrator()) {
            columns.push({
                Header: t('greylist.username'),
                id: 'username',
                width: 100,
                minWidth: 100,
                maxWidth: 150,
                accessor: (originalRow) => originalRow.username,
                canSort: true,
                disableResizing: true
            } as Column<Greylist>);
        }

        columns.push({
            Header: '',
            id: 'actions',
            width: 220,
            minWidth: 220,
            maxWidth: 220,
            disableSortBy: true,
            disableResizing: true,
            Cell: ({row: {original: row}}: CellProps<Greylist, string>) => {
                return <>
                    <MoveToWhiteList onMove={refetch} data={row}/>
                    <DeleteGreyList onDelete={refetch} data={row}/>
                </>;
            }
        } as Column<Greylist>);

        return columns;
    }, [t, refetch, isAdministrator]);

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
