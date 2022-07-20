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
                            <p className="mb-0">Full Name</p>
                        </div>
                        <div className="col-sm-9">
                            <p className="text-muted mb-0">Johnatan Smith</p>
                        </div>
                    </div>
                    <hr/>
                    <div className="row">
                        <div className="col-sm-3">
                            <p className="mb-0">Email</p>
                        </div>
                        <div className="col-sm-9">
                            <p className="text-muted mb-0">example@example.com</p>
                        </div>
                    </div>
                    <hr/>
                    <div className="row">
                        <div className="col-sm-3">
                            <p className="mb-0">Phone</p>
                        </div>
                        <div className="col-sm-9">
                            <p className="text-muted mb-0">(097) 234-5678</p>
                        </div>
                    </div>
                    <hr/>
                    <div className="row">
                        <div className="col-sm-3">
                            <p className="mb-0">Mobile</p>
                        </div>
                        <div className="col-sm-9">
                            <p className="text-muted mb-0">(098) 765-4321</p>
                        </div>
                    </div>
                    <hr/>
                    <div className="row">
                        <div className="col-sm-3">
                            <p className="mb-0">Address</p>
                        </div>
                        <div className="col-sm-9">
                            <p className="text-muted mb-0">Bay Area, San Francisco, CA</p>
                        </div>
                    </div>
                </div>
            </div>
    );
};

export default UserDetail;
