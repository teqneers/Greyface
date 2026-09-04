import {AtSign, Hourglass, ShieldBan, ShieldCheck, Sparkles, Users} from 'lucide-react';
import type {LucideIcon} from 'lucide-react';

export interface NavItem {
    /** Translation key for the label. */
    label: string,
    to: string,
    icon?: LucideIcon,
    /** Secondary links rendered indented below the item. */
    children?: NavItem[],
    adminOnly?: boolean,
}

export interface NavGroup {
    /** Translation key for the group heading; omitted for the ungrouped top items. */
    label?: string,
    items: NavItem[],
    adminOnly?: boolean,
}

/**
 * The sidebar for administrators; users only ever see the greylist.
 * The email/domain sub-links disappear once the list screens are merged.
 */
export const navigation: NavGroup[] = [
    {
        items: [
            {label: 'menu.greylist', to: '/greylist', icon: Hourglass},
        ],
    },
    {
        label: 'menu.lists',
        adminOnly: true,
        items: [
            {
                label: 'menu.whitelist', to: '/opt-out/emails', icon: ShieldCheck,
                children: [
                    {label: 'menu.emails', to: '/opt-out/emails'},
                    {label: 'menu.domains', to: '/opt-out/domains'},
                ],
            },
            {
                label: 'menu.blacklist', to: '/opt-in/emails', icon: ShieldBan,
                children: [
                    {label: 'menu.emails', to: '/opt-in/emails'},
                    {label: 'menu.domains', to: '/opt-in/domains'},
                ],
            },
            {
                label: 'menu.autoWhitelist', to: '/awl/emails', icon: Sparkles,
                children: [
                    {label: 'menu.emails', to: '/awl/emails'},
                    {label: 'menu.domains', to: '/awl/domains'},
                ],
            },
        ],
    },
    {
        label: 'menu.administration',
        adminOnly: true,
        items: [
            {label: 'menu.users', to: '/users', icon: Users},
            {label: 'menu.alias', to: '/users-aliases', icon: AtSign},
        ],
    },
];
