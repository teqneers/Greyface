import React, {useState} from 'react';
import {Alert} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useMutation} from 'react-query';

import {useApplication} from '../../../application/ApplicationContext';
import ModalForm from '../../../controllers/ModalForm';
import FormEmail from './FormEmail';
import {EmailRequest, EmailValues} from '../../../utils/yupSchema';

interface AddEmailProps {
    onCancel: () => void,
    onCreate: () => void,
}

const AddEmail: React.FC<AddEmailProps> = ({onCancel, onCreate}) => {
    const [error, setError] = useState<string | null>(null);
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const createEmails = useMutation(async (values: EmailRequest) => {
        return await fetch(`${apiUrl}/opt-out/emails`, {
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
            title="whitelist.email.addHeader"
            onHide={() => onCancel()}>

            {error && <Alert key="danger" variant="danger">
                {error}
            </Alert>}

            <FormEmail<EmailValues, EmailRequest>
                initialValues={{
                    email: []
                }}
                onSubmit={createEmails}
                onCancel={onCancel}
                submitBtn={t('button.save')}/>
        </ModalForm>
    );
};

export default AddEmail;
