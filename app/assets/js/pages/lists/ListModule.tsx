import type {TFunction} from 'i18next';
import React, {useMemo} from 'react';
import {useTranslation} from 'react-i18next';
import {Link, Navigate, Route, Routes, useLocation} from 'react-router-dom';

import ApplicationModuleContainer from '@/application/ApplicationModuleContainer';
import {PageHeader} from '@/components/PageHeader';
import {Tabs, TabsList, TabsTrigger} from '@/components/ui/tabs';

import type {ListConfig} from './config';
import {EntryList} from './EntryList';

export interface ListModuleProps {
    build: (t: TFunction) => ListConfig,
}

/**
 * A whitelist, blacklist or auto-whitelist screen: an emails tab and a
 * domains tab, each a route (/whitelist/emails, /whitelist/domains/create).
 */
export function ListModule({build}: ListModuleProps): React.ReactElement {
    const {t} = useTranslation();
    const config = useMemo(() => build(t), [build, t]);
    const location = useLocation();
    const base = `/${config.slug}`;
    const kind = location.pathname.startsWith(`${base}/domains`) ? 'domains' : 'emails';

    return (
        <ApplicationModuleContainer title={`${config.i18n}.header`}>
            <PageHeader title={t(`${config.i18n}.header`)} description={t(`${config.i18n}.description`)}/>

            <Tabs value={kind} className="mb-4">
                <TabsList>
                    <TabsTrigger value="emails" asChild>
                        <Link to={`${base}/emails`}>{t('menu.emails')}</Link>
                    </TabsTrigger>
                    <TabsTrigger value="domains" asChild>
                        <Link to={`${base}/domains`}>{t('menu.domains')}</Link>
                    </TabsTrigger>
                </TabsList>
            </Tabs>

            <Routes>
                <Route index element={<Navigate to="emails" replace/>}/>
                <Route path="emails" element={<EntryList key="emails" config={config.emails} i18n={config.i18n} creating={false}/>}/>
                <Route path="emails/create" element={<EntryList key="emails" config={config.emails} i18n={config.i18n} creating/>}/>
                <Route path="domains" element={<EntryList key="domains" config={config.domains} i18n={config.i18n} creating={false}/>}/>
                <Route path="domains/create" element={<EntryList key="domains" config={config.domains} i18n={config.i18n} creating/>}/>
                <Route path="*" element={<Navigate to="emails" replace/>}/>
            </Routes>
        </ApplicationModuleContainer>
    );
}
