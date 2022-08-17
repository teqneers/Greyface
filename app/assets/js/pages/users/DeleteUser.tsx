import React from 'react';
import {useRouteMatch} from 'react-router-dom';
import {useTranslation} from 'react-i18next';
import {useMutation, useQueryClient} from 'react-query';

import {useApplication} from '../../application/ApplicationContext';
import ModalConfirmation from '../../controllers/ModalConfirmation';

interface DeleteUserProps {
    onCancel: (id: string) => void,
    onDelete: () => void,
}

const DeleteUser: React.VFC<DeleteUserProps> = ({onCancel, onDelete}) => {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const {params: {id}} = useRouteMatch<{ id: string }>();
    const queryClient = useQueryClient();
    const deleteUser = useMutation(
        () => fetch(`${apiUrl}/users/${id}`, {
            method: 'DELETE'
        }).then(async response => {
            const data = await response.json();

            // check for error response
            if (!response.ok) {
                // get error message from body or default to response status
                const error = (data && data.message) || response.status;
                return Promise.reject(error);
            }
            onDelete();
            queryClient.removeQueries(['users', id]);
            await queryClient.invalidateQueries('users');
        }).catch(error => {
            console.error('There was an error!', error);
        }));

    return (
        <ModalConfirmation
            onConfirm={() => deleteUser.mutateAsync()}
            onCancel={() => onCancel(id)}
            title="user.deleteHeader">
            {t('user.deleteMessage')}
        </ModalConfirmation>
    );
};

export default DeleteUser;
