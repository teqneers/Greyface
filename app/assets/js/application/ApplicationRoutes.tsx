import React from 'react';
import {Redirect, Route, Switch} from 'react-router-dom';

const GreyListModule =  React.lazy(() => import('../pages/greylist/GreyListModule'));
const UserModule =  React.lazy(() => import('../pages/users/UserModule'));
const UserAliasModule =  React.lazy(() => import('../pages/usersAlias/UserAliasModule'));
const BlacklistDomainModule =  React.lazy(() => import('../pages/Blacklist/blacklistDomain/BlacklistDomainModule'));
const BlacklistEmailModule =  React.lazy(() => import('../pages/Blacklist/blacklistEmail/BlacklistEmailModule'));
const WhitelistDomainModule =  React.lazy(() => import('../pages/Whitelist/whitelistDomain/WhitelistDomainModule'));
const WhitelistEmailModule =  React.lazy(() => import('../pages/Whitelist/whitelistEmail/WhitelistEmailModule'));
const AutoWhitelistDomainModule =  React.lazy(() => import('../pages/AutoWhitelist/autoWhitelistDomain/AutoWhitelistDomainModule'));
const AutoWhitelistEmailModule =  React.lazy(() => import('../pages/AutoWhitelist/autoWhitelistEmail/AutoWhitelistEmailModule'));

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


            <Route path="/awl/emails">
                <AutoWhitelistEmailModule/>
            </Route>

            <Route path="/awl/domains">
                <AutoWhitelistDomainModule/>
            </Route>

            <Route path="/opt-out/emails">
                <WhitelistEmailModule/>
            </Route>

            <Route path="/opt-out/domains">
                <WhitelistDomainModule/>
            </Route>


            <Route path="/opt-in/emails">
                <BlacklistEmailModule/>
            </Route>

            <Route path="/opt-in/domains">
                <BlacklistDomainModule/>
            </Route>

            <Route><Redirect to="/"/></Route>
        </Switch>
    );
}

export default ApplicationRoutes;
