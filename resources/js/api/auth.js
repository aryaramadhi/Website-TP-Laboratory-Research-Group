import {
    apiClient,
    getAuthToken,
    setAuthToken,
    removeAuthToken,
    hasAuthToken,
    onUnauthorized,
    extractData,
} from './client.js';

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
    onUnauthorized,
};

export default authApi;
