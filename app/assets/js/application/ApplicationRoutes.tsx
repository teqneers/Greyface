import React from 'react';
import {Navigate, Route, Routes} from 'react-router-dom';

const GreyListModule =  React.lazy(() => import('../pages/greylist/GreyListModule'));
const UserModule =  React.lazy(() => import('../pages/users/UserModule'));
const UserAliasModule =  React.lazy(() => import('../pages/usersAlias/UserAliasModule'));
const BlacklistDomainModule =  React.lazy(() => import('../pages/Blacklist/blacklistDomain/BlacklistDomainModule'));
const BlacklistEmailModule =  React.lazy(() => import('../pages/Blacklist/blacklistEmail/BlacklistEmailModule'));
const WhitelistDomainModule =  React.lazy(() => import('../pages/Whitelist/whitelistDomain/WhitelistDomainModule'));
const WhitelistEmailModule =  React.lazy(() => import('../pages/Whitelist/whitelistEmail/WhitelistEmailModule'));
const AutoWhitelistDomainModule =  React.lazy(() => import('../pages/AutoWhitelist/autoWhitelistDomain/AutoWhitelistDomainModule'));
const AutoWhitelistEmailModule =  React.lazy(() => import('../pages/AutoWhitelist/autoWhitelistEmail/AutoWhitelistEmailModule'));

/**
 * Every module owns a subtree, so each path ends in "/*": the module itself
 * renders the nested create/edit/delete routes for its own rows.
 */
function ApplicationRoutes(): React.ReactElement {
    return (
        <Routes>
            <Route path="/" element={<Navigate to="/greylist" replace/>}/>

            <Route path="/greylist/*" element={<GreyListModule/>}/>
            <Route path="/users/*" element={<UserModule/>}/>
            <Route path="/users-aliases/*" element={<UserAliasModule/>}/>

            <Route path="/awl/emails/*" element={<AutoWhitelistEmailModule/>}/>
            <Route path="/awl/domains/*" element={<AutoWhitelistDomainModule/>}/>

            <Route path="/opt-out/emails/*" element={<WhitelistEmailModule/>}/>
            <Route path="/opt-out/domains/*" element={<WhitelistDomainModule/>}/>

            <Route path="/opt-in/emails/*" element={<BlacklistEmailModule/>}/>
            <Route path="/opt-in/domains/*" element={<BlacklistDomainModule/>}/>

            <Route path="*" element={<Navigate to="/greylist" replace/>}/>
        </Routes>
    );
}

export default ApplicationRoutes;
