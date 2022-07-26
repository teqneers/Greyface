import React, {useCallback, useState} from 'react';
import {Button} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useQuery} from 'react-query';
import {Route, useHistory, useRouteMatch} from 'react-router-dom';
import {TableState} from 'react-table';

import ApplicationModuleContainer from '../../application/ApplicationModuleContainer';
import LoadingIndicator from '../../controllers/LoadingIndicator';
import {UserAlias} from '../../types/user';
import CreateUserAlias from './CreateUserAlias';
import UserAliasTable from './UserAliasTable';

const TABLE_STATE_STORAGE_KEY = 'useralias.table.state';
const UserAliasModule: React.VFC = () => {

    const {t} = useTranslation();
    const history = useHistory();
    const {path, url} = useRouteMatch();

    const storage = window.localStorage;
    const storage_table_state_key = JSON.parse(storage.getItem(TABLE_STATE_STORAGE_KEY)) ?? [];
    const tableState = storage_table_state_key ?? {
        sortBy: [{id: 'aliasName', desc: false}],
        filters: [],
        pageSize: 10,
        pageIndex: 0
    };
    const [currentMaxResults, setCurrentMaxResults] = useState(tableState.pageSize);
    const [currentIndex, setCurrentIndex] = useState(tableState.pageIndex);

    // run every time the table state change
    const onStateChange = useCallback<(state: TableState<UserAlias>) => void>((state) => {
        console.log('onstate', state);
        storage.setItem(TABLE_STATE_STORAGE_KEY, JSON.stringify(state));
        setCurrentMaxResults(state.pageSize);
        setCurrentIndex(state.pageIndex);
        console.log('after set max', state);
    }, []);

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
        refetch
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
                    initialState={tableState}
                    onStateChange={onStateChange}/>)}
            </div>

            <Route path={`${path}/create`}>
                <CreateUserAlias onCancel={() => history.push(url)}
                                 onCreate={(id) => {
                                     history.push(`${url}/${id}`);
                                     refetch();
                                 }}/>
            </Route>

        </ApplicationModuleContainer>
    );
};

export default UserAliasModule;
