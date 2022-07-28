import React from 'react';
import {Redirect, Route, Switch} from 'react-router-dom';
import WhitelistDomainModule from '../pages/Whitelist/whitelistDomain/WhitelistDomainModule';
import WhitelistEmailModule from '../pages/Whitelist/whitelistEmail/WhitelistEmailModule';

const GreyListModule =  React.lazy(() => import('../pages/greylist/GreyListModule'));
const UserModule =  React.lazy(() => import('../pages/users/UserModule'));
const UserAliasModule =  React.lazy(() => import('../pages/usersAlias/UserAliasModule'));

function ApplicationRoutes(): React.ReactElement {
    return (
        <Switch>
            <Redirect from="/" exact to="/greylist"/>
            <Route path="/greylist">
                <GreyListModule/>
            </Route>
            <Route path="/users">
                <UserModule/>
            </Route>

            <Route path="/users-aliases">
                <UserAliasModule/>
            </Route>


            <Route path="/opt-in/emails">
                <WhitelistEmailModule/>
            </Route>

            <Route path="/opt-in/domains">
                <WhitelistDomainModule/>
            </Route>

            <Route><Redirect to="/"/></Route>
        </Switch>
    );
}

export default ApplicationRoutes;
