import React from 'react';
import {useTranslation} from 'react-i18next';
import {NavLink} from 'react-router-dom';

import {usePermissions} from '@/application/usePermissions';
import {cn} from '@/lib/utils';

import {navigation} from './navigation';
import type {NavItem} from './navigation';

interface SidebarNavProps {
    /** Called after a link is chosen, so the mobile sheet can close. */
    onNavigate?: () => void,
}

function Item({item, onNavigate}: { item: NavItem, onNavigate?: () => void }): React.ReactElement {
    const {t} = useTranslation();
    const Icon = item.icon;

    return (
        <li>
            <NavLink
                to={item.to}
                onClick={onNavigate}
                end={item.children !== undefined}
                className={({isActive}) => cn(
                    'flex items-center gap-2.5 rounded-md px-2.5 py-1.5 text-sm font-medium',
                    'text-sidebar-foreground/80 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground',
                    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sidebar-ring',
                    isActive && 'bg-sidebar-accent text-sidebar-accent-foreground',
                )}>
                {Icon && <Icon className="size-4 shrink-0" aria-hidden="true"/>}
                <span className="truncate">{t(item.label)}</span>
            </NavLink>
            {item.children && (
                <ul className="mt-0.5 ml-4 space-y-0.5 border-l border-sidebar-border pl-2.5">
                    {item.children.map((child) => (
                        <li key={child.to}>
                            <NavLink
                                to={child.to}
                                onClick={onNavigate}
                                className={({isActive}) => cn(
                                    'block rounded-md px-2.5 py-1 text-sm',
                                    'text-sidebar-foreground/70 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground',
                                    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sidebar-ring',
                                    isActive && 'bg-sidebar-accent text-sidebar-accent-foreground font-medium',
                                )}>
                                {t(child.label)}
                            </NavLink>
                        </li>
                    ))}
                </ul>
            )}
        </li>
    );
}

export function SidebarNav({onNavigate}: SidebarNavProps): React.ReactElement {
    const {t} = useTranslation();
    const {isAdministrator} = usePermissions();
    const admin = isAdministrator();

    return (
        <nav aria-label={t('menu.navigation')} className="flex flex-col gap-5">
            {navigation
                .filter((group) => admin || !group.adminOnly)
                .map((group, index) => (
                    <div key={group.label ?? index}>
                        {group.label && (
                            <h2 className="mb-1.5 px-2.5 text-xs font-semibold uppercase tracking-wider text-sidebar-foreground/50">
                                {t(group.label)}
                            </h2>
                        )}
                        <ul className="space-y-0.5">
                            {group.items
                                .filter((item) => admin || !item.adminOnly)
                                .map((item) => <Item key={item.to} item={item} onNavigate={onNavigate}/>)}
                        </ul>
                    </div>
                ))}
        </nav>
    );
}
