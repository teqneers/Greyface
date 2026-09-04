import {isValid, parseISO} from 'date-fns';
import React from 'react';

import {useLocalizedDate} from '@/application/i18n';
import type {DateObject} from '@/types/common';

export type DateStyle = 'date' | 'dateTime' | 'dateTimeSeconds';

/** Locale-aware date-fns patterns: "P" and "p" follow the active language. */
const patterns: Record<DateStyle, string> = {
    date: 'P',
    dateTime: 'P p',
    dateTimeSeconds: 'P pp',
};

export function toDate(value: DateObject | string | null | undefined): Date | null {
    const raw = typeof value === 'string' ? value : value?.date;
    if (!raw) {
        return null;
    }
    const parsed = parseISO(raw.replace(' ', 'T'));
    return isValid(parsed) ? parsed : null;
}

export interface FormattedDateProps {
    value: DateObject | string | null | undefined,
    style?: DateStyle,
}

export function FormattedDate({value, style = 'dateTime'}: FormattedDateProps): React.ReactElement {
    const {format} = useLocalizedDate();
    const date = toDate(value);
    if (!date) {
        return <span className="text-muted-foreground">–</span>;
    }
    return <time dateTime={date.toISOString()}>{format(date, patterns[style])}</time>;
}
