import {zodResolver} from '@hookform/resolvers/zod';
import {useMutation, useQuery, useQueryClient} from '@tanstack/react-query';
import React, {useEffect, useMemo} from 'react';
import {useForm} from 'react-hook-form';
import {useTranslation} from 'react-i18next';
import {toast} from 'sonner';
import {z} from 'zod';

import {useApplication} from '@/application/ApplicationContext';
import {FormDialog, FormFooter} from '@/components/dialogs';
import {useApiFormErrors} from '@/components/forms/useApiFormErrors';
import {Form, FormControl, FormField, FormItem, FormLabel, FormMessage} from '@/components/ui/form';
import {Input} from '@/components/ui/input';
import {Select, SelectContent, SelectItem, SelectTrigger, SelectValue} from '@/components/ui/select';
import {Skeleton} from '@/components/ui/skeleton';
import {USER_ROLES} from '@/types/user';
import type {UserRole} from '@/types/user';

import {usersApi} from './api';

const MIN_PASSWORD = 8;

function schema(t: ReturnType<typeof useTranslation>['t'], creating: boolean) {
    const base = {
        username: z.string().trim().min(1, t('errors.required')).max(128, t('errors.max', {max: 128})),
        email: z.string().trim().min(1, t('errors.required')).max(128, t('errors.max', {max: 128})).email(t('errors.email')),
        role: z.enum(USER_ROLES),
    };
    return creating
        ? z.object({...base, password: z.string().min(MIN_PASSWORD, t('errors.min', {min: MIN_PASSWORD})).max(128)})
        : z.object({...base, password: z.string().optional()});
}

type FormValues = { username: string, email: string, role: UserRole, password?: string };

export interface UserFormDialogProps {
    open: boolean,
    onOpenChange: (open: boolean) => void,
    /** Id of the user to edit; absent when creating. */
    userId?: string,
    /** Called with the new id after a successful create. */
    onCreated?: (id: string) => void,
}

export function UserFormDialog({open, onOpenChange, userId, onCreated}: UserFormDialogProps): React.ReactElement {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const queryClient = useQueryClient();
    const api = useMemo(() => usersApi(apiUrl), [apiUrl]);
    const editing = userId !== undefined;

    const {data: user, isLoading} = useQuery({
        queryKey: ['users', 'detail', userId],
        queryFn: () => api.get(userId as string),
        enabled: open && editing,
    });

    const form = useForm<FormValues>({
        resolver: zodResolver(schema(t, !editing)),
        defaultValues: {username: '', email: '', role: 'user', password: ''},
    });
    const {error, setError, handle} = useApiFormErrors(form, ['username', 'email', 'role', 'password']);

    useEffect(() => {
        if (!open) {
            return;
        }
        setError(null);
        form.reset(user && editing
            ? {username: user.username, email: user.email, role: user.role, password: ''}
            : {username: '', email: '', role: 'user', password: ''});
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, user, editing]);

    const save = useMutation({
        mutationFn: (values: FormValues) => editing
            ? api.update(userId, {username: values.username, email: values.email, role: values.role})
            : api.create({username: values.username, email: values.email, role: values.role, password: values.password ?? ''}),
        onSuccess: (result) => {
            toast.success(t(editing ? 'user.updated' : 'user.created'));
            queryClient.invalidateQueries({queryKey: ['users']});
            onOpenChange(false);
            if (!editing) {
                onCreated?.(result.user);
            }
        },
        onError: handle,
    });

    return (
        <FormDialog open={open} onOpenChange={(next) => !save.isPending && onOpenChange(next)}
                    title={t(editing ? 'user.editHeader' : 'user.createHeader')}>
            {editing && isLoading ? (
                <div className="space-y-4" aria-busy="true">
                    <Skeleton className="h-9 w-full"/>
                    <Skeleton className="h-9 w-full"/>
                    <Skeleton className="h-9 w-full"/>
                </div>
            ) : (
                <Form {...form}>
                    <form onSubmit={form.handleSubmit((values) => save.mutate(values))} className="space-y-4" noValidate>
                        <FormField control={form.control} name="username" render={({field}) => (
                            <FormItem>
                                <FormLabel>{t('user.username')}</FormLabel>
                                <FormControl><Input {...field} autoComplete="off" autoFocus/></FormControl>
                                <FormMessage/>
                            </FormItem>
                        )}/>
                        <FormField control={form.control} name="email" render={({field}) => (
                            <FormItem>
                                <FormLabel>{t('user.email')}</FormLabel>
                                <FormControl><Input {...field} type="email" autoComplete="off"/></FormControl>
                                <FormMessage/>
                            </FormItem>
                        )}/>
                        <FormField control={form.control} name="role" render={({field}) => (
                            <FormItem>
                                <FormLabel>{t('user.role')}</FormLabel>
                                <Select value={field.value} onValueChange={field.onChange} name={field.name}>
                                    <FormControl>
                                        <SelectTrigger className="w-full"><SelectValue/></SelectTrigger>
                                    </FormControl>
                                    <SelectContent>
                                        {USER_ROLES.map((role) => (
                                            <SelectItem key={role} value={role}>{t(`user.roles.${role}`)}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <FormMessage/>
                            </FormItem>
                        )}/>
                        {!editing && (
                            <FormField control={form.control} name="password" render={({field}) => (
                                <FormItem>
                                    <FormLabel>{t('user.password')}</FormLabel>
                                    <FormControl><Input {...field} type="password" autoComplete="new-password"/></FormControl>
                                    <FormMessage/>
                                </FormItem>
                            )}/>
                        )}
                        <FormFooter onCancel={() => onOpenChange(false)} submitLabel={t('button.save')}
                                    pending={save.isPending} error={error}/>
                    </form>
                </Form>
            )}
        </FormDialog>
    );
}
