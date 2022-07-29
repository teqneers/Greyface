import {TFunction} from 'i18next';
import React from 'react';
import {Button, Col, Form, InputGroup, Modal, Row} from 'react-bootstrap';
import {UseMutationResult} from 'react-query';
import * as yup from 'yup';
import {useTranslation} from 'react-i18next';
import {FieldArray, Formik} from 'formik';

import CancelButton from '../../../controllers/Buttons/CancelButton';
import SubmitButton from '../../../controllers/Buttons/SubmitButton';

export interface EmailValues {
    email: string[]
}

export interface EmailRequest {
    email: string[]
}


declare module 'yup' {
    interface ArraySchema<T> {
        unique(
            message: string,
            mapper?: (value: T, index?: number, list?: T[]) => T[]
        ): ArraySchema<T>;
    }
}

yup.addMethod(yup.array, 'unique', function (message, mapper = a => a) {
    return this.test('unique', message, function (list) {
        return list.length === new Set(list.map(mapper)).size;
    });
});

function Schema(t: TFunction): yup.AnySchema {
    return yup.object()
        .noUnknown()
        .shape(
            {
                email: yup.array()
                    .of(yup.string().required().max(128).email())
                    .min(1)
                    .max(5)
                    .unique(t('errors.unique'))
                    .default([]),
            }
        );
}

interface FormEmailProps<TValues extends object, TData, TRes, TError> {
    createMode: boolean,
    submitBtn?: string | null,
    onCancel?: () => void,
    initialValues: TValues,
    validationSchema?: yup.SchemaOf<any>,
    onSubmit: UseMutationResult<TRes, TError, TData>,
}

function FormEmail<TValues extends EmailValues, TData extends EmailRequest>(
    {
        createMode,
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
            validationSchema={Schema(t)}
            onSubmit={((values) => {
                let submitData = values;
                if (!createMode) {
                    submitData = {...values, email: values.email[0]};
                }
                // @ts-ignore
                onSubmit.mutate(submitData);
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
                            {/* @ts-ignore */}
                            <FieldArray
                                name="email"
                                render={arrayHelpers => {
                                    if (!values.email || values.email.length === 0) {
                                        values.email = [''];
                                    }
                                    const fieldsCount = values.email.length;
                                    return (
                                        <>
                                            {values.email.map((option, index) => (
                                                <Form.Group key={index} as={Col} md="12"
                                                            className="mt-2">
                                                    <Form.Label>{t('blacklist.email.email')}</Form.Label>

                                                    <InputGroup>
                                                        <Form.Control
                                                            type="email"
                                                            name={`email[${index}]`}
                                                            value={values.email[index]}
                                                            onChange={handleChange}
                                                            isInvalid={(errors.email instanceof Array) ? !!errors.email?.[index] : !!errors.email}>
                                                        </Form.Control>

                                                        {createMode && <Button variant="outline-warning"
                                                                               onClick={() => arrayHelpers.remove(index)}>X
                                                        </Button>}

                                                        <Form.Control.Feedback type="invalid">
                                                            {(errors.email instanceof Array) ? errors.email?.[index] : errors.email}
                                                        </Form.Control.Feedback>
                                                    </InputGroup>

                                                </Form.Group>
                                            ))}
                                            {createMode && fieldsCount < 5 &&
                                                <Button variant="link" className="mt-2 m-auto w-75"
                                                        onClick={() => arrayHelpers.push('')}>{t('placeholder.addMore')}
                                                </Button>}
                                        </>
                                    );
                                }}/>
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

FormEmail.defaultProps = {
    createMode: true
};

export default FormEmail;
