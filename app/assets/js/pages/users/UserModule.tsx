import React, {useCallback, useEffect, useState} from 'react';
import {useQuery} from 'react-query';
import {Route, useHistory, useRouteMatch} from 'react-router-dom';
import {TableState} from 'react-table';

import {useApplication} from '../../application/ApplicationContext';
import ApplicationModuleContainer from '../../application/ApplicationModuleContainer';
import {setSetting, useSettings} from '../../application/settings';
import DefaultButton from '../../controllers/Buttons/DefaultButton';
import LoadingIndicator from '../../controllers/LoadingIndicator';
import ModuleTopBar from '../../controllers/ModuleTopBar';
import {GreyTableState} from '../../types/greylist';
import CreateUser from './CreateUser';
import DeleteUser from './DeleteUser';
import EditUser from './EditUser';
import SetPassword from './SetPassword';
import UsersTable from './UsersTable';

const UserModule = () => {
    const history = useHistory();
    const {apiUrl} = useApplication();
    const {path, url} = useRouteMatch();
    const {users} = useSettings();
    const [tableState, setTableState] = useState<GreyTableState>(users);

    const [searchQuery, setSearchQuery] = useState<string>(users.searchQuery ?? '');

    // run every time the table state change
    const onStateChange = useCallback<(state: TableState<GreyTableState>) => void>((state) => {
        setSetting('users',
            {
                ...state,
                searchQuery: searchQuery
            });
        setTableState(prevState => ({...prevState, ...state, searchQuery: searchQuery}));
    }, [searchQuery]);

    // set pageIndex to 0 whenever search query change
    useEffect(() => {
        const state = {...tableState, pageIndex: 0, searchQuery: searchQuery};
        setSetting('users', state);
        setTableState(state);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [searchQuery]);

    const {
        isLoading,
        isError,
        error,
        data,
        isFetching,
        refetch
    } = useQuery(['users', tableState, searchQuery], () => {

        let url = `${apiUrl}/users?start=${tableState.pageIndex}&max=${tableState.pageSize}&query=${searchQuery}`;
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

            <ModuleTopBar title="user.header"
                          buttons={<DefaultButton
                              label="button.createUser"
                              onClick={() => history.push(`${url}/create`)}/>}
                          searchQuery={searchQuery}
                          setSearchQuery={setSearchQuery}/>

            {isError ? (//@ts-ignore
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

            <Route path={`${path}/:id/password`}>
                <SetPassword onCancel={() => history.push(url)}
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