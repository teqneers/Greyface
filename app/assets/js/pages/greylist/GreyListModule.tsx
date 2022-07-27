import React, {useCallback, useState} from 'react';
import {useQuery} from 'react-query';
import {TableState} from 'react-table';

import ApplicationModuleContainer from '../../application/ApplicationModuleContainer';
import LoadingIndicator from '../../controllers/LoadingIndicator';
import ModuleTopBar from '../../controllers/ModuleTopBar';
import {UserAlias} from '../../types/user';
import GreyListTable from './GreyListTable';

const TABLE_STATE_STORAGE_KEY = 'greylist.table.state';
const GreyListModule: React.VFC = () => {

    const [searchQuery, setSearchQuery] = useState('');

    const storage = window.localStorage;
    const storage_table_state_key = JSON.parse(storage.getItem(TABLE_STATE_STORAGE_KEY));
    const [tableState, setTableState] = useState(storage_table_state_key ?? {
        sortBy: [{id: 'username', desc: false}],
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
        isFetching
    } = useQuery(['greylist', tableState, searchQuery], () => {

        let url = `/api/greylist?start=${tableState.pageIndex}&max=${tableState.pageSize}&query=${searchQuery}`;
        if (tableState.sortBy[0]) {
            url += `&sortBy=${tableState.sortBy[0].id}&desc=${tableState.sortBy[0].desc ? 1 : 0}`;
        }

        return fetch(url).then((res) => res.json());

    }, {keepPreviousData: true});

    if (isLoading) {
        return <LoadingIndicator/>;
    }

    return (
        <ApplicationModuleContainer title="greylist.header">

            <ModuleTopBar title="greylist.header"
                          setSearchQuery={setSearchQuery}/>

                {isError ? (
                    <div>Error: {error}</div>
                ) : (<GreyListTable
                    data={data.results}
                    pageCount={Math.ceil(data.count / tableState.pageSize)}
                    isFetching={isFetching || isLoading}
                    initialState={tableState}
                    onStateChange={onStateChange}/>)}

        </ApplicationModuleContainer>
    );
};

export default GreyListModule;
