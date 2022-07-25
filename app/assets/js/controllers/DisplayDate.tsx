import React from 'react';
import {useTranslation} from 'react-i18next';

import {DATE_TIME_FORMAT, DateObject} from '../types/common';
import {format as dateFormat} from 'date-fns';

interface DisplayDateProps {
    date: DateObject,
    format?: string
}

const DisplayDate: React.VFC<DisplayDateProps> = ({date, format = DATE_TIME_FORMAT}) => {
    const {t} = useTranslation();

    return (
        <span>{dateFormat(new Date(date.date), format)}</span>
    );
};

export default DisplayDate;
