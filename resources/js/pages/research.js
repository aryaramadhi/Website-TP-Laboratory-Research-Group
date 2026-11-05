import { getResearchList, getResearchById } from '../api/research.js';
import { extractData } from '../api/client.js';

export const NEUTRAL_AUTHOR = 'TP Laboratory Research Group';

export function sanitizeImageUrl(url, fallback = '/images/research/research-1.jpg') {
    if (!url || typeof url !== 'string') {
        return fallback;
    }

    const trimmed = url.trim();
    if (!trimmed) {
        return fallback;
    }

    // Reject control characters, unencoded quotes, brackets, and whitespace
    if (/[\x00-\x1F\x7F<>"'`\s]/.test(trimmed)) {
        return fallback;
    }

    const lower = trimmed.toLowerCase();
    // Reject dangerous schemes
    if (lower.startsWith('javascript:') || lower.startsWith('data:') || lower.startsWith('vbscript:')) {
        return fallback;
    }

    // Full URLs: only allow http:// and https://
    if (lower.startsWith('http://') || lower.startsWith('https://')) {
        try {
            const parsed = new URL(trimmed);
            if (parsed.protocol === 'http:' || parsed.protocol === 'https:') {
                return trimmed;
            }
        } catch {
            return fallback;
        }
        return fallback;
    }

    // Local / relative paths: reject traversal and double slashes
    if (trimmed.startsWith('//') || trimmed.includes(':') || trimmed.includes('..')) {
        return fallback;
    }

    if (trimmed.startsWith('/images/') || trimmed.startsWith('/storage/')) {
        return trimmed;
    }

    if (trimmed.startsWith('images/') || trimmed.startsWith('storage/')) {
        return `/${trimmed}`;
    }

    if (trimmed.startsWith('/')) {
        return trimmed;
    }

    return `/storage/${trimmed}`;
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

export function resolveAuthor(raw) {
    if (!raw || typeof raw !== 'object') {
        return NEUTRAL_AUTHOR;
    }

    if (Array.isArray(raw.anggota_alumni) && raw.anggota_alumni.length > 0) {
        const names = raw.anggota_alumni
            .map((item) => (item && typeof item === 'object' ? item.nama_lengkap || item.nama : null))
            .filter((name) => typeof name === 'string' && name.trim().length > 0)
            .map((name) => name.trim());
        if (names.length > 0) {
            return names.join(', ');
        }
    }

    if (raw.admin && typeof raw.admin === 'object') {
        const adminName = raw.admin.nama_lengkap || raw.admin.name || raw.admin.nama;
        if (typeof adminName === 'string' && adminName.trim()) {
            return adminName.trim();
        }
    }

    const directAuthor = raw.author ?? raw.peneliti ?? raw.penulis ?? null;
    if (typeof directAuthor === 'string' && directAuthor.trim()) {
        return directAuthor.trim();
    }

    return NEUTRAL_AUTHOR;
}

export function formatIndonesianDate(dateString) {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return String(dateString);
        return new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        }).format(date);
    } catch {
        return String(dateString);
    }
}

export function mapResearchItem(raw) {
    if (!raw || typeof raw !== 'object') {
        return null;
    }

    const id = raw.proyek_id ?? raw.id ?? null;
    const title = raw.judul_penelitian_simulasi ?? raw.title ?? 'Proyek Riset';
    const excerpt = raw.deskripsi_singkat ?? raw.excerpt ?? raw.description ?? '';
    const content = raw.deskripsi_lengkap ?? raw.content ?? raw.deskripsi ?? excerpt;
    const status = raw.status_proyek ?? raw.status ?? 'Sedang Berjalan';
    const date = raw.tanggal_mulai ?? raw.date ?? '';
    const finishDate = raw.tanggal_selesai ?? raw.finish_date ?? null;

    const rawImage = raw.dokumentasi_visual_utama ?? raw.main_image ?? raw.image ?? null;
    const imageUrl = sanitizeImageUrl(rawImage, '/images/research/research-1.jpg');
    const author = resolveAuthor(raw);

    return {
        id,
        title,
        excerpt,
        content,
        status,
        date,
        finishDate,
        image: imageUrl,
        author,
        href: id ? `/riset/${id}` : '#',
        raw,
    };
}

