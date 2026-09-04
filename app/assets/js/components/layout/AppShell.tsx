import {Menu} from 'lucide-react';
import React, {useState} from 'react';
import {useTranslation} from 'react-i18next';
import {Link} from 'react-router-dom';

import {usePermissions} from '@/application/usePermissions';
import {Button} from '@/components/ui/button';
import {Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger} from '@/components/ui/sheet';

import {SidebarNav} from './SidebarNav';
import {UserMenu} from './UserMenu';
import {Wordmark} from './Wordmark';

function Footer(): React.ReactElement {
    const {t} = useTranslation();
    return (
        <footer className="border-t px-4 py-3 text-xs text-muted-foreground sm:px-6">
            {t('footer.credit')}{' '}
            <a href="https://www.teqneers.de/" target="_blank" rel="noreferrer"
               className="font-medium underline-offset-4 hover:underline">
                TEQneers GmbH &amp; Co. KG
            </a>
        </footer>
    );
}

/**
 * Administrators get a sidebar (a sheet on narrow screens); everyone else
 * sees only the greylist, so they get a plain top bar.
 */
export function AppShell({children}: { children: React.ReactNode }): React.ReactElement {
    const {t} = useTranslation();
    const {isAdministrator} = usePermissions();
    const [open, setOpen] = useState(false);

    if (!isAdministrator()) {
        return (
            <div className="flex min-h-screen flex-col">
                <header className="sticky top-0 z-20 flex h-14 items-center justify-between border-b bg-background/95 px-4 backdrop-blur sm:px-6">
                    <Link to="/" className="rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                        <Wordmark/>
                    </Link>
                    <UserMenu/>
                </header>
                <main className="flex-1 px-4 py-6 sm:px-6">{children}</main>
                <Footer/>
            </div>
        );
    }

    return (
        <div className="flex min-h-screen">
            <aside className="sticky top-0 hidden h-screen w-60 shrink-0 flex-col border-r bg-sidebar text-sidebar-foreground md:flex">
                <div className="flex h-14 items-center border-b border-sidebar-border px-4">
                    <Link to="/" className="rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sidebar-ring">
                        <Wordmark/>
                    </Link>
                </div>
                <div className="flex-1 overflow-y-auto p-3">
                    <SidebarNav/>
                </div>
            </aside>

            <div className="flex min-w-0 flex-1 flex-col">
                <header className="sticky top-0 z-20 flex h-14 items-center justify-between gap-3 border-b bg-background/95 px-4 backdrop-blur sm:px-6">
                    <div className="flex items-center gap-2 md:hidden">
                        <Sheet open={open} onOpenChange={setOpen}>
                            <SheetTrigger asChild>
                                <Button variant="ghost" size="icon" aria-label={t('menu.openMenu')}>
                                    <Menu aria-hidden="true"/>
                                </Button>
                            </SheetTrigger>
                            <SheetContent side="left" className="w-72 bg-sidebar p-0 text-sidebar-foreground">
                                <SheetHeader className="h-14 justify-center border-b border-sidebar-border px-4">
                                    <SheetTitle><Wordmark/></SheetTitle>
                                </SheetHeader>
                                <div className="p-3">
                                    <SidebarNav onNavigate={() => setOpen(false)}/>
                                </div>
                            </SheetContent>
                        </Sheet>
                        <Link to="/"><Wordmark/></Link>
                    </div>
                    <div className="ml-auto">
                        <UserMenu/>
                    </div>
                </header>
                <main className="flex-1 px-4 py-6 sm:px-6">{children}</main>
                <Footer/>
            </div>
        </div>
    );
}
