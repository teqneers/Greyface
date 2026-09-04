import {EventDispatcher, useSubscription} from '../utils/event';

export enum SettingsLocale {
    de_DE = 'de-DE',
    en_US = 'en-US',
}

export type ThemeSetting = 'light' | 'dark' | 'system';

type SettingsType = {
    locale: SettingsLocale,
    theme: ThemeSetting,
};

const INITIAL_SETTINGS: SettingsType = {
    locale: SettingsLocale.de_DE,
    theme: 'system',
};

const eventDispatcher = new EventDispatcher<SettingsType>();
let currentSettings = INITIAL_SETTINGS;

const STORAGE_KEY = 'teqneers.greyface.settings';
const storage = window.localStorage;

function updateSettings(settings: Partial<SettingsType>, storeSettings = true): SettingsType {
    const newSettings: Record<string, any> = {...currentSettings};
    Object.keys(INITIAL_SETTINGS).forEach((key) => {
        const settingsKey = key as keyof SettingsType;
        const value = settings[settingsKey];
        if (value !== undefined) {
            newSettings[settingsKey] = value;
        }
    });
    currentSettings = newSettings as SettingsType;
    eventDispatcher.dispatch(currentSettings);
    if (storeSettings) {
        storage.setItem(STORAGE_KEY, JSON.stringify(currentSettings));
    }
    return currentSettings;
}

export function initSettings(): void {
    let settings = {};
    try {
        const storedSettingsStr = storage.getItem(STORAGE_KEY);
        settings = (storedSettingsStr ? JSON.parse(storedSettingsStr) : null) || settings;
    } catch (e) {
        console.error(e);
    }
    updateSettings({...INITIAL_SETTINGS, ...settings}, false);
}

export function getCurrentSettings(): SettingsType {
    return currentSettings;
}

export function setSettings(settings: Partial<SettingsType>): SettingsType {
    return updateSettings(settings, true);
}

export function setSetting(key: keyof SettingsType, value: any): SettingsType {
    return updateSettings({[key]: value}, true);
}

export function resetSettings(): SettingsType {
    return updateSettings(INITIAL_SETTINGS, true);
}

export function useSettings(): SettingsType {
    return useSubscription<SettingsType>(currentSettings, eventDispatcher);
}

