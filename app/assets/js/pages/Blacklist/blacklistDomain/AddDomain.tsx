import React, {useState} from 'react';
import {Alert} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useMutation} from 'react-query';

import {useApplication} from '../../../application/ApplicationContext';
import ModalForm from '../../../controllers/ModalForm';
import FormDomain , {DomainRequest, DomainValues} from './FormDomain';

interface AddDomainProps {
    onCancel: () => void,
    onCreate: () => void,
}

const AddDomain: React.VFC<AddDomainProps> = ({onCancel, onCreate}) => {
    const [error, setError] = useState<string | null>(null);
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const createEmails = useMutation(async (values: DomainRequest) => {
        return await fetch(`${apiUrl}/opt-out/domains`, {
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
            title="blacklist.domain.addHeader"
            onHide={() => onCancel()}>

            {error && <Alert key="danger" variant="danger">
                {error}
            </Alert>}

            <FormDomain<DomainValues, DomainRequest>
                initialValues={{
                    domain: []
                }}
                onSubmit={createEmails}
                onCancel={onCancel}
                submitBtn={t('button.save')}/>
        </ModalForm>
    );
};

export default AddDomain;
