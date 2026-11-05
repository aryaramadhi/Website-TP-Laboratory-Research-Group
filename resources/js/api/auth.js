import {
    apiClient,
    getAuthToken,
    setAuthToken,
    removeAuthToken,
    hasAuthToken,
    onUnauthorized,
    extractData,
} from './client.js';

export const ADMIN_STORAGE_KEY = 'tp_lab_auth_admin';

export function getAdminUser() {
    if (typeof window === 'undefined' || !window.sessionStorage) return null;
    try {
        const raw = window.sessionStorage.getItem(ADMIN_STORAGE_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch {
        return null;
    }
}

export function setAdminUser(user) {
    if (typeof window === 'undefined' || !window.sessionStorage) return;
    if (user && typeof user === 'object') {
        window.sessionStorage.setItem(ADMIN_STORAGE_KEY, JSON.stringify(user));
    } else {
        window.sessionStorage.removeItem(ADMIN_STORAGE_KEY);
    }
}

export function removeAdminUser() {
    if (typeof window === 'undefined' || !window.sessionStorage) return;
    window.sessionStorage.removeItem(ADMIN_STORAGE_KEY);
}

export async function login(credentials, options = {}) {
    return apiClient.post('/login', credentials, options);
}

export async function getMe(options = {}) {
    return apiClient.get('/me', options);
}

export async function logout(options = {}) {
    return apiClient.post('/logout', null, options);
}

export {
    getAuthToken,
    setAuthToken,
    removeAuthToken,
    hasAuthToken,
    onUnauthorized,
    extractData as extractAuthData,
};

export const authApi = {
    login,
    getMe,
    logout,
    getToken: getAuthToken,
    setToken: setAuthToken,
    removeToken: removeAuthToken,
    hasToken: hasAuthToken,
    getAdminUser,
    setAdminUser,
    removeAdminUser,
    onUnauthorized,
};

export default authApi;
