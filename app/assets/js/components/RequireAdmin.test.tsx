import {screen} from '@testing-library/react';
import React from 'react';
import {describe, expect, it, vi} from 'vitest';
import {Route, Routes} from 'react-router-dom';

import {createTestUser, renderWithProviders} from '../test/render';
import {RequireAdmin} from './RequireAdmin';

vi.mock('sonner', () => ({toast: {error: vi.fn()}}));

import {toast} from 'sonner';

function renderGuardedAt(route: string, isAdmin: boolean) {
    return renderWithProviders(
        <Routes>
            <Route path="/greylist" element={<div>greylist</div>}/>
            <Route path="/users" element={<RequireAdmin><div>users</div></RequireAdmin>}/>
        </Routes>,
        {route, user: createTestUser({is_administrator: isAdmin, role: isAdmin ? 'admin' : 'user'})},
    );
}

describe('RequireAdmin', () => {
    it('renders the protected screen for administrators', async () => {
        renderGuardedAt('/users', true);
        expect(await screen.findByText('users')).toBeInTheDocument();
        expect(toast.error).not.toHaveBeenCalled();
    });

    it('redirects everyone else to the greylist and says why', async () => {
        renderGuardedAt('/users', false);
        expect(await screen.findByText('greylist')).toBeInTheDocument();
        expect(screen.queryByText('users')).not.toBeInTheDocument();
        expect(toast.error).toHaveBeenCalledWith('You are not allowed to open that page.', expect.anything());
    });
});
