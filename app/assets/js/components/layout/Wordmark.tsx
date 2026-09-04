import React from 'react';

import {cn} from '@/lib/utils';

/** The Greyface mark: an envelope held back behind a bar, for "delayed on purpose". */
export function Mark({className}: { className?: string }): React.ReactElement {
    return (
        <svg viewBox="0 0 32 32" aria-hidden="true" className={cn('size-7', className)}>
            <rect width="32" height="32" rx="8" className="fill-primary"/>
            <path
                d="M8 11.5h16v10a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1v-10Z"
                className="fill-none stroke-primary-foreground" strokeWidth="1.8" strokeLinejoin="round"/>
            <path d="m8 12 8 6 8-6" className="fill-none stroke-primary-foreground" strokeWidth="1.8"
                  strokeLinejoin="round"/>
            <path d="M6 8h20" className="stroke-primary-foreground" strokeWidth="2.2" strokeLinecap="round"/>
        </svg>
    );
}

export function Wordmark({className}: { className?: string }): React.ReactElement {
    return (
        <span className={cn('inline-flex items-center gap-2.5 font-semibold tracking-tight', className)}>
            <Mark/>
            <span className="text-base">Greyface</span>
        </span>
    );
}
