import React from 'react';
import {Button} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';

interface SubmitButtonProps {
    label: string,
    variant?: string,
    disabled?: boolean,
    type?: string,
    onClick?: () => void
}

type ButtonTypes = 'button' | 'submit' | 'reset';

function SubmitButton({
                           label,
                           variant,
                           disabled = false,
                           type = 'submit',
                           onClick
                       }: SubmitButtonProps): React.ReactElement {
    const {t} = useTranslation();

    return (
        <Button variant={variant ? variant : 'outline-primary'}
                size="sm"
                className="m-1" onClick={onClick ? onClick : null}
                disabled={disabled}
                type={type as ButtonTypes}>
            {t(label)}
        </Button>
    );
}

export default SubmitButton;
