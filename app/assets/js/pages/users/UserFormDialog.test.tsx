import {screen, waitFor} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';
import {describe, expect, it, vi} from 'vitest';

import {mockFetch, renderWithProviders} from '../../test/render';
import {UserFormDialog} from './UserFormDialog';

vi.mock('sonner', () => ({toast: {success: vi.fn(), error: vi.fn()}}));

/**
 * The mutation path: form submit → POST → cache invalidation → parent callback.
 * This is what a react-query or react-hook-form major has to keep working.
 */
describe('UserFormDialog', () => {
    const fillAndSubmit = async () => {
        await userEvent.type(screen.getByLabelText('Username'), 'carol');
        await userEvent.type(screen.getByLabelText('E-Mail Address'), 'carol@greyface.test');
        await userEvent.type(screen.getByLabelText('Password'), 'sup3rsecret');
        await userEvent.click(screen.getByRole('button', {name: 'Save'}));
    };

    it('posts the form values to the users endpoint', async () => {
        const fetchMock = mockFetch([[/\/users$/, {user: 'new-id'}]]);

        renderWithProviders(<UserFormDialog open onOpenChange={vi.fn()}/>);
        await fillAndSubmit();

        await waitFor(() => expect(fetchMock).toHaveBeenCalled());
        const [url, init] = fetchMock.mock.calls[0] as [string, RequestInit];
        expect(url).toMatch(/\/users$/);
        expect(init.method).toBe('POST');
        expect(JSON.parse(init.body as string)).toEqual({
            username: 'carol',
            email: 'carol@greyface.test',
            role: 'user',
            password: 'sup3rsecret',
        });
    });

    it('hands the new id back, closes, and invalidates the cached user list', async () => {
        mockFetch([[/\/users$/, {user: 'new-id'}]]);
        const onCreated = vi.fn();
        const onOpenChange = vi.fn();

        const {queryClient} = renderWithProviders(<UserFormDialog open onOpenChange={onOpenChange} onCreated={onCreated}/>);
        queryClient.setQueryData(['users', 'list', {page: 0}], {count: 0, results: []});
        const invalidate = vi.spyOn(queryClient, 'invalidateQueries');

        await fillAndSubmit();

        await waitFor(() => expect(onCreated).toHaveBeenCalledWith('new-id'));
        expect(onOpenChange).toHaveBeenCalledWith(false);
        expect(invalidate).toHaveBeenCalledWith({queryKey: ['users']});
    });

    it('shows the error the api reports', async () => {
        mockFetch([[/\/users$/, {error: 'Validation failed. (This value is already used.)'}, 422]]);

        renderWithProviders(<UserFormDialog open onOpenChange={vi.fn()}/>);
        await fillAndSubmit();

        expect(await screen.findByRole('alert')).toHaveTextContent('already used');
    });

    it('maps field errors from the api onto the fields', async () => {
        mockFetch([[/\/users$/, {error: 'Validation failed', errors: {username: 'Taken'}}, 422]]);

        renderWithProviders(<UserFormDialog open onOpenChange={vi.fn()}/>);
        await fillAndSubmit();

        expect(await screen.findByText('Taken')).toBeInTheDocument();
        expect(screen.queryByRole('alert')).not.toBeInTheDocument();
    });

    it('does not submit when required fields are empty', async () => {
        const fetchMock = mockFetch([[/\/users$/, {user: 'new-id'}]]);

        renderWithProviders(<UserFormDialog open onOpenChange={vi.fn()}/>);
        await userEvent.click(screen.getByRole('button', {name: 'Save'}));

        expect(await screen.findAllByText(/required/i)).not.toHaveLength(0);
        expect(fetchMock).not.toHaveBeenCalled();
    });

    it('closes without saving when cancelled', async () => {
        const fetchMock = mockFetch([[/\/users$/, {user: 'new-id'}]]);
        const onOpenChange = vi.fn();

        renderWithProviders(<UserFormDialog open onOpenChange={onOpenChange}/>);
        await userEvent.click(screen.getByRole('button', {name: 'Cancel'}));

        expect(onOpenChange).toHaveBeenCalledWith(false);
        expect(fetchMock).not.toHaveBeenCalled();
    });
});
