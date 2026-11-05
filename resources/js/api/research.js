import { apiClient, extractData } from './client.js';

export async function getResearchList(params = null, options = {}) {
    return apiClient.get('/proyek-riset', { params, ...options });
}

export async function getResearchById(id, options = {}) {
    return apiClient.get(`/proyek-riset/${id}`, options);
}

export async function createResearch(payload, options = {}) {
    return apiClient.post('/proyek-riset', payload, options);
}

export async function updateResearch(id, payload, options = {}) {
    return apiClient.put(`/proyek-riset/${id}`, payload, options);
}

export async function deleteResearch(id, options = {}) {
    return apiClient.delete(`/proyek-riset/${id}`, options);
}

export { extractData as extractResearchData };

export const researchApi = {
    getList: getResearchList,
    getById: getResearchById,
    create: createResearch,
    update: updateResearch,
    delete: deleteResearch,
    extract: extractData,
};

export default researchApi;
