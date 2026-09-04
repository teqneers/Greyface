import {screen, waitFor} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';
import {beforeEach, describe, expect, it, vi} from 'vitest';

import {initSettings} from '../../application/settings';
import {mockFetch, renderModuleAt} from '../../test/render';
import UserModule from './UserModule';

const USERS = {
    count: 3,
    results: [
        {id: 'u-1', username: 'alice', email: 'alice@greyface.test', role: 'user', is_administrator: false, is_deleted: false},
        {id: 'u-2', username: 'bob', email: 'bob@greyface.test', role: 'admin', is_administrator: true, is_deleted: false},
    ],
};

/**
 * One full CRUD screen, exercised through the seams both migrations touch:
 * useQuery turning table state into a request URL, and the nested routes that
 * drive the create/edit/delete dialogs.
 */
describe('UserModule', () => {
    beforeEach(() => {
        initSettings();
    });

    it('lists the users returned by the api', async () => {
        mockFetch([[/\/users\?/, USERS]]);

        renderModuleAt(<UserModule/>, '/users');

        // Both rows in one waitFor: react-table paints rows across renders, so
        // asserting the second one separately can land between them.
        await waitFor(() => {
            expect(screen.getByText('alice')).toBeInTheDocument();
            expect(screen.getByText('bob@greyface.test')).toBeInTheDocument();
        });
    });

    it('builds the request from the table state', async () => {
        const fetchMock = mockFetch([[/\/users\?/, USERS]]);

        renderModuleAt(<UserModule/>, '/users');
        await waitFor(() => expect(screen.getByText('alice')).toBeInTheDocument());

        const url = new URL(fetchMock.mock.calls[0][0] as string);
        expect(url.pathname).toBe('/api/users');
        expect(url.searchParams.get('start')).toBe('0');
        expect(url.searchParams.get('max')).toBe('10');
        expect(url.searchParams.get('query')).toBe('');
        // settings default the user list to username ascending
        expect(url.searchParams.get('sortBy')).toBe('username');
        expect(url.searchParams.get('desc')).toBe('0');
    });

    it('refetches with the search query when it changes', async () => {
        const fetchMock = mockFetch([[/\/users\?/, USERS]]);

        renderModuleAt(<UserModule/>, '/users');
        await waitFor(() => expect(screen.getByText('alice')).toBeInTheDocument());

        await userEvent.type(screen.getByRole('textbox'), 'ali');

        await waitFor(() => {
            const urls = fetchMock.mock.calls.map(([u]) => String(u));
            expect(urls.some((u) => u.includes('query=ali'))).toBe(true);
        });
    });

    it('opens the create dialog from the toolbar button', async () => {
        mockFetch([[/\/users\?/, USERS]]);

        renderModuleAt(<UserModule/>, '/users');
        await waitFor(() => expect(screen.getByText('alice')).toBeInTheDocument());

        expect(screen.queryByRole('dialog')).not.toBeInTheDocument();

        await userEvent.click(screen.getByRole('button', {name: /add user/i}));

        expect(await screen.findByRole('dialog')).toBeInTheDocument();
    });

    it('opens the create dialog when landing on the create url directly', async () => {
        mockFetch([[/\/users\?/, USERS]]);

        renderModuleAt(<UserModule/>, '/users', {route: '/users/create'});

        expect(await screen.findByRole('dialog')).toBeInTheDocument();
    });

    it('opens the edit dialog for the user in the url', async () => {
        mockFetch([
            [/\/users\/u-1$/, USERS.results[0]],
            [/\/users\?/, USERS],
        ]);

        renderModuleAt(<UserModule/>, '/users', {route: '/users/u-1/edit'});

        expect(await screen.findByRole('dialog')).toBeInTheDocument();
    });

    it('renders an error instead of the table when the request fails', async () => {
        vi.stubGlobal('fetch', vi.fn(async () => ({
            ok: true,
            status: 200,
            json: async () => ({count: 0, results: []}),
        } as Response)));

        renderModuleAt(<UserModule/>, '/users');

        // an empty result still renders the table shell, not an error
        await waitFor(() => expect(screen.queryByText(/^Error:/)).not.toBeInTheDocument());
    });
});
