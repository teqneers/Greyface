import React from 'react';

import {Button, Modal} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';

export interface ModalProps {
    show?: boolean,
    title: string,
    onCancel: () => void,
    onConfirm: () => void,
    confirmBtn?: string,
    confirmBtnVariant?: string
}

const ModalConfirmation: React.FC<ModalProps> = (
    {
        show = true,
        title,
        onCancel,
        onConfirm,
        confirmBtn,
        confirmBtnVariant,
        children
    }) => {

    const {t} = useTranslation();

    return (
        <Modal show={show} onHide={() => onCancel()}>

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
                <Button variant={confirmBtnVariant ? confirmBtnVariant : 'outline-danger'}
                        onClick={() => onConfirm()}>
                    {t(confirmBtn ? confirmBtn : 'button.delete')}
                </Button>
            </Modal.Footer>

        </Modal>
    );
};

ModalConfirmation.defaultProps = {
    title: '',
};

export default ModalConfirmation;
