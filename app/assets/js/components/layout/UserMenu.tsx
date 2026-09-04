import {ChevronDown, Globe, KeyRound, LogOut, Monitor, Moon, Sun, UserRound} from 'lucide-react';
import React from 'react';
import {useTranslation} from 'react-i18next';

import {useApplication} from '@/application/ApplicationContext';
import {setSetting, SettingsLocale, useSettings} from '@/application/settings';
import type {ThemeSetting} from '@/application/settings';
import {useTheme} from '@/application/theme';
import {Button} from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const themeIcons: Record<ThemeSetting, React.ElementType> = {light: Sun, dark: Moon, system: Monitor};
const themes: ThemeSetting[] = ['light', 'dark', 'system'];

export function UserMenu(): React.ReactElement {
    const {t} = useTranslation();
    const {user, changePasswordUrl, logoutUrl, version, build} = useApplication();
    const {locale} = useSettings();
    const {theme, setTheme} = useTheme();
    const ThemeIcon = themeIcons[theme];

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="sm" className="gap-2" aria-label={t('menu.accountMenu')}>
                    <span className="flex size-6 items-center justify-center rounded-full bg-muted">
                        <UserRound className="size-3.5" aria-hidden="true"/>
                    </span>
                    <span className="max-w-40 truncate">{user.username}</span>
                    <ChevronDown className="size-3.5 opacity-60" aria-hidden="true"/>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-56">
                <DropdownMenuLabel className="font-normal">
                    <div className="truncate text-sm font-medium">{user.username}</div>
                    <div className="truncate text-xs text-muted-foreground">{user.email}</div>
                </DropdownMenuLabel>
                <DropdownMenuSeparator/>

                {changePasswordUrl && (
                    <DropdownMenuItem asChild>
                        <a href={changePasswordUrl}>
                            <KeyRound aria-hidden="true"/>
                            {t('button.changePassword')}
                        </a>
                    </DropdownMenuItem>
                )}

                <DropdownMenuSub>
                    <DropdownMenuSubTrigger>
                        <ThemeIcon aria-hidden="true"/>
                        {t('theme.label')}
                    </DropdownMenuSubTrigger>
                    <DropdownMenuSubContent>
                        <DropdownMenuRadioGroup value={theme} onValueChange={(v) => setTheme(v as ThemeSetting)}>
                            {themes.map((value) => {
                                const Icon = themeIcons[value];
                                return (
                                    <DropdownMenuRadioItem key={value} value={value}>
                                        <Icon aria-hidden="true"/>
                                        {t(`theme.${value}`)}
                                    </DropdownMenuRadioItem>
                                );
                            })}
                        </DropdownMenuRadioGroup>
                    </DropdownMenuSubContent>
                </DropdownMenuSub>

                <DropdownMenuSub>
                    <DropdownMenuSubTrigger>
                        <Globe aria-hidden="true"/>
                        {t('button.language')}
                    </DropdownMenuSubTrigger>
                    <DropdownMenuSubContent>
                        <DropdownMenuRadioGroup value={locale} onValueChange={(v) => setSetting('locale', v)}>
                            {Object.entries(SettingsLocale).map(([key, value]) => (
                                <DropdownMenuRadioItem key={key} value={value}>
                                    {t(`locale.${key}`)}
                                </DropdownMenuRadioItem>
                            ))}
                        </DropdownMenuRadioGroup>
                    </DropdownMenuSubContent>
                </DropdownMenuSub>

                <DropdownMenuSeparator/>
                <DropdownMenuItem asChild>
                    <a href={logoutUrl}>
                        <LogOut aria-hidden="true"/>
                        {t('button.logout')}
                    </a>
                </DropdownMenuItem>

                <DropdownMenuSeparator/>
                <div className="px-2 py-1.5 text-xs text-muted-foreground">
                    {t('version.label', {version})}
                    <span className="ml-1 opacity-70">({build})</span>
                </div>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
