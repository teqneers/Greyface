import {screen, within} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import type {ColumnDef} from '@tanstack/react-table';
import React from 'react';
import {describe, expect, it, vi} from 'vitest';

import {renderWithProviders} from '../../test/render';
import {DataTable} from './DataTable';
import {DataTablePagination} from './DataTablePagination';

interface Row {
    id: string,
    name: string,
    domain: string,
}

const rows: Row[] = [
    {id: '1', name: 'alice', domain: 'a.example'},
    {id: '2', name: 'bob', domain: 'b.example'},
];

const columns: ColumnDef<Row>[] = [
    {accessorKey: 'name', header: 'Name'},
    {accessorKey: 'domain', header: 'Domain', enableSorting: false},
];

describe('DataTable', () => {
    it('renders rows and marks the sorted column for assistive tech', () => {
        renderWithProviders(
            <DataTable columns={columns} data={rows} getRowId={(r) => r.id} sort={{id: 'name', desc: true}} onSortChange={() => {}}/>
        );

        expect(screen.getByText('alice')).toBeInTheDocument();
        expect(screen.getByRole('columnheader', {name: /name/i})).toHaveAttribute('aria-sort', 'descending');
        expect(screen.getByRole('columnheader', {name: /domain/i})).not.toHaveAttribute('aria-sort');
    });

    it('cycles the sort direction through the callback instead of sorting locally', async () => {
        const onSortChange = vi.fn();
        renderWithProviders(
            <DataTable columns={columns} data={rows} getRowId={(r) => r.id} sort={{id: 'name', desc: false}} onSortChange={onSortChange}/>
        );

        await userEvent.click(screen.getByRole('button', {name: /name/i}));

        expect(onSortChange).toHaveBeenCalledWith({id: 'name', desc: true});
        // Order on screen is the server's business.
        const cells = screen.getAllByRole('row').slice(1).map((row) => within(row).getAllByRole('cell')[0].textContent);
        expect(cells).toEqual(['alice', 'bob']);
    });

    it('selects rows on the current page only', async () => {
        const onSelectionChange = vi.fn();
        renderWithProviders(
            <DataTable columns={columns} data={rows} getRowId={(r) => r.id} sort={null} onSortChange={() => {}}
                       selection={{}} onSelectionChange={onSelectionChange}/>
        );

        await userEvent.click(screen.getByRole('checkbox', {name: /select all rows on this page/i}));
        expect(onSelectionChange).toHaveBeenCalledWith({'1': true, '2': true});

        await userEvent.click(screen.getAllByRole('checkbox', {name: /select row/i})[1]);
        expect(onSelectionChange).toHaveBeenLastCalledWith({'2': true});
    });

    it('shows the empty state when there is nothing to list', () => {
        renderWithProviders(
            <DataTable columns={columns} data={[]} getRowId={(r) => r.id} sort={null} onSortChange={() => {}}/>
        );
        expect(screen.getByText('No Data')).toBeInTheDocument();
    });

    it('shows skeleton rows while loading', () => {
        renderWithProviders(
            <DataTable columns={columns} data={[]} getRowId={(r) => r.id} sort={null} onSortChange={() => {}} isLoading skeletonRows={3}/>
        );
        expect(screen.queryByText('No Data')).not.toBeInTheDocument();
        expect(screen.getAllByRole('row')).toHaveLength(4);
    });
});

describe('DataTablePagination', () => {
    it('describes the visible range and disables the edges', async () => {
        const onPageChange = vi.fn();
        renderWithProviders(
            <DataTablePagination page={0} pageSize={10} rowCount={42} onPageChange={onPageChange} onPageSizeChange={() => {}}/>
        );

        expect(screen.getByText('1–10 of 42')).toBeInTheDocument();
        expect(screen.getByText('Page 1 of 5')).toBeInTheDocument();
        expect(screen.getByRole('button', {name: /previous page/i})).toBeDisabled();

        await userEvent.click(screen.getByRole('button', {name: /next page/i}));
        expect(onPageChange).toHaveBeenCalledWith(1);

        await userEvent.click(screen.getByRole('button', {name: /last page/i}));
        expect(onPageChange).toHaveBeenCalledWith(4);
    });
});
