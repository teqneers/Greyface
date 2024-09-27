import React from 'react';

import {Modal} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import CancelButton from './Buttons/CancelButton';
import DefaultButton from './Buttons/DefaultButton';

export interface ModalProps {
    show?: boolean,
    title: string,
    onCancel: () => void,
    onConfirm: () => void,
    confirmBtn?: string,
    confirmBtnVariant?: string,
    children?: React.ReactNode,
}

const ModalConfirmation: React.FC<ModalProps> = (
    {
        show = true,
        title= '',
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
                <CancelButton onClick={() => onCancel()}/>
                <DefaultButton
                    label={confirmBtn ? confirmBtn : 'button.delete'}
                    variant={confirmBtnVariant ? confirmBtnVariant : 'outline-danger'}
                    onClick={() => onConfirm()}/>
            </Modal.Footer>

        </Modal>
    );
};


export default ModalConfirmation;
