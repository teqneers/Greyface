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
                <NavLink className="nav-link" to="/awl/emails">{t('menu.autoWhitelist')} {t('menu.email')}</NavLink>
                <NavLink className="nav-link" to="/awl/domains">{t('menu.autoWhitelist')} {t('menu.domain')}</NavLink>
                <NavLink className="nav-link" to="/opt-out/emails">{t('menu.whitelist')} {t('menu.email')}</NavLink>
                <NavLink className="nav-link" to="/opt-out/domains">{t('menu.whitelist')} {t('menu.domain')}</NavLink>
                <NavLink className="nav-link" to="/opt-in/emails">{t('menu.blacklist')} {t('menu.email')}</NavLink>
                <NavLink className="nav-link" to="/opt-in/domains">{t('menu.blacklist')} {t('menu.domain')}</NavLink>
                <NavLink className="nav-link" to="/users">{t('menu.users')}</NavLink>
                <NavLink className="nav-link" to="/users-aliases">{t('menu.alias')}</NavLink>
            </>)}
        </Nav>
    );
}

export default ApplicationMenu;
