import React from 'react';

import {useApplication} from '../../application/ApplicationContext';
import ApplicationModuleContainer from '../../application/ApplicationModuleContainer';

const GreyListModule: React.VFC = () => {

    const {changePasswordUrl, logoutUrl} = useApplication();
    return (
        <ApplicationModuleContainer title="greylist.header">
               <div>  Hello <br/>
                   <a href={changePasswordUrl}>Change password</a>
                   <br/>
                   <a href={logoutUrl}>Logout</a>
               </div>
        </ApplicationModuleContainer>
    );
};

export default GreyListModule;
