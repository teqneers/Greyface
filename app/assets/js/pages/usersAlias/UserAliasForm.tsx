import {TFunction} from 'i18next';
import React from 'react';
import {Button, Col, Form, InputGroup, Modal, Row} from 'react-bootstrap';
import {UseMutationResult, useQuery} from 'react-query';
import * as yup from 'yup';
import {useTranslation} from 'react-i18next';
import {FieldArray, Formik} from 'formik';

import {useApplication} from '../../application/ApplicationContext';
import CancelButton from '../../controllers/Buttons/CancelButton';
import SubmitButton from '../../controllers/Buttons/SubmitButton';
import LoadingIndicator from '../../controllers/LoadingIndicator';

export interface UserAliasValues {
    user_id?: string,
    alias_name: string[]
}

export interface UserAliasRequest {
    user_id?: string,
    alias_name: string[]
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
                user_id: yup.string()
                    .max(128)
                    .required(),
                alias_name: yup.array()
                    .of(yup.string().required().max(128).email())
                    .min(1)
                    .max(5)
                    .unique(t('errors.unique'))
                    .default([]),
            }
        );
}

interface UserAliasFromProps<TValues extends object, TData, TRes, TError> {
    createMode: boolean,
    submitBtn?: string | null,
    onCancel?: () => void,
    initialValues: TValues,
    validationSchema?: yup.SchemaOf<any>,
    onSubmit: UseMutationResult<TRes, TError, TData>,
}

function UserAliasForm<TValues extends UserAliasValues, TData extends UserAliasRequest>(
    {
        createMode,
        onSubmit,
        submitBtn,
        onCancel,
        ...rest
    }: UserAliasFromProps<TValues, TData, any, any>
): React.ReactElement {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();

    const {data: users, isLoading: usersLoading} = useQuery(['users'], () => {
        return fetch(`${apiUrl}/users`)
            .then((res) => res.json());
    }, {keepPreviousData: true});

    if (usersLoading) {
        return <LoadingIndicator/>;
    }

    return (
        <Formik
            validationSchema={Schema(t)}
            onSubmit={((values) => {
                let submitData = values;
                if (!createMode) {
                    submitData = {...values, alias_name: values.alias_name[0]};
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

                            <Form.Group as={Col} md="12">
                                <Form.Label>{t('alias.user')}</Form.Label>
                                <Form.Select
                                    name="user_id"
                                    value={values.user_id}
                                    onChange={handleChange}
                                    isInvalid={!!errors.user_id}>
                                    <option disabled value=""/>
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
                                    const fieldsCount = values.alias_name.length;
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
                                                            isInvalid={(errors.alias_name instanceof Array) ? !!errors.alias_name?.[index] : !!errors.alias_name}>
                                                        </Form.Control>

                                                        {createMode && fieldsCount > 1 && <Button variant="outline-warning"
                                                                               onClick={() => arrayHelpers.remove(index)}>X
                                                        </Button>}

                                                        <Form.Control.Feedback type="invalid">
                                                            {(errors.alias_name instanceof Array) ? errors.alias_name?.[index] : errors.alias_name}
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

UserAliasForm.defaultProps = {
    createMode: true
};

export default UserAliasForm;
