import React from 'react';
import {Form} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useQuery} from 'react-query';

import {useApplication} from '../application/ApplicationContext';
import LoadingIndicator from './LoadingIndicator';

interface UserFilterProps {
    user: string | null,
    setUser: (user: string) => void,
    filterFor?: string
}

const UserFilter: React.FC<UserFilterProps> = ({user, setUser, filterFor = 'userAlias'}) => {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();

    const {data: users, isLoading: usersLoading} = useQuery(['users'], () => {
        return fetch(`${apiUrl}/users2`)
            .then((res) => res.json());
    }, {keepPreviousData: true});

    if (usersLoading) {
        return <LoadingIndicator/>;
    }

    return (
        <>
            <Form.Label column>{t('placeholder.user')}</Form.Label>
            <Form.Select
                size="sm"
                value={user}
                onChange={(v) => setUser(v.target.value)}>
                <option value="">{t('placeholder.showAll')}</option>
                {filterFor === 'greylist' && <option value="show_unassigned">{t('placeholder.showUnassigned')}</option>}
                {users && users.results && users.results.map((u) => {
                    return (
                        <option key={u.id} value={u.id}>{u.username}</option>
                    );
                })}
            </Form.Select>
        </>
    );
};

export default UserFilter;
