/**
 * Thin fetch wrapper for the JSON API. Errors carry the 422 shape the backend
 * produces ({error, errors: {field: message}}) so forms can map them to fields.
 */
export interface ApiErrorBody {
    error?: string,
    message?: string,
    errors?: Record<string, string>,
}

export class ApiError extends Error {
    readonly status: number;
    readonly fieldErrors: Record<string, string>;

    constructor(status: number, body: ApiErrorBody | string | null) {
        const parsed = typeof body === 'object' && body !== null ? body : {};
        super(parsed.error ?? parsed.message ?? (typeof body === 'string' ? body : `HTTP ${status}`));
        this.name = 'ApiError';
        this.status = status;
        this.fieldErrors = parsed.errors ?? {};
    }
}

async function parseBody(response: Response): Promise<unknown> {
    const text = await response.text();
    if (!text) {
        return null;
    }
    try {
        return JSON.parse(text);
    } catch {
        return text;
    }
}

export async function apiFetch<T = unknown>(url: string, init: RequestInit = {}): Promise<T> {
    const headers = new Headers(init.headers);
    headers.set('Accept', 'application/json');
    if (init.body !== undefined && !headers.has('Content-Type')) {
        headers.set('Content-Type', 'application/json');
    }
    const response = await fetch(url, {...init, headers});
    const body = await parseBody(response);
    if (!response.ok) {
        throw new ApiError(response.status, body as ApiErrorBody | string | null);
    }
    return body as T;
}

export function apiJson<T = unknown>(url: string, method: string, payload: unknown): Promise<T> {
    return apiFetch<T>(url, {method, body: JSON.stringify(payload)});
}

/** Every list endpoint answers with this envelope. */
export interface ListResponse<T> {
    count: number,
    results: T[],
}

export interface SortState {
    id: string,
    desc: boolean,
}

export interface ListQuery {
    page: number,
    pageSize: number,
    sort: SortState | null,
    query: string,
}

/**
 * Builds the query string every list endpoint understands: start (page index),
 * max (page size), query (free text), sortBy + desc. Extra keys are appended
 * as-is when they have a value.
 */
export function listUrl(base: string, {page, pageSize, sort, query}: ListQuery, extra: Record<string, string | undefined> = {}): string {
    const params = new URLSearchParams();
    params.set('start', String(page));
    params.set('max', String(pageSize));
    params.set('query', query);
    if (sort) {
        params.set('sortBy', sort.id);
        params.set('desc', sort.desc ? '1' : '0');
    }
    for (const [key, value] of Object.entries(extra)) {
        if (value) {
            params.set(key, value);
        }
    }
    return `${base}?${params.toString()}`;
}
