import React, {useCallback, useEffect, useState} from 'react';
import {useQuery} from 'react-query';
import {Route, useHistory, useRouteMatch} from 'react-router-dom';
import {TableState} from 'react-table';

import ApplicationModuleContainer from '../../../application/ApplicationModuleContainer';
import {useApplication} from '../../../application/ApplicationContext';
import {setSetting, useSettings} from '../../../application/settings';
import DefaultButton from '../../../controllers/Buttons/DefaultButton';
import LoadingIndicator from '../../../controllers/LoadingIndicator';
import ModuleTopBar from '../../../controllers/ModuleTopBar';
import {GreyTableState, WhiteListDomain} from '../../../types/greylist';
import AddDomain from './AddDomain';
import WhitelistDomainTable from './WhitelistDomainTable';

const WhitelistDomainModule: React.VFC = () => {
    const history = useHistory();
    const {apiUrl} = useApplication();
    const {path, url} = useRouteMatch();
    const {whitelistDomain} = useSettings();

    const [tableState, setTableState] = useState<GreyTableState>(whitelistDomain);

    const [searchQuery, setSearchQuery] = useState<string>(whitelistDomain.searchQuery ?? '');

    // run every time the table state change
    const onStateChange = useCallback<(state: TableState<WhiteListDomain>) => void>((state) => {
        setSetting('whitelistDomain',
            {
                ...state,
                searchQuery: searchQuery
            });
        setTableState(prevState => ({...prevState, ...state, searchQuery: searchQuery}));
    }, [searchQuery]);

    // set pageIndex to 0 whenever search query change
    useEffect(() => {
        const state = {...tableState, pageIndex: 0, searchQuery: searchQuery};
        setSetting('whitelistDomain',state);
        setTableState(state);
    }, [searchQuery]);

    const {
        isLoading,
        isError,
        error,
        data,
        isFetching,
        refetch
    } = useQuery(['opt-out', 'domains', tableState, searchQuery], () => {

        let url = `${apiUrl}/opt-out/domains?start=${tableState.pageIndex}&max=${tableState.pageSize}&query=${searchQuery}`;
        if (tableState.sortBy[0]) {
            url += `&sortBy=${tableState.sortBy[0].id}&desc=${tableState.sortBy[0].desc ? 1 : 0}`;
        }

        return fetch(url).then((res) => res.json());

    }, {keepPreviousData: true});

    if (isLoading) {
        return <LoadingIndicator/>;
    }

    return (
        <ApplicationModuleContainer title="whitelist.domain.header">

            <ModuleTopBar title="whitelist.domain.header"
                          buttons={<DefaultButton
                              label="button.addDomain"
                              onClick={() => history.push(`${url}/add`)}/>}
                          searchQuery={searchQuery}
                          setSearchQuery={setSearchQuery}/>

            {isError ? (
                <div>Error: {error}</div>
            ) : (<WhitelistDomainTable
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

export default WhitelistDomainModule;
