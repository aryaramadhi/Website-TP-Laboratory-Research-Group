import { getPublicationList } from '../api/publication.js';
import { extractData } from '../api/client.js';

export const NEUTRAL_AUTHOR = 'TP Laboratory Research Group';

export function sanitizeExternalLink(url) {
    if (!url || typeof url !== 'string') {
        return null;
    }

    const trimmed = url.trim();
    if (!trimmed) {
        return null;
    }

    // Reject control characters, unencoded quotes, brackets, and whitespace
    if (/[\x00-\x1F\x7F<>"'`\s]/.test(trimmed)) {
        return null;
    }

    const lower = trimmed.toLowerCase();
    // Reject dangerous schemes
    if (lower.startsWith('javascript:') || lower.startsWith('data:') || lower.startsWith('vbscript:')) {
        return null;
    }

    // Reject protocol-relative URLs
    if (trimmed.startsWith('//')) {
        return null;
    }

    // Allow safe protocols
    if (lower.startsWith('http://') || lower.startsWith('https://')) {
        try {
            const parsed = new URL(trimmed);
            if (parsed.protocol === 'http:' || parsed.protocol === 'https:') {
                return trimmed;
            }
        } catch {
            return null;
        }
    }

    // Expand bare DOI strings (e.g. 10.1000/xyz123) to https://doi.org/...
    if (/^10\.\d{4,9}\/[-._;()/:A-Za-z0-9]+$/.test(trimmed)) {
        return `https://doi.org/${trimmed}`;
    }

    return null;
}

export function escapeHtml(str) {
    if (str === null || str === undefined) {
        return '';
    }
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

export function resolvePublicationAuthor(raw) {
    if (!raw || typeof raw !== 'object') {
        return NEUTRAL_AUTHOR;
    }

    // 1. Check eager-loaded penulis relation (many-to-many with AnggotaAlumni)
    if (Array.isArray(raw.penulis) && raw.penulis.length > 0) {
        const sorted = [...raw.penulis].sort((a, b) => {
            const orderA = a?.pivot?.urutan_penulis ?? 999;
            const orderB = b?.pivot?.urutan_penulis ?? 999;
            return orderA - orderB;
        });

        const names = sorted
            .map((p) => (p && typeof p === 'object' ? p.nama_lengkap || p.nama : null))
            .filter((name) => typeof name === 'string' && name.trim().length > 0)
            .map((name) => name.trim());

        if (names.length > 0) {
            return names.join(', ');
        }
    }

    // 2. Check direct author / penulis string
    const directAuthor = raw.author ?? raw.penulis ?? null;
    if (typeof directAuthor === 'string' && directAuthor.trim()) {
        return directAuthor.trim();
    }

    // 3. Check admin relation
    if (raw.admin && typeof raw.admin === 'object') {
        const adminName = raw.admin.nama_lengkap || raw.admin.name;
        if (typeof adminName === 'string' && adminName.trim()) {
            return adminName.trim();
        }
    }

    return NEUTRAL_AUTHOR;
}

export function mapPublicationItem(raw) {
    if (!raw || typeof raw !== 'object') {
        return null;
    }

    const id = raw.publikasi_hibah_id ?? raw.id ?? null;
    const title = raw.judul_karya_hibah ?? raw.title ?? 'Publikasi Ilmiah';
    const journal = raw.nama_jurnal_skema ?? raw.journal ?? raw.venue ?? '';
    const rawYear = raw.tahun ?? raw.year ?? null;
    let year = null;
    if (rawYear !== null && rawYear !== undefined) {
        const parsedYear = parseInt(rawYear, 10);
        if (!isNaN(parsedYear)) {
            year = parsedYear;
        }
    }

    const description = raw.deskripsi_pencapaian ?? raw.description ?? '';
    const rawLink = raw.tautan_eksternal ?? raw.link ?? raw.doi ?? null;
    const link = sanitizeExternalLink(rawLink);
    const doi = link || (typeof rawLink === 'string' && !/[\x00-\x1F\x7F<>"'`\s:]/.test(rawLink) ? rawLink.trim() : '');
    const jenis = raw.jenis ?? 'Publikasi Jurnal';
    const author = resolvePublicationAuthor(raw);

    return {
        id,
        title,
        journal,
        year,
        description,
        link,
        doi,
        jenis,
        author,
        raw,
    };
}

export function groupPublicationsByYear(items) {
    if (!Array.isArray(items)) return [];

    const map = new Map();
    items.forEach((item) => {
        if (!item) return;
        const rawYear = item.year;
        const yearKey = rawYear !== null && rawYear !== undefined && String(rawYear).trim() !== ''
            ? String(rawYear).trim()
            : 'Lainnya';

        if (!map.has(yearKey)) {
            map.set(yearKey, []);
        }
        map.get(yearKey).push(item);
    });

    const sortedKeys = Array.from(map.keys()).sort((a, b) => {
        if (a === 'Lainnya') return 1;
        if (b === 'Lainnya') return -1;
        const numA = parseInt(a, 10);
        const numB = parseInt(b, 10);
        if (isNaN(numA) && isNaN(numB)) return a.localeCompare(b);
        if (isNaN(numA)) return 1;
        if (isNaN(numB)) return -1;
        return numB - numA;
    });

    return sortedKeys.map((year) => ({
        year,
        items: map.get(year),
    }));
}

export function renderPublicationEntry(item) {
    const safeId = escapeHtml(item.id ?? '');
    const safeTitle = escapeHtml(item.title);
    const safeAuthor = escapeHtml(item.author);
    const safeJournal = escapeHtml(item.journal);
    const safeYear = escapeHtml(item.year ?? '');
    const safeDoi = escapeHtml(item.doi ?? '');
    const safeLink = escapeHtml(item.link ?? '');
    const safeDescription = escapeHtml(item.description);

    const href = item.link ? escapeHtml(item.link) : '#';
    const targetAttrs = item.link ? 'target="_blank" rel="noopener noreferrer"' : '';

    let venueHtml = '';
    if (safeJournal && safeYear) {
        venueHtml = `${safeJournal} &bull; ${safeYear}`;
    } else if (safeJournal) {
        venueHtml = safeJournal;
    } else if (safeYear) {
        venueHtml = safeYear;
    }

    return `
        <li class="publication-accordion__entry editable-item"
            data-item-id="${safeId}"
            data-item-type="publication"
            data-item-title="${safeTitle}"
            data-item-author="${safeAuthor}"
            data-item-journal="${safeJournal}"
            data-item-year="${safeYear}"
            data-item-doi="${safeDoi || safeLink}"
            data-item-link="${safeLink || safeDoi}"
            data-item-description="${safeDescription}"
        >
            <button
                type="button"
                class="admin-edit-btn"
                data-open-modal="edit-publication-modal"
                aria-label="Edit Publikasi"
            >
                <svg class="admin-edit-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                    <path d="M15 5l4 4"></path>
                </svg>
            </button>
            <h4 class="publication-accordion__entry-title">
                <a
                    href="${href}"
                    ${targetAttrs}
                    class="publication-accordion__entry-link"
                >${safeTitle}</a>
            </h4>
            <p class="publication-accordion__entry-authors">
                ${safeAuthor}
            </p>
            ${venueHtml ? `<p class="publication-accordion__entry-venue">${venueHtml}</p>` : ''}
        </li>
    `;
}

export function renderAccordionItem(year, items, isOpenByDefault = false) {
    const safeYear = escapeHtml(year);
    const isOpenClass = isOpenByDefault ? 'is-open' : '';
    const isExpanded = isOpenByDefault ? 'true' : 'false';
    const isHidden = isOpenByDefault ? '' : 'hidden';

    const entriesHtml = items.map(renderPublicationEntry).join('');

    return `
        <div class="publication-accordion__item ${isOpenClass}" data-accordion-item data-year="${safeYear}">
            <button
                type="button"
                class="publication-accordion__header"
                aria-expanded="${isExpanded}"
                data-accordion-trigger
            >
                <img
                    src="/images/icons/accordion-chevron.png"
                    alt=""
                    class="publication-accordion__icon"
                    aria-hidden="true"
                >
                <span class="publication-accordion__year">${safeYear}</span>
            </button>

            <div class="publication-accordion__body" data-accordion-content ${isHidden}>
                <div class="publication-accordion__content">
                    <ul class="publication-accordion__list">
                        ${entriesHtml}
                    </ul>
                </div>
            </div>
        </div>
    `;
}

export function attachAccordionDelegation(accordionContainer) {
    if (!accordionContainer || accordionContainer.dataset.accordionBound) return;
    accordionContainer.dataset.accordionBound = 'true';

    accordionContainer.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-accordion-trigger]');
        if (!trigger || !accordionContainer.contains(trigger)) return;

        const item = trigger.closest('[data-accordion-item]');
        if (!item) return;

        const content = item.querySelector('[data-accordion-content]');
        const isOpen = item.classList.contains('is-open');

        if (isOpen) {
            item.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
            if (content) content.hidden = true;
        } else {
            item.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
            if (content) content.hidden = false;
        }
    });
}

export async function loadPublicationList(pageContainer) {
    if (!pageContainer) return;

    const loadingEl = pageContainer.querySelector('[data-publication-state="loading"]');
    const emptyEl = pageContainer.querySelector('[data-publication-state="empty"]');
    const errorEl = pageContainer.querySelector('[data-publication-state="error"]');
    const errorMsgEl = pageContainer.querySelector('[data-publication-error-message]');
    const retryBtn = pageContainer.querySelector('[data-publication-retry]');
    const accordionEl = pageContainer.querySelector('[data-publication-accordion]');

    function showState(stateName, message = '') {
        if (loadingEl) loadingEl.style.display = stateName === 'loading' ? 'flex' : 'none';
        if (emptyEl) emptyEl.style.display = stateName === 'empty' ? 'flex' : 'none';
        if (accordionEl) accordionEl.style.display = stateName === 'content' ? 'flex' : 'none';

        if (errorEl) {
            errorEl.style.display = stateName === 'error' ? 'flex' : 'none';
            if (errorMsgEl && message) {
                errorMsgEl.textContent = message;
            }
        }
    }

    if (accordionEl) {
        attachAccordionDelegation(accordionEl);
    }

    async function fetchData() {
        showState('loading');
        if (accordionEl) {
            accordionEl.innerHTML = '';
        }

        try {
            const response = await getPublicationList();
            const rawData = extractData(response);

            if (!Array.isArray(rawData) || rawData.length === 0) {
                showState('empty');
                return;
            }

            const items = rawData.map(mapPublicationItem).filter(Boolean);
            if (items.length === 0) {
                showState('empty');
                return;
            }

            const groups = groupPublicationsByYear(items);
            if (groups.length === 0) {
                showState('empty');
                return;
            }

            const html = groups
                .map((group, index) => renderAccordionItem(group.year, group.items, index === 0))
                .join('');

            if (accordionEl) {
                accordionEl.innerHTML = html;
            }

            showState('content');
        } catch (error) {
            const message = error.message || 'Gagal memuat daftar publikasi. Periksa koneksi internet atau server backend.';
            showState('error', message);
        }
    }

    if (retryBtn && !retryBtn.dataset.boundRetry) {
        retryBtn.dataset.boundRetry = 'true';
        retryBtn.addEventListener('click', () => {
            fetchData();
        });
    }

    await fetchData();
}

export function initPublicationPage() {
    const pageContainer = document.querySelector('[data-publication-page], .publication-page');
    if (pageContainer) {
        loadPublicationList(pageContainer);
    }
}

export default {
    NEUTRAL_AUTHOR,
    sanitizeExternalLink,
    escapeHtml,
    resolvePublicationAuthor,
    mapPublicationItem,
    groupPublicationsByYear,
    renderPublicationEntry,
    renderAccordionItem,
    attachAccordionDelegation,
    loadPublicationList,
    initPublicationPage,
};
