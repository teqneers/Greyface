import React from 'react';
import {useParams} from 'react-router-dom';
import {useTranslation} from 'react-i18next';
import {useMutation, useQueryClient} from '@tanstack/react-query';

import {useApplication} from '../../application/ApplicationContext';
import ModalConfirmation from '../../controllers/ModalConfirmation';

interface DeleteUserAliasProps {
    onCancel: (id: string) => void,
    onDelete: () => void,
}

const DeleteUserAlias: React.FC<DeleteUserAliasProps> = ({onCancel, onDelete}) => {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const {id} = useParams<{ id: string }>();
    const queryClient = useQueryClient();
    const deleteUserAlias = useMutation({
        mutationFn: () => fetch(`${apiUrl}/users-aliases/${id}`, {
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
            queryClient.removeQueries(['users-aliases', id]);
            await queryClient.invalidateQueries({queryKey: ['users-aliases']});
        }).catch(error => {
            console.error('There was an error!', error);
        }),
    });

    return (
        <ModalConfirmation
            onConfirm={() => deleteUserAlias.mutateAsync()}
            onCancel={() => onCancel(id)}
            title="alias.deleteHeader">
            {t('alias.deleteMessage')}
        </ModalConfirmation>
    );
};

export default DeleteUserAlias;
