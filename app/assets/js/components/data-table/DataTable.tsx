import {
    flexRender,
    getCoreRowModel,
    useReactTable,
} from '@tanstack/react-table';
import type {ColumnDef, RowSelectionState, SortingState, Updater} from '@tanstack/react-table';
import {ArrowDown, ArrowUp, ArrowUpDown} from 'lucide-react';
import React, {useCallback, useMemo} from 'react';
import {useTranslation} from 'react-i18next';

import {Checkbox} from '@/components/ui/checkbox';
import {Skeleton} from '@/components/ui/skeleton';
import {Table, TableBody, TableCell, TableHead, TableHeader, TableRow} from '@/components/ui/table';
import type {SortState} from '@/lib/api';
import {cn} from '@/lib/utils';

import {EmptyState} from '../EmptyState';

export interface DataTableProps<T> {
    columns: ColumnDef<T, any>[],
    data: T[],
    getRowId: (row: T) => string,
    sort: SortState | null,
    onSortChange: (sort: SortState | null) => void,
    /** Skeleton rows instead of data. */
    isLoading?: boolean,
    /** Data present but a refetch is in flight: rows fade slightly. */
    isFetching?: boolean,
    /** Rendered instead of the body when there are no rows. */
    emptyState?: React.ReactNode,
    /** Turns on the selection column. */
    selection?: RowSelectionState,
    onSelectionChange?: (selection: RowSelectionState) => void,
    skeletonRows?: number,
    className?: string,
}

/**
 * Server-side table: sorting and paging happen in the API, this only renders
 * the current page. Sorting is a single column; clicking cycles asc → desc.
 */
export function DataTable<T>(
    {
        columns,
        data,
        getRowId,
        sort,
        onSortChange,
        isLoading = false,
        isFetching = false,
        emptyState,
        selection,
        onSelectionChange,
        skeletonRows = 5,
        className,
    }: DataTableProps<T>
): React.ReactElement {
    const {t} = useTranslation();
    const selectable = selection !== undefined && onSelectionChange !== undefined;

    const allColumns = useMemo<ColumnDef<T, any>[]>(() => {
        if (!selectable) {
            return columns;
        }
        const select: ColumnDef<T, any> = {
            id: 'select',
            header: ({table}) => (
                <Checkbox
                    checked={table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate')}
                    onCheckedChange={(value) => table.toggleAllPageRowsSelected(!!value)}
                    aria-label={t('table.selectAll')}
                />
            ),
            cell: ({row}) => (
                <Checkbox
                    checked={row.getIsSelected()}
                    onCheckedChange={(value) => row.toggleSelected(!!value)}
                    aria-label={t('table.selectRow')}
                />
            ),
            enableSorting: false,
            size: 36,
        };
        return [select, ...columns];
    }, [columns, selectable, t]);

    const sorting = useMemo<SortingState>(() => sort ? [{id: sort.id, desc: sort.desc}] : [], [sort]);

    const handleSorting = useCallback((updater: Updater<SortingState>) => {
        const next = typeof updater === 'function' ? updater(sorting) : updater;
        onSortChange(next[0] ? {id: next[0].id, desc: next[0].desc} : null);
    }, [sorting, onSortChange]);

    const handleSelection = useCallback((updater: Updater<RowSelectionState>) => {
        if (!onSelectionChange) {
            return;
        }
        onSelectionChange(typeof updater === 'function' ? updater(selection ?? {}) : updater);
    }, [selection, onSelectionChange]);

    const table = useReactTable({
        data,
        columns: allColumns,
        getRowId,
        getCoreRowModel: getCoreRowModel(),
        manualSorting: true,
        manualPagination: true,
        enableSortingRemoval: false,
        enableRowSelection: selectable,
        state: {sorting, rowSelection: selection ?? {}},
        onSortingChange: handleSorting,
        onRowSelectionChange: handleSelection,
    });

    const rows = table.getRowModel().rows;
    const columnCount = allColumns.length;

    return (
        <div className={cn('overflow-x-auto rounded-lg border bg-card', className)}>
            <Table>
                <TableHeader>
                    {table.getHeaderGroups().map((headerGroup) => (
                        <TableRow key={headerGroup.id} className="hover:bg-transparent">
                            {headerGroup.headers.map((header) => {
                                const canSort = header.column.getCanSort();
                                const direction = header.column.getIsSorted();
                                const label = flexRender(header.column.columnDef.header, header.getContext());
                                const Icon = direction === 'asc' ? ArrowUp : direction === 'desc' ? ArrowDown : ArrowUpDown;
                                return (
                                    <TableHead
                                        key={header.id}
                                        aria-sort={canSort ? (direction === 'asc' ? 'ascending' : direction === 'desc' ? 'descending' : 'none') : undefined}
                                        style={header.column.columnDef.size ? {width: header.column.columnDef.size} : undefined}
                                        className={cn('whitespace-nowrap', header.column.columnDef.meta?.align === 'right' && 'text-right')}>
                                        {canSort ? (
                                            <button
                                                type="button"
                                                onClick={header.column.getToggleSortingHandler()}
                                                className={cn(
                                                    'group -ml-2 inline-flex h-8 items-center gap-1.5 rounded-md px-2 font-medium',
                                                    'hover:bg-muted hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                                                    direction && 'text-foreground',
                                                )}>
                                                {label}
                                                <Icon
                                                    className={cn('size-3.5', !direction && 'opacity-0 group-hover:opacity-60 group-focus-visible:opacity-60')}
                                                    aria-hidden="true"/>
                                            </button>
                                        ) : label}
                                    </TableHead>
                                );
                            })}
                        </TableRow>
                    ))}
                </TableHeader>
                <TableBody className={cn(isFetching && !isLoading && 'opacity-60 transition-opacity')}>
                    {isLoading ? (
                        Array.from({length: skeletonRows}).map((_, index) => (
                            <TableRow key={index} className="hover:bg-transparent">
                                {allColumns.map((column, cellIndex) => (
                                    <TableCell key={column.id ?? cellIndex}>
                                        <Skeleton className="h-4 w-full max-w-48"/>
                                    </TableCell>
                                ))}
                            </TableRow>
                        ))
                    ) : rows.length === 0 ? (
                        <TableRow className="hover:bg-transparent">
                            <TableCell colSpan={columnCount} className="p-0">
                                {emptyState ?? <EmptyState title={t('placeholder.noData')}/>}
                            </TableCell>
                        </TableRow>
                    ) : (
                        rows.map((row) => (
                            <TableRow key={row.id} data-state={row.getIsSelected() ? 'selected' : undefined}>
                                {row.getVisibleCells().map((cell) => (
                                    <TableCell
                                        key={cell.id}
                                        className={cn(
                                            cell.column.columnDef.meta?.align === 'right' && 'text-right',
                                            cell.column.columnDef.meta?.nowrap && 'whitespace-nowrap',
                                        )}>
                                        {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                    </TableCell>
                                ))}
                            </TableRow>
                        ))
                    )}
                </TableBody>
            </Table>
        </div>
    );
}
