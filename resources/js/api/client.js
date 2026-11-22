const TOKEN_STORAGE_KEY = 'tp_lab_auth_token';

let customBaseUrl = null;
const unauthorizedListeners = new Set();

export class ApiError extends Error {
    constructor(message, { status = 0, data = null, response = null, originalError = null } = {}) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.data = data;
        this.response = response;
        this.originalError = originalError;
    }
}

export function setBaseUrl(url) {
    customBaseUrl = typeof url === 'string' ? url.trim() : null;
}

export function getBaseUrl() {
    if (customBaseUrl) {
        return customBaseUrl;
    }

    if (typeof window !== 'undefined' && window.__API_BASE_URL__) {
        return String(window.__API_BASE_URL__).trim();
    }

    if (typeof import.meta !== 'undefined' && import.meta.env && import.meta.env.VITE_API_BASE_URL) {
        return String(import.meta.env.VITE_API_BASE_URL).trim();
    }

    return '/api';
}

export function getAuthToken() {
    if (typeof window === 'undefined' || !window.sessionStorage) {
        return null;
    }

    return window.sessionStorage.getItem(TOKEN_STORAGE_KEY) || null;
}

export function setAuthToken(token) {
    if (typeof window === 'undefined' || !window.sessionStorage) {
        return;
    }

    if (token && typeof token === 'string') {
        window.sessionStorage.setItem(TOKEN_STORAGE_KEY, token.trim());
    } else {
        window.sessionStorage.removeItem(TOKEN_STORAGE_KEY);
    }
}

export function removeAuthToken() {
    if (typeof window === 'undefined' || !window.sessionStorage) {
        return;
    }

    window.sessionStorage.removeItem(TOKEN_STORAGE_KEY);
}

export function hasAuthToken() {
    return Boolean(getAuthToken());
}

export function onUnauthorized(callback) {
    if (typeof callback === 'function') {
        unauthorizedListeners.add(callback);
        return () => unauthorizedListeners.delete(callback);
    }

    return () => {};
}

function notifyUnauthorized(error) {
    unauthorizedListeners.forEach((listener) => {
        try {
            listener(error);
        } catch (listenerError) {
            console.error('Gagal mengeksekusi listener unauthorized:', listenerError);
        }
    });
}

export function extractData(payload) {
    if (payload && typeof payload === 'object' && !Array.isArray(payload) && Object.prototype.hasOwnProperty.call(payload, 'data')) {
        return payload.data;
    }

    return payload;
}

function buildUrl(endpoint, params = null) {
    const base = getBaseUrl().replace(/\/+$/, '');
    let cleanEndpoint = endpoint.startsWith('/') ? endpoint : `/${endpoint}`;

    if (base.endsWith('/api') && cleanEndpoint.startsWith('/api/')) {
        cleanEndpoint = cleanEndpoint.slice(4);
    }

    let url = `${base}${cleanEndpoint}`;

    if (params && typeof params === 'object') {
        const searchParams = new URLSearchParams();
        Object.entries(params).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                searchParams.append(key, String(value));
            }
        });

        const queryString = searchParams.toString();
        if (queryString) {
            url += (url.includes('?') ? '&' : '?') + queryString;
        }
    }

    return url;
}

function resolveErrorMessage(status, data) {
    if (data && typeof data === 'object') {
        if (typeof data.message === 'string' && data.message.trim()) {
            return data.message;
        }

        if (data.errors && typeof data.errors === 'object') {
            const firstKey = Object.keys(data.errors)[0];
            const firstMsg = Array.isArray(data.errors[firstKey]) ? data.errors[firstKey][0] : data.errors[firstKey];
            if (firstMsg) {
                return String(firstMsg);
            }
        }
    }

    switch (status) {
        case 401:
            return 'Sesi tidak valid atau telah berakhir. Silakan masuk kembali.';
        case 403:
            return 'Anda tidak memiliki hak akses untuk tindakan ini.';
        case 404:
            return 'Data atau endpoint yang diminta tidak ditemukan.';
        case 422:
            return 'Data yang dikirim tidak valid. Periksa kembali isian formulir.';
        case 500:
            return 'Terjadi kesalahan internal pada server.';
        default:
            return `Permintaan gagal dengan status ${status}.`;
    }
}

export async function request(endpoint, options = {}) {
    const {
        method = 'GET',
        params = null,
        body = null,
        headers: customHeaders = {},
        ...restOptions
    } = options;

    const url = buildUrl(endpoint, params);
    const headers = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...customHeaders,
    };

    const token = getAuthToken();
    if (token && !headers.Authorization && !headers.authorization) {
        headers.Authorization = `Bearer ${token}`;
    }

    const isFormData = typeof FormData !== 'undefined' && body instanceof FormData;
    let serializedBody = body;

    if (body !== undefined && body !== null) {
        if (isFormData) {
            delete headers['Content-Type'];
            delete headers['content-type'];
        } else if (typeof body === 'object') {
            headers['Content-Type'] = 'application/json';
            serializedBody = JSON.stringify(body);
        } else if (!headers['Content-Type'] && !headers['content-type']) {
            headers['Content-Type'] = 'application/json';
        }
    }

    let response;
    try {
        response = await fetch(url, {
            method: method.toUpperCase(),
            headers,
            body: ['GET', 'HEAD'].includes(method.toUpperCase()) ? undefined : serializedBody,
            ...restOptions,
        });
    } catch (networkError) {
        throw new ApiError('Gagal terhubung ke server backend. Periksa koneksi internet atau server.', {
            status: 0,
            originalError: networkError,
        });
    }

    let responseData = null;
    const contentType = response.headers.get('content-type') || '';

    if (response.status !== 204) {
        if (contentType.includes('application/json')) {
            try {
                responseData = await response.json();
            } catch (parseError) {
                responseData = null;
            }
        } else {
            try {
                responseData = await response.text();
            } catch (parseError) {
                responseData = null;
            }
        }
    }

    if (!response.ok) {
        const message = resolveErrorMessage(response.status, responseData);
        const apiError = new ApiError(message, {
            status: response.status,
            data: responseData,
            response,
        });

        const cleanEndpoint = (typeof endpoint === 'string' ? endpoint : '')
            .trim()
            .replace(/^\/+/, '')
            .replace(/^api\/+/, '');
        const isLoginRequest = cleanEndpoint === 'login' || cleanEndpoint.startsWith('login?');

        if (response.status === 401 && !isLoginRequest) {
            notifyUnauthorized(apiError);
        }

        throw apiError;
    }

    return responseData;
}

export const apiClient = {
    get(endpoint, options = {}) {
        return request(endpoint, { ...options, method: 'GET' });
    },
    post(endpoint, body = null, options = {}) {
        return request(endpoint, { ...options, method: 'POST', body });
    },
    put(endpoint, body = null, options = {}) {
        return request(endpoint, { ...options, method: 'PUT', body });
    },
    delete(endpoint, options = {}) {
        return request(endpoint, { ...options, method: 'DELETE' });
    },
    request,
};

export default apiClient;

