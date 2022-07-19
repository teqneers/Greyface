import {useCallback, useEffect, useMemo, useRef, useState} from 'react';

export type EventListenerType<T> = (event: T) => void;

export type EventListenerRemoverType = () => void;

export class EventDispatcher<T> {
    private readonly listeners: Set<EventListenerType<T>>;

    constructor() {
        this.listeners = new Set<EventListenerType<T>>();
    }

    addListener(listener: EventListenerType<T>): EventListenerRemoverType {
        this.listeners.add(listener);
        return () => {
            this.removeListener(listener);
        };
    }

    removeListener(listener: EventListenerType<T>): void {
        this.listeners.delete(listener);
    }

    clearListeners(): void {
        this.listeners.clear();
    }

    dispatch(event: T): void {
        this.listeners.forEach((listener) => listener(event));
    }
}

export function useSubscription<T>(initialValue: T, eventDispatcher: EventDispatcher<T>): T {
    const [value, setValue] = useState(initialValue);
    useEffect(() => {
        const listener = (newValue: T) => {
            setValue(newValue);
        };
        const removeListener = eventDispatcher.addListener(listener);
        listener(initialValue);
        return removeListener;
    }, [initialValue, eventDispatcher]);
    return value;
}

export type DispatchEvent<T> = (event: T) => void;
export type AddEventListener<T> = (listener: EventListenerType<T>) => EventListenerRemoverType;
type UseEventDispatcher<T> = [DispatchEvent<T>, AddEventListener<T>];

export function useEventDispatcher<T>(): UseEventDispatcher<T> {
    const ref = useRef<EventDispatcher<T>>(null);

    useEffect(() => {
        return () => {
            if (ref.current) {
                ref.current.clearListeners();
                ref.current = null;
            }
        };
    }, []);

    const getter = useCallback(() => {
        if (ref.current === null) {
            ref.current = new EventDispatcher<T>();
        }
        return ref.current;
    }, []);

    return useMemo<UseEventDispatcher<T>>(() => {
        return [
            (event: T) => {
                getter().dispatch(event);
            },
            (listener: EventListenerType<T>) => {
                return getter().addListener(listener);
            }
        ];
    }, [getter]);
}
