import * as DateFns from 'date-fns';
// date-fns 3 dropped the default export from each locale submodule; locales are
// named exports of date-fns/locale now.
import {de as dateFns_de, enUS as dateFns_en} from 'date-fns/locale';

import i18n from 'i18next';

import React, {useCallback, useContext, useEffect, useMemo, useState} from 'react';
import type {ReactNode} from 'react';
import {initReactI18next} from 'react-i18next';

import translation_de from '../../translations/de.json';
import translation_en from '../../translations/en.json';

import {EventDispatcher, useSubscription} from '../utils/event';

import {SettingsLocale, useSettings} from './settings';

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
                updateDependencies();
                currentLanguage = language;
                eventDispatcher.dispatch(currentLanguage);
                resolve(language);
            }
        });
    });
}

// Re-applies locale-dependent configuration after a language change.
function updateDependencies(): void {
    // Validation messages are translated where the schemas are built (zod);
    // nothing else needs to be told about a language change.
}

export async function initI18n(): Promise<void> {
    await i18n.use(initReactI18next)
        .init({
            resources: {de: translation_de, en: translation_en},
            lng: currentLanguage,
            fallbackLng: import.meta.env.PROD ? 'en' : 'dev',
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
    updateDependencies();
}

export function useLanguage(): string {
    return useSubscription<string>(currentLanguage, eventDispatcher);
}

interface Localization {
    locale: string,
    timezone: string,
}

type DateInput = Date | number | string;

const dateLocales: Record<string, DateFns.Locale> = {
    en: dateFns_en,
    de: dateFns_de,
};

export function useLocalizedDate() {
    const language = useLanguage();

    const optionsForLanguage = useCallback(<T extends object>(options: T): T & { locale: DateFns.Locale } => (
        Object.assign({}, options, {
            locale: dateLocales[language],
        })
    ), [language]);

    return useMemo(() => ({
        format: (date: DateInput, format: string, options: DateFns.FormatOptions = {}) => {
            return DateFns.format(date, format, optionsForLanguage(options));
        },
        parse: (dateString: string, format: string, backup: Date, options: DateFns.ParseOptions = {}) => {
            return DateFns.parse(dateString, format, backup, optionsForLanguage(options));
        },
        formatDistance: (date: DateInput, dateToCompare: DateInput, options: DateFns.FormatDistanceOptions = {}) => {
            return DateFns.formatDistance(date, dateToCompare, optionsForLanguage(options));
        },
        formatDistanceToNow: (date: DateInput, options: DateFns.FormatDistanceToNowOptions = {}) => {
            return DateFns.formatDistanceToNow(date, optionsForLanguage(options));
        },
        formatDistanceStrict: (date: DateInput, dateToCompare: DateInput, options: DateFns.FormatDistanceStrictOptions = {}) => {
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

const I18nContext = React.createContext<Localization>(systemLocalization);

export function useI18n(): Localization {
    return useContext(I18nContext);
}

const I18n: React.FC<{ children?: ReactNode }> = ({children}) => {
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
