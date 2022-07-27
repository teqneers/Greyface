import React from 'react';
import {Row, TableBodyProps as BaseTableBodyProps} from 'react-table';
import classNames from 'classnames';

import EmptyText from './EmptyText';

export type RowClickHandler<D extends object> = (row: D, id: string, index: number, tableRow: Row<D>) => void;

export interface TableBodyProps<D extends object> extends BaseTableBodyProps {
    data: Row<D>[],
    prepareRow: (row: Row<D>) => void,
    onRowClick?: RowClickHandler<D>,
    onRowDoubleClick?: RowClickHandler<D>,
    rowClassName?: (className: string, row: D, id: string, index: number, tableRow: Row<D>) => string,
}

function TableBody<D extends object>(
    {
        data,
        prepareRow,
        onRowClick,
        onRowDoubleClick,
        rowClassName,
        ...rest
    }: TableBodyProps<D>
): React.ReactElement {
    return (
        <tbody {...rest}>
            {data.length > 0
                ? data.map((row, index) => {
                    console.log(row);
                    prepareRow(row);
                    let className = classNames('tr', {clickable: !!onRowClick || !!onRowDoubleClick});
                    if (rowClassName) {
                        className = rowClassName(className, row.original, row.id, index, row);
                    }
                    return (
                        // eslint-disable-next-line react/jsx-key
                        <tr {...row.getRowProps()} className={className}
                            // onClick={onRowClick ? () => onRowClick(row.original, row.id, index, row) : null}
                             onDoubleClick={onRowDoubleClick ? () => onRowDoubleClick(row.original, row.id, index, row) : null}>
                            {row.cells.map((cell) => (
                                // eslint-disable-next-line react/jsx-key
                                <td {...cell.getCellProps()}
                                     onClick={(onRowClick && cell.column.rowClick) ? () => onRowClick(row.original, row.id, index, row) : null}>
                                    {cell.render('Cell')}
                                </td>
                            ))}
                        </tr>
                    );
                })
                : <EmptyText/>}
        </tbody>
    );
}

export default TableBody;
