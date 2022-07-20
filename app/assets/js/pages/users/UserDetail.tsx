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

    return (
            <div className="card mb-4">
                <div className="card-body">
                    <div className="row">
                        <div className="col-sm-3">
                            <p className="mb-0">{t('user.username')}</p>
                        </div>
                        <div className="col-sm-9">
                            <p className="text-muted mb-0">{data.username}</p>
                        </div>
                    </div>
                    <hr/>
                    <div className="row">
                        <div className="col-sm-3">
                            <p className="mb-0">{t('user.email')}</p>
                        </div>
                        <div className="col-sm-9">
                            <p className="text-muted mb-0">{data.email}</p>
                        </div>
                    </div>
                    <hr/>
                    <div className="row">
                        <div className="col-sm-3">
                            <p className="mb-0">{t('user.role')}</p>
                        </div>
                        <div className="col-sm-9">
                            <p className="text-muted mb-0">{t(`user.roles.${data.role}`)}</p>
                        </div>
                    </div>
                </div>
            </div>
    );
};

export default UserDetail;
