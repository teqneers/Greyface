import React from 'react';

import {Modal} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';

export interface ModalProps {
    title: string,
    onHide: () => void,
}

const ModalForm: React.FC<ModalProps> = ({title, onHide, children}) => {
    const {t} = useTranslation();

    return (
        <Modal show={true} onHide={() => onHide()}>
            <Modal.Header closeButton>
                <Modal.Title>{t(title)}</Modal.Title>
            </Modal.Header>
            <Modal.Body>
                {children}
            </Modal.Body>
        </Modal>
    );
};

ModalForm.defaultProps = {
    title: '',
};

export default ModalForm;
