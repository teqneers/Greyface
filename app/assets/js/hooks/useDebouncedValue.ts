import {useEffect, useState} from 'react';

export function useDebouncedValue<T>(value: T, delay = 300): T {
    const [debounced, setDebounced] = useState(value);
    useEffect(() => {
        const handle = window.setTimeout(() => setDebounced(value), delay);
        return () => window.clearTimeout(handle);
    }, [value, delay]);
    return debounced;
}
