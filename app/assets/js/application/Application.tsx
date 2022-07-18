import React from 'react';
import {ThemeProvider} from 'react-bootstrap';
import {hot} from 'react-hot-loader/root';
import {Helmet, HelmetProvider} from 'react-helmet-async';
import {BrowserRouter as Router} from 'react-router-dom';
import {QueryClient, QueryClientProvider} from 'react-query';
import {ReactQueryDevtools} from 'react-query/devtools';

import {ApplicationConfigProps, ApplicationProvider} from './ApplicationContext';
import ApplicationRoutes from './ApplicationRoutes';

export interface ApplicationProps extends ApplicationConfigProps {
    baseUrl: string
}

const queryClient = new QueryClient();

function Application({baseUrl, ...rest}: ApplicationProps): React.ReactElement {
    return (
        <ThemeProvider
            breakpoints={['xxxl', 'xxl', 'xl', 'lg', 'md', 'sm', 'xs', 'xxs']}
            minBreakpoint="xxs">
            <React.StrictMode>
                    {/* @ts-ignore */}
                    <HelmetProvider>
                        {/* @ts-ignore */}
                        <Helmet defaultTitle={'Greyface by TEQneers GmbH & Co KG'}
                                titleTemplate={'%s | Greyface by TEQneers GmbH & Co KG'}/>
                        <Router basename={baseUrl}>
                            <React.StrictMode>
                                    <QueryClientProvider client={queryClient}>
                                        <ApplicationProvider {...rest}>
                                                <React.Suspense fallback={''}>
                                                    <ApplicationRoutes/>
                                                </React.Suspense>
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
            </React.StrictMode>
        </ThemeProvider>
    );
}

export default hot(Application);
