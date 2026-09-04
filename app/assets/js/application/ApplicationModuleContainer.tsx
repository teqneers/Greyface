import React, {useEffect} from 'react';
import {useTranslation} from 'react-i18next';

export interface ApplicationModuleContainerProps {
    /** Translation key of the screen title; also becomes the document title. */
    title: string,
    children?: React.ReactNode,
}

const ApplicationModuleContainer: React.FC<ApplicationModuleContainerProps> = ({title, children}) => {
    const {t} = useTranslation();
    const label = t(title);

    useEffect(() => {
        document.title = `${label} · Greyface`;
    }, [label]);

    return (
        <div className="module-container mx-auto w-full max-w-7xl">
            {children}
        </div>
    );
};

export default ApplicationModuleContainer;
