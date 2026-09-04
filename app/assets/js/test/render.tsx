import {render, RenderResult} from '@testing-library/react';
import i18n from 'i18next';
import React, {ReactElement} from 'react';
import type {ReactNode} from 'react';
import {initReactI18next} from 'react-i18next';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {MemoryRouter, Route, Routes} from 'react-router-dom';

import {ApplicationProvider} from '../application/ApplicationContext';
import type {User} from '../types/user';

import translation_en from '../../translations/en.json';

/**
 * The single place tests know about the router, the query client and the
 * application context. Library migrations change this file; the tests using it
 * do not have to.
 */

let i18nReady = false;

function initTestI18n(): void {
    if (i18nReady) {
        return;
    }
    // Real translations, so tests assert the labels users actually see rather
    // than raw translation keys.
    i18n.use(initReactI18next).init({
        resources: {en: translation_en},
        lng: 'en',
        fallbackLng: 'en',
        supportedLngs: ['en'],
        ns: [],
        defaultNS: 'common',
        keySeparator: '.',
        interpolation: {escapeValue: false},
    });
    i18nReady = true;
}

export function createTestUser(overrides: Partial<User> = {}): User {
    return {
        id: '11111111-1111-4111-8111-111111111111',
        username: 'admin',
        email: 'admin@greyface.test',
        role: 'admin',
        all_roles: ['user', 'admin'],
        is_administrator: true,
        is_deleted: false,
        ...overrides,
    } as User;
}

export interface RenderOptions {
    /** Initial router entry, e.g. '/users/create'. */
    route?: string;
    apiUrl?: string;
    user?: User;
}

export interface RenderWithProviders extends RenderResult {
    queryClient: QueryClient;
}

export function renderWithProviders(ui: ReactElement, options: RenderOptions = {}): RenderWithProviders {
    const {route = '/', apiUrl = 'https://greyface.test/api', user = createTestUser()} = options;

    initTestI18n();

    const queryClient = new QueryClient({
        defaultOptions: {
            // A failing request should surface immediately instead of being
            // retried until the test times out.
            queries: {retry: false},
            mutations: {retry: false},
        },
    });

    const Wrapper = ({children}: { children?: ReactNode }): ReactElement => (
        // Mirrors the provider stack in Application.tsx.
        <MemoryRouter initialEntries={[route]}>
            <QueryClientProvider client={queryClient}>
                <ApplicationProvider
                    user={user}
                    apiUrl={apiUrl}
                    baseUrl="/"
                    logoutUrl="/logout"
                    changePasswordUrl="/password/change"
                >
                    {children}
                </ApplicationProvider>
            </QueryClientProvider>
        </MemoryRouter>
    );

    const result: RenderResult = render(ui, {wrapper: Wrapper});

    return {
        ...result,
        queryClient,
    };
}

/**
 * Stubs global fetch with a table of URL matchers. Anything unmatched fails
 * loudly rather than returning undefined and surfacing as a confusing render
 * error three layers down.
 */
export function mockFetch(routes: Array<[RegExp | string, unknown, number?]>): ReturnType<typeof vi.fn> {
    const impl = vi.fn(async (input: RequestInfo | URL) => {
        const url = typeof input === 'string' ? input : input.toString();
        for (const [matcher, body, status = 200] of routes) {
            const hit = typeof matcher === 'string' ? url.includes(matcher) : matcher.test(url);
            if (hit) {
                return {
                    ok: status < 400,
                    status,
                    json: async () => body,
                    text: async () => JSON.stringify(body),
                } as Response;
            }
        }
        throw new Error(`Unexpected fetch: ${url}`);
    });

    vi.stubGlobal('fetch', impl);

    return impl;
}

/**
 * Renders a page module the way the application does — mounted on a splat route
 * under its own base path — so that useParams() resolves and the module's nested
 * create/edit/delete routes match.
 *
 * The splat matters: a module owns a subtree, and without "/*" react-router
 * matches only the exact base path and every nested dialog route goes dead.
 */
export function renderModuleAt(
    ui: ReactElement,
    basePath: string,
    options: Omit<RenderOptions, 'route'> & { route?: string } = {}
): RenderWithProviders {
    const {route = basePath, ...rest} = options;

    return renderWithProviders(
        <Routes>
            <Route path={`${basePath}/*`} element={ui}/>
        </Routes>,
        {...rest, route}
    );
}
