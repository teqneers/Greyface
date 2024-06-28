import React from 'react';
import Button from 'react-bootstrap/Button';
import {useTranslation} from 'react-i18next';

interface DeleteButtonProps {
    label?: string,
    onClick: () => void
}

function DeleteButton({label, onClick}: DeleteButtonProps): React.ReactElement {
    const {t} = useTranslation();
    return (
        <Button variant="outline-danger" className="m-1" size="sm" onClick={onClick}>
            {t(label ? label : 'button.delete')}
        </Button>
    );
}

export default DeleteButton;
