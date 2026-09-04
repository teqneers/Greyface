import {X} from 'lucide-react';
import React from 'react';
import {useTranslation} from 'react-i18next';

import {Button} from '@/components/ui/button';

export interface DataTableBulkBarProps {
    count: number,
    onClear: () => void,
    children: React.ReactNode,
}

/** Appears above the table while rows are selected and hosts the bulk actions. */
export function DataTableBulkBar({count, onClear, children}: DataTableBulkBarProps): React.ReactElement | null {
    const {t} = useTranslation();
    if (count === 0) {
        return null;
    }
    return (
        <div role="region" aria-label={t('table.selection')}
             className="flex flex-wrap items-center gap-2 rounded-lg border border-primary/30 bg-primary/5 px-3 py-2 text-sm">
            <span className="font-medium">{t('table.selected', {count})}</span>
            <div className="flex items-center gap-2">{children}</div>
            <Button variant="ghost" size="sm" className="ml-auto" onClick={onClear}>
                <X aria-hidden="true"/>
                {t('table.clearSelection')}
            </Button>
        </div>
    );
}
