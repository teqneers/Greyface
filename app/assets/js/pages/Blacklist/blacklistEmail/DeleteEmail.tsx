import React, {useState} from 'react';
import {useTranslation} from 'react-i18next';
import {useMutation} from 'react-query';

import {useApplication} from '../../../application/ApplicationContext';
import DeleteButton from '../../../controllers/Buttons/DeleteButton';
import ModalConfirmation from '../../../controllers/ModalConfirmation';
import {BlackListEmail} from '../../../types/greylist';

interface DeleteEmailProps {
    onDelete: () => void,
    data: BlackListEmail
}

const DeleteEmail = ({onDelete, data}: DeleteEmailProps) => {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();

    const [show, setShow] = useState(false);

    const deleteRecord = useMutation(
        (data: BlackListEmail) => fetch(`${apiUrl}/opt-out/emails/delete`, {
            method: 'DELETE',
            body: JSON.stringify({
                'email': data.email
            })
        }).then(async response => {
            const data = await response.json();

            // check for error response
            if (!response.ok) {
                // get error message from body or default to response status
                const error = (data && data.message) || response.status;
                return Promise.reject(error);
            }
            onDelete();
        }).catch(error => {
            console.error('There was an error!', error);
        }));

    return (
        <>
            <DeleteButton onClick={() => setShow(true)}/>

            <ModalConfirmation
                show={show}
                onConfirm={() => deleteRecord.mutateAsync(data)}
                onCancel={() => setShow(false)}
                title="blacklist.email.deleteHeader">
                {t('blacklist.email.deleteMessage')}
            </ModalConfirmation>
        </>
    );
};

export default DeleteEmail;
