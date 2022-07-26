import {Row} from 'react-table';

export function getByPath<D extends object>(obj: D, path: string, def: any = null): any {
    const value = path.split('.').reduce((o: D | any, p) => {
        if (typeof o === 'object' && o[p] !== undefined) {
            return o[p] ?? null;
        }
        return null;
    }, obj);
    return value ?? def;
}

export function createGetRowId<D extends object>(idColumn?: string | null): (row: D, relativeIndex: number, parent?: Row<D>) => string {
    if (idColumn) {
        return (row: D, relativeIndex: number, parent?: Row<D>): string => {
            const id = getByPath<D>(row, idColumn);
            return id ? String(id) : String(parent ? [parent.id, relativeIndex].join('.') : relativeIndex);
        };
    } else {
        return (row: D, relativeIndex: number, parent?: Row<D>): string => {
            return String(parent ? [parent.id, relativeIndex].join('.') : relativeIndex);
        };
    }
}
