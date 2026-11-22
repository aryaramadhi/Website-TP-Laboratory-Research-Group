import { apiClient, extractData } from './client.js';

export async function getAchievementList(params = null, options = {}) {
    return apiClient.get('/prestasi-pencapaian', { params, ...options });
}

export async function getAchievementById(id, options = {}) {
    return apiClient.get(`/prestasi-pencapaian/${id}`, options);
}

export async function createAchievement(payload, options = {}) {
    return apiClient.post('/prestasi-pencapaian', payload, options);
}

export async function updateAchievement(id, payload, options = {}) {
    if (typeof FormData !== 'undefined' && payload instanceof FormData) {
        if (!payload.has('_method')) {
            payload.append('_method', 'PUT');
        }
        return apiClient.post(`/prestasi-pencapaian/${id}`, payload, options);
    }
    return apiClient.put(`/prestasi-pencapaian/${id}`, payload, options);
}

export async function deleteAchievement(id, options = {}) {
    return apiClient.delete(`/prestasi-pencapaian/${id}`, options);
}

export { extractData as extractAchievementData };

export const achievementApi = {
    getList: getAchievementList,
    getById: getAchievementById,
    create: createAchievement,
    update: updateAchievement,
    delete: deleteAchievement,
    extract: extractData,
};

export default achievementApi;
