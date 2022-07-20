import React from 'react';
import {useTranslation} from 'react-i18next';

const EmptyText: React.VFC = () => {
    const {t} = useTranslation();

    return (
        <div style={{
            minHeight: 100,
            maxHeight: '100%',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontWeight: 'normal',
            color: 'var(--color-note)',
        }}>
            {t('placeholder.noData')}
        </div>
    );
};

export default EmptyText;
