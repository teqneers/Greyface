import React from 'react';
import {Button, Col, Form, InputGroup, Modal, Row} from 'react-bootstrap';
import {UseMutationResult} from 'react-query';
import * as yup from 'yup';
import {useTranslation} from 'react-i18next';
import {FieldArray, Formik} from 'formik';

export interface DomainValues {
    domain: string[]
}

export interface DomainRequest {
    domain: string[]
}

const Schema: yup.SchemaOf<DomainValues> = yup.object()
    .noUnknown()
    .shape(
        {
            domain: yup.array()
                .of(yup.string().required().max(128))
                .min(1)
                .max(5)
                .default([]),
        }
    );

interface FormDomainProps<TValues extends object, TData, TRes, TError> {
    createMode: boolean,
    submitBtn?: string | null,
    cancelBtn?: string | null,
    onCancel?: () => void,
    initialValues: TValues,
    validationSchema?: yup.SchemaOf<any>,
    onSubmit: UseMutationResult<TRes, TError, TData>,
}

function FormDomain<TValues extends DomainValues, TData extends DomainRequest>(
    {
        createMode,
        onSubmit,
        submitBtn,
        cancelBtn,
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
                let submitData = values;
                if(!createMode) {
                    submitData = {...values, domain: values.domain[0]};
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
                                name="domain"
                                render={arrayHelpers => {
                                    if (!values.domain || values.domain.length === 0) {
                                        values.domain = [''];
                                    }
                                    const fieldsCount = values.domain.length;
                                    return (
                                        <>
                                            {values.domain.map((option, index) => (
                                                <Form.Group key={index} as={Col} md="12"
                                                            className="mt-2">
                                                    <Form.Label>{t('blacklist.domain.domain')}</Form.Label>

                                                    <InputGroup>
                                                        <Form.Control
                                                            type="text"
                                                            name={`domain[${index}]`}
                                                            value={values.domain[index]}
                                                            onChange={handleChange}
                                                            isInvalid={(errors.domain instanceof Array) ? !!errors.domain?.[index] : !!errors.domain}>
                                                        </Form.Control>

                                                        {createMode && <Button variant="outline-warning"
                                                                               onClick={() => arrayHelpers.remove(index)}>X
                                                        </Button>}

                                                        <Form.Control.Feedback type="invalid">
                                                            {(errors.domain instanceof Array) ? errors.domain?.[index] : errors.domain}
                                                        </Form.Control.Feedback>
                                                    </InputGroup>

                                                </Form.Group>
                                            ))}
                                            {createMode && fieldsCount < 5 && <Button variant="link" className="mt-2 m-auto w-75"
                                                                   onClick={() => arrayHelpers.push('')}>{t('placeholder.addMore')}
                                            </Button>}
                                        </>
                                    );
                                }}/>
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

FormDomain.defaultProps = {
    createMode: true
};

export default FormDomain;
