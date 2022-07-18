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