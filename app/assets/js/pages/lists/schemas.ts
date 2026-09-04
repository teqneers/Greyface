import type {TFunction} from 'i18next';
import {z} from 'zod';

const MAX = 128;
export const MAX_VALUES = 5;

// SQLGrey stores plain strings; the checks here are about catching typos,
// not about being an RFC validator.
const DOMAIN = /^(?!-)[a-z0-9-]+(\.[a-z0-9-]+)*\.?$/i;

export const emailValue = (t: TFunction) => z.string().trim().min(1, t('errors.required')).max(MAX, t('errors.max', {max: MAX}))
    .email(t('errors.email'));
export const domainValue = (t: TFunction) => z.string().trim().min(1, t('errors.required')).max(MAX, t('errors.max', {max: MAX}))
    .regex(DOMAIN, t('errors.domain'));
export const textValue = (t: TFunction) => z.string().trim().min(1, t('errors.required')).max(MAX, t('errors.max', {max: MAX}));

/**
 * Up to five distinct values. Each is wrapped in an object because
 * react-hook-form's field arrays do not support arrays of primitives.
 */
export type ValueItem = { v: string };

export function manyOf(t: TFunction, value: z.ZodString) {
    return z.array(z.object({v: value})).min(1).max(MAX_VALUES).superRefine((list, ctx) => {
        const seen = new Set<string>();
        list.forEach((item, index) => {
            const key = item.v.toLowerCase();
            if (seen.has(key)) {
                ctx.addIssue({code: 'custom', path: [index, 'v'], message: t('errors.unique')});
            }
            seen.add(key);
        });
    });
}
