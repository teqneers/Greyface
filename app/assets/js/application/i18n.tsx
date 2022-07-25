import React, {useCallback, useContext, useEffect, useMemo, useState} from 'react';
import * as DateFns from 'date-fns';
import dateFns_de from 'date-fns/locale/de';
import dateFns_en from 'date-fns/locale/en-US';

import {EventDispatcher, useSubscription} from '../utils/event';

import {SettingsLocale, useSettings} from './settings';

import numeral from 'numeral';
import 'numeral/locales/de';

import * as yup from 'yup';

import i18n from 'i18next';
import {initReactI18next} from 'react-i18next';
// @ts-ignore
import translation_de from '../../translations/de.json';
// @ts-ignore
import translation_en from '../../translations/en.json';

const eventDispatcher = new EventDispatcher<string>();
let [currentLanguage] = navigator.language.split('-');

function updateLanguageAsync(locale: string): Promise<string> {
    let [language] = locale.split('-');
    if (!['de', 'en'].includes(language)) {
        language = 'en';
    }
    return new Promise((resolve, reject) => {
        i18n.changeLanguage(language, (err: any) => {
            if (err) {
                reject(err);
            } else {
                updateDependencies(language);
                currentLanguage = language;
                eventDispatcher.dispatch(currentLanguage);
                resolve(language);
            }
        });
    });
}

function updateDependencies(language: string): void {
    numeral.locale(language);
    yup.setLocale({
        mixed: {
            default: i18n.t('errors.default'),
            required: i18n.t('errors.required'),
            // @ts-ignore
            typeError: i18n.t('errors.typeError'),
        },
        array: {
            min: i18n.t('errors.tooLess'),
            max: i18n.t('errors.tooMany')
        },
        number: {
            min: i18n.t('errors.min'),
            max: i18n.t('errors.max')
        },
        string: {
            length: i18n.t('errors.length'),
            min: i18n.t('errors.min'),
            max: i18n.t('errors.max')
        },
    });
}

export async function initI18n(): Promise<void> {
    await i18n.use(initReactI18next)
        .init({
            resources: {de: translation_de, en: translation_en},
            lng: currentLanguage,
            // @ts-ignore
            fallbackLng: IS_DEV ? 'dev' : 'en',
            //debug: !!__DEV__,
            supportedLngs: ['en', 'de', 'dev'],
            ns: [],
            defaultNS: 'common',
            cleanCode: true,
            keySeparator: '.',
            interpolation: {
                escapeValue: false
            }
        });
    updateDependencies(currentLanguage);
}

export function useLanguage(): string {
    return useSubscription<string>(currentLanguage, eventDispatcher);
}

interface Localization {
    locale: string,
    timezone: string,
}
const dateLocales = {
    en: dateFns_en,
    de: dateFns_de,
};

export function useLocalizedDate() {
    const language = useLanguage();

    const optionsForLanguage = useCallback((options) => (
        Object.assign({}, options, {
            locale: dateLocales[language],
        })
    ), [language]);

    return useMemo(() => ({
        format: (date, format, options = {}) => {
            return DateFns.format(date, format, optionsForLanguage(options));
        },
        parse: (dateString, format, backup, options = {}) => {
            return DateFns.parse(dateString, format, backup, optionsForLanguage(options));
        },
        formatDistance: (date, dateToCompare, options = {}) => {
            return DateFns.formatDistance(date, dateToCompare, optionsForLanguage(options));
        },
        formatDistanceToNow: (date, options = {}) => {
            return DateFns.formatDistanceToNow(date, optionsForLanguage(options));
        },
        formatDistanceStrict: (date, dateToCompare, options = {}) => {
            return DateFns.formatDistanceStrict(date, dateToCompare, optionsForLanguage(options));
        }
    }), [optionsForLanguage]);
}

const systemLocalization: Localization = {
    locale: navigator.language,
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
};

function getEffectiveLocalization(settingsLocale: SettingsLocale) {
    return {
        locale: settingsLocale as string,
        timezone: systemLocalization.timezone,
    };
}

const I18nContext = React.createContext<Localization>(null);

export function useI18n(): Localization {
    return useContext(I18nContext);
}

const I18n: React.FC = ({children}) => {
    const {locale: settingsLocale} = useSettings();
    const [localization, setLocalization] = useState<Localization>(getEffectiveLocalization(settingsLocale));
    useEffect(() => {
        setLocalization(getEffectiveLocalization(settingsLocale));
    }, [settingsLocale]);

    useEffect(() => {
        updateLanguageAsync(localization.locale);
    }, [localization.locale]);

    return (
        <I18nContext.Provider value={localization}>
            {children}
        </I18nContext.Provider>
    );
};

export default I18n;
