import React, {useState} from 'react';
import {useQuery} from 'react-query';
import {useRouteMatch} from 'react-router-dom';

import {useApplication} from '../../application/ApplicationContext';
import ApplicationModuleContainer from '../../application/ApplicationModuleContainer';

const GreyListModule: React.VFC = () => {
    const {path, url} = useRouteMatch();

    const [currentIndex, setCurrentIndex] = useState<number>(0);
    const [currentMaxResults, setCurrentMaxResults] = useState<number>(20);

    const query = useQuery(['optin-domains?', currentIndex, currentMaxResults], () => {
        return fetch('/api/optin-domains')
            .then((res) => res.json());
    }, {keepPreviousData: true});
console.log(query);
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
