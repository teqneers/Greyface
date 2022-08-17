import React, {useState} from 'react';
import {Alert} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useMutation, useQueryClient} from 'react-query';

import {useApplication} from '../../application/ApplicationContext';
import ModalForm from '../../controllers/ModalForm';
import UserForm, {CreateUserRequest, CreateUserValues} from './UserForm';

interface CreateUserProps {
    onCancel: () => void,
    onCreate: (id: string) => void,
}

const CreateUser: React.VFC<CreateUserProps> = ({onCancel, onCreate}) => {
    const [error, setError] = useState<string | null>(null);
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const queryClient = useQueryClient();
    const createUser = useMutation(async (values: CreateUserRequest) => {
        return await fetch(`${apiUrl}/users`, {
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
            title="user.createHeader"
            onHide={() => onCancel()}>

                {error && <Alert key="danger" variant="danger">
                    {error}
                </Alert>}

                <UserForm<CreateUserValues, CreateUserRequest>
                    initialValues={{
                        username: '',
                        email: '',
                        role: 'user',
                        password: ''
                    }}
                    onSubmit={createUser}
                    onCancel={onCancel}
                    createUser={true}
                    submitBtn={t('button.save')}/>
        </ModalForm>
    );
};

export default CreateUser;
