import React from 'react';
import {Navigate, Route, Routes} from 'react-router-dom';

import {RequireAdmin} from '@/components/RequireAdmin';

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
 * renders the nested create/edit/delete routes for its own rows. Everything
 * but the greylist is admin-only and wrapped in the route guard.
 */
function ApplicationRoutes(): React.ReactElement {
    return (
        <Routes>
            <Route path="/" element={<Navigate to="/greylist" replace/>}/>

            <Route path="/greylist/*" element={<GreyListModule/>}/>
            <Route path="/users/*" element={<RequireAdmin><UserModule/></RequireAdmin>}/>
            <Route path="/users-aliases/*" element={<RequireAdmin><UserAliasModule/></RequireAdmin>}/>

            <Route path="/awl/emails/*" element={<RequireAdmin><AutoWhitelistEmailModule/></RequireAdmin>}/>
            <Route path="/awl/domains/*" element={<RequireAdmin><AutoWhitelistDomainModule/></RequireAdmin>}/>

            <Route path="/opt-out/emails/*" element={<RequireAdmin><WhitelistEmailModule/></RequireAdmin>}/>
            <Route path="/opt-out/domains/*" element={<RequireAdmin><WhitelistDomainModule/></RequireAdmin>}/>

            <Route path="/opt-in/emails/*" element={<RequireAdmin><BlacklistEmailModule/></RequireAdmin>}/>
            <Route path="/opt-in/domains/*" element={<RequireAdmin><BlacklistDomainModule/></RequireAdmin>}/>

            <Route path="*" element={<Navigate to="/greylist" replace/>}/>
        </Routes>
    );
}

export default ApplicationRoutes;
