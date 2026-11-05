import { getAchievementList, getAchievementById } from '../api/achievement.js';
import { extractData } from '../api/client.js';

export const NEUTRAL_AUTHOR = 'TP Laboratory Research Group';
export const DEFAULT_ACHIEVEMENT_IMAGE = '/images/home/achievement-1.jpg';
export const DEFAULT_DETAIL_IMAGE = '/images/achievements/detail-featured.jpg';

export function sanitizeImageUrl(url, fallback = DEFAULT_ACHIEVEMENT_IMAGE) {
    if (!url || typeof url !== 'string') {
        return fallback;
    }

    const trimmed = url.trim();
    if (!trimmed) {
        return fallback;
    }

    if (/[\x00-\x1F\x7F<>"'`\s]/.test(trimmed)) {
        return fallback;
    }

    const lower = trimmed.toLowerCase();
    if (lower.startsWith('javascript:') || lower.startsWith('data:') || lower.startsWith('vbscript:')) {
        return fallback;
    }

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

export function resolveAchievementAuthor(raw) {
    if (!raw || typeof raw !== 'object') {
        return NEUTRAL_AUTHOR;
    }

    const alumni = raw.anggotaAlumni || raw.anggota_alumni;
    if (alumni && typeof alumni === 'object') {
        const memberName = alumni.nama_lengkap || alumni.nama;
        if (typeof memberName === 'string' && memberName.trim()) {
            return memberName.trim();
        }
    }

    const directAuthor = raw.author ?? raw.penulis ?? raw.penerima ?? null;
    if (typeof directAuthor === 'string' && directAuthor.trim()) {
        return directAuthor.trim();
    }

    return NEUTRAL_AUTHOR;
}

export function resolveAchievementDate(raw) {
    if (!raw || typeof raw !== 'object') {
        return { badge: '', isoDate: '', displayDate: '' };
    }

    const yearVal = raw.tahun_diperoleh !== undefined && raw.tahun_diperoleh !== null && raw.tahun_diperoleh !== ''
        ? String(raw.tahun_diperoleh).trim()
        : null;

    const rawDate = raw.tanggal || raw.date || raw.created_at;
    let isoDate = yearVal ? `${yearVal}-01-01` : '';
    let displayDate = yearVal ? `Tahun ${yearVal}` : '';
    let badge = yearVal || '';

    if (rawDate && typeof rawDate === 'string') {
        try {
            const dateObj = new Date(rawDate);
            if (!isNaN(dateObj.getTime())) {
                const parsedYear = String(dateObj.getFullYear());
                if (!badge) badge = parsedYear;
                isoDate = rawDate.slice(0, 10);
                displayDate = new Intl.DateTimeFormat('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                }).format(dateObj);
            }
        } catch {
            // keep year fallback
        }
    }

    return { badge, isoDate, displayDate };
}

export function mapAchievementItem(raw) {
    if (!raw || typeof raw !== 'object') {
        return null;
    }

    const id = raw.prestasi_id ?? raw.id ?? null;
    const title = raw.nama_prestasi ?? raw.title ?? 'Prestasi';
    const description = raw.deskripsi ?? raw.description ?? raw.excerpt ?? '';
    const organizer = raw.penyelenggara ?? '';
    const level = raw.tingkat ?? '';
    const dateInfo = resolveAchievementDate(raw);
    const author = resolveAchievementAuthor(raw);
    const authorId = raw.anggota_alumni_id ?? raw.anggotaAlumni?.anggota_alumni_id ?? null;

    const projectRelation = raw.proyekRiset || raw.proyek_riset;
    const projectTitle = projectRelation?.judul_penelitian_simulasi || '';

    const rawImage = raw.dokumentasi_bukti ?? raw.image_url ?? raw.image ?? null;
    const imageUrl = sanitizeImageUrl(rawImage, DEFAULT_ACHIEVEMENT_IMAGE);

    return {
        id,
        title,
        description,
        organizer,
        level,
        year: raw.tahun_diperoleh ?? null,
        badge: dateInfo.badge,
        isoDate: dateInfo.isoDate,
        displayDate: dateInfo.displayDate,
        image: imageUrl,
        author,
        authorId,
        project: projectTitle,
        href: id ? `/prestasi/${id}` : '#',
        raw,
    };
}

export function renderAchievementCard(item) {
    const safeId = escapeHtml(item.id ?? '');
    const safeTitle = escapeHtml(item.title);
    const safeDescription = escapeHtml(item.description);
    const safeAuthor = escapeHtml(item.author);
    const safeAuthorId = escapeHtml(item.authorId ?? item.raw?.anggota_alumni_id ?? item.raw?.anggotaAlumni?.anggota_alumni_id ?? '');
    const safeDate = escapeHtml(item.isoDate || item.badge || '');
    const safeYear = escapeHtml(item.year || item.badge || '');
    const safeBadge = escapeHtml(item.badge || '');
    const safeHref = escapeHtml(item.href || '#');
    const safeImage = escapeHtml(sanitizeImageUrl(item.image, DEFAULT_ACHIEVEMENT_IMAGE));

    const hasMetaClass = item.badge ? 'achievement-card--has-meta' : '';
    const badgeHtml = item.badge
        ? `<div class="achievement-card__badge">${safeBadge}</div>`
        : '';
    const dividerHtml = (!item.badge && !item.description)
        ? '<div class="achievement-card__divider" aria-hidden="true"></div>'
        : '';
    const descHtml = item.description
        ? `<p class="achievement-card__description">${safeDescription}</p>`
        : '';

    return `
        <article class="card achievement-card editable-item ${hasMetaClass}"
            data-item-id="${safeId}"
            data-item-type="achievement"
            data-item-title="${safeTitle}"
            data-item-author="${safeAuthor}"
            data-item-author-id="${safeAuthorId}"
            data-item-year="${safeYear}"
            data-item-date="${safeDate}"
            data-item-content="${safeDescription}"
            data-item-description="${safeDescription}"
            data-item-supporting-image-1=""
            data-item-supporting-image-2=""
        >
            <div class="achievement-card__media">
                <a href="${safeHref}" class="achievement-card__media-link">
                    <img src="${safeImage}" alt="${safeTitle}" class="achievement-card__image">
                </a>
                ${badgeHtml}
                <button
                    type="button"
                    class="admin-edit-btn"
                    data-open-modal="edit-achievement-modal"
                    aria-label="Edit Prestasi"
                >
                    <svg class="admin-edit-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                        <path d="M15 5l4 4"></path>
                    </svg>
                </button>
            </div>

            <a href="${safeHref}" class="achievement-card__inner">
                <div class="achievement-card__content">
                    ${dividerHtml}
                    <div class="achievement-card__body">
                        <h3 class="achievement-card__title">${safeTitle}</h3>
                        ${descHtml}
                    </div>
                </div>
            </a>
        </article>
    `;
}

export function renderDetailContent(paragraphsEl, bodyEl, contentText, metadata = {}) {
    const target = paragraphsEl || bodyEl;
    if (!target) return;

    if (paragraphsEl) {
        paragraphsEl.innerHTML = '';
    } else {
        const existingPara = bodyEl.querySelectorAll('.achievement-detail__paragraph, .achievement-detail__gallery');
        existingPara.forEach((el) => el.remove());
    }

    const textToRender = typeof contentText === 'string' ? contentText.trim() : '';
    if (!textToRender && !metadata.organizer && !metadata.level && !metadata.project) {
        const p = document.createElement('p');
        p.className = 'achievement-detail__paragraph';
        p.textContent = 'Belum ada deskripsi untuk prestasi dan pencapaian ini.';
        target.appendChild(p);
        return;
    }

    const fragment = document.createDocumentFragment();

    if (textToRender) {
        const blocks = textToRender
            .split(/\r?\n\s*\r?\n/)
            .map((b) => b.trim())
            .filter((b) => b.length > 0);

        blocks.forEach((block) => {
            const p = document.createElement('p');
            p.className = 'achievement-detail__paragraph';

            const lines = block.split(/\r?\n/);
            lines.forEach((line, lineIndex) => {
                if (lineIndex > 0) {
                    p.appendChild(document.createElement('br'));
                }
                p.appendChild(document.createTextNode(line));
            });

            fragment.appendChild(p);
        });
    }

    if (metadata.organizer || metadata.level || metadata.project) {
        const metaP = document.createElement('p');
        metaP.className = 'achievement-detail__paragraph';

        const parts = [];
        if (metadata.organizer) parts.push(`Penyelenggara: ${metadata.organizer}`);
        if (metadata.level) parts.push(`Tingkat: ${metadata.level}`);
        if (metadata.project) parts.push(`Terkait Riset: ${metadata.project}`);

        parts.forEach((part, idx) => {
            if (idx > 0) metaP.appendChild(document.createElement('br'));
            const strong = document.createElement('strong');
            strong.textContent = part.split(':')[0] + ':';
            metaP.appendChild(strong);
            metaP.appendChild(document.createTextNode(part.substring(part.indexOf(':') + 1)));
        });

        fragment.appendChild(metaP);
    }

    target.appendChild(fragment);
}

export async function loadAchievementCatalog(catalogContainer) {
    if (!catalogContainer) return;

    const loadingEl = catalogContainer.querySelector('[data-achievement-state="loading"]');
    const emptyEl = catalogContainer.querySelector('[data-achievement-state="empty"]');
    const errorEl = catalogContainer.querySelector('[data-achievement-state="error"]');
    const errorMsgEl = catalogContainer.querySelector('[data-achievement-error-message]');
    const retryBtn = catalogContainer.querySelector('[data-achievement-retry]');

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
        const existingCards = catalogContainer.querySelectorAll('.achievement-card');
        existingCards.forEach((card) => card.remove());
    }

    async function fetchData() {
        showState('loading');
        clearRenderedCards();

        try {
            const response = await getAchievementList();
            const rawData = extractData(response);

            if (!Array.isArray(rawData) || rawData.length === 0) {
                showState('empty');
                return;
            }

            const items = rawData.map(mapAchievementItem).filter(Boolean);
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

            catalogContainer.appendChild(fragment);
        } catch (error) {
            const userMessage = error.message || 'Gagal memuat prestasi & pencapaian. Periksa koneksi internet atau server backend.';
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

export async function loadAchievementDetail(detailContainer) {
    if (!detailContainer) return;

    const pathSegments = window.location.pathname.split('/').filter(Boolean);
    const idFromPath = pathSegments.length >= 2 ? pathSegments[1] : null;
    const achievementId = detailContainer.dataset.achievementId || idFromPath;

    if (!achievementId) {
        return;
    }

    const loadingEl = detailContainer.querySelector('[data-achievement-detail-loading]');
    const errorEl = detailContainer.querySelector('[data-achievement-detail-error]');
    const errorMsgEl = detailContainer.querySelector('[data-achievement-detail-error-message]');
    const retryBtn = detailContainer.querySelector('[data-achievement-detail-retry]');
    const cardEl = detailContainer.querySelector('[data-achievement-detail-card], .achievement-detail__card');
    const actionBarEl = detailContainer.querySelector('[data-achievement-detail-action], .admin-detail-action-bar');

    const titleEl = detailContainer.querySelector('[data-achievement-detail-title]');
    const dateEl = detailContainer.querySelector('[data-achievement-detail-date]');
    const authorEl = detailContainer.querySelector('[data-achievement-detail-author]');
    const authorWrapperEl = detailContainer.querySelector('[data-achievement-detail-author-wrapper]');
    const imageEl = detailContainer.querySelector('[data-achievement-detail-image]');
    const bodyEl = detailContainer.querySelector('[data-achievement-detail-content]');
    const paragraphsEl = detailContainer.querySelector('[data-achievement-detail-paragraphs]');

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
            const response = await getAchievementById(achievementId);
            const rawData = extractData(response);
            const item = mapAchievementItem(rawData);

            if (!item || !item.id) {
                showState('error', 'Prestasi & pencapaian tidak ditemukan.', false);
                return;
            }

            if (titleEl) {
                titleEl.textContent = item.title;
                document.title = `${item.title} | TP Laboratory`;
            }

            if (dateEl) {
                dateEl.textContent = item.displayDate || (item.year ? `Tahun ${item.year}` : 'Tanggal tidak tersedia');
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
                imageEl.src = sanitizeImageUrl(item.image, DEFAULT_DETAIL_IMAGE);
                imageEl.alt = item.title || 'Detail Prestasi';
            }

            if (cardEl) {
                cardEl.dataset.itemId = String(item.id || '');
                cardEl.dataset.itemTitle = item.title || '';
                cardEl.dataset.itemAuthor = item.author || '';
                cardEl.dataset.itemYear = String(item.year || item.badge || '');
                cardEl.dataset.itemDate = item.isoDate || (item.year ? `${item.year}-01-01` : '');
                cardEl.dataset.itemContent = item.description || '';
                cardEl.dataset.itemDescription = item.description || '';
            }

            renderDetailContent(paragraphsEl, bodyEl, item.description, {
                organizer: item.organizer,
                level: item.level,
                project: item.project,
            });

            showState('content');
        } catch (error) {
            const isNotFound = error.status === 404 || (error.message && error.message.toLowerCase().includes('tidak ditemukan'));
            if (isNotFound) {
                showState('error', 'Prestasi & pencapaian tidak ditemukan.', false);
            } else {
                const message = error.message || 'Gagal memuat detail prestasi. Periksa koneksi internet atau server backend.';
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

export function initAchievementPage() {
    const catalogContainer = document.querySelector('[data-achievement-grid], .achievement-grid');
    if (catalogContainer) {
        loadAchievementCatalog(catalogContainer);
    }

    const detailContainer = document.querySelector('[data-achievement-detail], .achievement-detail');
    if (detailContainer) {
        loadAchievementDetail(detailContainer);
    }
}

export default {
    NEUTRAL_AUTHOR,
    DEFAULT_ACHIEVEMENT_IMAGE,
    DEFAULT_DETAIL_IMAGE,
    sanitizeImageUrl,
    escapeHtml,
    resolveAchievementAuthor,
    resolveAchievementDate,
    mapAchievementItem,
    renderAchievementCard,
    renderDetailContent,
    loadAchievementCatalog,
    loadAchievementDetail,
    initAchievementPage,
};

