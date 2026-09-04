import type {LucideIcon} from 'lucide-react';
import React from 'react';
import {Link} from 'react-router-dom';

import {Skeleton} from '@/components/ui/skeleton';

export interface StatTileProps {
    icon: LucideIcon,
    label: string,
    value: number | undefined,
    /** Secondary figure shown after the value, e.g. "emails · domains". */
    detail?: string,
    to: string,
}

/** A headline number that links to the screen behind it. */
export function StatTile({icon: Icon, label, value, detail, to}: StatTileProps): React.ReactElement {
    return (
        <Link to={to}
              className="group flex items-start gap-3 rounded-lg border bg-card p-4 text-card-foreground transition-colors hover:border-primary/40 hover:bg-accent/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
            <div className="flex size-9 shrink-0 items-center justify-center rounded-md bg-muted text-muted-foreground group-hover:text-foreground">
                <Icon className="size-4" aria-hidden="true"/>
            </div>
            <div className="min-w-0">
                <div className="text-xs text-muted-foreground">{label}</div>
                <div className="mt-0.5 text-2xl font-semibold leading-tight">
                    {value === undefined ? <Skeleton className="mt-1 h-7 w-14"/> : value.toLocaleString()}
                </div>
                {detail && <div className="mt-0.5 truncate text-xs text-muted-foreground">{detail}</div>}
            </div>
        </Link>
    );
}
