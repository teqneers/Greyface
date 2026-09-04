import React, {useEffect} from 'react';
import {useTranslation} from 'react-i18next';
import {Navigate} from 'react-router-dom';
import {toast} from 'sonner';

import {usePermissions} from '@/application/usePermissions';

/**
 * Sends non-administrators back to the greylist with a toast. The menu already
 * hides admin links; this covers typed URLs and stale bookmarks.
 */
export function RequireAdmin({children}: { children: React.ReactNode }): React.ReactElement {
    const {t} = useTranslation();
    const {isAdministrator} = usePermissions();
    const allowed = isAdministrator();

    useEffect(() => {
        if (!allowed) {
            // A fixed id collapses StrictMode's double effect into one toast.
            toast.error(t('errors.forbidden'), {id: 'forbidden'});
        }
    }, [allowed, t]);

    if (!allowed) {
        return <Navigate to="/greylist" replace/>;
    }
    return <>{children}</>;
}
