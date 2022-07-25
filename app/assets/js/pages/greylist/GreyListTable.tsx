import React from 'react';
import {Button, Table} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useHistory} from 'react-router-dom';
import DisplayDate from '../../controllers/DisplayDate';

import EmptyText from '../../controllers/EmptyText';
import LoadingIndicator from '../../controllers/LoadingIndicator';
import Paginator from '../../controllers/Paginator';
import {DATE_TIME_SECONDS_FORMAT} from '../../types/common';
import {Greylist} from '../../types/greylist';


interface GreyListTableProps {
    data: Greylist[],
    isFetching: boolean,
    query: any,
    currentIndex: number,
    setCurrentIndex: (value: number) => void,
    currentMaxResults: number,
    setCurrentMaxResults: (value: number) => void,
}

const GreyListTable: React.VFC<GreyListTableProps> = (
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
                    <th>{t('greylist.sender')}</th>
                    <th>{t('greylist.domain')}</th>
                    <th>{t('greylist.source')}</th>
                    <th>{t('greylist.recipient')}</th>
                    <th>{t('greylist.firstSeen')}</th>
                    <th>{t('greylist.username')}</th>
                    <th/>
                </tr>
                </thead>
                <tbody>
                {data.length > 0 && data.map((d, index) => {
                   console.log(d);
                    return (
                        <tr key={index}>
                            <td>{d.connect.name}</td>
                            <td>{d.connect.domain}</td>
                            <td>{d.connect.source}</td>
                            <td>{d.connect.rcpt}</td>
                            <td> <DisplayDate date={d.connect.firstSeen} format={DATE_TIME_SECONDS_FORMAT}/></td>
                            <td>{d.username}</td>
                            <td>-</td>
                        </tr>
                    );
                })}
                {data.length <= 0 && <tr>
                    <td><EmptyText/></td>
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

export default GreyListTable;
