import React, {useState} from 'react';
import {Alert} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useMutation} from 'react-query';

import DefaultButton from '../../../controllers/Buttons/DefaultButton';
import ModalForm from '../../../controllers/ModalForm';
import {AutoWhiteListDomain} from '../../../types/greylist';
import FormDomain , {DomainRequest, DomainValues} from './FormDomain';

interface EditDomainProps {
    onUpdate: () => void,
    data: AutoWhiteListDomain
}

const EditDomain = ({onUpdate, data}: EditDomainProps) => {
    const {t} = useTranslation();

    const [show, setShow] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const editRecord = useMutation(async (values: DomainRequest) => {
        return await fetch('/api/awl/domains/edit', {
            method: 'PUT',
            body: JSON.stringify({
                'dynamicID': {
                    'domain': data.domain,
                    'source': data.source
                },
                'domain': values.domain,
                'source': values.source,
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
            <DefaultButton label="button.edit"  onClick={() => setShow(true)}/>

            <ModalForm
                show={show}
                title="autoWhitelist.domain.editHeader"
                onHide={() => setShow(false)}>

                {error && <Alert key="danger" variant="danger">
                    {error}
                </Alert>}

                <FormDomain<DomainValues, DomainRequest>
                    initialValues={{
                        domain: data.domain,
                        source: data.source
                    }}
                    onSubmit={editRecord}
                    onCancel={() => setShow(false)}
                    submitBtn={t('button.save')}/>
            </ModalForm>
        </>
    );
};

export default EditDomain;
