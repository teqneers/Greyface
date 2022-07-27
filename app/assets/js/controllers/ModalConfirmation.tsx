import React from 'react';

import {Button, Modal} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';

export interface ModalProps {
    title: string,
    onCancel: () => void,
    onConfirm: () => void,
}

const ModalConfirmation: React.FC<ModalProps> = (
    {
        title,
        onCancel,
        onConfirm,
        children
    }) => {

    const {t} = useTranslation();

    return (
        <Modal show={true} onHide={() => onCancel()}>

            <Modal.Header closeButton>
                <Modal.Title>{t(title)}</Modal.Title>
            </Modal.Header>

            <Modal.Body>
                {children}
            </Modal.Body>

            <Modal.Footer>
                <Button variant="outline-secondary" onClick={() => onCancel()}>
                    {t('button.cancel')}
                </Button>
                <Button variant="outline-danger" onClick={() => onConfirm()}>
                    {t('button.delete')}
                </Button>
            </Modal.Footer>

        </Modal>
    );
}

ModalConfirmation.defaultProps = {
    title: '',
};

export default ModalConfirmation;
