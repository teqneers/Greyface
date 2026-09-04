import React from 'react';
import {Helmet, HelmetProvider} from 'react-helmet-async';
import {BrowserRouter as Router} from 'react-router-dom';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import {ReactQueryDevtools} from '@tanstack/react-query-devtools';

import LoadingIndicator from '../controllers/LoadingIndicator';
import ApplicationContainer from './ApplicationContainer';
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
            <HelmetProvider>
                <Helmet defaultTitle={'Greyface by TEQneers GmbH & Co KG'}
                        titleTemplate={'%s | Greyface by TEQneers GmbH & Co KG'}/>
                <Router basename={baseUrl}>
                    <React.StrictMode>
                        <QueryClientProvider client={queryClient}>
                            <ApplicationProvider baseUrl={baseUrl} {...rest}>
                                <ApplicationContainer>
                                    <React.Suspense fallback={<LoadingIndicator/>}>
                                        <ApplicationRoutes/>
                                    </React.Suspense>
                                </ApplicationContainer>
                            </ApplicationProvider>

                            {/* v5 renamed the panel props; the floating trigger
                                is positioned with buttonPosition now. */}
                            <ReactQueryDevtools initialIsOpen={false}
                                                buttonPosition="bottom-right"/>
                        </QueryClientProvider>
                    </React.StrictMode>
                </Router>
            </HelmetProvider>
            </I18n>
        </React.StrictMode>
    );
}

export default Application;
