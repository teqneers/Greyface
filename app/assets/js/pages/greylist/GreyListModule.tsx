import React, {useState} from 'react';
import {Button} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useQuery} from 'react-query';
import {useHistory, useRouteMatch} from 'react-router-dom';

import ApplicationModuleContainer from '../../application/ApplicationModuleContainer';
import LoadingIndicator from '../../controllers/LoadingIndicator';
import GreyListTable from './GreyListTable';

const GreyListModule: React.VFC = () => {

    const {t} = useTranslation();
    const history = useHistory();
    const {path, url} = useRouteMatch();

    const [currentIndex, setCurrentIndex] = useState<number>(0);
    const [currentMaxResults, setCurrentMaxResults] = useState<number>(20);

    const query = useQuery(['greylist', currentIndex, currentMaxResults], () => {
        return fetch('/api/greylist?start=' + currentIndex + '&max=' + currentMaxResults)
            .then((res) => res.json());
    }, {keepPreviousData: true});

    const {
        isLoading,
        isError,
        error,
        data,
        isFetching,
    } = query;

    if (isLoading) {
        return <LoadingIndicator/>;
    }

    return (
        <ApplicationModuleContainer title="greylist.header">

            <div className="row">
                {isError ? (
                    <div>Error: {error}</div>
                ) : (<GreyListTable
                    data={data.results}
                    isFetching={isFetching || isLoading}
                    currentIndex={currentIndex}
                    setCurrentIndex={setCurrentIndex}
                    currentMaxResults={currentMaxResults}
                    setCurrentMaxResults={setCurrentMaxResults}
                    query={query}/>)}
            </div>

        </ApplicationModuleContainer>
    );
};

export default GreyListModule;