export function renderResearchCard(item, index) {
    const isReverse = index % 2 === 1;
    const reverseClass = isReverse ? 'research-card--reverse' : '';
    const badgePositionClass = isReverse ? 'research-card__badge--left' : 'research-card__badge--right';

    const rawStatus = (item.status || 'sedang-berjalan').toLowerCase().replace(/\s+/g, '-');
    const safeStatusClass = rawStatus.replace(/[^a-z0-9_-]/g, '') || 'sedang-berjalan';

    const safeId = escapeHtml(item.id ?? '');
    const safeTitle = escapeHtml(item.title);
    const safeExcerpt = escapeHtml(item.excerpt);
    const safeAuthor = escapeHtml(item.author);
    const safeStatus = escapeHtml(item.status);
    const safeDate = escapeHtml(item.date || '');
    const safeHref = escapeHtml(item.href || '#');
    const safeImage = escapeHtml(sanitizeImageUrl(item.image, '/images/research/research-1.jpg'));

    const actionHtml = !isReverse
        ? `<img src="/images/icons/chevron-right.png" alt="" class="research-card__link-icon" aria-hidden="true">
           <span>Lihat Detail Riset</span>`
        : `<span>Lihat Detail Riset</span>
           <img src="/images/icons/chevron-right.png" alt="" class="research-card__link-icon" aria-hidden="true">`;

    return `
        <article class="card research-card editable-item ${reverseClass}"
            data-item-id="${safeId}"
            data-item-type="research"
            data-item-title="${safeTitle}"
            data-item-author="${safeAuthor}"
            data-item-date="${safeDate}"
            data-item-status="${safeStatus}"
            data-item-description="${safeExcerpt}"
            data-item-excerpt="${safeExcerpt}"
            data-item-supporting-image-1=""
            data-item-supporting-image-2=""
        >
            <div class="research-card__media">
                <img src="${safeImage}" alt="${safeTitle}" class="research-card__image">
                <div class="research-card__badge ${badgePositionClass}">
                    <span class="badge badge--${safeStatusClass}">${safeStatus}</span>
                </div>
                <button
                    type="button"
                    class="admin-edit-btn"
                    data-open-modal="edit-research-modal"
                    aria-label="Edit Riset"
                >
                    <svg class="admin-edit-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                        <path d="M15 5l4 4"></path>
                    </svg>
                </button>
            </div>

            <div class="research-card__content">
                <h3 class="research-card__title">
                    <a href="${safeHref}">${safeTitle}</a>
                </h3>

                ${safeExcerpt ? `<p class="research-card__excerpt">${safeExcerpt}</p>` : ''}

                <div class="research-card__action">
                    <a href="${safeHref}" class="research-card__link">
                        ${actionHtml}
                    </a>
                </div>
            </div>
        </article>
    `;
}

export function renderDetailContent(paragraphsEl, bodyEl, contentText) {
    const target = paragraphsEl || bodyEl;
    if (!target) return;

    if (paragraphsEl) {
        paragraphsEl.innerHTML = '';
    } else {
        const existingPara = bodyEl.querySelectorAll('.research-detail__paragraph, .research-detail__gallery');
        existingPara.forEach((el) => el.remove());
    }

    const textToRender = typeof contentText === 'string' ? contentText.trim() : '';
    if (!textToRender) {
        const p = document.createElement('p');
        p.className = 'research-detail__paragraph';
        p.textContent = 'Belum ada deskripsi lengkap untuk proyek riset ini.';
        target.appendChild(p);
        return;
    }

    const blocks = textToRender
        .split(/\r?\n\s*\r?\n/)
        .map((b) => b.trim())
        .filter((b) => b.length > 0);

    const fragment = document.createDocumentFragment();
    blocks.forEach((block) => {
        const p = document.createElement('p');
        p.className = 'research-detail__paragraph';

        const lines = block.split(/\r?\n/);
        lines.forEach((line, lineIndex) => {
            if (lineIndex > 0) {
                p.appendChild(document.createElement('br'));
            }
            p.appendChild(document.createTextNode(line));
        });

        fragment.appendChild(p);
    });

    target.appendChild(fragment);
}

