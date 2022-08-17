import React, {useCallback, useState} from 'react';
import {useQuery} from 'react-query';
import {Route, useHistory, useRouteMatch} from 'react-router-dom';
import {TableState} from 'react-table';

import {useApplication} from '../../../application/ApplicationContext';
import ApplicationModuleContainer from '../../../application/ApplicationModuleContainer';
import DefaultButton from '../../../controllers/Buttons/DefaultButton';
import LoadingIndicator from '../../../controllers/LoadingIndicator';
import ModuleTopBar from '../../../controllers/ModuleTopBar';
import {UserAlias} from '../../../types/user';
import AddEmail from './AddEmail';
import WhitelistEmailTable from './WhitelistEmailTable';

const TABLE_STATE_STORAGE_KEY = 'greyface.whitelistEmail';

const WhitelistEmailModule: React.VFC = () => {

    const history = useHistory();
    const {apiUrl} = useApplication();
    const {path, url} = useRouteMatch();
    const [searchQuery, setSearchQuery] = useState('');

    const storage = window.localStorage;
    const storage_table_state_key = JSON.parse(storage.getItem(TABLE_STATE_STORAGE_KEY));
    const [tableState, setTableState] = useState(storage_table_state_key ?? {
        sortBy: [{id: 'email', desc: false}],
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
    } = useQuery(['opt-in', 'emails', tableState, searchQuery], () => {

        let url = `${apiUrl}/opt-in/emails?start=${tableState.pageIndex}&max=${tableState.pageSize}&query=${searchQuery}`;
        if (tableState.sortBy[0]) {
            url += `&sortBy=${tableState.sortBy[0].id}&desc=${tableState.sortBy[0].desc ? 1 : 0}`;
        }

        return fetch(url).then((res) => res.json());

    }, {keepPreviousData: true});

    if (isLoading) {
        return <LoadingIndicator/>;
    }

    return (
        <ApplicationModuleContainer title="whitelist.email.header">

            <ModuleTopBar title="whitelist.email.header"
                          buttons={<DefaultButton
                              label="button.addEmail"
                              onClick={() => history.push(`${url}/add`)}/>}
                          setSearchQuery={setSearchQuery}/>

            {isError ? (
                <div>Error: {error}</div>
            ) : (<WhitelistEmailTable
                data={data.results}
                refetch={refetch}
                pageCount={Math.ceil(data.count / tableState.pageSize)}
                isFetching={isFetching || isLoading}
                initialState={tableState}
                onStateChange={onStateChange}/>)}

            <Route path={`${path}/add`}>
                <AddEmail onCancel={() => history.push(url)}
                          onCreate={() => {
                              history.push(url);
                              refetch();
                          }}/>
            </Route>
        </ApplicationModuleContainer>
    );
};

export default WhitelistEmailModule;
