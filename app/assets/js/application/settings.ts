import {GreyTableState, GreyTableStateWithUser} from '../types/greylist';
import {EventDispatcher, useSubscription} from '../utils/event';

export enum SettingsLocale {
    de_DE = 'de-DE',
    en_US = 'en-US',
}

const normalSplitSize: [number, number] = [40, 60];

type SettingsType = {
    locale: SettingsLocale,
    splitViewSizes: Record<string, [number, number]> | null,
    greyList: GreyTableStateWithUser,
    autoWhitelistDomain: GreyTableState,
    autoWhitelistEmail: GreyTableState,
    whitelistDomain: GreyTableState,
    whitelistEmail: GreyTableState,
    blacklistDomain: GreyTableState,
    blacklistEmail: GreyTableState,
    userAlias: GreyTableStateWithUser,
    users: GreyTableState
};

const INITIAL_SETTINGS: SettingsType = {
    locale: SettingsLocale.de_DE,
    splitViewSizes: null,
    autoWhitelistDomain: {
        columnOrder: [],
        sortBy: [{id: 'domain', desc: false}],
        filters: [],
        pageSize: 10,
        pageIndex: 0,
        searchQuery: ''
    },
    autoWhitelistEmail: {
        columnOrder: [],
        sortBy: [{id: 'name', desc: false}],
        filters: [],
        pageSize: 10,
        pageIndex: 0,
        searchQuery: ''
    },
    whitelistDomain: {
        columnOrder: [],
        sortBy: [{id: 'domain', desc: false}],
        filters: [],
        pageSize: 10,
        pageIndex: 0,
        searchQuery: ''
    },
    whitelistEmail: {
        columnOrder: [],
        sortBy: [{id: 'email', desc: false}],
        filters: [],
        pageSize: 10,
        pageIndex: 0,
        searchQuery: ''
    },
    blacklistDomain: {
        columnOrder: [],
        sortBy: [{id: 'domain', desc: false}],
        filters: [],
        pageSize: 10,
        pageIndex: 0,
        searchQuery: ''
    },
    blacklistEmail: {
        columnOrder: [],
        sortBy: [{id: 'email', desc: false}],
        filters: [],
        pageSize: 10,
        pageIndex: 0,
        searchQuery: ''
    },
    users: {
        columnOrder: [],
        sortBy: [{id: 'username', desc: false}],
        filters: [],
        pageSize: 10,
        pageIndex: 0,
        searchQuery: ''
    },
    greyList: {
        columnOrder: [],
        sortBy: [{id: 'username', desc: false}],
        filters: [],
        pageSize: 10,
        pageIndex: 0,
        searchQuery: '',
        user: ''
    },
    userAlias: {
        columnOrder: [],
        sortBy: [{id: 'username', desc: false}],
        filters: [],
        pageSize: 10,
        pageIndex: 0,
        searchQuery: '',
        user: ''
    },
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
        settings = JSON.parse(storedSettingsStr) || settings;
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


export function getSplitViewSizes(moduleKey: string, defaultValues?: [number, number]): [number, number] {
    const settings = getCurrentSettings();
    const currentSizes = settings.splitViewSizes;
    if (currentSizes && currentSizes[moduleKey]) {
        return currentSizes[moduleKey];
    } else {
        return defaultValues ? defaultValues : normalSplitSize;
    }

}

export function setSplitViewSizes(sizes: [number, number], moduleKey: string): SettingsType {
    const settings = getCurrentSettings();
    const currentSizes = settings.splitViewSizes;
    return setSetting('splitViewSizes', {
        ...currentSizes,
        [moduleKey]: sizes
    });
}
