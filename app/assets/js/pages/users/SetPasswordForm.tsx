import React from 'react';
import {UseMutationResult} from 'react-query';
import {useTranslation} from 'react-i18next';
import {Button, Col, Form, Modal, Row} from 'react-bootstrap';
import {Formik} from 'formik';
import * as yup from 'yup';

export interface SetPasswordValues {
    password: string,
}

export interface SetPasswordRequest {
    password: string
}

const Schema: yup.SchemaOf<SetPasswordValues> = yup.object()
    .noUnknown()
    .shape(
        {
            password: yup.string()
                .required()
                .max(128)
        }
    );


interface UserFromProps<TValues extends object, TData, TRes, TError> {
    submitBtn?: string | null,
    cancelBtn?: string | null,
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
        cancelBtn,
        ...rest
    }: UserFromProps<TValues, TData, any, any>
): React.ReactElement {
    const {t} = useTranslation();
    return (
        <Formik
            validationSchema={Schema}
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

                        </Row>
                    </Modal.Body>
                    <Modal.Footer>
                        <Button variant="outline-secondary" onClick={() => onCancel()}>
                            {cancelBtn ? cancelBtn : t('button.cancel')}
                        </Button>
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
