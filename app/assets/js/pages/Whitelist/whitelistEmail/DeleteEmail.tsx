import React, {useState} from 'react';
import {Button} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useMutation} from 'react-query';

import ModalConfirmation from '../../../controllers/ModalConfirmation';
import {WhiteListEmail} from '../../../types/greylist';

interface DeleteEmailProps {
    onDelete: () => void,
    data: WhiteListEmail
}

const DeleteEmail = ({onDelete, data}: DeleteEmailProps) => {
    const {t} = useTranslation();

    const [show, setShow] = useState(false);

    const deleteRecord = useMutation(
        (data: WhiteListEmail) => fetch('/api/opt-in/emails/delete', {
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
            <Button variant="outline-danger" onClick={() => setShow(true)}>
                {t('button.delete')}
            </Button>

            <ModalConfirmation
                show={show}
                onConfirm={() => deleteRecord.mutateAsync(data)}
                onCancel={() => setShow(false)}
                title="whitelist.email.deleteHeader">
                {t('whitelist.email.deleteMessage')}
            </ModalConfirmation>
        </>
    );
};

export default DeleteEmail;
