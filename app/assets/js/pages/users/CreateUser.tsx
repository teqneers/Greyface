import React, {useState} from 'react';
import {Alert, CloseButton} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useMutation, useQueryClient} from 'react-query';

import UserForm, {CreateUserRequest, CreateUserValues} from './UserForm';

interface CreateUserProps {
    onCancel: () => void,
    onCreate: (id: string) => void,
}

const CreateUser: React.VFC<CreateUserProps> = ({onCancel, onCreate}) => {
    const [error, setError] = useState<string | null>(null);
    const {t} = useTranslation();
    const queryClient = useQueryClient();
    const createUser = useMutation(async (values: CreateUserRequest) => {
        return await fetch('/api/users', {
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
        <>
            <h4>{t('user.createHeader')}
                <CloseButton onClick={() => onCancel()} className="small"/>
            </h4>

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
        </>
    );
};

export default CreateUser;
