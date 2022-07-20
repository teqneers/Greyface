import React from 'react';
import {useTranslation} from 'react-i18next';
import {useQuery} from 'react-query';
import {useHistory, useRouteMatch} from 'react-router-dom';

import {usePermissions} from '../../application/usePermissions';
import LoadingIndicator from '../../controllers/LoadingIndicator';

interface UserDetailProps {
    onBack: () => void
}

const UserDetail: React.VFC<UserDetailProps> = ({onBack}) => {
    const {isAdministrator, isCurrentUser} = usePermissions();
    const history = useHistory();
    const {t} = useTranslation();
    const {url, params: {id}} = useRouteMatch<{ id: string }>();

    const {data, isLoading} = useQuery(['users', id], () => {
        return fetch('/api/users/' + id)
            .then((res) => res.json());
    });

    if (isLoading) {
        return <LoadingIndicator/>;
    }

    return (<>
            {data.username}
        </>
    );
};

export default UserDetail;
