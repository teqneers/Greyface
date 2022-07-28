import React, {useState} from 'react';
import {Button} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useMutation} from 'react-query';

import ModalConfirmation from '../../controllers/ModalConfirmation';
import {Greylist} from '../../types/greylist';

interface MoveToWhiteListProps {
    onMove: () => void,
    data: Greylist
}

const MoveToWhiteList = ({onMove, data}: MoveToWhiteListProps) => {
    const {t} = useTranslation();

    const [show, setShow] = useState(false);

    const moveRecord = useMutation(
        (data: Greylist) => fetch('/api/greylist/toWhiteList', {
            method: 'POST',
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
            onMove();
        }).catch(error => {
            console.error('There was an error!', error);
        }));

    return (
        <>
            <Button variant="outline-primary" className="m-1" onClick={() => setShow(true)}>
                {t('button.moveToWhitelist')}
            </Button>

            <ModalConfirmation
                show={show}
                confirmBtnVariant="outline-primary"
                confirmBtn="button.moveToWhitelist"
                onConfirm={() => moveRecord.mutateAsync(data)}
                onCancel={() => setShow(false)}
                title="greylist.moveToWhitelistHeader">
                {t('greylist.moveToWhitelistMessage')}
            </ModalConfirmation>
        </>
    );
};

export default MoveToWhiteList;
