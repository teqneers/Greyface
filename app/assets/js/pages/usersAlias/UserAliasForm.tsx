import React from 'react';
import {Button, Col, Form, InputGroup, Modal, Row} from 'react-bootstrap';
import {UseMutationResult, useQuery} from 'react-query';
import * as yup from 'yup';
import {useTranslation} from 'react-i18next';
import {FieldArray, Formik} from 'formik';

import LoadingIndicator from '../../controllers/LoadingIndicator';

export interface UserAliasValues {
    user_id?: string,
    alias_name: string[]
}

export interface UserAliasRequest {
    user_id?: string,
    alias_name: string[]
}


// @ts-ignore
const Schema: yup.SchemaOf<UserAliasValues> = yup.object()
    .noUnknown()
    .shape(
        {
            user_id: yup.string()
                .max(128)
                .required(),
            alias_name: yup.array()
                .of(yup.string().required().max(128).email())
                .min(1)
                .max(10)
                .default([]),
        }
    );

interface UserAliasFromProps<TValues extends object, TData, TRes, TError> {
    submitBtn?: string | null,
    cancelBtn?: string | null,
    onCancel?: () => void,
    initialValues: TValues,
    validationSchema?: yup.SchemaOf<any>,
    onSubmit: UseMutationResult<TRes, TError, TData>,
}

function UserAliasForm<TValues extends UserAliasValues, TData extends UserAliasRequest>(
    {
        onSubmit,
        submitBtn,
        cancelBtn,
        onCancel,
        ...rest
    }: UserAliasFromProps<TValues, TData, any, any>
): React.ReactElement {
    const {t} = useTranslation();

    const {data: users, isLoading: usersLoading} = useQuery(['users'], () => {
        return fetch('/api/users')
            .then((res) => res.json());
    }, {keepPreviousData: true});

    if (usersLoading) {
        return <LoadingIndicator/>;
    }


    return (
        <Formik
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

                            <Form.Group as={Col} md="12">
                                <Form.Label>{t('alias.user')}</Form.Label>
                                <Form.Select
                                    name="user_id"
                                    value={values.user_id}
                                    onChange={handleChange}
                                    isInvalid={!!errors.user_id}>
                                    {users.results.map((u) => {
                                        return (
                                            <option key={u.id} value={u.id}>{u.username}</option>
                                        );
                                    })}
                                </Form.Select>
                                <Form.Control.Feedback type="invalid">
                                    {errors.user_id}
                                </Form.Control.Feedback>
                            </Form.Group>

                            {/* @ts-ignore */}
                            <FieldArray
                                name="alias_name"
                                render={arrayHelpers => {
                                    if (!values.alias_name || values.alias_name.length === 0) {
                                        values.alias_name = [''];
                                    }
                                    return (
                                        <>
                                            {values.alias_name.map((option, index) => (
                                                <Form.Group key={index} as={Col} md="12"
                                                            className="mt-2">
                                                    <Form.Label>{t('alias.aliasName')}</Form.Label>

                                                    <InputGroup>
                                                        <Form.Control
                                                            type="email"
                                                            name={`alias_name[${index}]`}
                                                            value={values.alias_name[index]}
                                                            onChange={handleChange}
                                                            isInvalid={!!errors.alias_name?.[index]}>
                                                        </Form.Control>

                                                        <Button variant="outline-warning"
                                                                onClick={() => arrayHelpers.remove(index)}>X
                                                        </Button>

                                                        <Form.Control.Feedback type="invalid">
                                                            {errors.alias_name?.[index]}
                                                        </Form.Control.Feedback>
                                                    </InputGroup>

                                                </Form.Group>
                                            ))}
                                            <Button variant="link" className="mt-2 m-auto w-75"
                                                    onClick={() => arrayHelpers.push('')}>{t('alias.addMore')}
                                            </Button>
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

UserAliasForm.defaultProps = {};

export default UserAliasForm;
