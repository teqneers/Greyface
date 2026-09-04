import {screen, waitFor} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';
import {describe, expect, it, vi} from 'vitest';

import {mockFetch, renderWithProviders} from '../../test/render';
import CreateUser from './CreateUser';

/**
 * The mutation path: form submit → POST → cache invalidation → parent callback.
 * This is what a react-query major has to keep working.
 */
describe('CreateUser', () => {
    const fillAndSubmit = async () => {
        const container = screen.getByRole('dialog');
        await userEvent.type(container.querySelector('[name="username"]')!, 'carol');
        await userEvent.type(container.querySelector('[name="email"]')!, 'carol@greyface.test');
        await userEvent.type(container.querySelector('[name="password"]')!, 'sup3rsecret');
        await userEvent.click(screen.getByRole('button', {name: 'Save'}));
    };

    it('posts the form values to the users endpoint', async () => {
        const fetchMock = mockFetch([[/\/users$/, {user: 'new-id'}]]);

        renderWithProviders(<CreateUser onCancel={vi.fn()} onCreate={vi.fn()}/>);
        await fillAndSubmit();

        await waitFor(() => expect(fetchMock).toHaveBeenCalled());

        const [url, init] = fetchMock.mock.calls[0] as [string, RequestInit];
        expect(url).toMatch(/\/users$/);
        expect(init.method).toBe('POST');
        expect(JSON.parse(init.body as string)).toMatchObject({
            username: 'carol',
            email: 'carol@greyface.test',
            role: 'user',
        });
    });

    it('hands the new id back to its parent', async () => {
        mockFetch([[/\/users$/, {user: 'new-id'}]]);
        const onCreate = vi.fn();

        renderWithProviders(<CreateUser onCancel={vi.fn()} onCreate={onCreate}/>);
        await fillAndSubmit();

        await waitFor(() => expect(onCreate).toHaveBeenCalledWith('new-id'));
    });

    it('invalidates the cached user list so the table refreshes', async () => {
        mockFetch([[/\/users$/, {user: 'new-id'}]]);

        const {queryClient} = renderWithProviders(<CreateUser onCancel={vi.fn()} onCreate={vi.fn()}/>);
        const invalidate = vi.spyOn(queryClient, 'invalidateQueries');

        await fillAndSubmit();

        await waitFor(() => expect(invalidate).toHaveBeenCalled());
    });

    it('shows the error the api reports', async () => {
        mockFetch([[/\/users$/, {error: 'Validation failed. (This value is already used.)'}, 422]]);

        renderWithProviders(<CreateUser onCancel={vi.fn()} onCreate={vi.fn()}/>);
        await fillAndSubmit();

        expect(await screen.findByText(/This value is already used/)).toBeInTheDocument();
    });

    it('does not submit when required fields are empty', async () => {
        const fetchMock = mockFetch([[/\/users$/, {user: 'new-id'}]]);

        renderWithProviders(<CreateUser onCancel={vi.fn()} onCreate={vi.fn()}/>);
        await userEvent.click(screen.getByRole('button', {name: 'Save'}));

        await waitFor(() => expect(fetchMock).not.toHaveBeenCalled());
    });

    it('closes without saving when cancelled', async () => {
        mockFetch([[/\/users$/, {user: 'new-id'}]]);
        const onCancel = vi.fn();

        renderWithProviders(<CreateUser onCancel={onCancel} onCreate={vi.fn()}/>);
        await userEvent.click(screen.getByRole('button', {name: 'Cancel'}));

        expect(onCancel).toHaveBeenCalled();
    });
});
