import {useMemo} from 'react';

import type {User} from '../types/user';
import {useApplication} from './ApplicationContext';

interface UsePermissions {
    isAdministrator(): boolean,
    isCurrentUser(checkUser: User): boolean,
}

export function usePermissions(): UsePermissions {
    const {user} = useApplication();

    return useMemo<UsePermissions>(() => ({
        isAdministrator(): boolean {
            return user.is_administrator;
        },
        isCurrentUser(checkUser: User): boolean {
            return user.id === checkUser.id;
        }
    }), [user]);
}
