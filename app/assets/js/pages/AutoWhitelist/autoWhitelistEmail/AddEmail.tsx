import React, {useState} from 'react';
import {Alert} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useMutation} from 'react-query';

import {useApplication} from '../../../application/ApplicationContext';
import ModalForm from '../../../controllers/ModalForm';
import FormEmail, {EmailRequest, EmailValues} from './FormEmail';

interface AddEmailProps {
    onCancel: () => void,
    onCreate: () => void,
}

const AddEmail: React.VFC<AddEmailProps> = ({onCancel, onCreate}) => {
    const [error, setError] = useState<string | null>(null);
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const createEmails = useMutation(async (values: EmailRequest) => {
        return await fetch(`${apiUrl}/awl/emails`, {
            method: 'POST',
            body: JSON.stringify(values)
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
            onCreate();
        }
    });

    return (
        <ModalForm
            title="autoWhitelist.email.addHeader"
            onHide={() => onCancel()}>

            {error && <Alert key="danger" variant="danger">
                {error}
            </Alert>}

            <FormEmail<EmailValues, EmailRequest>
                initialValues={{
                    name: '',
                    domain: '',
                    source: ''
                }}
                onSubmit={createEmails}
                onCancel={onCancel}
                submitBtn={t('button.save')}/>
        </ModalForm>
    );
};

export default AddEmail;
