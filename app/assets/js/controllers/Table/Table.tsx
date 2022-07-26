import React, {useCallback} from 'react';
import {Col, Form, Pagination, Row, Table as BTable} from 'react-bootstrap';
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
import {DOTS, useCustomPagination} from './useCustomPagination';

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
        columns,
        state,
    } = useTable<D>(
        {
            ...rest,
            manualPagination: true,
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

    // custom pagination numbers with ...
    const paginationRange = useCustomPagination({
        totalPageCount: pageCount,
        currentPage: pageIndex
    });

    // table state change
    useDeepCompareEffect(() => {
        if (onStateChange) {
            onStateChange(state);
        }
    }, [onStateChange, state]);

    return (
        <>
            <BTable size="sm" {...getTableProps()}>
                <thead>
                {headerGroups.map(headerGroup => (
                    <tr key={headerGroup.id} {...headerGroup.getHeaderGroupProps()}>
                        {headerGroup.headers.map(column => (
                            <th key={column.id} {...column.getHeaderProps()}>
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

            {/* Pagination */}
            {paginationRange.length > 0 &&
                <Row>
                    <Col>
                        <Pagination>
                            <Pagination.First onClick={() => gotoPage(0)} disabled={!canPreviousPage}/>
                            <Pagination.Prev onClick={() => previousPage()} disabled={!canPreviousPage}/>

                            {paginationRange.map((pageNumber, index) => {
                                if (pageNumber === DOTS) {
                                    return (
                                        <Pagination.Ellipsis/>
                                    );
                                }

                                if ((pageNumber - 1) === pageIndex) {
                                    return (
                                        <Pagination.Item
                                            key={index}
                                            active
                                            onClick={() => gotoPage(pageNumber - 1)}>{pageNumber}</Pagination.Item>
                                    );
                                }

                                return (
                                    <Pagination.Item
                                        key={index}
                                        onClick={() => gotoPage(pageNumber - 1)}>{pageNumber}</Pagination.Item>
                                );
                            })}

                            <Pagination.Next onClick={() => nextPage()} disabled={!canNextPage}/>
                            <Pagination.Last onClick={() => gotoPage(pageCount - 1)} disabled={!canNextPage}/>
                        </Pagination>
                    </Col>
                    <Col md={4}>
                        <Form.Select
                            size="sm"
                            className="w-25"
                            value={pageSize}
                            onChange={e => {
                                setPageSize(Number(e.target.value));
                            }}>
                            {[5, 10, 15, 20, 25, 30, 40, 50].map(pageSize => (
                                <option key={pageSize} value={pageSize}>
                                    Show {pageSize}
                                </option>
                            ))}
                        </Form.Select>
                    </Col>
                </Row>}
        </>
    );
}

Table.defaultProps = {
    showHeader: true,
    showFooter: false,
    idColumn: 'id',
};

export default Table;
