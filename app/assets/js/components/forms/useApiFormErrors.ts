import {useState} from 'react';
import type {FieldValues, Path, UseFormReturn} from 'react-hook-form';

import {ApiError} from '@/lib/api';

/**
 * Maps an API failure onto the form: 422 field errors go to their fields,
 * anything else becomes the general message shown above the buttons.
 */
export function useApiFormErrors<V extends FieldValues>(form: UseFormReturn<V>, fieldNames: string[]) {
    const [error, setError] = useState<string | null>(null);

    const handle = (failure: Error) => {
        if (failure instanceof ApiError) {
            let mapped = false;
            for (const [field, message] of Object.entries(failure.fieldErrors)) {
                const target = fieldNames.find((name) => field === name || field.startsWith(`${name}.`) || field.startsWith(`${name}[`));
                if (target) {
                    form.setError(target as Path<V>, {message});
                    mapped = true;
                }
            }
            setError(mapped ? null : failure.message);
            return;
        }
        setError(failure.message);
    };

    return {error, setError, handle};
}
