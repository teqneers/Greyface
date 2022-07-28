import React, {useState} from 'react';
import {Alert} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useMutation, useQueryClient} from 'react-query';

import ModalForm from '../../../controllers/ModalForm';
import FormEmail, {EmailRequest, EmailValues} from './FormEmail';

interface AddEmailProps {
    onCancel: () => void,
    onCreate: (id: string) => void,
}

const AddEmail: React.VFC<AddEmailProps> = ({onCancel, onCreate}) => {
    const [error, setError] = useState<string | null>(null);
    const {t} = useTranslation();
    const queryClient = useQueryClient();
    const createEmails = useMutation(async (values: EmailRequest) => {
        return await fetch('/api/opt-in/emails', {
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
        onSuccess: async ({user: id}) => {
            await queryClient.invalidateQueries('users');
            onCreate(id);
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
