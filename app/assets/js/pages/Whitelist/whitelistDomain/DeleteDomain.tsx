import React, {useState} from 'react';
import {Button} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useMutation} from 'react-query';

import ModalConfirmation from '../../../controllers/ModalConfirmation';
import {WhiteListDomain} from '../../../types/greylist';

interface DeleteDomainProps {
    onDelete: () => void,
    data: WhiteListDomain
}

const DeleteDomain = ({onDelete, data}: DeleteDomainProps) => {
    const {t} = useTranslation();

    const [show, setShow] = useState(false);

    const deleteRecord = useMutation(
        (data: WhiteListDomain) => fetch('/api/opt-in/domains/delete', {
            method: 'DELETE',
            body: JSON.stringify({
                'domain': data.domain
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
                title="whitelist.domain.deleteHeader">
                {t('whitelist.domain.deleteMessage')}
            </ModalConfirmation>
        </>
    );
};

export default DeleteDomain;
