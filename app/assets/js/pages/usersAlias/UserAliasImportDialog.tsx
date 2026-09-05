import {useMutation, useQuery, useQueryClient} from '@tanstack/react-query';
import {AlertTriangle, Upload} from 'lucide-react';
import React, {useRef, useState} from 'react';
import {useTranslation} from 'react-i18next';
import {toast} from 'sonner';

import {useApplication} from '@/application/ApplicationContext';
import {FormDialog, FormFooter} from '@/components/dialogs';
import {Button} from '@/components/ui/button';
import {Checkbox} from '@/components/ui/checkbox';
import {Label} from '@/components/ui/label';
import {Select, SelectContent, SelectItem, SelectTrigger, SelectValue} from '@/components/ui/select';
import {apiFetch, apiJson} from '@/lib/api';
import type {ListResponse} from '@/lib/api';
import type {User} from '@/types/user';

/** Mirrors App\Domain\UserAlias\Import\AliasImportResult. */
interface ImportResult {
    created: number,
    moved: number,
    unchanged: number,
    removed: number,
    moves: { address: string, from: string, to: string }[],
    removals: { address: string, from: string }[],
    problems: { line: number, text: string, reason: string }[],
}

const ALL_ACCOUNTS = '__two_column__';

export interface UserAliasImportDialogProps {
    open: boolean,
    onOpenChange: (open: boolean) => void,
}

/**
 * Assigns many addresses at once, so this does not have to be done one at a time.
 *
 * Two shapes, because both are things people actually have: a plain list of
 * addresses that all belong to one account, which is what someone pastes; and a
 * two-column list naming an account per address, which is what a mail system
 * exports.
 *
 * Nothing is written until a preview has been shown. The preview is the same
 * request with dryRun set, so what it reports is produced by the code that will
 * act rather than by a second opinion.
 */
