import React, {useState} from 'react';
import {Alert, Button} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useMutation} from 'react-query';

import ModalForm from '../../../controllers/ModalForm';
import {WhiteListDomain} from '../../../types/greylist';
import FormDomain , {DomainRequest, DomainValues} from './FormDomain';

interface EditDomainProps {
    onUpdate: () => void,
    data: WhiteListDomain
}

const EditDomain = ({onUpdate, data}: EditDomainProps) => {
    const {t} = useTranslation();

    const [show, setShow] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const editRecord = useMutation(async (values: DomainRequest) => {
        return await fetch('/api/opt-in/domains/edit', {
            method: 'PUT',
            body: JSON.stringify({
                'dynamicID': {
                    'domain': data.domain
                },
                'domain': values.domain
            })
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
            onUpdate();
        }
    });

    return (
        <>
            <Button variant="outline-primary" className="m-1" onClick={() => setShow(true)}>
                {t('button.edit')}
            </Button>
            <ModalForm
                show={show}
                title="whitelist.domain.editHeader"
                onHide={() => setShow(false)}>

                {error && <Alert key="danger" variant="danger">
                    {error}
                </Alert>}

                <FormDomain<DomainValues, DomainRequest>
                    initialValues={{
                        domain: [data.domain]
                    }}
                    onSubmit={editRecord}
                    createMode={false}
                    onCancel={() => setShow(false)}
                    submitBtn={t('button.save')}/>
            </ModalForm>
        </>
    );
};

export default EditDomain;
