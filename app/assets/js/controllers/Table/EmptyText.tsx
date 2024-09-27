import React from 'react';
import {useTranslation} from 'react-i18next';

const EmptyText: React.FC = () => {
    const {t} = useTranslation();

    return (
        <div style={{
            height: 100,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            textTransform: 'uppercase'
        }}>{t('placeholder.noData')}</div>
    );
};

export default EmptyText;
