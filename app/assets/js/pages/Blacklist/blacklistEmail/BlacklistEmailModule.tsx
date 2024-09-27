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
import {BlackListEmail, GreyTableState} from '../../../types/greylist';
import AddEmail from './AddEmail';
import BlacklistEmailTable from './BlacklistEmailTable';

const BlacklistEmailModule: React.FC = () => {

    const history = useHistory();
    const {apiUrl} = useApplication();
    const {path, url} = useRouteMatch();
    const {blacklistEmail} = useSettings();
    const [tableState, setTableState] = useState<GreyTableState>(blacklistEmail);

    const [searchQuery, setSearchQuery] = useState<string>(blacklistEmail.searchQuery ?? '');

    // run every time the table state change
    const onStateChange = useCallback<(state: TableState<BlackListEmail>) => void>((state) => {
        setSetting('blacklistEmail',
            {
                ...state,
                searchQuery: searchQuery
            });
        setTableState(prevState => ({...prevState, ...state, searchQuery: searchQuery}));
    }, [searchQuery]);

    // set pageIndex to 0 whenever search query change
    useEffect(() => {
        const state = {...tableState, pageIndex: 0, searchQuery: searchQuery};
        setSetting('blacklistEmail', state);
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
        <ApplicationModuleContainer title="blacklist.email.header">

            <ModuleTopBar title="blacklist.email.header"
                          buttons={<DefaultButton
                              label="button.addEmail"
                              onClick={() => history.push(`${url}/add`)}/>}
                          searchQuery={searchQuery}
                          setSearchQuery={setSearchQuery}/>

            {isError ? (//@ts-ignore
                <div>Error: {error}</div>
            ) : (<BlacklistEmailTable
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

export default BlacklistEmailModule;
