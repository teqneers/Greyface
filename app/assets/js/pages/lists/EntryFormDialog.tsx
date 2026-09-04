import {zodResolver} from '@hookform/resolvers/zod';
import {useMutation, useQueryClient} from '@tanstack/react-query';
import {Plus, X} from 'lucide-react';
import React, {useEffect, useState} from 'react';
import {useFieldArray, useForm} from 'react-hook-form';
import type {FieldValues, Path} from 'react-hook-form';
import {useTranslation} from 'react-i18next';
import {toast} from 'sonner';

import {useApplication} from '@/application/ApplicationContext';
import {FormDialog, FormFooter} from '@/components/dialogs';
import {Button} from '@/components/ui/button';
import {Form, FormControl, FormField, FormItem, FormLabel, FormMessage} from '@/components/ui/form';
import {Input} from '@/components/ui/input';
import {ApiError, apiJson} from '@/lib/api';

import type {EntryConfig} from './config';
import {MAX_VALUES} from './schemas';

export interface EntryFormDialogProps<T, V extends FieldValues> {
    config: EntryConfig<T, V>,
    i18n: string,
    open: boolean,
    onOpenChange: (open: boolean) => void,
    /** Present when editing; absent when creating. */
    row?: T,
}

/**
 * Create/edit dialog driven by the entry config. Server-side 422 errors are
 * mapped onto the matching field when the names line up, otherwise shown
 * above the buttons.
 */
export function EntryFormDialog<T, V extends FieldValues>(
    {config, i18n, open, onOpenChange, row}: EntryFormDialogProps<T, V>
): React.ReactElement {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const queryClient = useQueryClient();
    const editing = row !== undefined;
    const {form: formConfig} = config;
    const multi = !editing && formConfig.multiField !== undefined;
    const [error, setError] = useState<string | null>(null);

    const form = useForm<V>({
        resolver: zodResolver(formConfig.schema as any),
        defaultValues: (editing ? formConfig.fromRow(row) : formConfig.empty()) as any,
    });

    // Reopening the dialog for another row must not show the previous attempt's
    // error. Cleared during render; the form's own values are reset below.
    const [shownFor, setShownFor] = useState<{ open: boolean, row?: T }>({open, row});
    if (shownFor.open !== open || shownFor.row !== row) {
        setShownFor({open, row});
        if (open) {
            setError(null);
        }
    }

    useEffect(() => {
        if (open) {
            form.reset((editing ? formConfig.fromRow(row) : formConfig.empty()) as any);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, row]);

    const save = useMutation({
        mutationFn: (values: V) => editing
            ? apiJson(`${apiUrl}${config.apiPath}/edit`, 'PUT', config.updateBody(row, values))
            : apiJson(`${apiUrl}${config.apiPath}`, 'POST', config.createBody(values)),
        onSuccess: () => {
            toast.success(t(editing ? 'lists.updated' : 'lists.created'));
            queryClient.invalidateQueries({queryKey: ['list', config.apiPath]});
            onOpenChange(false);
        },
        onError: (failure: Error) => {
            if (failure instanceof ApiError) {
                let mapped = false;
                for (const [field, message] of Object.entries(failure.fieldErrors)) {
                    const target = formConfig.fields.find((candidate) => candidate.name === field || field.startsWith(`${candidate.name}`));
                    if (target) {
                        form.setError(target.name as Path<V>, {message});
                        mapped = true;
                    }
                }
                setError(mapped ? null : failure.message);
            } else {
                setError(failure.message);
            }
        },
    });

    const title = t(`${i18n}.${config.kind === 'emails' ? 'email' : 'domain'}.${editing ? 'editHeader' : 'addHeader'}`);

    return (
        <FormDialog open={open} onOpenChange={(next) => !save.isPending && onOpenChange(next)} title={title}>
            <Form {...form}>
                <form onSubmit={form.handleSubmit((values) => save.mutate(values))} className="space-y-4" noValidate>
                    {multi
                        ? <MultiValueField form={form} name={formConfig.multiField as Path<V>} field={formConfig.fields[0]}/>
                        : formConfig.fields.map((field, index) => (
                            <FormField
                                key={field.name}
                                control={form.control}
                                name={field.name as Path<V>}
                                render={({field: control}) => (
                                    <FormItem>
                                        <FormLabel>{t(field.label)}</FormLabel>
                                        <FormControl>
                                            <Input {...control} value={control.value ?? ''} placeholder={field.placeholder}
                                                   autoComplete={field.autoComplete} autoFocus={index === 0}/>
                                        </FormControl>
                                        <FormMessage/>
                                    </FormItem>
                                )}
                            />
                        ))}
                    <FormFooter onCancel={() => onOpenChange(false)} submitLabel={t(editing ? 'button.save' : 'button.add')}
                                pending={save.isPending} error={error}/>
                </form>
            </Form>
        </FormDialog>
    );
}

interface MultiValueFieldProps<V extends FieldValues> {
    form: ReturnType<typeof useForm<V>>,
    name: Path<V>,
    field: { label: string, placeholder?: string, autoComplete?: string },
}

/** One input per value, up to five, with add/remove controls. */
function MultiValueField<V extends FieldValues>({form, name, field}: MultiValueFieldProps<V>): React.ReactElement {
    const {t} = useTranslation();
    const {fields, append, remove} = useFieldArray({control: form.control, name: name as any});
    const listError = form.getFieldState(name).error;

    return (
        <fieldset className="space-y-2">
            <legend className="mb-1 text-sm font-medium">{t(field.label)}</legend>
            {fields.map((item, index) => (
                <FormField
                    key={item.id}
                    control={form.control}
                    name={`${name}.${index}.v` as Path<V>}
                    render={({field: control}) => (
                        <FormItem>
                            <div className="flex items-center gap-2">
                                <FormControl>
                                    <Input {...control} value={control.value ?? ''} placeholder={field.placeholder}
                                           autoComplete={field.autoComplete} autoFocus={index === 0}
                                           aria-label={`${t(field.label)} ${index + 1}`}/>
                                </FormControl>
                                {fields.length > 1 && (
                                    <Button type="button" variant="ghost" size="icon-sm" onClick={() => remove(index)}
                                            aria-label={t('lists.removeValue', {index: index + 1})}>
                                        <X aria-hidden="true"/>
                                    </Button>
                                )}
                            </div>
                            <FormMessage/>
                        </FormItem>
                    )}
                />
            ))}
            {listError?.message && <p className="text-sm text-destructive">{listError.message}</p>}
            {fields.length < MAX_VALUES && (
                <Button type="button" variant="ghost" size="sm" onClick={() => append({v: ''} as any)}>
                    <Plus aria-hidden="true"/>
                    {t('placeholder.addMore')}
                </Button>
            )}
        </fieldset>
    );
}
