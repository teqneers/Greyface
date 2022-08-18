import React from 'react';
import {Container, Form, Nav, Navbar} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';

export interface ModalProps {
    title: string,
    buttons?: React.ReactNode,
    userFilter?: React.ReactNode,
    searchQuery?: string,
    setSearchQuery: (query: string) => void,
}

const ModalTopBar: React.FC<ModalProps> = (
    {
        title,
        buttons,
        userFilter,
        searchQuery = '',
        setSearchQuery
    }) => {
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

                    <div className="d-flex gap-2">
                        {userFilter}
                        <Form.Label column>{t('placeholder.searchByText')}</Form.Label>
                        <Form.Control
                            type="input"
                            name="search"
                            placeholder={t('placeholder.search')}
                            value={searchQuery}
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
