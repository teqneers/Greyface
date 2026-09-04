import React, {useState} from 'react';
import {Alert} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useParams} from 'react-router-dom';
import {useMutation, useQueryClient} from '@tanstack/react-query';

import ModalForm from '../../controllers/ModalForm';
import {useApplication} from '../../application/ApplicationContext';
import SetPasswordForm from './SetPasswordForm';
import type {SetPasswordRequest, SetPasswordValues} from './SetPasswordForm';

interface SetPasswordProps {
    onCancel: () => void,
    onUpdate: (id: string) => void,
}

const SetPassword: React.FC<SetPasswordProps> = ({onCancel, onUpdate}) => {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const {user, logoutUrl} = useApplication();
    const queryClient = useQueryClient();

    const [error, setError] = useState<string | null>(null);

    const {id} = useParams<{ id: string }>();

    const setPassword = useMutation({
        mutationFn: async (values: SetPasswordRequest) => {
        return await fetch(`${apiUrl}/users/${id}/password`, {
            method: 'PUT',
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
                error.json().then((body: { error?: string }) => {
                    setError(body.error ?? null);
                });
            });
    },
        onSuccess: async () => {
            if (id === user.id) { // if current user changed his password then redirect to login screen
                window.location.href = logoutUrl;
            } else {
                await queryClient.invalidateQueries({queryKey: ['users']});
                onUpdate(id ?? '');
            }
        },
    });

    return (
        <ModalForm onHide={() => onCancel()} title="user.setPassword">

            {error && <Alert key="danger" variant="danger">
                {error}
            </Alert>}

            <SetPasswordForm<SetPasswordValues, SetPasswordRequest>
                initialValues={{
                    password: '',
                    passwordConfirmation: ''
                }}
                onSubmit={setPassword}
                onCancel={onCancel}
                submitBtn={t('button.save')}/>
        </ModalForm>
    );
};

export default SetPassword;
