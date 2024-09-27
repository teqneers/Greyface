import React from 'react';
import {Helmet, HelmetProvider} from 'react-helmet-async';
import {BrowserRouter as Router} from 'react-router-dom';
import {QueryClient, QueryClientProvider} from 'react-query';
import {ReactQueryDevtools} from 'react-query/devtools';

import LoadingIndicator from '../controllers/LoadingIndicator';
import ApplicationContainer from './ApplicationContainer';
import {ApplicationConfigProps, ApplicationProvider} from './ApplicationContext';
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

                            <ReactQueryDevtools initialIsOpen={false}
                                                position="bottom-right"
                                                toggleButtonProps={{
                                                    style: {
                                                        right: -6,
                                                        bottom: 40,
                                                        zoom: 0.8
                                                    }
                                                }}/>
                        </QueryClientProvider>
                    </React.StrictMode>
                </Router>
            </HelmetProvider>
            </I18n>
        </React.StrictMode>
    );
}

export default Application;
