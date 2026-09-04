import {useQuery} from '@tanstack/react-query';
import {AtSign, Hourglass, ShieldBan, ShieldCheck, Sparkles, Users} from 'lucide-react';
import React from 'react';
import {useTranslation} from 'react-i18next';

import {useApplication} from '@/application/ApplicationContext';
import ApplicationModuleContainer from '@/application/ApplicationModuleContainer';
import {PageHeader} from '@/components/PageHeader';
import {apiFetch} from '@/lib/api';

import {ActivityChart} from './ActivityChart';
import type {DashboardCounts} from './api';
import {StatTile} from './StatTile';

const DashboardModule: React.FC = () => {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();

    const {data} = useQuery({
        queryKey: ['dashboard', 'counts'],
        queryFn: () => apiFetch<DashboardCounts>(`${apiUrl}/dashboard/counts`),
        refetchInterval: 60_000,
    });

    const pair = (emails?: number, domains?: number) => data === undefined ? undefined
        : t('dashboard.pair', {emails, domains});

    return (
        <ApplicationModuleContainer title="dashboard.header">
            <PageHeader title={t('dashboard.header')} description={t('dashboard.description')}/>

            <div className="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                <StatTile icon={Hourglass} label={t('dashboard.tiles.greylist')} value={data?.greylist} to="/greylist"/>
                <StatTile icon={Sparkles} label={t('dashboard.tiles.autoWhitelist')}
                          value={data === undefined ? undefined : data.autoWhitelistEmails + data.autoWhitelistDomains}
                          detail={pair(data?.autoWhitelistEmails, data?.autoWhitelistDomains)} to="/auto-whitelist"/>
                <StatTile icon={ShieldCheck} label={t('dashboard.tiles.whitelist')}
                          value={data === undefined ? undefined : data.whitelistEmails + data.whitelistDomains}
                          detail={pair(data?.whitelistEmails, data?.whitelistDomains)} to="/whitelist"/>
                <StatTile icon={ShieldBan} label={t('dashboard.tiles.blacklist')}
                          value={data === undefined ? undefined : data.blacklistEmails + data.blacklistDomains}
                          detail={pair(data?.blacklistEmails, data?.blacklistDomains)} to="/blacklist"/>
                <StatTile icon={Users} label={t('dashboard.tiles.users')} value={data?.users} to="/users"/>
                <StatTile icon={AtSign} label={t('dashboard.tiles.aliases')} value={data?.aliases} to="/users-aliases"/>
            </div>

            <ActivityChart/>
        </ApplicationModuleContainer>
    );
};

export default DashboardModule;
