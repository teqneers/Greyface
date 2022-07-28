import React from 'react';
import {Button} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';

interface CancelButtonProps {
    label?: string,
    onClick: () => void
}

function CancelButton({label, onClick}: CancelButtonProps): React.ReactElement {
    const {t} = useTranslation();
    return (
        <Button variant="outline-secondary" className="m-1" size="sm" onClick={onClick}>
            {t(label ? label : 'button.cancel')}
        </Button>
    );
}

export default CancelButton;
