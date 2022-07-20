import React, {useState} from 'react';
import {useQuery} from 'react-query';
import {Route, Switch, useHistory, useRouteMatch} from 'react-router-dom';

import ApplicationModuleContainer from '../../application/ApplicationModuleContainer';
import EmptyRoute from '../../application/EmptyRoute';
import LoadingIndicator from '../../controllers/LoadingIndicator';
import SplitView from '../../controllers/SplitView';
import UserDetail from './UserDetail';
import UsersTable from './UsersTable';

const UserModule = () => {
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
            <SplitView sizes={[70, 30]}>
                {isError ? (
                    <div>Error: {error}</div>
                ) : (
                    <UsersTable
                        data={data.results}
                        isFetching={isFetching}
                        currentIndex={currentIndex}
                        setCurrentIndex={setCurrentIndex}
                        currentMaxResults={currentMaxResults}
                        setCurrentMaxResults={setCurrentMaxResults}
                        query={query}
                        onItemClick={(u) => {
                            history.push(`${url}/${u.id}`);
                        }}/>
                )}
                <div className="detail-panel">
                    <Switch>
                        <Route path={`${path}/:id`}>
                            <UserDetail onBack={() => history.push(url)}/>
                        </Route>
                        <EmptyRoute/>
                    </Switch>
                </div>
            </SplitView>
        </ApplicationModuleContainer>
    );
};
export default UserModule;