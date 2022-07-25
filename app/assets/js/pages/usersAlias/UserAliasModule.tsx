import React, {useState} from 'react';
import {Button} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useQuery} from 'react-query';
import {Route, useHistory, useRouteMatch} from 'react-router-dom';

import ApplicationModuleContainer from '../../application/ApplicationModuleContainer';
import LoadingIndicator from '../../controllers/LoadingIndicator';
import CreateUserAlias from './CreateUserAlias';
import UserAliasTable from './UserAliasTable';

const UserAliasModule: React.VFC = () => {

    const {t} = useTranslation();
    const history = useHistory();
    const {path, url} = useRouteMatch();

    const [currentIndex, setCurrentIndex] = useState<number>(0);
    const [currentMaxResults, setCurrentMaxResults] = useState<number>(20);

    const query = useQuery(['users-aliases', currentIndex, currentMaxResults], () => {
        return fetch('/api/users-aliases?start=' + currentIndex + '&max=' + currentMaxResults)
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
        <ApplicationModuleContainer title="alias.header">

            <div className="flex-row mb-2">
                <Button
                    variant="brand"
                    onClick={() => history.push(`${url}/create`)}>{t('button.createUserAlias')}</Button>
            </div>

            <div className="row">
                {isError ? (
                    <div>Error: {error}</div>
                ) : (<UserAliasTable
                    data={data.results}
                    isFetching={isFetching || isLoading}
                    currentIndex={currentIndex}
                    setCurrentIndex={setCurrentIndex}
                    currentMaxResults={currentMaxResults}
                    setCurrentMaxResults={setCurrentMaxResults}
                    query={query}/>)}
            </div>

            <Route path={`${path}/create`}>
                <CreateUserAlias onCancel={() => history.push(url)}
                            onCreate={(id) => history.push(`${url}/${id}`)}/>
            </Route>

        </ApplicationModuleContainer>
    );
};

export default UserAliasModule;
