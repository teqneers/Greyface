import React, {useCallback, useState} from 'react';
import {useQuery} from 'react-query';
import {TableState} from 'react-table';

import {useApplication} from '../../application/ApplicationContext';
import ApplicationModuleContainer from '../../application/ApplicationModuleContainer';
import {usePermissions} from '../../application/usePermissions';
import LoadingIndicator from '../../controllers/LoadingIndicator';
import ModuleTopBar from '../../controllers/ModuleTopBar';
import UserFilter from '../../controllers/UserFilter';
import {Greylist} from '../../types/greylist';
import DeleteByDate from './DeleteByDate';
import GreyListTable from './GreyListTable';

const TABLE_STATE_STORAGE_KEY = 'greylist.table';
const GreyListModule: React.VFC = () => {

    const {apiUrl} = useApplication();

    const {isAdministrator} = usePermissions();

    const storage = window.localStorage;
    const storage_table_state_key = JSON.parse(storage.getItem(TABLE_STATE_STORAGE_KEY));
    const [tableState, setTableState] = useState(storage_table_state_key ?? {
        sortBy: [{id: 'username', desc: false}],
        pageSize: 10,
        pageIndex: 0,
        searchQuery: '',
        user: ''
    });

    const [searchQuery, setSearchQuery] = useState(tableState.searchQuery ?? '');
    const [user, setUser] = useState(tableState.user ?? '');

    // run every time the table state change
    const onStateChange = useCallback<(state: TableState<Greylist>) => void>((state) => {
        storage.setItem(TABLE_STATE_STORAGE_KEY,
            JSON.stringify({
                ...state,
                searchQuery: searchQuery,
                user: user
            }));
        setTableState(state);
    }, [storage, user, searchQuery]);

    const {
        isLoading,
        isError,
        error,
        data,
        isFetching,
        refetch
    } = useQuery(['greylist', tableState, searchQuery, user], () => {

        let url = `${apiUrl}/greylist?start=${tableState.pageIndex}&max=${tableState.pageSize}&query=${searchQuery}`;

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
        <ApplicationModuleContainer title="greylist.header">

            {isAdministrator() && <ModuleTopBar title="greylist.header"
                                                buttons={<DeleteByDate onDelete={refetch}/>}
                                                userFilter={<UserFilter user={user} setUser={setUser}
                                                                        filterFor="greylist"/>}
                                                searchQuery={searchQuery}
                                                setSearchQuery={setSearchQuery}/>}

            {!isAdministrator() && <ModuleTopBar title="greylist.header"
                                                 setSearchQuery={setSearchQuery}/>}

            {isError ? (
                <div>Error: {error}</div>
            ) : (<GreyListTable
                data={data.results}
                refetch={refetch}
                pageCount={Math.ceil(data.count / tableState.pageSize)}
                isFetching={isFetching || isLoading}
                initialState={tableState}
                onStateChange={onStateChange}/>)}

        </ApplicationModuleContainer>
    );
};

export default GreyListModule;
