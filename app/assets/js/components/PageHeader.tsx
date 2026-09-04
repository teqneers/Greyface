import React from 'react';

export interface PageHeaderProps {
    title: string,
    description?: React.ReactNode,
    actions?: React.ReactNode,
}

export function PageHeader({title, description, actions}: PageHeaderProps): React.ReactElement {
    return (
        <div className="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 className="text-xl font-semibold tracking-tight">{title}</h1>
                {description && <p className="mt-1 max-w-2xl text-sm text-muted-foreground">{description}</p>}
            </div>
            {actions && <div className="flex items-center gap-2">{actions}</div>}
        </div>
    );
}
