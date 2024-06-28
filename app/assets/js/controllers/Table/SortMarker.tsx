import React, {HTMLAttributes} from 'react';

export interface SortMarkerProps extends HTMLAttributes<HTMLDivElement> {
    canSort?: boolean,
    sortDescFirst?: boolean,
    isSorted?: boolean,
    isSortedDesc?: boolean,
}

const SortMarker: React.FC<SortMarkerProps> = ({canSort = false, sortDescFirst, isSorted, isSortedDesc, children, ...rest}) => {
    if (!canSort) {
        return <>{children}</>;
    }
    return (
        <div className="d-flex th" role="sort-marker" {...rest}>
            <div className="content">{children}</div>
            {isSorted ? (
                isSortedDesc
                    ? <span className="sort-icon active"> ↓</span>
                    : <span className="sort-icon active"> ↑</span>
            ) : (
                sortDescFirst
                    ? <span className="sort-icon"> ⇅ </span>
                    : <span className="sort-icon"> ⇅ </span>
            )}
        </div>
    );
};

export default SortMarker;
