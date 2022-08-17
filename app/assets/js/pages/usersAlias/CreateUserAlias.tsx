import React, {useState} from 'react';
import {Alert} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useMutation} from 'react-query';

import {useApplication} from '../../application/ApplicationContext';
import ModalForm from '../../controllers/ModalForm';
import UserAliasForm, {UserAliasRequest, UserAliasValues} from './UserAliasForm';

interface CreateUserAliasProps {
    onCancel: () => void,
    onCreate: () => void,
}

const CreateUserAlias: React.VFC<CreateUserAliasProps> = ({onCancel, onCreate}) => {
    const {user} = useApplication();
    const {apiUrl} = useApplication();
    const [error, setError] = useState<string | null>(null);
    const {t} = useTranslation();
    const createUserAlias = useMutation(async (values: UserAliasRequest) => {
        return await fetch(`${apiUrl}/users-aliases`, {
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
            title="alias.createHeader"
            onHide={() => onCancel()}>

            {error && <Alert key="danger" variant="danger">
                {error}
            </Alert>}

            <UserAliasForm<UserAliasValues, UserAliasRequest>
                initialValues={{
                    user_id: user.id,
                    alias_name: []
                }}
                onSubmit={createUserAlias}
                onCancel={onCancel}
                submitBtn={t('button.save')}/>
        </ModalForm>
    );
};

export default CreateUserAlias;
