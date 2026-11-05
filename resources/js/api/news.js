import { apiClient, extractData } from './client.js';

export async function getNewsList(params = null, options = {}) {
    return apiClient.get('/berita-isu', { params, ...options });
}

export async function getNewsById(id, options = {}) {
    return apiClient.get(`/berita-isu/${id}`, options);
}

export async function createNews(payload, options = {}) {
    return apiClient.post('/berita-isu', payload, options);
}

export async function updateNews(id, payload, options = {}) {
    return apiClient.put(`/berita-isu/${id}`, payload, options);
}

export async function deleteNews(id, options = {}) {
    return apiClient.delete(`/berita-isu/${id}`, options);
}

export { extractData as extractNewsData };

export const newsApi = {
    getList: getNewsList,
    getById: getNewsById,
    create: createNews,
    update: updateNews,
    delete: deleteNews,
    extract: extractData,
};

export default newsApi;
