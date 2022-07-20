import React from 'react';
import {Button, Modal} from 'react-bootstrap';
import {useRouteMatch} from 'react-router-dom';
import {useTranslation} from 'react-i18next';
import {useMutation, useQueryClient} from 'react-query';

interface DeleteUserProps {
    onCancel: (id: string) => void,
    onDelete: () => void,
}

const DeleteUser: React.VFC<DeleteUserProps> = ({onCancel, onDelete}) => {
    const {t} = useTranslation();
    const {params: {id}} = useRouteMatch<{ id: string }>();
    const queryClient = useQueryClient();
    const deleteUser = useMutation(
        () => fetch(`/api/users/${id}`, {
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
        <>
            <Modal show={true} onHide={() => onCancel(id)}>
                <Modal.Header closeButton>
                    <Modal.Title>{t('user.deleteHeader')}</Modal.Title>
                </Modal.Header>
                <Modal.Body>{t('user.deleteMessage')}</Modal.Body>
                <Modal.Footer>
                    <Button variant="secondary" onClick={() => onCancel(id)}>
                        {t('button.cancel')}
                    </Button>
                    <Button variant="primary" onClick={() => deleteUser.mutateAsync()}>
                        {t('button.delete')}
                    </Button>
                </Modal.Footer>
            </Modal>
        </>
    );
};

export default DeleteUser;
