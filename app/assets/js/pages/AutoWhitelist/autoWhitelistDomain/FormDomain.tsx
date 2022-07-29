import React from 'react';
import {Col, Form, Modal, Row} from 'react-bootstrap';
import {UseMutationResult} from 'react-query';
import * as yup from 'yup';
import {useTranslation} from 'react-i18next';
import {Formik} from 'formik';

import CancelButton from '../../../controllers/Buttons/CancelButton';
import SubmitButton from '../../../controllers/Buttons/SubmitButton';

export interface DomainValues {
    domain: string,
    source: string
}

export interface DomainRequest {
    domain: string,
    source: string
}

const Schema: yup.SchemaOf<DomainValues> = yup.object()
    .noUnknown()
    .shape(
        {
            domain: yup.string()
                .required()
                .min(1)
                .max(128),
            source: yup.string()
                .required()
                .max(128),
        }
    );

interface FormDomainProps<TValues extends object, TData, TRes, TError> {
    submitBtn?: string | null,
    onCancel?: () => void,
    initialValues: TValues,
    validationSchema?: yup.SchemaOf<any>,
    onSubmit: UseMutationResult<TRes, TError, TData>,
}

function FormDomain<TValues extends DomainValues, TData extends DomainRequest>(
    {
        onSubmit,
        submitBtn,
        onCancel,
        ...rest
    }: FormDomainProps<TValues, TData, any, any>
): React.ReactElement {
    const {t} = useTranslation();

    return (
        <Formik
            validateOnBlur={true}
            validationSchema={Schema}
            onSubmit={((values) => {
                // @ts-ignore
                onSubmit.mutate(values);
            })}
            {...rest}>
            {({
                  handleSubmit,
                  handleChange,
                  values,
                  errors,
                  isSubmitting
              }) => (

                <Form noValidate onSubmit={handleSubmit}>
                    <Modal.Body>
                        <Row className="mb-3">
                            <Form.Group as={Col} md="12" controlId="validationFormik01">
                                <Form.Label>{t('autoWhitelist.domain.domain')}</Form.Label>
                                <Form.Control
                                    type="text"
                                    name="domain"
                                    value={values.domain}
                                    onChange={handleChange}
                                    isInvalid={!!errors.domain}/>

                                <Form.Control.Feedback type="invalid">
                                    {errors.domain}
                                </Form.Control.Feedback>
                            </Form.Group>

                            <Form.Group as={Col} md="12" controlId="validationFormik02">
                                <Form.Label>{t('autoWhitelist.domain.source')}</Form.Label>
                                <Form.Control
                                    type="text"
                                    name="source"
                                    value={values.source}
                                    onChange={handleChange}
                                    isInvalid={!!errors.source}/>
                                <Form.Control.Feedback type="invalid">
                                    {errors.source}
                                </Form.Control.Feedback>
                            </Form.Group>
                        </Row>
                    </Modal.Body>
                    <Modal.Footer>
                        <CancelButton onClick={() => onCancel()}/>

                        <SubmitButton label={submitBtn}
                                      disabled={isSubmitting && !onSubmit.isError}/>
                    </Modal.Footer>
                </Form>
            )}
        </Formik>
    );
}

FormDomain.defaultProps = {
};

export default FormDomain;
