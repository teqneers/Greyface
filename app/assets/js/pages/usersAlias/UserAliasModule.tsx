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
import UserFilter from '../../controllers/UserFilter';
import {GreyTableStateWithUser} from '../../types/greylist';
import {UserAlias} from '../../types/user';
import CreateUserAlias from './CreateUserAlias';
import DeleteUserAlias from './DeleteUserAlias';
import EditUserAlias from './EditUserAlias';
import UserAliasTable from './UserAliasTable';

const UserAliasModule: React.FC = () => {

    const history = useHistory();
    const {apiUrl} = useApplication();
    const {path, url} = useRouteMatch();
    const {userAlias} = useSettings();
    const [tableState, setTableState] = useState<GreyTableStateWithUser>(userAlias);

    const [searchQuery, setSearchQuery] = useState<string>(userAlias.searchQuery ?? '');
    const [user, setUser] = useState(userAlias.user ?? '');

    // run every time the table state change
    const onStateChange = useCallback<(state: TableState<UserAlias>) => void>((state) => {
        setSetting('userAlias',
            {
                ...state,
                searchQuery: searchQuery,
                user: user
            });
        setTableState(prevState => ({...prevState, ...state, searchQuery: searchQuery}));
    }, [searchQuery, user]);

    // set pageIndex to 0 whenever search query change
    useEffect(() => {
        const state = {...tableState, pageIndex: 0, searchQuery: searchQuery, user: user};
        setSetting('userAlias', state);
        setTableState(state);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [searchQuery, user]);

    const {
        isLoading,
        isError,
        error,
        data,
        isFetching,
        refetch
    } = useQuery(['users-aliases', tableState, searchQuery, user], () => {

        let url = `${apiUrl}/users-aliases?start=${tableState.pageIndex}&max=${tableState.pageSize}&query=${searchQuery}`;

        if (tableState.sortBy[0]) {
            url += `&sortBy=${tableState.sortBy[0].id}&desc=${tableState.sortBy[0].desc ? 1 : 0}`;
        }

        if (user) {
            url += `&user=${user}`;
        }

        return fetch(url).then((res) => res.json());

    }, {keepPreviousData: true});

    if (isLoading) {
        return <LoadingIndicator/>;
    }

    return (
        <ApplicationModuleContainer title="alias.header">


            <ModuleTopBar title="alias.header"
                          buttons={<DefaultButton
                              label="button.createUserAlias"
                              onClick={() => history.push(`${url}/create`)}/>}
                          userFilter={<UserFilter user={user} setUser={setUser}/>}
                          searchQuery={searchQuery}
                          setSearchQuery={setSearchQuery}/>

            {isError ? (
                <div>Error: {error}</div>
            ) : (<UserAliasTable
                data={data.results}
                pageCount={Math.ceil(data.count / tableState.pageSize)}
                isFetching={isFetching || isLoading}
                initialState={tableState}
                onStateChange={onStateChange}/>)}

            <Route path={`${path}/create`}>
                <CreateUserAlias onCancel={() => history.push(url)}
                                 onCreate={() => {
                                     history.push(url);
                                     refetch();
                                 }}/>
            </Route>

            <Route path={`${path}/:id/edit`}>
                <EditUserAlias onCancel={() => history.push(url)}
                               onUpdate={() => {
                                   history.push(url);
                               }}/>
            </Route>

            <Route path={`${path}/:id/delete`}>
                <DeleteUserAlias
                    onCancel={() => history.push(url)}
                    onDelete={() => {
                        history.push(url);
                    }}/>
            </Route>

        </ApplicationModuleContainer>
    );
};

export default UserAliasModule;
