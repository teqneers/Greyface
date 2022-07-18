import {AuditTracking, HasId} from './common';

export const USER_ROLES = ['user', 'admin'] as const;

export type UserRole = typeof USER_ROLES[number];

export interface User extends HasId, AuditTracking {
    username: string,
    name: string,
    role: UserRole,
    all_roles: UserRole[],
    is_administrator: boolean,
    is_disabled: boolean,
    is_deleted: boolean,
    is_locked: boolean,
}
