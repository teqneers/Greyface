import React, {useState} from 'react';
import {Button} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useQuery} from 'react-query';
import {Route, Switch, useHistory, useRouteMatch} from 'react-router-dom';

import ApplicationModuleContainer from '../../application/ApplicationModuleContainer';
import EmptyRoute from '../../application/EmptyRoute';
import LoadingIndicator from '../../controllers/LoadingIndicator';
import CreateUser from './CreateUser';
import DeleteUser from './DeleteUser';
import EditUser from './EditUser';
import UserDetail from './UserDetail';
import UsersTable from './UsersTable';

const UserModule = () => {
    const {t} = useTranslation();
    const history = useHistory();
    const {path, url} = useRouteMatch();

    const [currentIndex, setCurrentIndex] = useState<number>(0);
    const [currentMaxResults, setCurrentMaxResults] = useState<number>(20);

    const query = useQuery(['users', currentIndex, currentMaxResults], () => {
        return fetch('/api/users?start=' + currentIndex + '&max=' + currentMaxResults)
            .then((res) => res.json());
    }, {keepPreviousData: true});

    const {
        isLoading,
        isError,
        error,
        data,
        isFetching,
    } = query;

    if (isLoading) {
        return <LoadingIndicator/>;
    }

    return (
        <ApplicationModuleContainer title="user.header">

            <div className="flex-row mb-2">
                <Button
                    variant="brand"
                    onClick={() => history.push(`${url}/create`)}>{t('button.createUser')}</Button>
            </div>

            <div className="row">
                <div className="col-lg-8">
                    {isError ? (
                        <div>Error: {error}</div>
                    ) : (<UsersTable
                        data={data.results}
                        isFetching={isFetching}
                        currentIndex={currentIndex}
                        setCurrentIndex={setCurrentIndex}
                        currentMaxResults={currentMaxResults}
                        setCurrentMaxResults={setCurrentMaxResults}
                        query={query}
                        onItemClick={(u) => {
                            history.push(`${url}/${u.id}`);
                        }}/>)}
                </div>
                <div className="col-lg-4">
                    <Switch>
                        <Route path={`${path}/create`}>
                            <CreateUser onCancel={() => history.push(url)}
                                        onCreate={(id) => history.push(`${url}/${id}`)}/>
                        </Route>

                        <Route path={`${path}/:id/edit`}>
                            <EditUser onCancel={() => history.push(url)}
                                      onUpdate={(id) => history.push(`${url}/${id}`)}/>
                        </Route>

                        <Route path={`${path}/:id`}>
                            <UserDetail onBack={() => history.push(url)}/>
                        </Route>
                        <EmptyRoute/>
                    </Switch>
                    <Route path={`${path}/:id/delete`}>
                        <DeleteUser onCancel={(id) => history.push(`${url}/${id}`)} onDelete={() => history.push(url)}/>
                    </Route>
                </div>
            </div>
        </ApplicationModuleContainer>
    );
};
export default UserModule;