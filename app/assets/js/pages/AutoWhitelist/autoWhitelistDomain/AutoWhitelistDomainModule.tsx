import React, {useCallback, useEffect, useState} from 'react';
import {useQuery} from 'react-query';
import {Route, useHistory, useRouteMatch} from 'react-router-dom';
import {TableState} from 'react-table';

import {useApplication} from '../../../application/ApplicationContext';
import ApplicationModuleContainer from '../../../application/ApplicationModuleContainer';
import {setSetting, useSettings} from '../../../application/settings';
import DefaultButton from '../../../controllers/Buttons/DefaultButton';
import LoadingIndicator from '../../../controllers/LoadingIndicator';
import ModuleTopBar from '../../../controllers/ModuleTopBar';
import {AutoWhiteListDomain, GreyTableState} from '../../../types/greylist';
import AddDomain from './AddDomain';
import AutoWhitelistDomainTable from './AutoWhitelistDomainTable';

const AutoDomainModule: React.FC = () => {
    const {apiUrl} = useApplication();
    const history = useHistory();
    const {path, url} = useRouteMatch();
    const {autoWhitelistDomain} = useSettings();

    const [tableState, setTableState] = useState<GreyTableState>(autoWhitelistDomain);

    const [searchQuery, setSearchQuery] = useState<string>(autoWhitelistDomain.searchQuery ?? '');

    // run every time the table state change
    const onStateChange = useCallback<(state: TableState<AutoWhiteListDomain>) => void>((state) => {
        setSetting('autoWhitelistDomain',
            {
                ...state,
                searchQuery: searchQuery
            });
        setTableState(prevState => ({...prevState, ...state, searchQuery: searchQuery}));
    }, [searchQuery]);

    // set pageIndex to 0 whenever search query change
    useEffect(() => {
        const state = {...tableState, pageIndex: 0, searchQuery: searchQuery};
        setSetting('autoWhitelistDomain',state);
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

            {isError ? (    // @ts-ignore
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
