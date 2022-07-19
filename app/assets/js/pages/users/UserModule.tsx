import React, {useState} from 'react';
import {Table} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useQuery} from 'react-query';

import ApplicationModuleContainer from '../../application/ApplicationModuleContainer';
import LoadingIndicator from '../../controllers/LoadingIndicator';
import Paginator from '../../controllers/Paginator';

const UserModule = () => {
    const {t} = useTranslation();
    const [currentIndex, setCurrentIndex] = useState<number>(0);
    const [currentMaxResults, setCurrentMaxResults] = useState<number>(20);

    const query = useQuery(['users', currentIndex, currentMaxResults], () => {
       return fetch('/api/users?start=' + currentIndex + '&max=' + currentMaxResults)
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
        <ApplicationModuleContainer title="user.header">
            {isLoading ? (
                <LoadingIndicator/>
            ) : isError ? (
                <div>Error: {error}</div>
            ) : (
                <Table striped bordered hover>
                    <thead>
                    <tr>
                        <th>{t('user.username')}</th>
                        <th>{t('user.email')}</th>
                        <th>{t('user.role')}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {data.results.map(user => (
                        <tr key={user.id}>
                            <td>{user.username}</td>
                            <td>{user.email}</td>
                            <td>{t(`user.roles.${user.role}`)}</td>
                        </tr>
                    ))}
                    </tbody>
                </Table>
            )}

            <Paginator currentIndex={currentIndex}
                       setCurrentIndex={setCurrentIndex}
                       currentMaxResults={currentMaxResults}
                       setCurrentMaxResults={setCurrentMaxResults}
                       query={query}/>


            {isFetching ? <LoadingIndicator/> : null}
        </ApplicationModuleContainer>
    );
};
export default UserModule;