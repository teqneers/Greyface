import React from 'react';

import Spinner from 'react-bootstrap/Spinner';

export interface LoadingIndicatorProps {
    text?: string,
}

function LoadingIndicator(
    {
        text = 'Loading'
    }: LoadingIndicatorProps
): React.ReactElement {

    return (
        <div className="spinner">
            <Spinner animation="border" variant="primary" className="m-1"/>{text}
        </div>
    );
}


export default LoadingIndicator;
