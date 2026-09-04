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
