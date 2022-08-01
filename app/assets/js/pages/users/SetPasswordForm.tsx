import {TFunction} from 'i18next';
import React from 'react';
import {UseMutationResult} from 'react-query';
import {useTranslation} from 'react-i18next';
import {Button, Col, Form, Modal, Row} from 'react-bootstrap';
import {Formik} from 'formik';
import * as yup from 'yup';

import CancelButton from '../../controllers/Buttons/CancelButton';

export interface SetPasswordValues {
    password: string,
    passwordConfirmation: string
}

export interface SetPasswordRequest {
    password: string
}

function Schema(t: TFunction): yup.AnySchema {
    return yup.object()
        .noUnknown()
        .shape(
            {
                password: yup.string()
                    .required()
                    .min(5, t('errors.min', {min: 5}))
                    .max(128),
                passwordConfirmation: yup.string()
                    .required()
                    .when("password", {
                        is: password => (password && password.length > 0),
                        then: yup.string().oneOf([yup.ref("password")], "Password doesn't match")
                    })
            }
        );
}


interface UserFromProps<TValues extends object, TData, TRes, TError> {
    submitBtn?: string | null,
    onCancel?: () => void,
    initialValues: TValues,
    validationSchema?: yup.SchemaOf<any>,
    onSubmit: UseMutationResult<TRes, TError, TData>,
}

function SetPasswordForm<TValues extends SetPasswordValues, TData extends SetPasswordRequest>(
    {
        onSubmit,
        submitBtn,
        onCancel,
        ...rest
    }: UserFromProps<TValues, TData, any, any>
): React.ReactElement {
    const {t} = useTranslation();
    return (
        <Formik
            validationSchema={Schema(t)}
            onSubmit={((values, {setFieldError}) => {
                const submitData = {password: values.password.trim()};
                if (submitData.password.length > 0) {
                    // @ts-ignore
                    onSubmit.mutate(submitData);
                } else {
                    setFieldError('password', t('errors.required'));
                }
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
                            <Form.Group as={Col} md="12" controlId="validationFormik04">
                                <Form.Label>{t('user.password')}</Form.Label>

                                <Form.Control
                                    type="text"
                                    name="password"
                                    value={values.password}
                                    onChange={handleChange}
                                    isInvalid={!!errors.password}/>

                                <Form.Control.Feedback type="invalid">
                                    {errors.password}
                                </Form.Control.Feedback>
                            </Form.Group>

                            <Form.Group as={Col} md="12" controlId="validationFormik04">
                                <Form.Label>{t('user.passwordRetype')}</Form.Label>

                                <Form.Control
                                    type="text"
                                    name="passwordConfirmation"
                                    value={values.passwordConfirmation}
                                    onChange={handleChange}
                                    isInvalid={!!errors.passwordConfirmation}/>

                                <Form.Control.Feedback type="invalid">
                                    {errors.passwordConfirmation}
                                </Form.Control.Feedback>
                            </Form.Group>

                        </Row>
                    </Modal.Body>
                    <Modal.Footer>
                        <CancelButton onClick={() => onCancel()}/>

                        <Button variant="outline-primary" type="submit"
                                disabled={isSubmitting && !onSubmit.isError}>{submitBtn}</Button>
                    </Modal.Footer>
                </Form>
            )}
        </Formik>
    );
}

SetPasswordForm.defaultProps = {};

export default SetPasswordForm;
