import React from 'react';
import {Redirect, Route, Switch} from 'react-router-dom';
import DashboardModule from '../pages/dashboard/DashboardModule';

function ApplicationRoutes(): React.ReactElement {
    return (
        <Switch>
            <Redirect from="/" exact to="/dashboard"/>
            <Route path="/dashboard">
                <DashboardModule/>
            </Route>

            <Route><Redirect to="/"/></Route>
        </Switch>
    );
}

export default ApplicationRoutes;
