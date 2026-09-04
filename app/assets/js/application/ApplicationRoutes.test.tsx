import {screen} from '@testing-library/react';
import React from 'react';
import {describe, expect, it, vi} from 'vitest';

import {renderWithProviders} from '../test/render';
import ApplicationRoutes from './ApplicationRoutes';

// Every route target is lazy-loaded and each real module fetches on mount, so
// they are replaced with markers. What is under test here is the URL-to-module
// map itself — precisely what a router migration can silently rewire.
const stub = (name: string) => ({
    __esModule: true,
    default: () => <div data-testid="module">{name}</div>,
});

vi.mock('../pages/greylist/GreyListModule', () => stub('greylist'));
vi.mock('../pages/users/UserModule', () => stub('users'));
vi.mock('../pages/usersAlias/UserAliasModule', () => stub('users-aliases'));
vi.mock('../pages/lists/WhitelistModule', () => stub('whitelist'));
vi.mock('../pages/lists/BlacklistModule', () => stub('blacklist'));
vi.mock('../pages/lists/AutoWhitelistModule', () => stub('auto-whitelist'));

async function renderAt(route: string): Promise<string> {
    renderWithProviders(
        <React.Suspense fallback={<div>loading</div>}>
            <ApplicationRoutes/>
        </React.Suspense>,
        {route}
    );

    return (await screen.findByTestId('module')).textContent ?? '';
}

describe('ApplicationRoutes', () => {
    it.each([
        ['/greylist', 'greylist'],
        ['/users', 'users'],
        ['/users-aliases', 'users-aliases'],
        ['/whitelist/emails', 'whitelist'],
        ['/blacklist/domains/create', 'blacklist'],
        ['/auto-whitelist', 'auto-whitelist'],
    ])('routes %s to the %s module', async (route, expected) => {
        expect(await renderAt(route)).toBe(expected);
    });

    it.each([
        ['/opt-out/emails', 'whitelist'],
        ['/opt-in/domains', 'blacklist'],
        ['/awl/emails', 'auto-whitelist'],
    ])('redirects the old path %s to the %s module', async (route, expected) => {
        expect(await renderAt(route)).toBe(expected);
    });

    it('redirects the root to the greylist', async () => {
        expect(await renderAt('/')).toBe('greylist');
    });

    it('sends an unknown path back to the greylist', async () => {
        expect(await renderAt('/does-not-exist')).toBe('greylist');
    });

    // Nested paths must stay on their parent module: the module itself renders
    // the create/edit/delete dialogs as child routes.
    it.each([
        ['/users/create', 'users'],
        ['/users/some-id/edit', 'users'],
        ['/auto-whitelist/domains/create', 'auto-whitelist'],
        ['/blacklist/emails/create', 'blacklist'],
    ])('keeps %s on the %s module', async (route, expected) => {
        expect(await renderAt(route)).toBe(expected);
    });
});
