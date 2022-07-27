import React from 'react';
import {Button, Col, Form, Row} from 'react-bootstrap';
import {UseMutationResult} from 'react-query';
import * as yup from 'yup';
import {useTranslation} from 'react-i18next';
import {UserRole, USER_ROLES} from '../../types/user';
import {Formik} from 'formik';

interface UserValues {
    password?: string | number | string[];
    username: string,
    email: string,
    role: UserRole,
}

export type UpdateUserValues = UserValues;

export interface CreateUserValues extends UserValues {
    password: string,
}

interface UserRequest {
    username: string,
    email: string,
    role: UserRole,
}

export type UpdateUserRequest = UserRequest;

export interface CreateUserRequest extends UserRequest {
    password: string,
}

export interface CreateUserResponse {
    user: string,
}

// @ts-ignore
const UpdateSchema: yup.SchemaOf<UserValues> = yup.object()
    .noUnknown()
    .shape(
        {
            username: yup.string()
                .required()
                .max(128),
            email: yup.string()
                .required()
                .max(128)
                .email(),
            role: yup.string()
                .required()
                .max(16)
                .oneOf([...USER_ROLES]),
        }
    );

// @ts-ignore
const CreateSchema = UpdateSchema.shape({
    password: yup.string()
        .required()
        .max(4096),
});

interface UserFromProps<TValues extends object, TData, TRes, TError> {
    createUser: boolean,
    submitBtn?: string | null,
    cancelBtn?: string | null,
    onCancel?: () => void,
    initialValues: TValues,
    validationSchema?: yup.SchemaOf<any>,
    onSubmit: UseMutationResult<TRes, TError, TData>,
}

function UserForm<TValues extends UserValues, TData extends UserRequest>(
    {
        createUser,
        onSubmit,
        submitBtn,
        ...rest
    }: UserFromProps<TValues, TData, any, any>
): React.ReactElement {
    const {t} = useTranslation();
    return (
        <Formik
            validationSchema={createUser ? CreateSchema : UpdateSchema}
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

                    <Row className="mb-3">

                        <Form.Group as={Col} md="12" controlId="validationFormik01">
                            <Form.Label>{t('user.username')}</Form.Label>
                            <Form.Control
                                type="text"
                                name="username"
                                value={values.username}
                                onChange={handleChange}
                                isInvalid={!!errors.username}/>

                            <Form.Control.Feedback type="invalid">
                                {errors.username}
                            </Form.Control.Feedback>
                        </Form.Group>

                        <Form.Group as={Col} md="12" controlId="validationFormik02">
                            <Form.Label>{t('user.email')}</Form.Label>
                            <Form.Control
                                type="email"
                                name="email"
                                value={values.email}
                                onChange={handleChange}
                                isInvalid={!!errors.email}/>
                            <Form.Control.Feedback type="invalid">
                                {errors.email}
                            </Form.Control.Feedback>
                        </Form.Group>

                        <Form.Group as={Col} md="12" controlId="validationFormik03">
                            <Form.Label>{t('user.role')}</Form.Label>
                            <Form.Select
                                name="role"
                                value={values.role}
                                onChange={handleChange}
                                isInvalid={!!errors.role}>
                                {USER_ROLES.map((r) => {
                                    return (
                                        <option key={r} value={r}>{t(`user.roles.${r}`)}</option>
                                    );
                                })}
                            </Form.Select>
                            <Form.Control.Feedback type="invalid">
                                {errors.role}
                            </Form.Control.Feedback>
                        </Form.Group>

                        {createUser && (<Form.Group as={Col} md="12" controlId="validationFormik04">
                            <Form.Label>{t('user.password')}</Form.Label>

                            {/* @ts-ignore */}
                            <Form.Control
                                type="text"
                                name="password"
                                value={values.password}
                                onChange={handleChange}
                                isInvalid={!!errors.password}/>

                            <Form.Control.Feedback type="invalid">
                                {errors.password}
                            </Form.Control.Feedback>
                        </Form.Group>)}

                    </Row>
                    <Button variant="outline-primary" type="submit" disabled={isSubmitting && !onSubmit.isError}>{submitBtn}</Button>
                </Form>
            )}
        </Formik>
    );
}

UserForm.defaultProps = {};

export default UserForm;
