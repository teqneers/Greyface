import {zodResolver} from '@hookform/resolvers/zod';
import {useMutation, useQuery, useQueryClient} from '@tanstack/react-query';
import {Plus, X} from 'lucide-react';
import React, {useEffect, useMemo} from 'react';
import {useFieldArray, useForm} from 'react-hook-form';
import {useTranslation} from 'react-i18next';
import {toast} from 'sonner';
import {z} from 'zod';

import {useApplication} from '@/application/ApplicationContext';
import {FormDialog, FormFooter} from '@/components/dialogs';
import {useApiFormErrors} from '@/components/forms/useApiFormErrors';
import {Button} from '@/components/ui/button';
import {Form, FormControl, FormField, FormItem, FormLabel, FormMessage} from '@/components/ui/form';
import {Input} from '@/components/ui/input';
import {Select, SelectContent, SelectItem, SelectTrigger, SelectValue} from '@/components/ui/select';
import {Skeleton} from '@/components/ui/skeleton';
import {apiFetch, apiJson} from '@/lib/api';
import type {ListResponse} from '@/lib/api';
import type {User, UserAlias} from '@/types/user';

const MAX_ALIASES = 5;

export interface UserAliasFormDialogProps {
    open: boolean,
    onOpenChange: (open: boolean) => void,
    /** Id of the alias to edit; absent when creating. */
    aliasId?: string,
}

type FormValues = { user_id: string, aliases: { v: string }[] };

/**
 * Create adds up to five aliases for one user; edit changes a single alias.
 */
export function UserAliasFormDialog({open, onOpenChange, aliasId}: UserAliasFormDialogProps): React.ReactElement {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const queryClient = useQueryClient();
    const editing = aliasId !== undefined;

    const schema = useMemo(() => z.object({
        user_id: z.string().min(1, t('errors.required')),
        aliases: z.array(z.object({
            v: z.string().trim().min(1, t('errors.required')).max(128, t('errors.max', {max: 128})).email(t('errors.email')),
        })).min(1).max(MAX_ALIASES).superRefine((list, ctx) => {
            const seen = new Set<string>();
            list.forEach((item, index) => {
                const key = item.v.toLowerCase();
                if (seen.has(key)) {
                    ctx.addIssue({code: 'custom', path: [index, 'v'], message: t('errors.unique')});
                }
                seen.add(key);
            });
        }),
    }), [t]);

    const {data: users} = useQuery({
        queryKey: ['users', 'all'],
        queryFn: () => apiFetch<ListResponse<User>>(`${apiUrl}/users`),
        staleTime: 60_000,
        enabled: open,
    });
    const {data: alias, isLoading} = useQuery({
        queryKey: ['users-aliases', 'detail', aliasId],
        queryFn: () => apiFetch<UserAlias>(`${apiUrl}/users-aliases/${aliasId}`),
        enabled: open && editing,
    });

    const form = useForm<FormValues>({
        resolver: zodResolver(schema),
        defaultValues: {user_id: '', aliases: [{v: ''}]},
    });
    const {fields, append, remove} = useFieldArray({control: form.control, name: 'aliases'});
    const {error, setError, handle} = useApiFormErrors(form, ['user_id', 'aliases']);

    useEffect(() => {
        if (!open) {
            return;
        }
        setError(null);
        form.reset(alias && editing
            ? {user_id: alias.user.id, aliases: [{v: alias.alias_name}]}
            : {user_id: '', aliases: [{v: ''}]});
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, alias, editing]);

    const save = useMutation({
        mutationFn: (values: FormValues) => editing
            ? apiJson(`${apiUrl}/users-aliases/${aliasId}`, 'PUT', {user_id: values.user_id, alias_name: values.aliases[0].v})
            : apiJson(`${apiUrl}/users-aliases`, 'POST', {user_id: values.user_id, alias_name: values.aliases.map((item) => item.v)}),
        onSuccess: () => {
            toast.success(t(editing ? 'alias.updated' : 'alias.created'));
            queryClient.invalidateQueries({queryKey: ['users-aliases']});
            onOpenChange(false);
        },
        onError: handle,
    });

    return (
        <FormDialog open={open} onOpenChange={(next) => !save.isPending && onOpenChange(next)}
                    title={t(editing ? 'alias.editHeader' : 'alias.createHeader')} description={t('alias.formIntro')}>
            {editing && isLoading ? (
                <div className="space-y-4" aria-busy="true">
                    <Skeleton className="h-9 w-full"/>
                    <Skeleton className="h-9 w-full"/>
                </div>
            ) : (
                <Form {...form}>
                    <form onSubmit={form.handleSubmit((values) => save.mutate(values))} className="space-y-4" noValidate>
                        <FormField control={form.control} name="user_id" render={({field}) => (
                            <FormItem>
                                <FormLabel>{t('alias.user')}</FormLabel>
                                <Select value={field.value} onValueChange={field.onChange} name={field.name}>
                                    <FormControl>
                                        <SelectTrigger className="w-full"><SelectValue placeholder={t('alias.chooseUser')}/></SelectTrigger>
                                    </FormControl>
                                    <SelectContent>
                                        {users?.results.map((user) => (
                                            <SelectItem key={user.id} value={user.id}>{user.username}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <FormMessage/>
                            </FormItem>
                        )}/>

                        <fieldset className="space-y-2">
                            <legend className="mb-1 text-sm font-medium">{t('alias.aliasName')}</legend>
                            {fields.map((item, index) => (
                                <FormField key={item.id} control={form.control} name={`aliases.${index}.v`} render={({field}) => (
                                    <FormItem>
                                        <div className="flex items-center gap-2">
                                            <FormControl>
                                                <Input {...field} type="email" autoComplete="off"
                                                       aria-label={`${t('alias.aliasName')} ${index + 1}`}/>
                                            </FormControl>
                                            {!editing && fields.length > 1 && (
                                                <Button type="button" variant="ghost" size="icon-sm" onClick={() => remove(index)}
                                                        aria-label={t('lists.removeValue', {index: index + 1})}>
                                                    <X aria-hidden="true"/>
                                                </Button>
                                            )}
                                        </div>
                                        <FormMessage/>
                                    </FormItem>
                                )}/>
                            ))}
                            {!editing && fields.length < MAX_ALIASES && (
                                <Button type="button" variant="ghost" size="sm" onClick={() => append({v: ''})}>
                                    <Plus aria-hidden="true"/>
                                    {t('placeholder.addMore')}
                                </Button>
                            )}
                        </fieldset>

                        <FormFooter onCancel={() => onOpenChange(false)} submitLabel={t('button.save')}
                                    pending={save.isPending} error={error}/>
                    </form>
                </Form>
            )}
        </FormDialog>
    );
}
