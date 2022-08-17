import React, {useState} from 'react';
import {Alert} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useMutation, useQuery, useQueryClient} from 'react-query';
import {useRouteMatch} from 'react-router-dom';

import {useApplication} from '../../application/ApplicationContext';
import LoadingIndicator from '../../controllers/LoadingIndicator';
import ModalForm from '../../controllers/ModalForm';
import UserAliasForm, {UserAliasRequest, UserAliasValues} from './UserAliasForm';

interface EditUserAliasProps {
    onCancel: () => void,
    onUpdate: () => void,
}

const EditUserAlias: React.VFC<EditUserAliasProps> = ({onCancel, onUpdate}) => {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const queryClient = useQueryClient();

    const [error, setError] = useState<string | null>(null);

    const {params: {id}} = useRouteMatch<{ id: string }>();

    const {data, isLoading} = useQuery(['users', id], () => {
        return fetch(`${apiUrl}/users-aliases/${id}`)
            .then((res) => res.json());
    });


    const updateUser = useMutation(async (values: UserAliasRequest) => {
        return await fetch(`${apiUrl}/users-aliases/${id}`, {
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
            await queryClient.invalidateQueries('users-aliases');
            onUpdate();
        }
    });

    if (isLoading) {
        return <LoadingIndicator/>;
    }

    return (
        <ModalForm onHide={() => onCancel()} title="alias.editHeader">

            {error && <Alert key="danger" variant="danger">
                {error}
            </Alert>}

            <UserAliasForm<UserAliasValues, UserAliasRequest>
                initialValues={{
                    user_id: !data.user.is_deleted ? data.user.id : '',
                    alias_name: [data.alias_name]
                }}
                onSubmit={updateUser}
                onCancel={onCancel}
                createMode={false}
                submitBtn={t('button.save')}/>
        </ModalForm>
    );
};

export default EditUserAlias;
