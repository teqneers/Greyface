import {AtSign, Hourglass, LayoutDashboard, ShieldBan, ShieldCheck, Sparkles, Users} from 'lucide-react';
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
 */
export const navigation: NavGroup[] = [
    {
        items: [
            {label: 'menu.dashboard', to: '/dashboard', icon: LayoutDashboard, adminOnly: true},
            {label: 'menu.greylist', to: '/greylist', icon: Hourglass},
        ],
    },
    {
        label: 'menu.lists',
        adminOnly: true,
        items: [
            {label: 'menu.whitelist', to: '/whitelist', icon: ShieldCheck},
            {label: 'menu.blacklist', to: '/blacklist', icon: ShieldBan},
            {label: 'menu.autoWhitelist', to: '/auto-whitelist', icon: Sparkles},
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
