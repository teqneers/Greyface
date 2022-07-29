import React, {useState} from 'react';
import {Alert} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useMutation} from 'react-query';

import DefaultButton from '../../../controllers/Buttons/DefaultButton';
import ModalForm from '../../../controllers/ModalForm';
import {AutoWhiteListEmail} from '../../../types/greylist';
import FormEmail, {EmailRequest, EmailValues} from './FormEmail';

interface EditEmailProps {
    onUpdate: () => void,
    data: AutoWhiteListEmail
}

const EditEmail = ({onUpdate, data}: EditEmailProps) => {
    const {t} = useTranslation();

    const [show, setShow] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const editRecord = useMutation(async (values: EmailRequest) => {
        return await fetch('/api/awl/emails/edit', {
            method: 'PUT',
            body: JSON.stringify({
                'dynamicID': {
                    'name': data.name,
                    'domain': data.domain,
                    'source': data.source
                },
                'name': values.name,
                'domain': values.domain,
                'source': values.source,
            })
        }).then(function (response) {
            if (!response.ok) {
                throw response;
            }
            setError(null);
            return response;
        })
            .then((res) => res.json())
            .catch(error => {
                error.json().then(body => {
                    setError(body.error);
                });
            });
    }, {
        onSuccess: async () => {
            onUpdate();
        }
    });

    return (
        <>
            <DefaultButton label="button.edit" onClick={() => setShow(true)}/>

            <ModalForm
                show={show}
                title="autoWhitelist.email.editHeader"
                onHide={() => setShow(false)}>

                {error && <Alert key="danger" variant="danger">
                    {error}
                </Alert>}

                <FormEmail<EmailValues, EmailRequest>
                    initialValues={{
                        name: data.name,
                        domain: data.domain,
                        source: data.source
                    }}
                    onSubmit={editRecord}
                    onCancel={() => setShow(false)}
                    submitBtn={t('button.save')}/>
            </ModalForm>
        </>
    );
};

export default EditEmail;
