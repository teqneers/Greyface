import {screen, waitFor} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';
import {describe, expect, it, vi} from 'vitest';

import {mockFetch, renderWithProviders} from '../test/render';
import UserFilter from './UserFilter';

const USERS = {
    count: 2,
    results: [
        {id: 'u-1', username: 'alice'},
        {id: 'u-2', username: 'bob'},
    ],
};

describe('UserFilter', () => {
    it('loads the user list and offers one option per user', async () => {
        mockFetch([[/\/users$/, USERS]]);

        renderWithProviders(<UserFilter user="" setUser={vi.fn()}/>);

        expect(await screen.findByRole('option', {name: 'alice'})).toBeInTheDocument();
        expect(screen.getByRole('option', {name: 'bob'})).toBeInTheDocument();
    });

    it('requests the users endpoint under the configured api url', async () => {
        const fetchMock = mockFetch([[/\/users$/, USERS]]);

        renderWithProviders(<UserFilter user="" setUser={vi.fn()}/>, {apiUrl: 'https://example.test/api'});

        await screen.findByRole('option', {name: 'alice'});
        expect(fetchMock).toHaveBeenCalledWith('https://example.test/api/users');
    });

    it('always offers "show all"', async () => {
        mockFetch([[/\/users$/, USERS]]);

        renderWithProviders(<UserFilter user="" setUser={vi.fn()}/>);

        const showAll = await screen.findByRole('option', {name: 'Show All'});
        expect(showAll).toHaveValue('');
    });

    it('offers the unassigned bucket only on the greylist', async () => {
        mockFetch([[/\/users$/, USERS]]);

        const {unmount} = renderWithProviders(<UserFilter user="" setUser={vi.fn()} filterFor="greylist"/>);
        expect(await screen.findByRole('option', {name: 'Show Unassigned'})).toHaveValue('show_unassigned');
        unmount();

        renderWithProviders(<UserFilter user="" setUser={vi.fn()} filterFor="userAlias"/>);
        await screen.findByRole('option', {name: 'alice'});
        expect(screen.queryByRole('option', {name: 'Show Unassigned'})).not.toBeInTheDocument();
    });

    it('reports the selected user id to its parent', async () => {
        mockFetch([[/\/users$/, USERS]]);
        const setUser = vi.fn();

        renderWithProviders(<UserFilter user="" setUser={setUser}/>);
        await screen.findByRole('option', {name: 'alice'});

        await userEvent.selectOptions(screen.getByRole('combobox'), 'u-2');

        expect(setUser).toHaveBeenCalledWith('u-2');
    });

    it('shows a loading indicator until the users arrive', async () => {
        let release: (v: unknown) => void = () => undefined;
        vi.stubGlobal('fetch', vi.fn(() => new Promise((resolve) => {
            release = () => resolve({ok: true, status: 200, json: async () => USERS} as Response);
        })));

        renderWithProviders(<UserFilter user="" setUser={vi.fn()}/>);

        expect(screen.queryByRole('combobox')).not.toBeInTheDocument();

        release(undefined);
        await waitFor(() => expect(screen.getByRole('combobox')).toBeInTheDocument());
    });
});