export async function loadResearchCatalog(catalogContainer) {
    if (!catalogContainer) return;

    const loadingEl = catalogContainer.querySelector('[data-research-state="loading"]');
    const emptyEl = catalogContainer.querySelector('[data-research-state="empty"]');
    const errorEl = catalogContainer.querySelector('[data-research-state="error"]');
    const errorMsgEl = catalogContainer.querySelector('[data-research-error-message]');
    const retryBtn = catalogContainer.querySelector('[data-research-retry]');

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

    function clearRenderedCards() {
        const existingCards = catalogContainer.querySelectorAll('.research-card');
        existingCards.forEach((card) => card.remove());
    }

    async function fetchData() {
        showState('loading');
        clearRenderedCards();

        try {
            const response = await getResearchList();
            const rawData = extractData(response);

            if (!Array.isArray(rawData) || rawData.length === 0) {
                showState('empty');
                return;
            }

            const items = rawData.map(mapResearchItem).filter(Boolean);
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

            catalogContainer.appendChild(fragment);
        } catch (error) {
            const userMessage = error.message || 'Gagal memuat katalog riset. Periksa koneksi internet atau server backend.';
            showState('error', userMessage);
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

export async function loadResearchDetail(detailContainer) {
    if (!detailContainer) return;

    const pathSegments = window.location.pathname.split('/').filter(Boolean);
    const idFromPath = pathSegments.length >= 2 ? pathSegments[1] : null;
    const researchId = detailContainer.dataset.researchId || idFromPath;

    if (!researchId) {
        return;
    }

    const loadingEl = detailContainer.querySelector('[data-research-detail-loading]');
    const errorEl = detailContainer.querySelector('[data-research-detail-error]');
    const errorMsgEl = detailContainer.querySelector('[data-research-detail-error-message]');
    const retryBtn = detailContainer.querySelector('[data-research-detail-retry]');
    const cardEl = detailContainer.querySelector('[data-research-detail-card], .research-detail__card');
    const actionBarEl = detailContainer.querySelector('[data-research-detail-action], .admin-detail-action-bar');

    const titleEl = detailContainer.querySelector('[data-research-detail-title]');
    const dateEl = detailContainer.querySelector('[data-research-detail-date]');
    const authorEl = detailContainer.querySelector('[data-research-detail-author]');
    const authorWrapperEl = detailContainer.querySelector('[data-research-detail-author-wrapper]');
    const imageEl = detailContainer.querySelector('[data-research-detail-image]');
    const bodyEl = detailContainer.querySelector('[data-research-detail-content]');
    const paragraphsEl = detailContainer.querySelector('[data-research-detail-paragraphs]');

    function showState(stateName, message = '', canRetry = false) {
        if (loadingEl) loadingEl.style.display = stateName === 'loading' ? 'flex' : 'none';
        if (cardEl) cardEl.style.display = stateName === 'content' ? 'block' : 'none';
        if (actionBarEl) actionBarEl.style.display = stateName === 'content' ? 'flex' : 'none';

        if (errorEl) {
            errorEl.style.display = stateName === 'error' ? 'flex' : 'none';
            if (errorMsgEl && message) {
                errorMsgEl.textContent = message;
            }
            if (retryBtn) {
                retryBtn.style.display = canRetry ? 'inline-flex' : 'none';
            }
        }
    }

    async function fetchDetail() {
        showState('loading');

        try {
            const response = await getResearchById(researchId);
            const rawData = extractData(response);
            const item = mapResearchItem(rawData);

            if (!item || !item.id) {
                showState('error', 'Proyek riset tidak ditemukan.', false);
                return;
            }

            if (titleEl) {
                titleEl.textContent = item.title;
                document.title = `${item.title} | TP Laboratory`;
            }

            if (dateEl) {
                dateEl.textContent = item.date ? formatIndonesianDate(item.date) : 'Tanggal tidak tersedia';
            }

            if (authorEl) {
                if (item.author) {
                    authorEl.textContent = `Oleh: ${item.author}`;
                    if (authorWrapperEl) authorWrapperEl.style.display = 'inline-flex';
                } else if (authorWrapperEl) {
                    authorWrapperEl.style.display = 'none';
                }
            }

            if (imageEl) {
                imageEl.src = sanitizeImageUrl(item.image, '/images/research/detail-featured.jpg');
                imageEl.alt = item.title || 'Detail Riset';
            }

            if (cardEl) {
                cardEl.dataset.itemId = String(item.id || '');
                cardEl.dataset.itemTitle = item.title || '';
                cardEl.dataset.itemAuthor = item.author || '';
                cardEl.dataset.itemDate = item.date || '';
                cardEl.dataset.itemStatus = item.status || '';
                cardEl.dataset.itemDescription = item.excerpt || '';
                cardEl.dataset.itemExcerpt = item.excerpt || '';
            }

            renderDetailContent(paragraphsEl, bodyEl, item.content || item.excerpt);

            showState('content');
        } catch (error) {
            const isNotFound = error.status === 404 || (error.message && error.message.toLowerCase().includes('tidak ditemukan'));
            if (isNotFound) {
                showState('error', 'Proyek riset tidak ditemukan.', false);
            } else {
                const message = error.message || 'Gagal memuat detail riset. Periksa koneksi internet atau server backend.';
                showState('error', message, true);
            }
        }
    }

    if (retryBtn && !retryBtn.dataset.boundRetry) {
        retryBtn.dataset.boundRetry = 'true';
        retryBtn.addEventListener('click', () => {
            fetchDetail();
        });
    }

    await fetchDetail();
}

export function initResearchPage() {
    const catalogContainer = document.querySelector('[data-research-catalog-list], #research-catalog-list');
    if (catalogContainer) {
        loadResearchCatalog(catalogContainer);
    }

    const detailContainer = document.querySelector('[data-research-detail], .research-detail');
    if (detailContainer) {
        loadResearchDetail(detailContainer);
    }
}

export default {
    NEUTRAL_AUTHOR,
    sanitizeImageUrl,
    escapeHtml,
    resolveAuthor,
    formatIndonesianDate,
    mapResearchItem,
    renderResearchCard,
    renderDetailContent,
    loadResearchCatalog,
    loadResearchDetail,
    initResearchPage,
};