export function UserAliasImportDialog({open, onOpenChange}: UserAliasImportDialogProps): React.ReactElement {
    const {t} = useTranslation();
    const {apiUrl} = useApplication();
    const queryClient = useQueryClient();

    const [content, setContent] = useState('');
    const [account, setAccount] = useState<string>(ALL_ACCOUNTS);
    const [prune, setPrune] = useState(false);
    const [preview, setPreview] = useState<ImportResult | null>(null);
    const fileInput = useRef<HTMLInputElement>(null);

    const {data: users} = useQuery({
        queryKey: ['users', 'all'],
        queryFn: () => apiFetch<ListResponse<User>>(`${apiUrl}/users`),
        enabled: open,
    });

    const send = (dryRun: boolean) => apiJson<ImportResult>(`${apiUrl}/users-aliases/import`, 'POST', {
        content,
        username: account === ALL_ACCOUNTS ? undefined : account,
        prune,
        dryRun,
    });

    const check = useMutation({
        mutationFn: () => send(true),
        onSuccess: setPreview,
        onError: (error: Error) => toast.error(error.message),
    });

    const apply = useMutation({
        mutationFn: () => send(false),
        onSuccess: (result) => {
            queryClient.invalidateQueries({queryKey: ['userAliases']});
            toast.success(t('alias.import.applied', {
                count: result.created + result.moved + result.removed,
            }));
            close();
        },
        onError: (error: Error) => toast.error(error.message),
    });

    const close = () => {
        setContent('');
        setPreview(null);
        setPrune(false);
        setAccount(ALL_ACCOUNTS);
        onOpenChange(false);
    };

    // Read in the browser rather than uploading: the endpoint takes text, which
    // keeps a pasted list and a chosen file on exactly one code path.
    const readFile = async (file: File | undefined): Promise<void> => {
        if (!file) {
            return;
        }
        setContent(await file.text());
        setPreview(null);
    };

    return (
        <FormDialog
            open={open}
            onOpenChange={(next) => (next ? onOpenChange(true) : close())}
            title={t('alias.import.title')}
            description={t('alias.import.description')}
        >
            <form
                className="space-y-4"
                onSubmit={(event) => {
                    event.preventDefault();
                    (preview ? apply : check).mutate();
                }}
            >
                <div className="space-y-1.5">
                    <Label htmlFor="import-account">{t('alias.import.account')}</Label>
                    <Select value={account} onValueChange={(value) => { setAccount(value); setPreview(null); }}>
                        <SelectTrigger id="import-account">
                            <SelectValue/>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={ALL_ACCOUNTS}>{t('alias.import.twoColumn')}</SelectItem>
                            {(users?.results ?? []).map((user) => (
                                <SelectItem key={user.id} value={user.username}>{user.username}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <p className="text-muted-foreground text-xs">
                        {account === ALL_ACCOUNTS ? t('alias.import.twoColumnHint') : t('alias.import.oneAccountHint')}
                    </p>
                </div>

                <div className="space-y-1.5">
                    <Label htmlFor="import-content">{t('alias.import.addresses')}</Label>
                    <textarea
                        id="import-content"
                        className="border-input placeholder:text-muted-foreground focus-visible:ring-ring/50 min-h-40 w-full rounded-md border bg-transparent px-3 py-2 font-mono text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                        value={content}
                        spellCheck={false}
                        placeholder={account === ALL_ACCOUNTS
                            ? 'anna@example.com,anna\nsales@example.com,anna'
                            : 'anna@example.com\nsales@example.com'}
                        onChange={(event) => { setContent(event.target.value); setPreview(null); }}
                    />
                    <div className="flex items-center gap-2">
                        <Button type="button" variant="outline" size="sm" onClick={() => fileInput.current?.click()}>
                            <Upload aria-hidden="true"/>
                            {t('alias.import.chooseFile')}
                        </Button>
                        <input ref={fileInput} type="file" accept=".csv,.txt,text/plain,text/csv" className="hidden"
                               onChange={(event) => void readFile(event.target.files?.[0])}/>
                    </div>
                </div>

                <div className="flex items-start gap-2">
                    <Checkbox id="import-prune" checked={prune}
                              onCheckedChange={(value) => { setPrune(value === true); setPreview(null); }}/>
                    <div className="space-y-0.5">
                        <Label htmlFor="import-prune">{t('alias.import.prune')}</Label>
                        <p className="text-muted-foreground text-xs">{t('alias.import.pruneHint')}</p>
                    </div>
                </div>

                {preview && <ImportPreview result={preview}/>}

                <FormFooter
                    onCancel={close}
                    submitLabel={preview ? t('alias.import.apply') : t('alias.import.check')}
                    pending={check.isPending || apply.isPending}
                    submitDisabled={content.trim() === '' || (preview !== null && !changesAnything(preview))}
                />
            </form>
        </FormDialog>
    );
}

function changesAnything(result: ImportResult): boolean {
    return result.created > 0 || result.moved > 0 || result.removed > 0;
}

/**
 * What the import would do. Counts first, because that is the decision, then the
 * things somebody would want to look at before agreeing: addresses changing
 * hands, addresses about to be dropped, and lines that could not be read.
 */
function ImportPreview({result}: { result: ImportResult }): React.ReactElement {
    const {t} = useTranslation();

    return (
        <div className="bg-muted/40 space-y-3 rounded-md border p-3 text-sm">
            <p className="font-medium">
                {t('alias.import.summary', {
                    created: result.created,
                    moved: result.moved,
                    unchanged: result.unchanged,
                    removed: result.removed,
                })}
            </p>

            {result.moves.length > 0 && (
                <div>
                    <p className="text-muted-foreground text-xs">{t('alias.import.reassigned')}</p>
                    <ul className="mt-1 space-y-0.5 font-mono text-xs">
                        {result.moves.map((move) => (
                            <li key={move.address}>{move.address}: {move.from} → {move.to}</li>
                        ))}
                    </ul>
                </div>
            )}

            {result.removals.length > 0 && (
                <div>
                    <p className="text-muted-foreground text-xs">{t('alias.import.willRemove')}</p>
                    <ul className="mt-1 space-y-0.5 font-mono text-xs">
                        {result.removals.map((removal) => (
                            <li key={removal.address}>{removal.address} ({removal.from})</li>
                        ))}
                    </ul>
                </div>
            )}

            {result.problems.length > 0 && (
                <div>
                    <p className="text-destructive flex items-center gap-1 text-xs">
                        <AlertTriangle className="size-3" aria-hidden="true"/>
                        {t('alias.import.skipped', {count: result.problems.length})}
                    </p>
                    <ul className="mt-1 space-y-0.5 font-mono text-xs">
                        {result.problems.map((problem) => (
                            <li key={`${problem.line}-${problem.text}`}>
                                {t('alias.import.line', {line: problem.line})} {problem.text} — {t(`alias.import.reason.${problem.reason}`)}
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
}
