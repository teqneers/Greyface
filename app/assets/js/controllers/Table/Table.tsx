import React, {useCallback, useEffect} from 'react';
import {Table as BTable} from 'react-bootstrap';
import {useSticky} from 'react-table-sticky';
import useDeepCompareEffect from 'use-deep-compare-effect';
import {
    TableOptions,
    TableState, useColumnOrder,
    useFlexLayout,
    UseRowStateInstanceProps,
    useSortBy,
    useTable,
    UseTableColumnOptions,
    UseTableColumnProps,
    UseTableOptions,
    useResizeColumns,
    UseResizeColumnsColumnProps,
    UseResizeColumnsOptions,
    UseResizeColumnsState,
    usePagination, UsePaginationOptions, UsePaginationInstanceProps, UsePaginationState
} from 'react-table';
import TableBody, {TableBodyProps} from './TableBody';

import {createGetRowId} from './utils';

declare module 'react-table' {
    interface TableOptions<D>
        extends UseTableOptions<D>,
            UseSortByOptions<D>,
            UseResizeColumnsOptions<D>,
            UsePaginationOptions<D> {
    }

    interface TableInstance<D>
        extends UseColumnOrderInstanceProps<D>,
            UseRowStateInstanceProps<D>,
            UseSortByInstanceProps<D>,
            UsePaginationInstanceProps<D> {
    }

    interface TableState<D>
        extends UseSortByState<D>,
            UseResizeColumnsState<D>,
            UsePaginationState<D> {
    }

    interface ColumnInterface<D> extends UseTableColumnOptions<D>,
        UseSortByColumnOptions<D>,
        UseResizeColumnsOptions<D> {
        canHide?: boolean,
        sticky?: 'left' | 'right',
        rowClick?: boolean
    }

    interface ColumnInstance<D> extends UseTableColumnProps<D>,
        UseSortByColumnProps<D>,
        UseResizeColumnsColumnProps<D> {
    }

}

export interface TableProps<D extends object> extends Pick<TableBodyProps<D>, 'onRowClick' | 'onRowDoubleClick' | 'rowClassName'> {
    showHeader?: boolean,
    showFooter?: boolean,
    className?: string,
    idColumn?: string,
    onStateChange?: (state: TableState<D>) => void,
    pageCount?: number,
}

function Table<D extends object>(
    {
        showHeader,
        showFooter,
        className,
        idColumn,
        onRowClick,
        onRowDoubleClick,
        rowClassName,
        onStateChange,
        pageCount: controlledPageCount,
        ...rest
    }: TableProps<D> & TableOptions<D>
): React.ReactElement {
    const {
        getTableProps,
        getTableBodyProps,
        headerGroups,
        prepareRow,
        page,
        canPreviousPage,
        canNextPage,
        pageOptions,
        pageCount,
        gotoPage,
        nextPage,
        previousPage,
        setPageSize,
        // Get the state from the instance
        columns,
        state,
    } = useTable<D>(
        {
            ...rest,
            manualPagination: true, // Tell the usePagination
            // hook that we'll handle our own data fetching
            // This means we'll also have to provide our own
            // pageCount.
            pageCount: controlledPageCount,
            // eslint-disable-next-line react-hooks/exhaustive-deps
            getRowId: useCallback(createGetRowId(idColumn), [idColumn])
        },
        useSortBy,
        useSticky,
        useFlexLayout,
        useColumnOrder,
        useResizeColumns,
        usePagination
    );
    const {pageIndex, pageSize} = state;

    // table state change
    useDeepCompareEffect(() => {
        if (onStateChange) {
            console.log(state);
            onStateChange(state);
        }
    }, [onStateChange, state]);

    return (
        <>
            <BTable striped bordered hover size="sm" {...getTableProps()}>
                <thead>
                {headerGroups.map(headerGroup => (
                    <tr {...headerGroup.getHeaderGroupProps()}>
                        {headerGroup.headers.map(column => (
                            <th {...column.getHeaderProps()}>
                                {column.render('Header')}
                                <span>
                    {column.isSorted
                        ? column.isSortedDesc
                            ? ' 🔽'
                            : ' 🔼'
                        : ''}
                  </span>
                            </th>
                        ))}
                    </tr>
                ))}
                </thead>

                <TableBody data={page} prepareRow={prepareRow} {...getTableBodyProps()}
                           onRowClick={onRowClick} onRowDoubleClick={onRowDoubleClick} rowClassName={rowClassName}/>

           </BTable>

            {/*
        Pagination can be built however you'd like.
        This is just a very basic UI implementation:
      */}
            <div className="pagination">
                <button onClick={() => gotoPage(0)} disabled={!canPreviousPage}>
                    {'<<'}
                </button>
                {' '}
                <button onClick={() => previousPage()} disabled={!canPreviousPage}>
                    {'<'}
                </button>
                {' '}
                <button onClick={() => nextPage()} disabled={!canNextPage}>
                    {'>'}
                </button>
                {' '}
                <button onClick={() => gotoPage(pageCount - 1)} disabled={!canNextPage}>
                    {'>>'}
                </button>
                {' '}
                <span>
          Page{' '}
                    <strong>
            {pageIndex + 1} of {pageOptions.length}
          </strong>{' '}
        </span>
                <span>
          | Go to page:{' '}
                    <input
                        type="number"
                        defaultValue={pageIndex + 1}
                        onChange={e => {
                            const page = e.target.value ? Number(e.target.value) - 1 : 0
                            gotoPage(page)
                        }}
                        style={{width: '100px'}}
                    />
        </span>{' '}
                <select
                    value={pageSize}
                    onChange={e => {
                        setPageSize(Number(e.target.value))
                    }}
                >
                    {[5, 10, 20, 30, 40, 50].map(pageSize => (
                        <option key={pageSize} value={pageSize}>
                            Show {pageSize}
                        </option>
                    ))}
                </select>
            </div>

        </>
    );
}

Table.defaultProps = {
    showHeader: true,
    showFooter: false,
    idColumn: 'id',
};

export default Table;
