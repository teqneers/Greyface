import React from 'react';
import {Button} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';

interface DefaultButtonProps {
    label: string,
    variant?: string,
    onClick: () => void
}

function DefaultButton({label, variant, onClick}: DefaultButtonProps): React.ReactElement {
    const {t} = useTranslation();
    return (
        <Button variant={variant ? variant : 'outline-primary'} size="sm" className="m-1" onClick={onClick}>
            {t(label)}
        </Button>
    );
}

export default DefaultButton;
