import React from 'react';
import {Col, Form, Modal, Row} from 'react-bootstrap';
import {UseMutationResult} from 'react-query';
import * as yup from 'yup';
import {useTranslation} from 'react-i18next';
import {Formik} from 'formik';

import CancelButton from '../../../controllers/Buttons/CancelButton';
import SubmitButton from '../../../controllers/Buttons/SubmitButton';

export interface EmailValues {
    name: string,
    domain: string
    source: string
}

export interface EmailRequest {
    name: string,
    domain: string
    source: string
}

const Schema: yup.ObjectSchema<EmailValues> = yup.object()
    .noUnknown()
    .shape(
        {
            name: yup.string()
                .required()
                .max(128),
            domain: yup.string()
                .required()
                .min(1)
                .max(128),
            source: yup.string()
                .required()
                .max(128),
        }
    );

interface FormEmailProps<TValues extends object, TData, TRes, TError> {
    submitBtn?: string | null,
    onCancel?: () => void,
    initialValues: TValues,
    validationSchema?: any | (() => any),
    onSubmit: UseMutationResult<TRes, TError, TData>,
}

function FormEmail<TValues extends EmailValues, TData extends EmailRequest>(
    {
        onSubmit,
        submitBtn,
        onCancel,
        ...rest
    }: FormEmailProps<TValues, TData, any, any>
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

                            <Form.Group as={Col} md="12" controlId="validationFormik00">
                                <Form.Label>{t('autoWhitelist.email.name')}</Form.Label>
                                <Form.Control
                                    type="text"
                                    name="name"
                                    value={values.name}
                                    onChange={handleChange}
                                    isInvalid={!!errors.name}/>

                                <Form.Control.Feedback type="invalid">
                                    {/*// @ts-ignore*/}
                                    {errors.name}
                                </Form.Control.Feedback>
                            </Form.Group>

                            <Form.Group as={Col} md="12" controlId="validationFormik01">
                                <Form.Label>{t('autoWhitelist.email.domain')}</Form.Label>
                                <Form.Control
                                    type="text"
                                    name="domain"
                                    value={values.domain}
                                    onChange={handleChange}
                                    isInvalid={!!errors.domain}/>

                                <Form.Control.Feedback type="invalid">
                                    {/*// @ts-ignore*/}
                                    {errors.domain}
                                </Form.Control.Feedback>
                            </Form.Group>

                            <Form.Group as={Col} md="12" controlId="validationFormik02">
                                <Form.Label>{t('autoWhitelist.email.source')}</Form.Label>
                                <Form.Control
                                    type="text"
                                    name="source"
                                    value={values.source}
                                    onChange={handleChange}
                                    isInvalid={!!errors.source}/>
                                <Form.Control.Feedback type="invalid">
                                    {/*// @ts-ignore*/}
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

export default FormEmail;
