import React from 'react';
import {Container} from 'react-bootstrap';
import {Helmet} from 'react-helmet-async';
import {useTranslation} from 'react-i18next';

export interface ApplicationModuleContainerProps {
    title: string,
}

const ApplicationModuleContainer: React.FC<ApplicationModuleContainerProps> = ({title, children}) => {
    const {t} = useTranslation();


    return (
        <div className="container-fluid mt-4">
            {/* @ts-ignore */}
            <Helmet>
                <title>{t(title)}</title>
            </Helmet>
            {children}
        </div>
    );
};

ApplicationModuleContainer.defaultProps = {};

export default ApplicationModuleContainer;
