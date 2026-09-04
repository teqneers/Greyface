import React from 'react';
import {Navigate, Route, Routes, useLocation} from 'react-router-dom';

import {RequireAdmin} from '@/components/RequireAdmin';

const GreyListModule = React.lazy(() => import('../pages/greylist/GreyListModule'));
const UserModule = React.lazy(() => import('../pages/users/UserModule'));
const UserAliasModule = React.lazy(() => import('../pages/usersAlias/UserAliasModule'));
const WhitelistModule = React.lazy(() => import('../pages/lists/WhitelistModule'));
const BlacklistModule = React.lazy(() => import('../pages/lists/BlacklistModule'));
const AutoWhitelistModule = React.lazy(() => import('../pages/lists/AutoWhitelistModule'));

/** Old bookmarks used SQLGrey's opt-in/opt-out naming; keep them working. */
const LEGACY_PREFIXES: Record<string, string> = {
    '/opt-out': '/whitelist',
    '/opt-in': '/blacklist',
    '/awl': '/auto-whitelist',
};

function LegacyRedirect({prefix}: { prefix: string }): React.ReactElement {
    const {pathname, search} = useLocation();
    return <Navigate to={pathname.replace(prefix, LEGACY_PREFIXES[prefix]) + search} replace/>;
}

/**
 * Every module owns a subtree, so each path ends in "/*": the module itself
 * renders its nested routes (tabs, create dialogs). Everything but the
 * greylist is admin-only and wrapped in the route guard.
 */
function ApplicationRoutes(): React.ReactElement {
    return (
        <Routes>
            <Route path="/" element={<Navigate to="/greylist" replace/>}/>

            <Route path="/greylist/*" element={<GreyListModule/>}/>
            <Route path="/users/*" element={<RequireAdmin><UserModule/></RequireAdmin>}/>
            <Route path="/users-aliases/*" element={<RequireAdmin><UserAliasModule/></RequireAdmin>}/>

            <Route path="/whitelist/*" element={<RequireAdmin><WhitelistModule/></RequireAdmin>}/>
            <Route path="/blacklist/*" element={<RequireAdmin><BlacklistModule/></RequireAdmin>}/>
            <Route path="/auto-whitelist/*" element={<RequireAdmin><AutoWhitelistModule/></RequireAdmin>}/>

            {Object.keys(LEGACY_PREFIXES).map((prefix) => (
                <Route key={prefix} path={`${prefix}/*`} element={<LegacyRedirect prefix={prefix}/>}/>
            ))}

            <Route path="*" element={<Navigate to="/greylist" replace/>}/>
        </Routes>
    );
}

export default ApplicationRoutes;
