import {Inbox} from 'lucide-react';
import type {LucideIcon} from 'lucide-react';
import React from 'react';

import {cn} from '@/lib/utils';

export interface EmptyStateProps {
    icon?: LucideIcon,
    title: string,
    description?: string,
    action?: React.ReactNode,
    className?: string,
}

export function EmptyState({icon: Icon = Inbox, title, description, action, className}: EmptyStateProps): React.ReactElement {
    return (
        <div className={cn('flex flex-col items-center justify-center gap-2 px-6 py-12 text-center', className)}>
            <div className="flex size-10 items-center justify-center rounded-full bg-muted text-muted-foreground">
                <Icon className="size-5" aria-hidden="true"/>
            </div>
            <p className="text-sm font-medium">{title}</p>
            {description && <p className="max-w-sm text-sm text-muted-foreground">{description}</p>}
            {action && <div className="mt-2">{action}</div>}
        </div>
    );
}
