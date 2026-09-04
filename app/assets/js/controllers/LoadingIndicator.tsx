import {Loader2} from 'lucide-react';
import React from 'react';
import {useTranslation} from 'react-i18next';

export interface LoadingIndicatorProps {
    text?: string,
}

function LoadingIndicator({text}: LoadingIndicatorProps): React.ReactElement {
    const {t} = useTranslation();
    const label = text ?? t('loading');

    return (
        <div role="status" className="flex items-center gap-2 py-6 text-sm text-muted-foreground">
            <Loader2 className="size-4 animate-spin" aria-hidden="true"/>
            {label}
        </div>
    );
}

export default LoadingIndicator;
