import React from 'react';

import {DATE_TIME_FORMAT, DateObject} from '../types/common';
import {format as dateFormat} from 'date-fns';

interface DisplayDateProps {
    date: DateObject,
    format?: string
}

const DisplayDate: React.VFC<DisplayDateProps> = ({date, format = DATE_TIME_FORMAT}) => {

    return (
        <span>{dateFormat(new Date(date.date), format)}</span>
    );
};

export default DisplayDate;
