import {Row} from 'react-table';

export function getByPath<D extends object>(obj: D, path: string, relativeIndex: number, def: any = null): any {
   const arrayKeys =  path.split(',');
   const returnValue = [];
    arrayKeys.map((path) => {
        const value = path.split('.').reduce((o: D | any, p) => {
            if (typeof o === 'object' && o[p] !== undefined) {
                return o[p] ?? null;
            }
            return null;
        }, obj);
        returnValue.push(value ?? def);
    });
    returnValue.push(relativeIndex);
    return returnValue.join('-');
}

export function createGetRowId<D extends object>(idColumn?: string | null): (row: D, relativeIndex: number, parent?: Row<D>) => string {
    if (idColumn) {
        return (row: D, relativeIndex: number, parent?: Row<D>): string => {
            const id = getByPath<D>(row, idColumn, relativeIndex);
            return id ? String(id) : String(parent ? [parent.id, relativeIndex].join('.') : relativeIndex);
        };
    } else {
        return (row: D, relativeIndex: number, parent?: Row<D>): string => {
            return String(parent ? [parent.id, relativeIndex].join('.') : relativeIndex);
        };
    }
}
