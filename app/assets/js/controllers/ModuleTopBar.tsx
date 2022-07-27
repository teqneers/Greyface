import React from 'react';

import {Container, Form, Nav, Navbar} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';

export interface ModalProps {
    title: string,
    buttons?: React.ReactNode,
    setSearchQuery: (query: string) => void,
}

const ModalTopBar: React.FC<ModalProps> = ({title, buttons, setSearchQuery}) => {
    const {t} = useTranslation();

    return (
        <Navbar bg="light" expand="lg">
            <Container fluid>
                <Navbar.Brand>{t(title)}</Navbar.Brand>
                <Navbar.Toggle aria-controls="navbarScroll"/>
                <Navbar.Collapse id="navbarScroll">
                    <Nav className="me-auto my-2 my-lg-0">
                        {buttons}
                    </Nav>

                    <div className="d-flex">
                        <Form.Control
                            type="input"
                            name="search"
                            placeholder={t('placeholder.search')}
                            onChange={(e) => {
                                setSearchQuery(e.target.value);
                            }}/>
                    </div>
                </Navbar.Collapse>
            </Container>
        </Navbar>
    );
};

ModalTopBar.defaultProps = {
    title: '',
};

export default ModalTopBar;
