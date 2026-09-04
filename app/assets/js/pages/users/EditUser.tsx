import React, {useState} from 'react';
import {Alert} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useMutation, useQuery, useQueryClient} from '@tanstack/react-query';
import {useParams} from 'react-router-dom';

import {useApplication} from '../../application/ApplicationContext';
import LoadingIndicator from '../../controllers/LoadingIndicator';
import ModalForm from '../../controllers/ModalForm';

import UserForm from './UserForm';
import type {UpdateUserValues, UpdateUserRequest} from './UserForm';

interface EditUserProps {
    onCancel: () => void,
    onUpdate: (id: string) => void,
}

const EditUser: React.FC<EditUserProps> = ({onCancel, onUpdate}) => {
    const [error, setError] = useState<string | null>(null);
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const queryClient = useQueryClient();

    const {id} = useParams<{ id: string }>();

    const {data, isLoading} = useQuery({
        queryKey: ['users', id],
        queryFn: () => {
        return fetch(`${apiUrl}/users/${id}`)
            .then((res) => res.json());
    },
    });


    const updateUser = useMutation({
        mutationFn: async (values: UpdateUserRequest) => {
        return await fetch(`${apiUrl}/users/${id}`, {
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
    },
        onSuccess: async ({user: id}) => {
            await queryClient.invalidateQueries({queryKey: ['users']});
            onUpdate(id);
        },
    });

    if (isLoading) {
        return <LoadingIndicator/>;
    }

    return (
        <ModalForm onHide={() => onCancel()} title="user.editHeader">

            {error && <Alert key="danger" variant="danger">
                {error}
            </Alert>}

            <UserForm<UpdateUserValues, UpdateUserRequest>
                initialValues={{
                    username: data.username,
                    email: data.email,
                    role: data.role
                }}
                onSubmit={updateUser}
                onCancel={onCancel}
                createUser={false}
                submitBtn={t('button.save')}/>
        </ModalForm>
    );
};

export default EditUser;
