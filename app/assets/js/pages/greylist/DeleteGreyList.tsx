import React, {useState} from 'react';
import {useTranslation} from 'react-i18next';
import {useMutation} from 'react-query';

import {useApplication} from '../../application/ApplicationContext';
import DeleteButton from '../../controllers/Buttons/DeleteButton';
import ModalConfirmation from '../../controllers/ModalConfirmation';
import {Greylist} from '../../types/greylist';

interface DeleteGreyListProps {
    onDelete: () => void,
    data: Greylist
}

const DeleteGreyList = ({onDelete, data}: DeleteGreyListProps) => {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();

    const withConfirmation = false;
    const [show, setShow] = useState(false);

    const deleteRecord = useMutation(
        (data: Greylist) => fetch(`${apiUrl}/greylist/delete`, {
            method: 'DELETE',
            body: JSON.stringify({
                'name': data.connect.name,
                'domain': data.connect.domain,
                'source': data.connect.source,
                'rcpt': data.connect.rcpt
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
            {/* Delete with confirmation */}
            {withConfirmation && <><DeleteButton onClick={() => setShow(true)}/>

                <ModalConfirmation
                    show={show}
                    onConfirm={() => deleteRecord.mutateAsync(data)}
                    onCancel={() => setShow(false)}
                    title="greylist.deleteHeader">
                    {t('greylist.deleteMessage')}
                </ModalConfirmation></>}

            {/* Delete without confirmation */}
            {!withConfirmation && <DeleteButton onClick={() => deleteRecord.mutateAsync(data)}/>}
        </>
    );
};

export default DeleteGreyList;
