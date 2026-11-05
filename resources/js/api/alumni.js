import { apiClient, extractData } from './client.js';

export async function getAlumniList(params = null, options = {}) {
    return apiClient.get('/anggota-alumni', { params, ...options });
}

export async function getAlumniById(id, options = {}) {
    return apiClient.get(`/anggota-alumni/${id}`, options);
}

export { extractData as extractAlumniData };

export const alumniApi = {
    getList: getAlumniList,
    getById: getAlumniById,
    extract: extractData,
};

export default alumniApi;

