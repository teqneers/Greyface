import {apiFetch, apiJson} from '@/lib/api';
import type {User, UserRole} from '@/types/user';

export interface UserValues {
    username: string,
    email: string,
    role: UserRole,
}

export const usersApi = (apiUrl: string) => ({
    get: (id: string) => apiFetch<User>(`${apiUrl}/users/${id}`),
    create: (values: UserValues & { password: string }) => apiJson<{ user: string }>(`${apiUrl}/users`, 'POST', values),
    update: (id: string, values: UserValues) => apiJson<{ user: string }>(`${apiUrl}/users/${id}`, 'PUT', values),
    setPassword: (id: string, password: string) => apiJson(`${apiUrl}/users/${id}/password`, 'PUT', {password}),
    remove: (id: string) => apiFetch(`${apiUrl}/users/${id}`, {method: 'DELETE'}),
});
