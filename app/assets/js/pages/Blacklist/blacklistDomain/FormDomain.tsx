import React from 'react';
import {Button, Col, Form, InputGroup, Modal, Row} from 'react-bootstrap';
import {UseMutationResult} from 'react-query';
import {useTranslation} from 'react-i18next';
import {FieldArray, Formik} from 'formik';

import {DomainRequest, DomainSchema, DomainValues} from '../../../utils/yupSchema';
import CancelButton from '../../../controllers/Buttons/CancelButton';
import SubmitButton from '../../../controllers/Buttons/SubmitButton';

interface FormDomainProps<TValues extends object, TData, TRes, TError> {
    createMode?: boolean,
    submitBtn?: string | null,
    onCancel?: () => void,
    initialValues: TValues,
    validationSchema?: any | (() => any),
    onSubmit: UseMutationResult<TRes, TError, TData>,
}

function FormDomain<TValues extends DomainValues, TData extends DomainRequest>(
    {
        createMode = true,
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
            validationSchema={DomainSchema(t)}
            onSubmit={((values) => {
                let submitData = values;
                if (!createMode) {
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

                                                        {createMode && fieldsCount > 1 && <Button variant="outline-warning"
                                                                               onClick={() => arrayHelpers.remove(index)}>X
                                                        </Button>}

                                                        <Form.Control.Feedback type="invalid">
                                                            {(errors.domain instanceof Array) ? errors.domain?.[index] : errors.domain}
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


export default FormDomain;
