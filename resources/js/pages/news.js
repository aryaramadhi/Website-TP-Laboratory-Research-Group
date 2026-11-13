import { getNewsList, getNewsById } from '../api/news.js';
import { extractData } from '../api/client.js';

export const NEUTRAL_AUTHOR = 'TP Laboratory Research Group';

export function isDocumentFile(url) {
    if (!url || typeof url !== 'string') {
        return false;
    }
    const clean = url.split('?')[0].split('#')[0].trim().toLowerCase();
    return clean.endsWith('.pdf') || clean.endsWith('.doc') || clean.endsWith('.docx');
}

export function sanitizeImageUrl(url, fallback = '/images/news/news-1.jpg') {
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

export function resolveNewsAuthor(raw) {
    if (!raw || typeof raw !== 'object') {
        return NEUTRAL_AUTHOR;
    }

    if (raw.admin && typeof raw.admin === 'object') {
        const adminName = raw.admin.nama_lengkap || raw.admin.name || raw.admin.nama;
        if (typeof adminName === 'string' && adminName.trim()) {
            return adminName.trim();
        }
    }

    const directAuthor = raw.author ?? raw.penulis ?? null;
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

export function stripGalleryMarkers(text) {
    if (typeof text !== 'string') return '';
    return text
        .replace(/\s*\[\[(?:RESEARCH|NEWS|ACHIEVEMENT)_IMAGES\]\]\s*/gi, ' ')
        .replace(/[ \t]{2,}/g, ' ')
        .trim();
}

export function mapNewsItem(raw) {
    if (!raw || typeof raw !== 'object') {
        return null;
    }

    const id = raw.berita_id ?? raw.id ?? null;
    const title = raw.judul_berita ?? raw.title ?? 'Berita';
    const excerpt = raw.isi_artikel_ringkas ?? raw.excerpt ?? raw.description ?? '';
    const content = raw.isi_artikel_ringkas ?? raw.content ?? excerpt;
    const date = raw.tanggal_rilis ?? raw.date ?? '';
    const category = raw.topik_utama ?? raw.category ?? '';

    const rawImage = raw.gambar_pendukung ?? raw.main_image ?? raw.image ?? null;
    const isDoc = isDocumentFile(rawImage);
    const imageUrl = isDoc
        ? '/images/news/news-1.jpg'
        : sanitizeImageUrl(rawImage, '/images/news/news-1.jpg');
    const author = resolveNewsAuthor(raw);

    const rawSupp1 = raw.supporting_image_1_url ?? raw.gambar_pendukung_1 ?? raw.supporting_image_1 ?? null;
    const rawSupp2 = raw.supporting_image_2_url ?? raw.gambar_pendukung_2 ?? raw.supporting_image_2 ?? null;
    const supportingImage1 = (rawSupp1 && typeof rawSupp1 === 'string' && rawSupp1.trim()) ? sanitizeImageUrl(rawSupp1, '') : null;
    const supportingImage2 = (rawSupp2 && typeof rawSupp2 === 'string' && rawSupp2.trim()) ? sanitizeImageUrl(rawSupp2, '') : null;

    return {
        id,
        title,
        excerpt,
        content,
        date,
        category,
        image: imageUrl,
        rawImage,
        supportingImage1,
        supportingImage2,
        isDocument: isDoc,
        author,
        href: id ? `/berita/${id}` : '#',
        raw,
    };
}

export function renderNewsCard(item, index) {
    const isReverse = index % 2 === 1;
    const reverseClass = isReverse ? 'news-card--reverse' : '';

    const safeId = escapeHtml(item.id ?? '');
    const safeTitle = escapeHtml(item.title);
    const safeExcerpt = escapeHtml(item.excerpt);
    const safeAuthor = escapeHtml(item.author);
    const safeDate = escapeHtml(item.date || '');
    const safeCategory = escapeHtml(item.category || '');
    const safeHref = escapeHtml(item.href || '#');
    const imageCandidate = isDocumentFile(item.image) ? '/images/news/news-1.jpg' : item.image;
    const safeImage = escapeHtml(sanitizeImageUrl(imageCandidate, '/images/news/news-1.jpg'));
    const rawCandidate = item.rawImage ?? item.raw?.gambar_pendukung ?? item.image ?? '';
    const safeRawImage = (typeof rawCandidate === 'string' && /^(javascript|data|vbscript):/i.test(rawCandidate.trim()))
        ? ''
        : escapeHtml(rawCandidate);
    const safeVisibleExcerpt = escapeHtml(stripGalleryMarkers(item.excerpt));

    const actionHtml = !isReverse
        ? `<img src="/images/icons/chevron-right.png" alt="" class="news-card__link-icon" aria-hidden="true">
           <span>Lihat Detail Berita</span>`
        : `<span>Lihat Detail Berita</span>
           <img src="/images/icons/chevron-right.png" alt="" class="news-card__link-icon" aria-hidden="true">`;

    return `
        <article class="card news-card editable-item ${reverseClass}"
            data-item-id="${safeId}"
            data-item-type="news"
            data-item-title="${safeTitle}"
            data-item-author="${safeAuthor}"
            data-item-date="${safeDate}"
            data-item-category="${safeCategory}"
            data-item-content="${escapeHtml(item.content || item.excerpt)}"
            data-item-description="${escapeHtml(item.content || item.excerpt)}"
            data-item-excerpt="${safeExcerpt}"
            data-item-image="${safeRawImage}"
            data-item-supporting-image-1="${escapeHtml(item.supportingImage1 || '')}"
            data-item-supporting-image-2="${escapeHtml(item.supportingImage2 || '')}"
        >
            <div class="news-card__media">
                <a href="${safeHref}">
                    <img src="${safeImage}" alt="${safeTitle}" class="news-card__image" onerror="this.onerror=null;this.src='/images/news/news-1.jpg';">
                </a>
                <button
                    type="button"
                    class="admin-edit-btn"
                    data-open-modal="edit-news-modal"
                    aria-label="Edit Berita"
                >
                    <svg class="admin-edit-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                        <path d="M15 5l4 4"></path>
                    </svg>
                </button>
            </div>

            <div class="news-card__content">
                <h3 class="news-card__title">
                    <a href="${safeHref}">${safeTitle}</a>
                </h3>

                ${safeVisibleExcerpt ? `<p class="news-card__excerpt">${safeVisibleExcerpt}</p>` : ''}

                <div class="news-card__action">
                    <a href="${safeHref}" class="news-card__link">
                        ${actionHtml}
                    </a>
                </div>
            </div>
        </article>
    `;
}

export function createGalleryElement(prefix, img1, img2, altTitle = '') {
    if (!img1 && !img2) return null;

    const gallery = document.createElement('div');
    gallery.className = `${prefix}-detail__gallery`;

    if (img1) {
        const item1 = document.createElement('div');
        item1.className = `${prefix}-detail__gallery-item ${img2 ? `${prefix}-detail__gallery-item--wide` : ''}`;
        const img = document.createElement('img');
        img.src = sanitizeImageUrl(img1);
        img.alt = altTitle ? `${altTitle} - Gambar Pendukung 1` : 'Gambar Pendukung 1';
        img.className = `${prefix}-detail__gallery-img`;
        img.loading = 'lazy';
        item1.appendChild(img);
        gallery.appendChild(item1);
    }

    if (img2) {
        const item2 = document.createElement('div');
        item2.className = `${prefix}-detail__gallery-item ${img1 ? `${prefix}-detail__gallery-item--narrow` : `${prefix}-detail__gallery-item--wide`}`;
        const img = document.createElement('img');
        img.src = sanitizeImageUrl(img2);
        img.alt = altTitle ? `${altTitle} - Gambar Pendukung 2` : 'Gambar Pendukung 2';
        img.className = `${prefix}-detail__gallery-img`;
        img.loading = 'lazy';
        item2.appendChild(img);
        gallery.appendChild(item2);
    }

    return gallery;
}

export function renderDetailContent(paragraphsEl, bodyEl, contentText, supp1 = null, supp2 = null, altTitle = '') {
    const target = paragraphsEl || bodyEl;
    if (!target) return;

    if (paragraphsEl) {
        paragraphsEl.innerHTML = '';
    } else {
        const existingPara = bodyEl.querySelectorAll('.news-detail__paragraph, .news-detail__gallery');
        existingPara.forEach((el) => el.remove());
    }

    const textToRender = typeof contentText === 'string' ? contentText.trim() : '';
    if (!textToRender) {
        const p = document.createElement('p');
        p.className = 'news-detail__paragraph';
        p.textContent = 'Belum ada isi artikel untuk berita ini.';
        target.appendChild(p);
        return;
    }

    const galleryEl = createGalleryElement('news', supp1, supp2, altTitle);
    const markerRegex = /\[\[NEWS_IMAGES\]\]/i;
    let galleryInserted = false;

    const sections = textToRender.split(markerRegex);
    const fragment = document.createDocumentFragment();

    sections.forEach((section, sIndex) => {
        const trimmedSection = section.trim();
        if (trimmedSection) {
            const blocks = trimmedSection
                .split(/\r?\n\s*\r?\n/)
                .map((b) => b.trim())
                .filter((b) => b.length > 0);

            blocks.forEach((block, bIndex) => {
                const p = document.createElement('p');
                p.className = 'news-detail__paragraph';

                const lines = block.split(/\r?\n/);
                lines.forEach((line, lineIndex) => {
                    if (lineIndex > 0) {
                        p.appendChild(document.createElement('br'));
                    }
                    p.appendChild(document.createTextNode(line));
                });

                fragment.appendChild(p);

                if (!galleryInserted && galleryEl && sections.length === 1 && bIndex === 0 && blocks.length > 1) {
                    fragment.appendChild(galleryEl);
                    galleryInserted = true;
                }
            });
        }

        if (sIndex < sections.length - 1) {
            if (galleryEl && !galleryInserted) {
                fragment.appendChild(galleryEl);
                galleryInserted = true;
            }
        }
    });

    if (galleryEl && !galleryInserted) {
        fragment.appendChild(galleryEl);
    }

    if (fragment.childNodes.length === 0) {
        const p = document.createElement('p');
        p.className = 'news-detail__paragraph';
        p.textContent = 'Belum ada isi artikel untuk berita ini.';
        fragment.appendChild(p);
    }

    target.appendChild(fragment);
}

export async function loadNewsCatalog(catalogContainer) {
    if (!catalogContainer) return;

    const loadingEl = catalogContainer.querySelector('[data-news-state="loading"]');
    const emptyEl = catalogContainer.querySelector('[data-news-state="empty"]');
    const errorEl = catalogContainer.querySelector('[data-news-state="error"]');
    const errorMsgEl = catalogContainer.querySelector('[data-news-error-message]');
    const retryBtn = catalogContainer.querySelector('[data-news-retry]');

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
        const existingCards = catalogContainer.querySelectorAll('.news-card');
        existingCards.forEach((card) => card.remove());
    }

    async function fetchData() {
        showState('loading');
        clearRenderedCards();

        try {
            const response = await getNewsList();
            const rawData = extractData(response);

            if (!Array.isArray(rawData) || rawData.length === 0) {
                showState('empty');
                return;
            }

            const items = rawData.map(mapNewsItem).filter(Boolean);
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

            catalogContainer.appendChild(fragment);
        } catch (error) {
            const userMessage = error.message || 'Gagal memuat daftar berita. Periksa koneksi internet atau server backend.';
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

export async function loadNewsDetail(detailContainer) {
    if (!detailContainer) return;

    const pathSegments = window.location.pathname.split('/').filter(Boolean);
    const idFromPath = pathSegments.length >= 2 ? pathSegments[1] : null;
    const newsId = detailContainer.dataset.newsId || idFromPath;

    if (!newsId) {
        return;
    }

    const loadingEl = detailContainer.querySelector('[data-news-detail-loading]');
    const errorEl = detailContainer.querySelector('[data-news-detail-error]');
    const errorMsgEl = detailContainer.querySelector('[data-news-detail-error-message]');
    const retryBtn = detailContainer.querySelector('[data-news-detail-retry]');
    const cardEl = detailContainer.querySelector('[data-news-detail-card], .news-detail__card');
    const actionBarEl = detailContainer.querySelector('[data-news-detail-action], .admin-detail-action-bar');

    const titleEl = detailContainer.querySelector('[data-news-detail-title]');
    const dateEl = detailContainer.querySelector('[data-news-detail-date]');
    const authorEl = detailContainer.querySelector('[data-news-detail-author]');
    const authorWrapperEl = detailContainer.querySelector('[data-news-detail-author-wrapper]');
    const imageEl = detailContainer.querySelector('[data-news-detail-image]');
    const bodyEl = detailContainer.querySelector('[data-news-detail-content]');
    const paragraphsEl = detailContainer.querySelector('[data-news-detail-paragraphs]');

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
            const response = await getNewsById(newsId);
            const rawData = extractData(response);
            const item = mapNewsItem(rawData);

            if (!item || !item.id) {
                showState('error', 'Berita tidak ditemukan.', false);
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
                const detailCandidate = isDocumentFile(item.image) ? '/images/news/detail-featured.jpg' : item.image;
                imageEl.onerror = () => {
                    imageEl.onerror = null;
                    imageEl.src = '/images/news/detail-featured.jpg';
                };
                imageEl.src = sanitizeImageUrl(detailCandidate, '/images/news/detail-featured.jpg');
                imageEl.alt = item.title || 'Detail Berita';
            }

            if (cardEl) {
                cardEl.dataset.itemId = String(item.id || '');
                cardEl.dataset.itemTitle = item.title || '';
                cardEl.dataset.itemAuthor = item.author || '';
                cardEl.dataset.itemDate = item.date || '';
                cardEl.dataset.itemCategory = item.category || '';
                cardEl.dataset.itemContent = item.content || item.excerpt || '';
                cardEl.dataset.itemDescription = item.content || item.excerpt || '';
                cardEl.dataset.itemExcerpt = item.excerpt || '';
                cardEl.dataset.itemImage = item.rawImage ?? item.raw?.gambar_pendukung ?? item.image ?? '';
                cardEl.dataset.itemSupportingImage1 = item.supportingImage1 || '';
                cardEl.dataset.itemSupportingImage2 = item.supportingImage2 || '';
            }

            renderDetailContent(
                paragraphsEl,
                bodyEl,
                item.content || item.excerpt,
                item.supportingImage1,
                item.supportingImage2,
                item.title
            );

            showState('content');
        } catch (error) {
            const isNotFound = error.status === 404 || (error.message && error.message.toLowerCase().includes('tidak ditemukan'));
            if (isNotFound) {
                showState('error', 'Berita tidak ditemukan.', false);
            } else {
                const message = error.message || 'Gagal memuat detail berita. Periksa koneksi internet atau server backend.';
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

export function initNewsPage() {
    const catalogContainer = document.querySelector('[data-news-list], #news-list');
    if (catalogContainer) {
        loadNewsCatalog(catalogContainer);
    }

    const detailContainer = document.querySelector('[data-news-detail], .news-detail');
    if (detailContainer) {
        loadNewsDetail(detailContainer);
    }
}

export default {
    NEUTRAL_AUTHOR,
    isDocumentFile,
    sanitizeImageUrl,
    escapeHtml,
    resolveNewsAuthor,
    formatIndonesianDate,
    mapNewsItem,
    renderNewsCard,
    renderDetailContent,
    loadNewsCatalog,
    loadNewsDetail,
    initNewsPage,
};

