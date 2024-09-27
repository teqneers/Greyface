import React, {useState} from 'react';
import {Alert} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useRouteMatch} from 'react-router-dom';
import {useMutation, useQueryClient} from 'react-query';

import ModalForm from '../../controllers/ModalForm';
import {useApplication} from '../../application/ApplicationContext';
import SetPasswordForm, {SetPasswordRequest, SetPasswordValues} from './SetPasswordForm';

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

    const {params: {id}} = useRouteMatch<{ id: string }>();

    const setPassword = useMutation(async (values: SetPasswordRequest) => {
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
                error.json().then(body => {
                    setError(body.error);
                });
            });
    }, {
        onSuccess: async () => {
            if (id === user.id) { // if current user changed his password then redirect to login screen
                window.location.href = logoutUrl;
            } else {
                await queryClient.invalidateQueries('users');
                onUpdate(id);
            }
        }
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
