import {Loader2} from 'lucide-react';
import React from 'react';
import {useTranslation} from 'react-i18next';

import {Button} from '@/components/ui/button';
import {DialogFooter} from '@/components/ui/dialog';

export interface FormFooterProps {
    onCancel: () => void,
    submitLabel: string,
    pending?: boolean,
    /** A server-side error that is not tied to a field. */
    error?: string | null,
    /** For a form whose submit is meaningless until something is filled in. */
    submitDisabled?: boolean,
}

export function FormFooter({onCancel, submitLabel, pending = false, error, submitDisabled = false}: FormFooterProps): React.ReactElement {
    const {t} = useTranslation();
    return (
        <>
            {error && (
                <p role="alert" className="rounded-md border border-destructive/40 bg-destructive/10 px-3 py-2 text-sm text-destructive">
                    {error}
                </p>
            )}
            <DialogFooter>
                <Button type="button" variant="outline" onClick={onCancel} disabled={pending}>
                    {t('button.cancel')}
                </Button>
                <Button type="submit" disabled={pending || submitDisabled}>
                    {pending && <Loader2 className="animate-spin" aria-hidden="true"/>}
                    {submitLabel}
                </Button>
            </DialogFooter>
        </>
    );
}
