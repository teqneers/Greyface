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
import {AutoWhiteListEmail, GreyTableState} from '../../../types/greylist';
import AddEmail from './AddEmail';
import AutoWhitelistEmailTable from './AutoWhitelistEmailTable';

const AutoEmailModule: React.FC = () => {

    const history = useHistory();
    const {apiUrl} = useApplication();
    const {path, url} = useRouteMatch();
    const {autoWhitelistEmail} = useSettings();

    const [tableState, setTableState] = useState<GreyTableState>(autoWhitelistEmail);

    const [searchQuery, setSearchQuery] = useState<string>(autoWhitelistEmail.searchQuery ?? '');

    // run every time the table state change
    const onStateChange = useCallback<(state: TableState<AutoWhiteListEmail>) => void>((state) => {
        setSetting('autoWhitelistEmail',
            {
                ...state,
                searchQuery: searchQuery
            });
        setTableState(prevState => ({...prevState, ...state, searchQuery: searchQuery}));
    }, [searchQuery]);

    // set pageIndex to 0 whenever search query change
    useEffect(() => {
        const state = {...tableState, pageIndex: 0, searchQuery: searchQuery};
        setSetting('autoWhitelistEmail',state);
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
    } = useQuery(['awl', 'emails', tableState, searchQuery], () => {

        let url = `${apiUrl}/awl/emails?start=${tableState.pageIndex}&max=${tableState.pageSize}&query=${searchQuery}`;
        if (tableState.sortBy[0]) {
            url += `&sortBy=${tableState.sortBy[0].id}&desc=${tableState.sortBy[0].desc ? 1 : 0}`;
        }

        return fetch(url).then((res) => res.json());

    }, {keepPreviousData: true});

    if (isLoading) {
        return <LoadingIndicator/>;
    }

    return (
        <ApplicationModuleContainer title="autoWhitelist.email.header">

            <ModuleTopBar title="autoWhitelist.email.header"
                          buttons={<DefaultButton
                              label="button.addEmail"
                              onClick={() => history.push(`${url}/add`)}/>}
                          searchQuery={searchQuery}
                          setSearchQuery={setSearchQuery}/>

            {isError ? ( //@ts-ignore
                <div>Error: {error}</div>
            ) : (<AutoWhitelistEmailTable
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

export default AutoEmailModule;
