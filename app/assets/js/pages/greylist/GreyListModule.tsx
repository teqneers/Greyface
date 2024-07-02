import React, {useCallback, useEffect, useState} from 'react';
import {useQuery} from 'react-query';
import {TableState} from 'react-table';

import {useApplication} from '../../application/ApplicationContext';
import ApplicationModuleContainer from '../../application/ApplicationModuleContainer';
import {setSetting, useSettings} from '../../application/settings';
import {usePermissions} from '../../application/usePermissions';
import LoadingIndicator from '../../controllers/LoadingIndicator';
import ModuleTopBar from '../../controllers/ModuleTopBar';
import UserFilter from '../../controllers/UserFilter';
import {Greylist, GreyTableStateWithUser} from '../../types/greylist';
import DeleteByDate from './DeleteByDate';
import GreyListTable from './GreyListTable';

const GreyListModule: React.VFC = () => {

    const {apiUrl} = useApplication();

    const {isAdministrator} = usePermissions();
    const {greyList} = useSettings();
    const [tableState, setTableState] = useState<GreyTableStateWithUser>(greyList);

    const [searchQuery, setSearchQuery] = useState<string>(greyList.searchQuery ?? '');
    const [user, setUser] = useState(greyList.user ?? '');

    // run every time the table state change
    const onStateChange = useCallback<(state: TableState<Greylist>) => void>((state) => {
        setSetting('greyList',
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
        setSetting('greyList', state);
        setTableState(state);
    }, [searchQuery, user]);

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
