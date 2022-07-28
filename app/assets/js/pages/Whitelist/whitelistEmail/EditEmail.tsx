import React, {useState} from 'react';
import {Alert, Button} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useMutation} from 'react-query';

import ModalForm from '../../../controllers/ModalForm';
import {WhiteListEmail} from '../../../types/greylist';
import FormEmail, {EmailRequest, EmailValues} from './FormEmail';

interface EditEmailProps {
    onUpdate: () => void,
    data: WhiteListEmail
}

const EditEmail = ({onUpdate, data}: EditEmailProps) => {
    const {t} = useTranslation();

    const [show, setShow] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const editRecord = useMutation(async (values: EmailRequest) => {
        return await fetch('/api/opt-in/emails/edit', {
            method: 'PUT',
            body: JSON.stringify({
                'dynamicID': {
                    'email': data.email
                },
                'email': values.email
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
            <Button variant="outline-primary" className="m-1" onClick={() => setShow(true)}>
                {t('button.edit')}
            </Button>
            <ModalForm
                show={show}
                title="whitelist.email.editHeader"
                onHide={() => setShow(false)}>

                {error && <Alert key="danger" variant="danger">
                    {error}
                </Alert>}

                <FormEmail<EmailValues, EmailRequest>
                    initialValues={{
                        email: [data.email]
                    }}
                    onSubmit={editRecord}
                    createMode={false}
                    onCancel={() => setShow(false)}
                    submitBtn={t('button.save')}/>
            </ModalForm>
        </>
    );
};

export default EditEmail;
