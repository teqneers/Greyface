import React from 'react';

import {Modal} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';

export interface ModalProps {
    show?: boolean,
    title: string,
    onHide: () => void,
}

const ModalForm: React.FC<ModalProps> = (
    {
        show = true, title, onHide, children
    }) => {
    const {t} = useTranslation();

    return (
        <Modal show={show} onHide={() => onHide()}>
            <Modal.Header closeButton>
                <Modal.Title>{t(title)}</Modal.Title>
            </Modal.Header>

            {children}
        </Modal>
    );
};

ModalForm.defaultProps = {
    title: '',
};

export default ModalForm;
