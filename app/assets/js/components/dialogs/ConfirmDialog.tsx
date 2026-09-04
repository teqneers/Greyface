import {Loader2} from 'lucide-react';
import React from 'react';
import {useTranslation} from 'react-i18next';

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import {buttonVariants} from '@/components/ui/button';
import {cn} from '@/lib/utils';

export interface ConfirmDialogProps {
    open: boolean,
    onOpenChange: (open: boolean) => void,
    title: string,
    description?: React.ReactNode,
    confirmLabel?: string,
    /** Destructive actions get the red button. */
    destructive?: boolean,
    /** Disables the buttons and shows a spinner while the mutation runs. */
    pending?: boolean,
    onConfirm: () => void,
    /** Extra content between the description and the buttons, e.g. a field. */
    children?: React.ReactNode,
}

export function ConfirmDialog(
    {open, onOpenChange, title, description, confirmLabel, destructive = false, pending = false, onConfirm, children}: ConfirmDialogProps
): React.ReactElement {
    const {t} = useTranslation();

    return (
        <AlertDialog open={open} onOpenChange={(next) => !pending && onOpenChange(next)}>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{title}</AlertDialogTitle>
                    {description && <AlertDialogDescription>{description}</AlertDialogDescription>}
                </AlertDialogHeader>
                {children}
                <AlertDialogFooter>
                    <AlertDialogCancel disabled={pending}>{t('button.cancel')}</AlertDialogCancel>
                    <AlertDialogAction
                        disabled={pending}
                        className={cn(destructive && buttonVariants({variant: 'destructive'}))}
                        onClick={(event) => {
                            // Keep the dialog open until the caller closes it after the mutation.
                            event.preventDefault();
                            onConfirm();
                        }}>
                        {pending && <Loader2 className="animate-spin" aria-hidden="true"/>}
                        {confirmLabel ?? t('button.delete')}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
