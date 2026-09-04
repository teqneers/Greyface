import {useQuery} from '@tanstack/react-query';
import React from 'react';
import {useTranslation} from 'react-i18next';

import {useApplication} from '@/application/ApplicationContext';
import {Select, SelectContent, SelectItem, SelectTrigger, SelectValue} from '@/components/ui/select';
import {apiFetch} from '@/lib/api';
import type {ListResponse} from '@/lib/api';
import type {User} from '@/types/user';

const ALL = '__all__';
export const UNASSIGNED = 'show_unassigned';

export interface UserSelectProps {
    value: string,
    onChange: (value: string) => void,
    /** Adds the "unassigned" choice the greylist understands. */
    withUnassigned?: boolean,
    className?: string,
}

/** Filter by user; the empty value means "everyone". */
export function UserSelect({value, onChange, withUnassigned = false, className}: UserSelectProps): React.ReactElement {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const {data} = useQuery({
        queryKey: ['users', 'all'],
        queryFn: () => apiFetch<ListResponse<User>>(`${apiUrl}/users`),
        staleTime: 60_000,
    });

    return (
        <Select value={value || ALL} onValueChange={(next) => onChange(next === ALL ? '' : next)}>
            <SelectTrigger className={className ?? 'w-full sm:w-52'} aria-label={t('placeholder.user')}>
                <SelectValue/>
            </SelectTrigger>
            <SelectContent>
                <SelectItem value={ALL}>{t('placeholder.showAll')}</SelectItem>
                {withUnassigned && <SelectItem value={UNASSIGNED}>{t('placeholder.showUnassigned')}</SelectItem>}
                {data?.results.map((user) => (
                    <SelectItem key={user.id} value={user.id}>{user.username}</SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}
