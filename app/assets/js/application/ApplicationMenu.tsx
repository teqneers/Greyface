import React from 'react';
import {Nav} from 'react-bootstrap';
import {NavLink} from 'react-router-dom';
import {useTranslation} from 'react-i18next';

import {usePermissions} from './usePermissions';

function ApplicationMenu(): React.ReactElement {
    const {t} = useTranslation();
    const {isAdministrator} = usePermissions();

    return (
        <Nav defaultActiveKey="/greylist" variant="tabs">
            <NavLink className="nav-link" to="/greylist">{t('menu.greylist')}</NavLink>
            {isAdministrator() && (<>
                <NavLink className="nav-link" to="/users">{t('menu.users')}</NavLink>
            </>)}
        </Nav>
    );
}

export default ApplicationMenu;
