import React from 'react';

import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

export interface FormDialogProps {
    open: boolean,
    /** Called with false when the user dismisses the dialog. */
    onOpenChange: (open: boolean) => void,
    title: string,
    description?: string,
    children: React.ReactNode,
}

/**
 * Dialog shell for create/edit forms. The form itself (fields, footer buttons)
 * is the child; this only owns the frame, the title and dismissal.
 */
export function FormDialog({open, onOpenChange, title, description, children}: FormDialogProps): React.ReactElement {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md" onInteractOutside={(event) => event.preventDefault()}>
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                    {description ? <DialogDescription>{description}</DialogDescription> : <DialogDescription className="sr-only">{title}</DialogDescription>}
                </DialogHeader>
                {children}
            </DialogContent>
        </Dialog>
    );
}
