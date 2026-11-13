import { getResearchList } from '../api/research.js';
import { getNewsList } from '../api/news.js';
import { getAchievementList } from '../api/achievement.js';
import { extractData } from '../api/client.js';

import { mapResearchItem, renderResearchCard } from './research.js';
import { mapNewsItem, renderNewsCard } from './news.js';
import { mapAchievementItem, renderAchievementCard } from './achievement.js';

export const MAX_HOME_ITEMS = 2;

export function sortResearchItemsByLatest(items) {
    if (!Array.isArray(items)) return [];

    return [...items].sort((a, b) => {
        const timeA = a && a.date ? Date.parse(a.date) : NaN;
        const timeB = b && b.date ? Date.parse(b.date) : NaN;
        const validA = !Number.isNaN(timeA);
        const validB = !Number.isNaN(timeB);

        if (validA && validB && timeA !== timeB) {
            return timeB - timeA;
        }

        const idA = a && a.id !== null && a.id !== undefined ? Number(a.id) : NaN;
        const idB = b && b.id !== null && b.id !== undefined ? Number(b.id) : NaN;
        const validIdA = !Number.isNaN(idA);
        const validIdB = !Number.isNaN(idB);

        if (validIdA && validIdB && idA !== idB) {
            return idB - idA;
        }
        if (validIdA && !validIdB) return -1;
        if (!validIdA && validIdB) return 1;

        return String((b && b.id) || '').localeCompare(String((a && a.id) || ''));
    });
}

export function clearSectionCards(container, cardClass) {
    if (!container) return;
    const cards = container.querySelectorAll(`.${cardClass}`);
    cards.forEach((card) => card.remove());
}

export async function loadHomeResearch(container) {
    if (!container) return;

    const loadingEl = container.querySelector('[data-home-research-state="loading"]');
    const emptyEl = container.querySelector('[data-home-research-state="empty"]');
    const errorEl = container.querySelector('[data-home-research-state="error"]');
    const errorMsgEl = container.querySelector('[data-home-research-error-message]');
    const retryBtn = container.querySelector('[data-home-research-retry]');

    function showState(stateName, message = '') {
        if (loadingEl) loadingEl.style.display = stateName === 'loading' ? 'flex' : 'none';
        if (emptyEl) emptyEl.style.display = stateName === 'empty' ? 'flex' : 'none';
        if (errorEl) {
            errorEl.style.display = stateName === 'error' ? 'flex' : 'none';
            if (errorMsgEl && message) {
                errorMsgEl.textContent = message;
            }
        }
    }

    async function fetchResearch() {
        showState('loading');
        clearSectionCards(container, 'research-card');

        try {
            const response = await getResearchList();
            const rawData = extractData(response);

            if (!Array.isArray(rawData) || rawData.length === 0) {
                showState('empty');
                return;
            }

            const items = rawData
                .map(mapResearchItem)
                .filter(Boolean)
                .slice(0, MAX_HOME_ITEMS);

            if (items.length === 0) {
                showState('empty');
                return;
            }

            showState('none');

            const fragment = document.createDocumentFragment();
            const tempWrapper = document.createElement('div');
            tempWrapper.innerHTML = items.map(renderResearchCard).join('');

            while (tempWrapper.firstElementChild) {
                fragment.appendChild(tempWrapper.firstElementChild);
            }

            container.appendChild(fragment);
        } catch (error) {
            const userMessage = error.message || 'Gagal memuat hasil riset. Periksa koneksi internet atau server backend.';
            showState('error', userMessage);
        }
    }

    if (retryBtn && !retryBtn.dataset.boundRetry) {
        retryBtn.dataset.boundRetry = 'true';
        retryBtn.addEventListener('click', () => {
            fetchResearch();
        });
    }

    await fetchResearch();
}

