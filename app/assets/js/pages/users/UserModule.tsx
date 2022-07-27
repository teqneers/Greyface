import React, {useCallback, useState} from 'react';
import {Button} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useQuery} from 'react-query';
import {Route, useHistory, useRouteMatch} from 'react-router-dom';
import {TableState} from 'react-table';

import ApplicationModuleContainer from '../../application/ApplicationModuleContainer';
import LoadingIndicator from '../../controllers/LoadingIndicator';
import {UserAlias} from '../../types/user';
import CreateUser from './CreateUser';
import DeleteUser from './DeleteUser';
import EditUser from './EditUser';
import UsersTable from './UsersTable';

const TABLE_STATE_STORAGE_KEY = 'users.table.state';

const UserModule = () => {
    const {t} = useTranslation();
    const history = useHistory();
    const {path, url} = useRouteMatch();


    const storage = window.localStorage;
    const storage_table_state_key = JSON.parse(storage.getItem(TABLE_STATE_STORAGE_KEY));
    const [tableState, setTableState] = useState(storage_table_state_key ?? {
        sortBy: [{id: 'username', desc: false}],
        filters: [],
        pageSize: 10,
        pageIndex: 0
    });

    // run every time the table state change
    const onStateChange = useCallback<(state: TableState<UserAlias>) => void>((state) => {
        storage.setItem(TABLE_STATE_STORAGE_KEY, JSON.stringify(state));
        setTableState(state);
    }, [storage]);

    const {
        isLoading,
        isError,
        error,
        data,
        isFetching,
        refetch
    } = useQuery(['users', tableState], () => {

        let url = `/api/users?start=${tableState.pageIndex}&max=${tableState.pageSize}`;
        if (tableState.sortBy[0]) {
            url += `&sortBy=${tableState.sortBy[0].id}&desc=${tableState.sortBy[0].desc ? 1 : 0}`;
        }

        return fetch(url).then((res) => res.json());

    }, {keepPreviousData: true});

    if (isLoading) {
        return <LoadingIndicator/>;
    }
    return (
        <ApplicationModuleContainer title="user.header">

            <div className="flex-row mb-2">
                <Button
                    variant="outline-primary"
                    onClick={() => history.push(`${url}/create`)}>{t('button.createUser')}</Button>
            </div>

            {isError ? (
                <div>Error: {error}</div>
            ) : (<UsersTable
                data={data.results}
                pageCount={Math.ceil(data.count / tableState.pageSize)}
                isFetching={isFetching || isLoading}
                initialState={tableState}
                onStateChange={onStateChange}/>)}


            <Route path={`${path}/create`}>
                <CreateUser onCancel={() => history.push(url)}
                            onCreate={() => {
                                history.push(url);
                                refetch();
                            }}/>
            </Route>

            <Route path={`${path}/:id/edit`}>
                <EditUser onCancel={() => history.push(url)}
                          onUpdate={() => {
                              history.push(url);
                              refetch();
                          }}/>
            </Route>

            <Route path={`${path}/:id/delete`}>
                <DeleteUser
                    onCancel={(id) => history.push(`${url}/${id}`)}
                    onDelete={() => {
                        history.push(url);
                        refetch();
                    }}/>
            </Route>

        </ApplicationModuleContainer>
    );
};
export default UserModule;