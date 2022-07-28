import React, {useMemo} from 'react';
import {useTranslation} from 'react-i18next';
import {CellProps, Column, TableState} from 'react-table';

import LoadingIndicator from '../../../controllers/LoadingIndicator';
import Table from '../../../controllers/Table/Table';
import {WhiteListDomain} from '../../../types/greylist';
import DeleteDomain from './DeleteDomain';
import EditDomain from './EditDomain';

interface WhitelistDomainTableProps {
    data: WhiteListDomain[],
    refetch: () => void,
    pageCount: number,
    isFetching: boolean,
    initialState?: Partial<TableState<WhiteListDomain>>,
    onStateChange?: (state: TableState<WhiteListDomain>) => void,
}

const WhitelistDomainTable: React.VFC<WhitelistDomainTableProps> = (
    {
        data,
        refetch,
        isFetching,
        pageCount,
        initialState,
        onStateChange
    }) => {

    const {t} = useTranslation();

    const columns = useMemo<Column<WhiteListDomain>[]>(() => [{
        Header: t('whitelist.domain.domain'),
        id: 'domain',
        accessor: (originalRow) => originalRow.domain,
        canSort: true,
        disableResizing: true
    }, {
        Header: '',
        id: 'actions',
        disableSortBy: true,
        disableResizing: true,
        Cell: ({row: {original: row}}: CellProps<WhiteListDomain, string>) => {
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
        <Table<WhiteListDomain>
            idColumn="domain"
            data={data}
            pageCount={pageCount}
            columns={columns}
            disableSortRemove={true}
            onStateChange={onStateChange}
            initialState={initialState}/>
    );
};

export default WhitelistDomainTable;
