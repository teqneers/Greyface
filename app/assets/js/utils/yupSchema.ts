import {TFunction} from 'i18next';
import * as yup from 'yup';

declare module 'yup' {
    // @ts-ignore
    interface ArraySchema<T> {
        // @ts-ignore
        unique(mapper: (a: T) => T, message?: any): ArraySchema<T>;
    }
}

yup.addMethod(yup.array, 'unique', function (message, mapper = a => a) {
    return this.test('unique', message, function (list) {
        return list.length === new Set(list.map(mapper)).size;
    });
});

export {yup};

export interface DomainValues {
    domain: string[]
}

export interface DomainRequest {
    domain: string[]
}

export function DomainSchema(t: TFunction): yup.AnySchema {
    return yup.object()
        .noUnknown()
        .shape(
            {
                domain: yup.array()
                    .of(yup.string().required().max(128))
                    .min(1)
                    .max(5)
                    .unique(t('errors.unique'))
                    .default([]),
            }
        );
}

export interface EmailValues {
    email: string[]
}

export interface EmailRequest {
    email: string[]
}

export function EmailSchema(t: TFunction): yup.AnySchema {
    return yup.object()
        .noUnknown()
        .shape(
            {
                email: yup.array()
                    .of(yup.string().required().max(128).email())
                    .min(1)
                    .max(5)
                    .unique(t('errors.unique'))
                    .default([]),
            }
        );
}