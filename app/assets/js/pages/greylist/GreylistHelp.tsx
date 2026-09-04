import {CircleHelp, X} from 'lucide-react';
import React, {useState} from 'react';
import {useTranslation} from 'react-i18next';

import {Button} from '@/components/ui/button';
import {Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger} from '@/components/ui/sheet';

const DISMISSED_KEY = 'teqneers.greyface.help.greylist';

function readDismissed(): boolean {
    try {
        return window.localStorage.getItem(DISMISSED_KEY) === '1';
    } catch {
        return false;
    }
}

const columns = ['sender', 'domain', 'source', 'recipient', 'firstSeen'] as const;

/** The "what is this" text, shared by the callout and the side panel. */
function HelpText(): React.ReactElement {
    const {t} = useTranslation();
    return (
        <div className="space-y-3 text-sm leading-relaxed">
            <p>{t('greylist.help.what')}</p>
            <p>{t('greylist.help.whitelist')}</p>
        </div>
    );
}

export function GreylistHelpButton(): React.ReactElement {
    const {t} = useTranslation();
    return (
        <Sheet>
            <SheetTrigger asChild>
                <Button variant="outline" size="icon" aria-label={t('greylist.help.open')}>
                    <CircleHelp aria-hidden="true"/>
                </Button>
            </SheetTrigger>
            <SheetContent className="overflow-y-auto sm:max-w-md">
                <SheetHeader>
                    <SheetTitle>{t('greylist.help.title')}</SheetTitle>
                    <SheetDescription>{t('greylist.help.subtitle')}</SheetDescription>
                </SheetHeader>
                <div className="space-y-6 px-4 pb-6">
                    <HelpText/>
                    <div>
                        <h3 className="mb-2 text-sm font-semibold">{t('greylist.help.columnsTitle')}</h3>
                        <dl className="space-y-2 text-sm">
                            {columns.map((column) => (
                                <div key={column} className="grid grid-cols-[7rem_1fr] gap-2">
                                    <dt className="font-medium">{t(`greylist.${column}`)}</dt>
                                    <dd className="text-muted-foreground">{t(`greylist.help.columns.${column}`)}</dd>
                                </div>
                            ))}
                        </dl>
                    </div>
                </div>
            </SheetContent>
        </Sheet>
    );
}

/** Shown to end users until dismissed; remembered per browser. */
export function GreylistHelpCallout(): React.ReactElement | null {
    const {t} = useTranslation();
    const [dismissed, setDismissed] = useState(readDismissed);

    if (dismissed) {
        return null;
    }

    const dismiss = () => {
        try {
            window.localStorage.setItem(DISMISSED_KEY, '1');
        } catch {
            // nothing to do; it will show again next time
        }
        setDismissed(true);
    };

    return (
        <aside className="relative mb-5 rounded-lg border border-primary/30 bg-primary/5 p-4 pr-12">
            <h2 className="mb-2 text-sm font-semibold">{t('greylist.help.title')}</h2>
            <HelpText/>
            <Button variant="ghost" size="icon-sm" className="absolute top-2 right-2" onClick={dismiss}
                    aria-label={t('greylist.help.dismiss')}>
                <X aria-hidden="true"/>
            </Button>
        </aside>
    );
}
