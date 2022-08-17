import React, {useState} from 'react';
import {useTranslation} from 'react-i18next';
import {useMutation} from 'react-query';

import {useApplication} from '../../../application/ApplicationContext';
import DeleteButton from '../../../controllers/Buttons/DeleteButton';
import ModalConfirmation from '../../../controllers/ModalConfirmation';
import {BlackListDomain} from '../../../types/greylist';

interface DeleteDomainProps {
    onDelete: () => void,
    data: BlackListDomain
}

const DeleteDomain = ({onDelete, data}: DeleteDomainProps) => {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();

    const [show, setShow] = useState(false);

    const deleteRecord = useMutation(
        (data: BlackListDomain) => fetch(`${apiUrl}/opt-out/domains/delete`, {
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
            <DeleteButton onClick={() => setShow(true)}/>

            <ModalConfirmation
                show={show}
                onConfirm={() => deleteRecord.mutateAsync(data)}
                onCancel={() => setShow(false)}
                title="blacklist.domain.deleteHeader">
                {t('blacklist.domain.deleteMessage')}
            </ModalConfirmation>
        </>
    );
};

export default DeleteDomain;
