import React from 'react';
import type {ReactNode} from 'react';
import {Nav, Navbar, NavDropdown} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';

import {useApplication} from './ApplicationContext';
import ApplicationMenu from './ApplicationMenu';
import {setSetting, SettingsLocale, useSettings} from './settings';

import logo from '../../images/teqneers_logo.png';
import flagDe from '../../images/language/de.png';
import flagEn from '../../images/language/en.png';

const flags: Record<string, string> = {de: flagDe, en: flagEn};

const ApplicationContainer: React.FC<{ children?: ReactNode }> = ({children}) => {
    const {t} = useTranslation();
    const {locale} = useSettings();
    const {user, changePasswordUrl, logoutUrl, baseUrl} = useApplication();
    let [flag] = locale.split('-');
    if (!['de', 'en'].includes(flag)) {
        flag = 'en';
    }
    return (
        <>
            <Navbar bg="light" sticky="top" className="m-1">
                <Navbar.Brand href={`${baseUrl}`}>
                    <img
                        src={logo}
                        height="30"
                        className="d-inline-block align-top"
                        alt="Greyface by TEQneers GmbH & Co KG"/>
                </Navbar.Brand>
                <Navbar.Toggle/>

                <Navbar.Collapse className="justify-content-end">
                    <Nav>
                        {/* User dropdown */}
                        <NavDropdown title={user.username}>
                            <NavDropdown.Item
                                onClick={() => window.location.href = changePasswordUrl ?? ''}>{t('button.changePassword')}</NavDropdown.Item>
                            <NavDropdown.Item
                                onClick={() => window.location.href = logoutUrl}>{t('button.logout')}</NavDropdown.Item>
                        </NavDropdown>

                        {/* Language Dropdown */}
                        <NavDropdown
                            title={<><img
                                style={{marginRight: 5}}
                                src={flags[flag]}
                                className="d-inline-block" alt={flag}/>
                                {t(`locale.${locale.replace('-', '_')}`)}</>}>

                            {Object.entries(SettingsLocale).map(([key, value]) => {
                                const [langFlag] = value.split('-');
                                return (
                                    <NavDropdown.Item
                                        key={key}
                                        onClick={() => setSetting('locale', value)}>
                                        <img
                                            style={{marginRight: 5}}
                                            src={flags[langFlag]}
                                            className="d-inline-block m-1"
                                            alt={key}/>
                                        {t(`locale.${key}`)}
                                    </NavDropdown.Item>
                                );
                            })}
                        </NavDropdown>
                    </Nav>
                </Navbar.Collapse>

            </Navbar>
            {/* Application Menu */}
            <ApplicationMenu/>

            {React.Children.only(children)}

            {/* Footer */}
            <Navbar fixed="bottom" className="justify-content-center footer">
                Greyface by TEQneers GmbH & Co. KG
            </Navbar>
        </>
    );
};

export default ApplicationContainer;
