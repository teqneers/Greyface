import React from 'react';
import {Button, Table} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useHistory} from 'react-router-dom';
import DisplayDate from '../../controllers/DisplayDate';

import EmptyText from '../../controllers/EmptyText';
import LoadingIndicator from '../../controllers/LoadingIndicator';
import Paginator from '../../controllers/Paginator';
import {UserAlias} from '../../types/user';


interface UserAliasTableProps {
    data: UserAlias[],
    isFetching: boolean,
    query: any,
    currentIndex: number,
    setCurrentIndex: (value: number) => void,
    currentMaxResults: number,
    setCurrentMaxResults: (value: number) => void,
}

const UserAliasTable: React.VFC<UserAliasTableProps> = (
    {
        data,
        isFetching,
        query,
        currentIndex,
        setCurrentIndex,
        currentMaxResults,
        setCurrentMaxResults
    }) => {

    const {t} = useTranslation();
    const history = useHistory();

    if (isFetching) {
        return <LoadingIndicator/>;
    }
    console.log(data);
    return (
        <div>
            <Table striped bordered hover>
                <thead>
                <tr>
                    <th>{t('alias.aliasName')}</th>
                    <th>{t('user.username')}</th>
                    <th/>
                </tr>
                </thead>
                <tbody>
                {data.length > 0 && data.map((d, index) => {
                    return (
                        <tr key={index}>
                            <td>{d.alias_name}</td>
                            <td>{d.user.username}</td>
                            <td>-</td>
                        </tr>
                    );
                })}
                {data.length <= 0 && <tr>
                    <td colSpan={3}><EmptyText/></td>
                </tr>}
                </tbody>
            </Table>
            <Paginator currentIndex={currentIndex}
                       setCurrentIndex={setCurrentIndex}
                       currentMaxResults={currentMaxResults}
                       setCurrentMaxResults={setCurrentMaxResults}
                       query={query}/>
        </div>
    );
};

export default UserAliasTable;
