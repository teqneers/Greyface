import React from 'react';
import {Col, Container, Row} from 'react-bootstrap';
import {useApplication} from '../../application/ApplicationContext';

const DashboardModule: React.VFC = () => {

    const {changePasswordUrl, logoutUrl} = useApplication();
    return (
        <Container>
            <Row className="justify-content-md-center">
               <Col>  Hello <br/>
                   <a href={changePasswordUrl}>Change password</a>
                   <br/>
                   <a href={logoutUrl}>Logout</a></Col>
            </Row>
        </Container>
    );
};

export default DashboardModule;
