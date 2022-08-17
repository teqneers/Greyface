import React, {useContext} from 'react';

import {User} from '../types/user';

export interface ApplicationConfigProps {
    user: User
    apiUrl: string,
    logoutUrl: string,
    baseUrl: string,
    changePasswordUrl: string | null
}

const ApplicationContext = React.createContext<ApplicationConfigProps>({} as ApplicationConfigProps);

export function useApplication(): ApplicationConfigProps {
    return useContext(ApplicationContext);
}

export const ApplicationProvider: React.FC<ApplicationConfigProps> = ({children, ...rest}) => {
    return (
        <ApplicationContext.Provider value={rest}>
            {children}
        </ApplicationContext.Provider>
    );
};
