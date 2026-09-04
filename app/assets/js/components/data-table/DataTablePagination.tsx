import {ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight} from 'lucide-react';
import React from 'react';
import {useTranslation} from 'react-i18next';

import {Button} from '@/components/ui/button';
import {Select, SelectContent, SelectItem, SelectTrigger, SelectValue} from '@/components/ui/select';
import {PAGE_SIZES} from '@/hooks/useListState';

export interface DataTablePaginationProps {
    page: number,
    pageSize: number,
    rowCount: number,
    onPageChange: (page: number) => void,
    onPageSizeChange: (pageSize: number) => void,
}

export function DataTablePagination(
    {page, pageSize, rowCount, onPageChange, onPageSizeChange}: DataTablePaginationProps
): React.ReactElement {
    const {t} = useTranslation();
    const pageCount = Math.max(1, Math.ceil(rowCount / pageSize));
    const from = rowCount === 0 ? 0 : page * pageSize + 1;
    const to = Math.min(rowCount, (page + 1) * pageSize);
    const first = page <= 0;
    const last = page >= pageCount - 1;

    return (
        <nav aria-label={t('paging.label')} className="flex flex-wrap items-center justify-between gap-3 text-sm">
            <p className="text-muted-foreground" aria-live="polite">
                {t('paging.range', {from, to, count: rowCount})}
            </p>
            <div className="flex items-center gap-4">
                <label className="flex items-center gap-2 text-muted-foreground">
                    <span>{t('paging.itemsPerPage')}</span>
                    <Select value={String(pageSize)} onValueChange={(value) => onPageSizeChange(Number(value))}>
                        <SelectTrigger size="sm" className="w-18" aria-label={t('paging.itemsPerPage')}>
                            <SelectValue/>
                        </SelectTrigger>
                        <SelectContent>
                            {PAGE_SIZES.map((size) => (
                                <SelectItem key={size} value={String(size)}>{size}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </label>
                <span className="text-muted-foreground">
                    {t('paging.pageOf', {page: page + 1, count: pageCount})}
                </span>
                <div className="flex items-center gap-1">
                    <Button variant="outline" size="icon-sm" disabled={first} onClick={() => onPageChange(0)}
                            aria-label={t('paging.first')}>
                        <ChevronsLeft aria-hidden="true"/>
                    </Button>
                    <Button variant="outline" size="icon-sm" disabled={first} onClick={() => onPageChange(page - 1)}
                            aria-label={t('paging.previous')}>
                        <ChevronLeft aria-hidden="true"/>
                    </Button>
                    <Button variant="outline" size="icon-sm" disabled={last} onClick={() => onPageChange(page + 1)}
                            aria-label={t('paging.next')}>
                        <ChevronRight aria-hidden="true"/>
                    </Button>
                    <Button variant="outline" size="icon-sm" disabled={last} onClick={() => onPageChange(pageCount - 1)}
                            aria-label={t('paging.last')}>
                        <ChevronsRight aria-hidden="true"/>
                    </Button>
                </div>
            </div>
        </nav>
    );
}
