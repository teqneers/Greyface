import React, {useState} from 'react';
import {Alert, CloseButton, Modal} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useMutation, useQuery, useQueryClient} from 'react-query';
import {useRouteMatch} from 'react-router-dom';
import LoadingIndicator from '../../controllers/LoadingIndicator';

import UserForm, {UpdateUserValues, UpdateUserRequest} from './UserForm';

interface EditUserProps {
    onCancel: () => void,
    onUpdate: (id: string) => void,
}

const EditUser: React.VFC<EditUserProps> = ({onCancel, onUpdate}) => {
    const [error, setError] = useState<string | null>(null);
    const {t} = useTranslation();
    const queryClient = useQueryClient();

    const {url, params: {id}} = useRouteMatch<{ id: string }>();

    const {data, isLoading} = useQuery(['users', id], () => {
        return fetch(`/api/users/${id}`)
            .then((res) => res.json());
    });


    const updateUser = useMutation(async (values: UpdateUserRequest) => {
        return await fetch(`/api/users/${id}`, {
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
        onSuccess: async ({user: id}) => {
            await queryClient.invalidateQueries('users');
            onUpdate(id);
        }
    });

    if (isLoading) {
        return <LoadingIndicator/>;
    }

    return (
        <Modal show={true} onHide={() => onCancel()}>
            <Modal.Header closeButton>
                <Modal.Title>{t('user.editHeader')}</Modal.Title>
            </Modal.Header>
            <Modal.Body>


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
            </Modal.Body>
        </Modal>
    );
};

export default EditUser;
