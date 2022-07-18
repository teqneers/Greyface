import {useApplication} from './ApplicationContext';
import {useMemo} from 'react';

interface UsePermissions {
    isAdministrator(): boolean,
}

export function usePermissions(): UsePermissions {
    const {user} = useApplication();

    return useMemo<UsePermissions>(() => ({
        isAdministrator(): boolean {
            return true;
        },
    }), [user]);
}
