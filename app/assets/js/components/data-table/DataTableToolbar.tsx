import {Search, X} from 'lucide-react';
import React, {useEffect, useState} from 'react';
import {useTranslation} from 'react-i18next';

import {Button} from '@/components/ui/button';
import {Input} from '@/components/ui/input';
import {useDebouncedValue} from '@/hooks/useDebouncedValue';

export interface DataTableToolbarProps {
    query: string,
    onQueryChange: (query: string) => void,
    /** Filter controls, rendered next to the search box. */
    filters?: React.ReactNode,
    /** Primary actions, rendered at the far end. */
    actions?: React.ReactNode,
    searchPlaceholder?: string,
}

/**
 * Search box plus filter and action slots. The search value is local and
 * debounced so typing does not fire one request per keystroke.
 */
export function DataTableToolbar(
    {query, onQueryChange, filters, actions, searchPlaceholder}: DataTableToolbarProps
): React.ReactElement {
    const {t} = useTranslation();
    const [value, setValue] = useState(query);
    const debounced = useDebouncedValue(value);

    // External resets (a shared link, the back button) flow into the box. Adjusting
    // during render rather than in an effect avoids a second render pass:
    // https://react.dev/learn/you-might-not-need-an-effect
    const [lastQuery, setLastQuery] = useState(query);
    if (query !== lastQuery) {
        setLastQuery(query);
        setValue(query);
    }

    useEffect(() => {
        if (debounced !== query) {
            onQueryChange(debounced);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [debounced]);

    return (
        <div className="flex flex-wrap items-center gap-2">
            <div className="relative w-full sm:w-64">
                <Search className="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground"
                        aria-hidden="true"/>
                <Input
                    type="search"
                    role="searchbox"
                    value={value}
                    onChange={(event) => setValue(event.target.value)}
                    placeholder={searchPlaceholder ?? t('placeholder.search')}
                    aria-label={t('placeholder.searchByText')}
                    className="pl-8 pr-8"
                    autoComplete="off"
                />
                {value && (
                    <Button
                        type="button" variant="ghost" size="icon-xs"
                        className="absolute top-1/2 right-1.5 -translate-y-1/2"
                        onClick={() => setValue('')}
                        aria-label={t('button.clearSearch')}>
                        <X aria-hidden="true"/>
                    </Button>
                )}
            </div>
            {filters}
            {actions && <div className="ml-auto flex items-center gap-2">{actions}</div>}
        </div>
    );
}
