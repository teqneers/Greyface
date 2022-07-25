export const DATE_FORMAT = 'dd.MM.yyyy';
export const DATE_TIME_FORMAT = 'dd.MM.yyyy HH:mm';
export const DATE_TIME_SECONDS_FORMAT = 'dd.MM.yyyy HH:mm:ss';

export interface HasId {
    id: string,
}

export interface CreateTracking {
    created_at: string,
    created_by?: string,
}

export interface UpdateTracking {
    updated_at: string,
    updated_by?: string,
}

export interface AuditTracking extends CreateTracking, UpdateTracking {
}

export interface DateObject {
    date: string,
    timezone: string,
    timezone_type: number
}