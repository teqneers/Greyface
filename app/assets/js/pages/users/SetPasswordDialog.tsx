import {zodResolver} from '@hookform/resolvers/zod';
import {useMutation} from '@tanstack/react-query';
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

import {usersApi} from './api';

const MIN_PASSWORD = 8;

export interface SetPasswordDialogProps {
    open: boolean,
    onOpenChange: (open: boolean) => void,
    userId: string | undefined,
    username?: string,
}

export function SetPasswordDialog({open, onOpenChange, userId, username}: SetPasswordDialogProps): React.ReactElement {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const api = useMemo(() => usersApi(apiUrl), [apiUrl]);

    const schema = useMemo(() => z.object({
        password: z.string().min(MIN_PASSWORD, t('errors.min', {min: MIN_PASSWORD})).max(128),
        confirmation: z.string(),
    }).refine((values) => values.password === values.confirmation, {
        path: ['confirmation'],
        message: t('errors.passNotMatch'),
    }), [t]);

    const form = useForm<z.infer<typeof schema>>({
        resolver: zodResolver(schema),
        defaultValues: {password: '', confirmation: ''},
    });
    const {error, setError, handle} = useApiFormErrors(form, ['password']);

    useEffect(() => {
        if (open) {
            form.reset({password: '', confirmation: ''});
            setError(null);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open]);

    const save = useMutation({
        mutationFn: (values: { password: string }) => api.setPassword(userId as string, values.password),
        onSuccess: () => {
            toast.success(t('user.passwordSet'));
            onOpenChange(false);
        },
        onError: handle,
    });

    return (
        <FormDialog open={open} onOpenChange={(next) => !save.isPending && onOpenChange(next)}
                    title={t('user.setPassword')} description={username ? t('user.setPasswordFor', {username}) : undefined}>
            <Form {...form}>
                <form onSubmit={form.handleSubmit((values) => save.mutate(values))} className="space-y-4" noValidate>
                    <FormField control={form.control} name="password" render={({field}) => (
                        <FormItem>
                            <FormLabel>{t('user.password')}</FormLabel>
                            <FormControl><Input {...field} type="password" autoComplete="new-password" autoFocus/></FormControl>
                            <FormMessage/>
                        </FormItem>
                    )}/>
                    <FormField control={form.control} name="confirmation" render={({field}) => (
                        <FormItem>
                            <FormLabel>{t('user.passwordRetype')}</FormLabel>
                            <FormControl><Input {...field} type="password" autoComplete="new-password"/></FormControl>
                            <FormMessage/>
                        </FormItem>
                    )}/>
                    <FormFooter onCancel={() => onOpenChange(false)} submitLabel={t('button.save')}
                                pending={save.isPending} error={error}/>
                </form>
            </Form>
        </FormDialog>
    );
}
