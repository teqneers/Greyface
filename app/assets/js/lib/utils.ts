import {clsx, type ClassValue} from 'clsx';
import {twMerge} from 'tailwind-merge';

/** Merges Tailwind class lists, later classes winning over earlier ones. */
export function cn(...inputs: ClassValue[]): string {
    return twMerge(clsx(inputs));
}
