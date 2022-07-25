import React from 'react';
import {Redirect, Route, Switch} from 'react-router-dom';

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

            <Route><Redirect to="/"/></Route>
        </Switch>
    );
}

export default ApplicationRoutes;
