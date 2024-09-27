import React from 'react';
import {Container} from 'react-bootstrap';
import {Helmet} from 'react-helmet-async';
import {useTranslation} from 'react-i18next';

export interface ApplicationModuleContainerProps {
    title: string,
    children?: React.ReactNode,
}

const ApplicationModuleContainer: React.FC<ApplicationModuleContainerProps> = ({title, children}) => {
    const {t} = useTranslation();


    return (
        <Container fluid className="module-container mt-4">
            <Helmet>
                <title>{t(title)}</title>
            </Helmet>
            {children}
        </Container>
    );
};

export default ApplicationModuleContainer;
