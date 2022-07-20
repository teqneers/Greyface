import React from 'react';
import {Route} from 'react-router-dom';
import {useTranslation} from 'react-i18next';

function EmptyRoute(): React.ReactElement {
    const {t} = useTranslation();

    return (
        <Route>
            <div className="align-content-center align-items-center">
                <img src={require('../../images/empty.png')} width={200}/>
                <br/>
                <h4>{t('emptyHeader')}</h4>
                <p style={{
                    padding: 'var(--text-spacing)',
                    maxWidth: 300,
                    textAlign: 'center',
                }}>{t('emptyBody')}</p>
            </div>
        </Route>
    );
}

export default EmptyRoute;
