import React, {useCallback, useState} from 'react';
import {useQuery} from 'react-query';
import {Route, useHistory, useRouteMatch} from 'react-router-dom';
import {TableState} from 'react-table';

import {useApplication} from '../../../application/ApplicationContext';
import ApplicationModuleContainer from '../../../application/ApplicationModuleContainer';
import DefaultButton from '../../../controllers/Buttons/DefaultButton';
import LoadingIndicator from '../../../controllers/LoadingIndicator';
import ModuleTopBar from '../../../controllers/ModuleTopBar';
import {AutoWhiteListDomain} from '../../../types/greylist';
import AddDomain from './AddDomain';
import AutoWhitelistDomainTable from './AutoWhitelistDomainTable';

const TABLE_STATE_STORAGE_KEY = 'greyface.autoWhitelistDomain';

const AutoDomainModule: React.VFC = () => {
    const {apiUrl} = useApplication();
    const history = useHistory();
    const {path, url} = useRouteMatch();

    const storage = window.localStorage;
    const storage_table_state_key = JSON.parse(storage.getItem(TABLE_STATE_STORAGE_KEY));
    const [tableState, setTableState] = useState(storage_table_state_key ?? {
        sortBy: [{id: 'domain', desc: false}],
        filters: [],
        pageSize: 10,
        pageIndex: 0,
        searchQuery: ''
    });

    const [searchQuery, setSearchQuery] = useState(tableState.searchQuery ?? '');

    // run every time the table state change
    const onStateChange = useCallback<(state: TableState<AutoWhiteListDomain>) => void>((state) => {
        storage.setItem(TABLE_STATE_STORAGE_KEY,
            JSON.stringify({
                ...state,
                searchQuery: searchQuery
            }));
        setTableState(state);
    }, [storage, searchQuery]);

    const {
        isLoading,
        isError,
        error,
        data,
        isFetching,
        refetch
    } = useQuery(['awl', 'domains', tableState, searchQuery], () => {

        let url = `${apiUrl}/awl/domains?start=${tableState.pageIndex}&max=${tableState.pageSize}&query=${searchQuery}`;
        if (tableState.sortBy[0]) {
            url += `&sortBy=${tableState.sortBy[0].id}&desc=${tableState.sortBy[0].desc ? 1 : 0}`;
        }

        return fetch(url).then((res) => res.json());

    }, {keepPreviousData: true});

    if (isLoading) {
        return <LoadingIndicator/>;
    }

    return (
        <ApplicationModuleContainer title="autoWhitelist.domain.header">

            <ModuleTopBar title="autoWhitelist.domain.header"
                          buttons={<DefaultButton
                              label="button.addDomain"
                              onClick={() => history.push(`${url}/add`)}/>}
                          searchQuery={searchQuery}
                          setSearchQuery={setSearchQuery}/>

            {isError ? (
                <div>Error: {error}</div>
            ) : (<AutoWhitelistDomainTable
                data={data.results}
                refetch={refetch}
                pageCount={Math.ceil(data.count / tableState.pageSize)}
                isFetching={isFetching || isLoading}
                initialState={tableState}
                onStateChange={onStateChange}/>)}

            <Route path={`${path}/add`}>
                <AddDomain onCancel={() => history.push(url)}
                           onCreate={() => {
                               history.push(url);
                               refetch();
                           }}/>
            </Route>
        </ApplicationModuleContainer>
    );
};

export default AutoDomainModule;
