import React from 'react';

import {DATE_TIME_FORMAT, DateObject} from '../types/common';

import {format as dateFormat, isValid, parseISO} from 'date-fns';

interface DisplayDateProps {
    date: DateObject,
    format?: string
}

const DisplayDate: React.FC<DisplayDateProps> = ({date, format = DATE_TIME_FORMAT}) => {

    const d = parseISO(date?.date);

    if (!isValid(d)) {
        console.log(date, d);
    }

    return (
        <span>{isValid(d) ? dateFormat(d, format) : '-'}</span>
    );
};

export default DisplayDate;
