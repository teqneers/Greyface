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
        {id: 'u-1', username: 'alice', email: 'alice@greyface.test', role: 'user', is_administrator: false, is_deleted: false, created_at: '2024-01-01T00:00:00+00:00'},
        {id: 'u-2', username: 'bob', email: 'bob@greyface.test', role: 'admin', is_administrator: true, is_deleted: false, created_at: '2024-01-02T00:00:00+00:00'},
    ],
};

/**
 * One full CRUD screen, exercised through the seams library migrations touch:
 * list state turning into a request URL, and the nested routes that drive
 * the create/edit/password dialogs.
 */
describe('UserModule', () => {
    beforeEach(() => {
        initSettings();
        window.sessionStorage.clear();
    });

    it('lists the users returned by the api', async () => {
        mockFetch([[/\/users\?/, USERS]]);

        renderModuleAt(<UserModule/>, '/users');

        await waitFor(() => {
            expect(screen.getByText('alice')).toBeInTheDocument();
            expect(screen.getByText('bob@greyface.test')).toBeInTheDocument();
        });
    });

    it('builds the request from the list state', async () => {
        const fetchMock = mockFetch([[/\/users\?/, USERS]]);

        renderModuleAt(<UserModule/>, '/users');
        await waitFor(() => expect(screen.getByText('alice')).toBeInTheDocument());

        const url = new URL(fetchMock.mock.calls[0][0] as string);
        expect(url.pathname).toBe('/api/users');
        expect(url.searchParams.get('start')).toBe('0');
        expect(url.searchParams.get('max')).toBe('10');
        expect(url.searchParams.get('query')).toBe('');
        expect(url.searchParams.get('sortBy')).toBe('username');
        expect(url.searchParams.get('desc')).toBe('0');
    });

    it('refetches with the search query when it changes', async () => {
        const fetchMock = mockFetch([[/\/users\?/, USERS]]);

        renderModuleAt(<UserModule/>, '/users');
        await waitFor(() => expect(screen.getByText('alice')).toBeInTheDocument());

        await userEvent.type(screen.getByRole('searchbox'), 'ali');

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

        expect(await screen.findByRole('dialog', {name: /create new user/i})).toBeInTheDocument();
    });

    it('opens the create dialog when landing on the create url directly', async () => {
        mockFetch([[/\/users\?/, USERS]]);

        renderModuleAt(<UserModule/>, '/users', {route: '/users/create'});

        expect(await screen.findByRole('dialog', {name: /create new user/i})).toBeInTheDocument();
    });

    it('opens the edit dialog for the user in the url with their data', async () => {
        mockFetch([
            [/\/users\/u-1$/, USERS.results[0]],
            [/\/users\?/, USERS],
        ]);

        renderModuleAt(<UserModule/>, '/users', {route: '/users/u-1/edit'});

        expect(await screen.findByRole('dialog', {name: /edit user/i})).toBeInTheDocument();
        await waitFor(() => expect(screen.getByLabelText('Username')).toHaveValue('alice'));
    });

    it('renders the empty state, not an error, for an empty result', async () => {
        vi.stubGlobal('fetch', vi.fn(async () => ({
            ok: true,
            status: 200,
            text: async () => JSON.stringify({count: 0, results: []}),
        } as Response)));

        renderModuleAt(<UserModule/>, '/users');

        expect(await screen.findByText('No Data')).toBeInTheDocument();
        expect(screen.queryByText(/could not be loaded/)).not.toBeInTheDocument();
    });
});
