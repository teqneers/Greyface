import React from 'react';
import {Table} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import EmptyText from '../../controllers/EmptyText';
import LoadingIndicator from '../../controllers/LoadingIndicator';
import Paginator from '../../controllers/Paginator';
import {User} from '../../types/user';


interface UsersTableProps {
    data: User[],
    isFetching: boolean,
    selectedItemId?: string
    onItemClick?: (item: User) => void,
    query: any,
    currentIndex: number,
    setCurrentIndex: (value: number) => void,
    currentMaxResults: number,
    setCurrentMaxResults: (value: number) => void,
}

const UsersTable: React.VFC<UsersTableProps> = (
    {
        data,
        isFetching,
        selectedItemId,
        onItemClick,
        query,
        currentIndex,
        setCurrentIndex,
        currentMaxResults,
        setCurrentMaxResults
    }) => {

    const {t} = useTranslation();

    if (isFetching) {
        return <LoadingIndicator/>;
    }

    return (
        <div>
            <Table striped bordered hover>
                <thead>
                <tr>
                    <th>{t('user.username')}</th>
                    <th>{t('user.email')}</th>
                    <th>{t('user.role')}</th>
                </tr>
                </thead>
                <tbody>
                {data.map(user => (
                    <tr key={user.id}
                        onClick={() => onItemClick ? onItemClick(user) : null}
                        className={`clickable${selectedItemId === user.id ? ' selected' : ''}`}>
                        <td>{user.username}</td>
                        <td>{user.email}</td>
                        <td>{t(`user.roles.${user.role}`)}</td>
                    </tr>
                ))}
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

export default UsersTable;
