import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';
import {describe, expect, it, vi} from 'vitest';

import {renderWithProviders} from '../../test/render';
import {ConfirmDialog} from './ConfirmDialog';
import {FormDialog} from './FormDialog';
import {FormFooter} from './FormFooter';

describe('ConfirmDialog', () => {
    it('confirms through the callback and leaves closing to the caller', async () => {
        const onConfirm = vi.fn();
        const onOpenChange = vi.fn();
        renderWithProviders(
            <ConfirmDialog open title="Delete entry" description="Really?" destructive onConfirm={onConfirm} onOpenChange={onOpenChange}/>
        );

        expect(screen.getByRole('alertdialog', {name: 'Delete entry'})).toBeInTheDocument();
        await userEvent.click(screen.getByRole('button', {name: 'Delete'}));

        expect(onConfirm).toHaveBeenCalledTimes(1);
        expect(onOpenChange).not.toHaveBeenCalled();
    });

    /**
     * The `destructive` prop was passed to AlertDialogAction as a className for a
     * long time, and lost: that component renders a Button with asChild, so its
     * own variant classes land on the same element and win the merge. Every
     * destructive confirmation in the application looked like an ordinary one.
     */
    it('gives a destructive action the destructive button, and an ordinary one not', () => {
        const {unmount} = renderWithProviders(
            <ConfirmDialog open title="Delete entry" destructive onConfirm={vi.fn()} onOpenChange={vi.fn()}/>
        );
        // bg-, not just the word: the base button classes mention destructive
        // for aria-invalid states in either variant.
        expect(screen.getByRole('button', {name: 'Delete'}).className).toContain('bg-destructive');
        unmount();

        renderWithProviders(
            <ConfirmDialog open title="Send it" confirmLabel="Send" onConfirm={vi.fn()} onOpenChange={vi.fn()}/>
        );
        expect(screen.getByRole('button', {name: 'Send'}).className).toContain('bg-primary');
    });

    it('cannot be dismissed while the action is pending', async () => {
        const onOpenChange = vi.fn();
        renderWithProviders(
            <ConfirmDialog open title="Delete entry" pending onConfirm={() => {}} onOpenChange={onOpenChange}/>
        );

        expect(screen.getByRole('button', {name: 'Delete'})).toBeDisabled();
        await userEvent.keyboard('{Escape}');
        expect(onOpenChange).not.toHaveBeenCalled();
    });
});

describe('FormDialog', () => {
    it('renders the form with a title and submits through the footer', async () => {
        const onSubmit = vi.fn((event: React.FormEvent) => event.preventDefault());
        const onOpenChange = vi.fn();
        renderWithProviders(
            <FormDialog open title="Add user" onOpenChange={onOpenChange}>
                <form onSubmit={onSubmit}>
                    <input aria-label="Username"/>
                    <FormFooter onCancel={() => onOpenChange(false)} submitLabel="Save" error="Username taken"/>
                </form>
            </FormDialog>
        );

        expect(screen.getByRole('dialog', {name: 'Add user'})).toBeInTheDocument();
        expect(screen.getByRole('alert')).toHaveTextContent('Username taken');

        await userEvent.click(screen.getByRole('button', {name: 'Save'}));
        expect(onSubmit).toHaveBeenCalledTimes(1);

        await userEvent.click(screen.getByRole('button', {name: 'Cancel'}));
        expect(onOpenChange).toHaveBeenCalledWith(false);
    });
});
