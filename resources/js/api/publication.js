import { apiClient, extractData } from './client.js';

export async function getPublicationList(params = null, options = {}) {
    return apiClient.get('/publikasi-hibah', { params, ...options });
}

export async function getPublicationById(id, options = {}) {
    return apiClient.get(`/publikasi-hibah/${id}`, options);
}

export async function createPublication(payload, options = {}) {
    return apiClient.post('/publikasi-hibah', payload, options);
}

export async function updatePublication(id, payload, options = {}) {
    return apiClient.put(`/publikasi-hibah/${id}`, payload, options);
}

export async function deletePublication(id, options = {}) {
    return apiClient.delete(`/publikasi-hibah/${id}`, options);
}

export { extractData as extractPublicationData };

export const publicationApi = {
    getList: getPublicationList,
    getById: getPublicationById,
    create: createPublication,
    update: updatePublication,
    delete: deletePublication,
    extract: extractData,
};

export default publicationApi;
