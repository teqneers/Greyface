import React from 'react';
import {useTranslation} from 'react-i18next';

function EmptyRoute(): React.ReactElement {
    const {t} = useTranslation();

    return (
        // Plain presentational empty state. It used to be wrapped in a bare
        // <Route>, which react-router 7 does not allow outside <Routes>; nothing
        // references this component, so the wrapper simply went away.
        <div className="align-content-center text-center">
                <h4>{t('emptyHeader')}</h4>
                <p style={{
                    padding: 'var(--text-spacing)',
                    textAlign: 'center',
                }}>{t('emptyBody')}</p>
        </div>
    );
}

export default EmptyRoute;
