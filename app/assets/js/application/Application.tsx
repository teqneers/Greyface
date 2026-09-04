import React from 'react';
import {BrowserRouter as Router} from 'react-router-dom';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {ReactQueryDevtools} from '@tanstack/react-query-devtools';

import {AppShell} from '@/components/layout/AppShell';
import {Toaster} from '@/components/ui/sonner';
import {TooltipProvider} from '@/components/ui/tooltip';
import LoadingIndicator from '@/components/LoadingIndicator';
import {ApplicationProvider} from './ApplicationContext';
import type {ApplicationConfigProps} from './ApplicationContext';
import ApplicationRoutes from './ApplicationRoutes';
import I18n from './i18n';

export interface ApplicationProps extends ApplicationConfigProps {
    baseUrl: string
}

const queryClient = new QueryClient();

function Application({baseUrl, ...rest}: ApplicationProps): React.ReactElement {
    return (
        <React.StrictMode>
            <I18n>
                <Router basename={baseUrl}>
                    <QueryClientProvider client={queryClient}>
                        <ApplicationProvider baseUrl={baseUrl} {...rest}>
                            <TooltipProvider>
                                <AppShell>
                                    <React.Suspense fallback={<LoadingIndicator/>}>
                                        <ApplicationRoutes/>
                                    </React.Suspense>
                                </AppShell>
                                <Toaster position="bottom-right"/>
                            </TooltipProvider>
                        </ApplicationProvider>

                        {import.meta.env.DEV && (
                            <ReactQueryDevtools initialIsOpen={false} buttonPosition="bottom-left"/>
                        )}
                    </QueryClientProvider>
                </Router>
            </I18n>
        </React.StrictMode>
    );
}

export default Application;