export async function loadHomeNews(container) {
    if (!container) return;

    const loadingEl = container.querySelector('[data-home-news-state="loading"]');
    const emptyEl = container.querySelector('[data-home-news-state="empty"]');
    const errorEl = container.querySelector('[data-home-news-state="error"]');
    const errorMsgEl = container.querySelector('[data-home-news-error-message]');
    const retryBtn = container.querySelector('[data-home-news-retry]');

    function showState(stateName, message = '') {
        if (loadingEl) loadingEl.style.display = stateName === 'loading' ? 'flex' : 'none';
        if (emptyEl) emptyEl.style.display = stateName === 'empty' ? 'flex' : 'none';
        if (errorEl) {
            errorEl.style.display = stateName === 'error' ? 'flex' : 'none';
            if (errorMsgEl && message) {
                errorMsgEl.textContent = message;
            }
        }
    }

    async function fetchNews() {
        showState('loading');
        clearSectionCards(container, 'news-card');

        try {
            const response = await getNewsList();
            const rawData = extractData(response);

            if (!Array.isArray(rawData) || rawData.length === 0) {
                showState('empty');
                return;
            }

            const items = rawData
                .map(mapNewsItem)
                .filter(Boolean)
                .slice(0, MAX_HOME_ITEMS);

            if (items.length === 0) {
                showState('empty');
                return;
            }

            showState('none');

            const fragment = document.createDocumentFragment();
            const tempWrapper = document.createElement('div');
            tempWrapper.innerHTML = items.map(renderNewsCard).join('');

            while (tempWrapper.firstElementChild) {
                fragment.appendChild(tempWrapper.firstElementChild);
            }

            container.appendChild(fragment);
        } catch (error) {
            const userMessage = error.message || 'Gagal memuat berita terkini. Periksa koneksi internet atau server backend.';
            showState('error', userMessage);
        }
    }

    if (retryBtn && !retryBtn.dataset.boundRetry) {
        retryBtn.dataset.boundRetry = 'true';
        retryBtn.addEventListener('click', () => {
            fetchNews();
        });
    }

    await fetchNews();
}

export async function loadHomeAchievements(container) {
    if (!container) return;

    const loadingEl = container.querySelector('[data-home-achievement-state="loading"]');
    const emptyEl = container.querySelector('[data-home-achievement-state="empty"]');
    const errorEl = container.querySelector('[data-home-achievement-state="error"]');
    const errorMsgEl = container.querySelector('[data-home-achievement-error-message]');
    const retryBtn = container.querySelector('[data-home-achievement-retry]');

    function showState(stateName, message = '') {
        if (loadingEl) loadingEl.style.display = stateName === 'loading' ? 'flex' : 'none';
        if (emptyEl) emptyEl.style.display = stateName === 'empty' ? 'flex' : 'none';
        if (errorEl) {
            errorEl.style.display = stateName === 'error' ? 'flex' : 'none';
            if (errorMsgEl && message) {
                errorMsgEl.textContent = message;
            }
        }
    }

    async function fetchAchievements() {
        showState('loading');
        clearSectionCards(container, 'achievement-card');

        try {
            const response = await getAchievementList();
            const rawData = extractData(response);

            if (!Array.isArray(rawData) || rawData.length === 0) {
                showState('empty');
                return;
            }

            const items = rawData
                .map(mapAchievementItem)
                .filter(Boolean)
                .slice(0, MAX_HOME_ITEMS);

            if (items.length === 0) {
                showState('empty');
                return;
            }

            showState('none');

            const fragment = document.createDocumentFragment();
            const tempWrapper = document.createElement('div');
            tempWrapper.innerHTML = items.map(renderAchievementCard).join('');

            while (tempWrapper.firstElementChild) {
                fragment.appendChild(tempWrapper.firstElementChild);
            }

            container.appendChild(fragment);
        } catch (error) {
            const userMessage = error.message || 'Gagal memuat prestasi & pencapaian. Periksa koneksi internet atau server backend.';
            showState('error', userMessage);
        }
    }

    if (retryBtn && !retryBtn.dataset.boundRetry) {
        retryBtn.dataset.boundRetry = 'true';
        retryBtn.addEventListener('click', () => {
            fetchAchievements();
        });
    }

    await fetchAchievements();
}

export function initHomePage() {
    const homeContent = document.querySelector('[data-home-page], .home-content');
    if (!homeContent) return;

    const researchContainer = document.querySelector('[data-home-research-list], #home-research-list');
    const newsContainer = document.querySelector('[data-home-news-list], #home-news-list');
    const achievementContainer = document.querySelector('[data-home-achievement-list], #home-achievement-list');

    // Run each section independently to prevent one failure from blocking others
    if (researchContainer) {
        loadHomeResearch(researchContainer);
    }
    if (newsContainer) {
        loadHomeNews(newsContainer);
    }
    if (achievementContainer) {
        loadHomeAchievements(achievementContainer);
    }
}

export default {
    MAX_HOME_ITEMS,
    clearSectionCards,
    sortResearchItemsByLatest,
    loadHomeResearch,
    loadHomeNews,
    loadHomeAchievements,
    initHomePage,
};

