import React from 'react';
import {useTranslation} from 'react-i18next';

const EmptyText: React.VFC = () => {
    const {t} = useTranslation();

    return (
        <div style={{
            height: 200,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: 'var(--color-note)',
            textTransform: 'uppercase'
        }}>{t('placeholder.noData')}</div>
    );
};

export default EmptyText;
