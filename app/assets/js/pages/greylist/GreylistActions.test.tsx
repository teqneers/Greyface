import {screen, waitFor} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import React from 'react';
import {beforeEach, describe, expect, it, vi} from 'vitest';

let fetchMock: ReturnType<typeof mockFetch>;

import {createTestUser, mockFetch, renderWithProviders} from '../../test/render';
import {GreylistRowActions} from './GreylistActions';
import type {Greylist} from '@/types/greylist';

vi.mock('sonner', () => ({toast: {success: vi.fn(), error: vi.fn()}}));

const row: Greylist = {
    connect: {
        name: 'shop',
        domain: 'example.com',
        source: '198.51.100',
        rcpt: 'info@greyface.de',
        firstSeen: {date: '2026-09-05 10:00:00.000000', timezone_type: 3, timezone: 'UTC'},
    },
    aliasName: null,
    username: null,
    userID: null,
} as unknown as Greylist;

function renderActions(isAdmin: boolean) {
    return renderWithProviders(<GreylistRowActions row={row}/>, {
        user: createTestUser({is_administrator: isAdmin, role: isAdmin ? 'admin' : 'user'}),
    });
}

describe('greylist row actions', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        fetchMock = mockFetch([[/./, {moved: 1, target: 'whitelist-email', entries: []}]]);
    });

    /**
     * Every list the menu writes to is administrators-only on the server, so
     * showing it to a user would be offering them a guaranteed 403.
     */
    it('offers the other destinations only to administrators', async () => {
        const {unmount} = renderActions(false);
        expect(screen.getByRole('button', {name: /auto whitelist/i})).toBeInTheDocument();
        expect(screen.queryByRole('button', {name: /other destinations/i})).not.toBeInTheDocument();
        unmount();

        renderActions(true);
        expect(screen.getByRole('button', {name: /other destinations/i})).toBeInTheDocument();
    });

    it('keeps the everyday action a single click for administrators too', async () => {
        renderActions(true);
        // The split button's primary half is still a plain button, not a menu.
        const primary = screen.getByRole('button', {name: /auto whitelist/i});
        expect(primary).not.toHaveAttribute('aria-haspopup');
    });

    it('sends a whitelisted sender straight off, without asking', async () => {
        const user = userEvent.setup();
        renderActions(true);

        await user.click(screen.getByRole('button', {name: /other destinations/i}));
        await user.click(await screen.findByRole('menuitem', {name: 'Never greylist shop@example.com'}));

        await waitFor(() => expect(fetchMock).toHaveBeenCalled());
        const [url, init] = fetchMock.mock.calls.at(-1)!;
        expect(String(url)).toContain('/greylist/toList');
        expect(JSON.parse(String((init as RequestInit).body))).toMatchObject({target: 'whitelist-email'});
    });

    /**
     * Blacklisting starts delaying mail that is arriving fine, so it asks first
     * and must not have called the server before the operator agrees.
     */
    it('asks before blacklisting, and does not act until confirmed', async () => {
        const user = userEvent.setup();
        renderActions(true);

        await user.click(screen.getByRole('button', {name: /other destinations/i}));
        await user.click(await screen.findByRole('menuitem', {name: 'Always greylist shop@example.com'}));

        expect(await screen.findByRole('alertdialog')).toBeInTheDocument();
        expect(fetchMock).not.toHaveBeenCalled();
    });
});
