import {useCallback, useEffect, useState} from 'react';

import {getCurrentSettings, setSetting, useSettings} from './settings';
import type {ThemeSetting} from './settings';

export type ResolvedTheme = 'light' | 'dark';

const darkQuery = () => window.matchMedia('(prefers-color-scheme: dark)');

export function resolveTheme(theme: ThemeSetting): ResolvedTheme {
    if (theme === 'system') {
        return darkQuery().matches ? 'dark' : 'light';
    }
    return theme;
}

function applyTheme(theme: ThemeSetting): void {
    document.documentElement.classList.toggle('dark', resolveTheme(theme) === 'dark');
}

/**
 * Applies the persisted theme once at startup. base.html.twig runs the same
 * logic inline before the first paint; this keeps the DOM in sync afterwards.
 * Settings must be initialised first.
 */
export function initTheme(): void {
    applyTheme(getCurrentSettings().theme ?? 'system');
}

export interface UseTheme {
    theme: ThemeSetting,
    resolved: ResolvedTheme,
    setTheme: (theme: ThemeSetting) => void,
}

export function useTheme(): UseTheme {
    const settings = useSettings();
    // Older stored settings objects predate the key.
    const theme = settings.theme ?? 'system';
    const [resolved, setResolved] = useState<ResolvedTheme>(() => resolveTheme(theme));

    useEffect(() => {
        const update = () => {
            applyTheme(theme);
            setResolved(resolveTheme(theme));
        };
        update();
        const query = darkQuery();
        query.addEventListener('change', update);
        return () => query.removeEventListener('change', update);
    }, [theme]);

    const setTheme = useCallback((next: ThemeSetting) => {
        setSetting('theme', next);
    }, []);

    return {theme, resolved, setTheme};
}
