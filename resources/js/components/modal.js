import { closeDropdown } from './dropdown.js';
import {
    login,
    logout,
    getMe,
    setAuthToken,
    removeAuthToken,
    hasAuthToken,
    setAdminUser,
    removeAdminUser,
    getAdminUser,
    onUnauthorized,
} from '../api/auth.js';
import { ApiError, extractData } from '../api/client.js';
import { getAlumniList } from '../api/alumni.js';
import { createResearch, updateResearch, deleteResearch } from '../api/research.js';
import { createNews, updateNews, deleteNews } from '../api/news.js';
import { createPublication, updatePublication, deletePublication } from '../api/publication.js';
import { createAchievement, updateAchievement, deleteAchievement } from '../api/achievement.js';

export function updateAuthUI(admin) {
    const isAuthenticated = Boolean(admin && hasAuthToken());

    if (typeof document === 'undefined') return;

    if (isAuthenticated) {
        document.body.classList.add('is-admin');
    } else {
        document.body.classList.remove('is-admin');
    }

    const dropdown = document.querySelector('[data-profile-dropdown]');
    if (!dropdown) return;

    const guestState = dropdown.querySelector('[data-auth-guest]');
    const authState = dropdown.querySelector('[data-auth-authenticated]');
    const emailEl = dropdown.querySelector('[data-admin-email]');

    if (isAuthenticated) {
        if (guestState) guestState.style.display = 'none';
        if (authState) {
            authState.style.display = 'flex';
            if (emailEl) {
                emailEl.textContent = admin.email || admin.nama_lengkap || 'Admin';
            }
        }
    } else {
        if (guestState) guestState.style.display = 'flex';
        if (authState) authState.style.display = 'none';
        if (emailEl) emailEl.textContent = '';
    }
}

export function handleLogoutCleanup() {
    removeAuthToken();
    removeAdminUser();
    updateAuthUI(null);
}

export async function handleLogout() {
    closeDropdown();

    try {
        if (hasAuthToken()) {
            await logout();
        }
    } catch (err) {
        console.warn('Logout request failed, continuing local cleanup:', err?.message || err);
    } finally {
        handleLogoutCleanup();
    }
}

let unauthorizedRegistered = false;

export async function initAuthSession() {
    if (!unauthorizedRegistered) {
        unauthorizedRegistered = true;
        onUnauthorized(() => {
            handleLogoutCleanup();
        });
    }

    if (!hasAuthToken()) {
        handleLogoutCleanup();
        return false;
    }

    try {
        const response = await getMe();
        const adminData = extractData(response);
        if (adminData && typeof adminData === 'object') {
            setAdminUser(adminData);
            updateAuthUI(adminData);
            return true;
        } else {
            handleLogoutCleanup();
            return false;
        }
    } catch (err) {
        const is401 = (err instanceof ApiError && err.status === 401) ||
                      (err && err.status === 401);

        if (is401) {
            handleLogoutCleanup();
        } else {
            // Network error (status 0), HTTP 5xx, or temporary connection glitch:
            // Do not delete token or admin data from sessionStorage.
            // Ensure .is-admin is not active during startup verification failure.
            updateAuthUI(null);
        }
        return false;
    }
}

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

let cachedAlumniList = null;
let alumniFetchPromise = null;

export async function fetchAlumniList(forceRefresh = false) {
    if (cachedAlumniList && !forceRefresh) {
        return cachedAlumniList;
    }
    if (alumniFetchPromise && !forceRefresh) {
        return alumniFetchPromise;
    }
    alumniFetchPromise = (async () => {
        try {
            const response = await getAlumniList();
            const data = extractData(response);
            const list = Array.isArray(data) ? data : [];
            cachedAlumniList = list;
            return list;
        } catch (err) {
            cachedAlumniList = null;
            throw err;
        } finally {
            alumniFetchPromise = null;
        }
    })();
    return alumniFetchPromise;
}

export function clearAlumniCache() {
    cachedAlumniList = null;
    alumniFetchPromise = null;
}

export async function setupAchievementAlumniSelect(modal, forceRefresh = false) {
    if (!modal) return;
    const selectEl = modal.querySelector('select[data-alumni-select]') ||
        modal.querySelector('#' + modal.id + '-author');
    if (!selectEl) return;

    const submitBtn = modal.querySelector('.btn-modal-submit');
    const errorEl = modal.querySelector('#' + modal.id + '-author-error');

    selectEl.disabled = true;
    selectEl.innerHTML = '<option value="" disabled selected>Memuat daftar anggota/alumni...</option>';
    if (errorEl) {
        errorEl.style.display = 'none';
        errorEl.setAttribute('hidden', '');
    }

    try {
        const list = await fetchAlumniList(forceRefresh);

        if (!Array.isArray(list) || list.length === 0) {
            selectEl.innerHTML = '<option value="" disabled selected>Tidak ada anggota/alumni tersedia</option>';
            selectEl.disabled = true;
            if (submitBtn) {
                submitBtn.disabled = true;
            }
            if (errorEl) {
                errorEl.style.display = 'flex';
                errorEl.removeAttribute('hidden');
                errorEl.innerHTML = `
                    <svg class="form-error-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span class="form-error-text">Daftar anggota/alumni kosong. Tambahkan anggota alumni terlebih dahulu.</span>
                `;
            }
            return;
        }

        let optionsHtml = '<option value="" disabled selected>Pilih Anggota/Alumni...</option>';
        for (const item of list) {
            const id = item.anggota_alumni_id ?? item.id;
            const name = item.nama_lengkap ?? item.nama ?? 'Anggota';
            optionsHtml += `<option value="${escapeHtml(id)}">${escapeHtml(name)}</option>`;
        }
        selectEl.innerHTML = optionsHtml;
        selectEl.disabled = false;
        if (submitBtn) {
            submitBtn.disabled = false;
        }
        if (errorEl) {
            errorEl.style.display = 'none';
            errorEl.setAttribute('hidden', '');
        }

        const pendingId = selectEl.dataset.pendingSelectedId;
        const pendingName = selectEl.dataset.pendingSelectedName;
        if (pendingId && selectEl.querySelector(`option[value="${pendingId}"]`)) {
            selectEl.value = String(pendingId);
        } else if (pendingName) {
            const match = list.find(a => (a.nama_lengkap || a.nama || '').trim().toLowerCase() === pendingName.trim().toLowerCase());
            if (match) {
                selectEl.value = String(match.anggota_alumni_id ?? match.id);
            } else {
                selectEl.value = '';
            }
        } else {
            selectEl.value = '';
        }
    } catch (err) {
        selectEl.innerHTML = '<option value="" disabled selected>Gagal memuat anggota/alumni</option>';
        selectEl.disabled = true;
        if (submitBtn) {
            submitBtn.disabled = true;
        }
        if (errorEl) {
            errorEl.style.display = 'flex';
            errorEl.removeAttribute('hidden');
            errorEl.innerHTML = `
                <svg class="form-error-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span class="form-error-text">Gagal memuat daftar anggota. <button type="button" class="form-retry-link" data-retry-alumni style="background:none;border:none;color:inherit;text-decoration:underline;cursor:pointer;font-weight:600;padding:0;margin-left:4px;">Coba lagi</button></span>
            `;
        }
    }
}

export function showFieldError(input, errorContainerId, message) {
    if (!input) return;
    input.setAttribute('aria-invalid', 'true');
    input.setAttribute('aria-describedby', errorContainerId);

    const modal = input.closest ? input.closest('[data-modal]') : null;
    const errorEl = (modal ? modal.querySelector(`#${errorContainerId}`) : null) ||
        (typeof document !== 'undefined' ? document.getElementById(errorContainerId) : null);
    if (errorEl) {
        const textEl = errorEl.querySelector('.form-error-text');
        if (textEl && message) {
            textEl.textContent = message;
        }
        errorEl.style.display = 'flex';
        errorEl.removeAttribute('hidden');
    }
}

export function clearFieldError(input, errorContainerId) {
    if (input) {
        input.setAttribute('aria-invalid', 'false');
        input.removeAttribute('aria-describedby');
    }
    const modal = input?.closest ? input.closest('[data-modal]') : null;
    const errorEl = (modal ? modal.querySelector(`#${errorContainerId}`) : null) ||
        (typeof document !== 'undefined' ? document.getElementById(errorContainerId) : null);
    if (errorEl) {
        errorEl.style.display = 'none';
        errorEl.setAttribute('hidden', '');
    }
}

export function clearModalErrors(modal) {
    if (!modal) return;
    modal.querySelectorAll('.form-field-error').forEach((el) => {
        el.style.display = 'none';
        el.setAttribute('hidden', '');
    });
    modal.querySelectorAll('input, textarea, select').forEach((input) => {
        input.setAttribute('aria-invalid', 'false');
        input.removeAttribute('aria-describedby');
    });
    clearGalleryNotices(modal);
}

export const GALLERY_MARKERS = {
    research: '[[RESEARCH_IMAGES]]',
    news: '[[NEWS_IMAGES]]',
    achievement: '[[ACHIEVEMENT_IMAGES]]',
};

export function clearGalleryNotices(modal) {
    if (!modal) return;
    modal.querySelectorAll('[data-gallery-notice]').forEach((noticeEl) => {
        noticeEl.hidden = true;
        noticeEl.setAttribute('hidden', '');
        noticeEl.textContent = '';
        noticeEl.classList.remove('form-gallery-notice--info');
    });
}

export function showGalleryNotice(modal, message, isInfo = false) {
    if (!modal || !message) return;
    const noticeEl = modal.querySelector('[data-gallery-notice]');
    if (!noticeEl) return;
    noticeEl.textContent = message;
    noticeEl.classList.toggle('form-gallery-notice--info', Boolean(isInfo));
    noticeEl.hidden = false;
    noticeEl.removeAttribute('hidden');
}

function getFieldParts(scope, name) {
    const input = scope?.querySelector?.(`input[name="${name}"]`) || null;
    const group = input?.closest?.('.form-group') || null;
    return {
        input,
        group,
        textEl: group?.querySelector?.('[data-file-text]') || null,
        preview: group?.querySelector?.('[data-image-preview]') || null,
    };
}

function getSupportingFields(scope) {
    return {
        supp1: getFieldParts(scope, 'supporting_image_1'),
        supp2: getFieldParts(scope, 'supporting_image_2'),
    };
}

function setSupportingStatusVisible(scope, visible) {
    const statusEl = scope?.querySelector?.('[data-supporting-status]');
    if (!statusEl) return;
    statusEl.hidden = !visible;
    if (visible) {
        statusEl.removeAttribute('hidden');
    } else {
        statusEl.setAttribute('hidden', '');
    }
}

function clearInputFiles(input) {
    if (!input) return;
    input.value = '';
    try {
        if (Array.isArray(input.files)) {
            input.files = [];
        }
    } catch {}
}

export function hasSupportingImagesInModal(modal) {
    if (!modal) return false;
    const { supp1, supp2 } = getSupportingFields(modal);
    const has1 = Boolean(
        (supp1.input?.files?.length > 0) ||
        (supp1.preview?.dataset?.existingUrl && supp1.preview?.dataset?.removedExisting !== 'true')
    );
    const has2 = Boolean(
        (supp2.input?.files?.length > 0) ||
        (supp2.preview?.dataset?.existingUrl && supp2.preview?.dataset?.removedExisting !== 'true')
    );
    return has1 && has2;
}

export function insertGalleryMarker(textarea, marker) {
    if (!textarea || !marker) {
        return { inserted: false, reason: 'invalid' };
    }

    const currentValue = typeof textarea.value === 'string' ? textarea.value : '';
    const escapedMarker = marker.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    if (new RegExp(escapedMarker, 'i').test(currentValue)) {
        textarea.focus?.();
        return { inserted: false, reason: 'duplicate', value: currentValue };
    }

    const start = typeof textarea.selectionStart === 'number' ? textarea.selectionStart : currentValue.length;
    const end = typeof textarea.selectionEnd === 'number' ? textarea.selectionEnd : start;
    const before = currentValue.slice(0, start);
    const after = currentValue.slice(end);
    const prefix = before.length > 0 && !/\s$/.test(before) ? ' ' : '';
    const suffix = after.length > 0 && !/^\s/.test(after) ? ' ' : '';

    textarea.value = `${before}${prefix}${marker}${suffix}${after}`;
    const caretPos = start + prefix.length + marker.length;
    textarea.focus?.();
    if (typeof textarea.setSelectionRange === 'function') {
        textarea.setSelectionRange(caretPos, caretPos);
    }
    if (typeof textarea.dispatchEvent === 'function' && typeof Event !== 'undefined') {
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }

    return { inserted: true, reason: 'inserted', value: textarea.value, caretPos };
}

export function handleInsertGalleryClick(button) {
    if (!button) return { inserted: false, reason: 'invalid' };
    const modal = button.closest?.('[data-modal]');
    const marker = button.dataset?.insertGallery;
    const targetId = button.dataset?.targetTextarea;
    const textarea = (modal && targetId ? modal.querySelector(`#${targetId}`) : null) ||
        modal?.querySelector('textarea');

    const result = insertGalleryMarker(textarea, marker);
    if (result.reason === 'duplicate') {
        showGalleryNotice(modal, `Penanda ${marker} sudah ada di dalam teks.`, true);
    } else if (result.inserted && !hasSupportingImagesInModal(modal)) {
        showGalleryNotice(
            modal,
            `Penanda ${marker} disisipkan. Pilih kedua gambar pendukung secara berpasangan agar galeri tampil pada halaman detail.`,
            false
        );
    } else {
        clearGalleryNotices(modal);
    }
    return result;
}

export function showModalGeneralError(modal, modalId, message) {
    const genErr = modal?.querySelector ? modal.querySelector(`#${modalId}-general-error`) : null;
    const errorEl = genErr || (typeof document !== 'undefined' ? document.getElementById(`${modalId}-general-error`) : null);
    if (errorEl) {
        const textEl = errorEl.querySelector('.form-error-text');
        if (textEl && message) {
            textEl.textContent = message;
        }
        errorEl.style.display = 'flex';
        errorEl.removeAttribute('hidden');
    }
}

export const CMS_FIELD_MAPPINGS = {
    research: {
        judul_penelitian_simulasi: 'title',
        deskripsi_singkat: 'description',
        tanggal_mulai: 'date',
        status_proyek: 'status',
        image: 'image',
        supporting_image_1: 'supporting_image_1',
        supporting_image_2: 'supporting_image_2',
        dokumentasi_visual_utama: 'image',
        gambar_pendukung_1: 'supporting_image_1',
        gambar_pendukung_2: 'supporting_image_2',
    },
    news: {
        judul_berita: 'title',
        isi_artikel_ringkas: 'content',
        tanggal_rilis: 'date',
        image: 'image',
        supporting_image_1: 'supporting_image_1',
        supporting_image_2: 'supporting_image_2',
        gambar_pendukung: 'image',
        gambar_pendukung_1: 'supporting_image_1',
        gambar_pendukung_2: 'supporting_image_2',
    },
    publication: {
        judul_karya_hibah: 'title',
        nama_jurnal_skema: 'journal',
        tahun: 'year',
        tautan_eksternal: 'doi',
    },
    achievement: {
        nama_prestasi: 'title',
        anggota_alumni_id: 'author',
        tahun_diperoleh: 'date',
        deskripsi: 'content',
        image: 'image',
        supporting_image_1: 'supporting_image_1',
        supporting_image_2: 'supporting_image_2',
        dokumentasi_bukti: 'image',
        gambar_utama: 'image',
        gambar_pendukung_1: 'supporting_image_1',
        gambar_pendukung_2: 'supporting_image_2',
    },
};

export function isImageMarkedForRemoval(formOrModal, fieldName) {
    if (!formOrModal || !fieldName) return false;
    const input = formOrModal.querySelector?.(`input[name="${fieldName}"]`);
    if (input?.files?.length > 0) return false;
    const preview = input?.closest?.('.form-group')?.querySelector?.('[data-image-preview]');
    return preview?.dataset?.removedExisting === 'true';
}

function appendCmsImagePayloadFields(formData, form, isEdit) {
    const mainFile = form?.querySelector?.('[name="image"]')?.files?.[0];
    if (mainFile) {
        formData.append('image', mainFile);
    }

    const supp1File = form?.querySelector?.('[name="supporting_image_1"]')?.files?.[0];
    const supp2File = form?.querySelector?.('[name="supporting_image_2"]')?.files?.[0];
    if (supp1File) {
        formData.append('supporting_image_1', supp1File);
    }
    if (supp2File) {
        formData.append('supporting_image_2', supp2File);
    }

    if (
        isEdit &&
        !supp1File &&
        !supp2File &&
        isImageMarkedForRemoval(form, 'supporting_image_1') &&
        isImageMarkedForRemoval(form, 'supporting_image_2')
    ) {
        formData.append('remove_supporting_image_1', '1');
        formData.append('remove_supporting_image_2', '1');
    }
}

export function buildResearchPayload(form, isEdit = false) {
    const admin = getAdminUser();
    const adminId = admin?.admin_id ?? admin?.id;

    const title = form?.querySelector('[name="title"]')?.value?.trim() || '';
    const description = form?.querySelector('[name="description"]')?.value?.trim() || '';
    const date = form?.querySelector('[name="date"]')?.value?.trim() || '';
    const status = form?.querySelector('[name="status"]')?.value?.trim() || 'Sedang Berjalan';

    const formData = new FormData();
    formData.append('judul_penelitian_simulasi', title);
    formData.append('deskripsi_singkat', description);
    formData.append('tanggal_mulai', date);
    formData.append('status_proyek', status);

    if (!isEdit) {
        formData.append('tanggal_selesai', '');
    } else {
        formData.append('_method', 'PUT');
    }

    if (adminId !== undefined && adminId !== null && adminId !== '') {
        formData.append('admin_id', String(adminId));
    }

    appendCmsImagePayloadFields(formData, form, isEdit);

    return formData;
}

export function buildNewsPayload(form, isEdit = false) {
    const admin = getAdminUser();
    const adminId = admin?.admin_id ?? admin?.id;

    const title = form?.querySelector('[name="title"]')?.value?.trim() || '';
    const content = form?.querySelector('[name="content"]')?.value?.trim() || '';
    const rawDate = form?.querySelector('[name="date"]')?.value?.trim() || '';

    const formData = new FormData();
    formData.append('judul_berita', title);
    formData.append('isi_artikel_ringkas', content);
    if (rawDate) {
        formData.append('tanggal_rilis', rawDate);
    }

    if (!isEdit) {
        formData.append('topik_utama', '');
    } else {
        formData.append('_method', 'PUT');
    }

    if (adminId !== undefined && adminId !== null && adminId !== '') {
        formData.append('admin_id', String(adminId));
    }

    appendCmsImagePayloadFields(formData, form, isEdit);

    return formData;
}

export function buildPublicationPayload(form, isEdit = false) {
    const admin = getAdminUser();
    const adminId = admin?.admin_id ?? admin?.id;

    const title = form?.querySelector('[name="title"]')?.value?.trim() || '';
    const journal = form?.querySelector('[name="journal"]')?.value?.trim() || null;
    const rawYear = form?.querySelector('[name="year"]')?.value?.trim() || '';
    const parsedYear = rawYear && !isNaN(parseInt(rawYear, 10)) ? parseInt(rawYear, 10) : null;
    const rawDoi = form?.querySelector('[name="doi"]')?.value?.trim() || '';
    const doi = rawDoi ? rawDoi : null;

    const payload = {
        judul_karya_hibah: title,
        nama_jurnal_skema: journal,
        tahun: parsedYear,
        tautan_eksternal: doi,
    };

    if (!isEdit) {
        payload.jenis = null;
        payload.deskripsi_pencapaian = null;
        payload.proyek_id = null;
    }

    if (adminId !== undefined && adminId !== null && adminId !== '') {
        payload.admin_id = Number(adminId);
    }

    return payload;
}

export function buildAchievementPayload(form, isEdit = false) {
    const title = form?.querySelector('[name="title"]')?.value?.trim() || '';
    const rawAuthor = form?.querySelector('[name="anggota_alumni_id"]')?.value?.trim() ||
        form?.querySelector('[name="author"]')?.value?.trim() || '';
    const alumniId = rawAuthor && !isNaN(parseInt(rawAuthor, 10)) ? parseInt(rawAuthor, 10) : null;

    const rawDate = form?.querySelector('[name="date"]')?.value?.trim() || '';
    let parsedYear = null;
    if (rawDate) {
        const yearPart = rawDate.split('-')[0].trim();
        if (yearPart && !isNaN(parseInt(yearPart, 10))) {
            parsedYear = parseInt(yearPart, 10);
        }
    }

    const content = form?.querySelector('[name="content"]')?.value?.trim() || '';

    const formData = new FormData();
    formData.append('nama_prestasi', title);
    formData.append('deskripsi', content);
    if (parsedYear !== null) {
        formData.append('tahun_diperoleh', String(parsedYear));
    }
    if (alumniId !== null) {
        formData.append('anggota_alumni_id', String(alumniId));
    }

    if (isEdit) {
        formData.append('_method', 'PUT');
    }

    appendCmsImagePayloadFields(formData, form, isEdit);

    return formData;
}

export function handleCmsValidationErrors(form, modal, modalId, resourceType, errors) {
    if (!errors || typeof errors !== 'object') return;

    const mapping = CMS_FIELD_MAPPINGS[resourceType] || {};
    let hasFieldErrors = false;
    const unmappedMessages = [];

    for (const [backendField, messages] of Object.entries(errors)) {
        const message = Array.isArray(messages) ? messages[0] : String(messages);
        const frontendFieldName = mapping[backendField];

        if (frontendFieldName) {
            const inputField = form?.querySelector(`[name="${frontendFieldName}"]`) ||
                (frontendFieldName === 'author' ? form?.querySelector('[name="anggota_alumni_id"]') : null) ||
                (frontendFieldName === 'anggota_alumni_id' ? form?.querySelector('[name="author"]') : null);
            const errorContainerId = (frontendFieldName === 'anggota_alumni_id' || frontendFieldName === 'author')
                ? `${modalId}-author-error`
                : `${modalId}-${frontendFieldName}-error`;

            if (inputField) {
                showFieldError(inputField, errorContainerId, message);
                hasFieldErrors = true;
                continue;
            }
        }

        unmappedMessages.push(message);
    }

    if (unmappedMessages.length > 0) {
        showModalGeneralError(modal, modalId, unmappedMessages.join(' '));
    }

    if (hasFieldErrors && form) {
        const firstInvalid = form.querySelector('[aria-invalid="true"]');
        firstInvalid?.focus();
    }
}

function revokePreviewObjectUrl(previewEl) {
    const existingObjUrl = previewEl?.dataset?.objectUrl;
    if (existingObjUrl && typeof URL !== 'undefined' && typeof URL.revokeObjectURL === 'function') {
        try {
            URL.revokeObjectURL(existingObjUrl);
        } catch (e) {}
    }
    if (previewEl?.dataset) {
        delete previewEl.dataset.objectUrl;
    }
}

function setClearButtonVisible(previewEl, visible) {
    const clearBtn = previewEl?.querySelector?.('[data-clear-file], .form-image-preview__remove');
    if (!clearBtn) return;

    if (isMainPreviewElement(previewEl)) {
        clearBtn.hidden = true;
        clearBtn.setAttribute('hidden', '');
        if (clearBtn.style) {
            clearBtn.style.display = 'none';
        }
        clearBtn.remove?.();
        return;
    }

    clearBtn.hidden = !visible;
    if (visible) {
        clearBtn.removeAttribute('hidden');
        if (clearBtn.style) {
            clearBtn.style.display = '';
        }
    } else {
        clearBtn.setAttribute('hidden', '');
        if (clearBtn.style) {
            clearBtn.style.display = 'none';
        }
    }
}

export function resolvePreviewFallbackUrl(previewEl) {
    const id = String(previewEl?.id || '').toLowerCase();
    if (id.includes('news')) return '/images/news/news-1.jpg';
    if (id.includes('achievement')) return '/images/home/achievement-1.jpg';
    return '/images/research/research-1.jpg';
}

export function normalizePreviewImageUrl(url, previewEl) {
    if (typeof url !== 'string') return '';
    const trimmed = url.trim();
    if (!trimmed) return '';
    if (/^(javascript|data|vbscript):/i.test(trimmed)) return '';

    const pathOnly = trimmed.split('?')[0].toLowerCase();
    if (pathOnly.endsWith('.pdf')) {
        return resolvePreviewFallbackUrl(previewEl);
    }

    if (/^(blob:|https?:\/\/)/i.test(trimmed)) {
        return trimmed;
    }
    if (trimmed.startsWith('/images/') || trimmed.startsWith('/storage/')) {
        return trimmed;
    }
    if (trimmed.startsWith('images/') || trimmed.startsWith('storage/')) {
        return `/${trimmed}`;
    }
    if (trimmed.includes('..')) {
        return resolvePreviewFallbackUrl(previewEl);
    }
    return `/storage/${trimmed.replace(/^\/+/, '')}`;
}

function isMainPreviewElement(previewEl) {
    if (!previewEl) return false;
    if (previewEl.dataset?.previewRole === 'main') return true;
    if (previewEl.dataset?.previewRole === 'supporting') return false;
    const inputName = previewEl.closest?.('.form-group')?.querySelector?.('[data-file-input]')?.name;
    if (inputName) {
        return inputName === 'image';
    }
    const id = String(previewEl.id || '');
    return id.endsWith('-image-preview') && !id.includes('supporting-image');
}

export function setPreviewImage(previewEl, url, filename = '', caption = '', options = {}) {
    if (!previewEl) return;
    revokePreviewObjectUrl(previewEl);

    const img = previewEl.querySelector('[data-preview-img]');
    const nameEl = previewEl.querySelector('[data-preview-name]');
    const capEl = previewEl.querySelector('[data-preview-caption]');
    const isMain = options.allowClear === false || isMainPreviewElement(previewEl);

    if (previewEl.dataset) {
        previewEl.dataset.previewRole = isMain ? 'main' : 'supporting';
    }

    const normalizedUrl = url ? normalizePreviewImageUrl(url, previewEl) : '';
    if (!normalizedUrl) {
        previewEl.style.display = 'none';
        if (img) {
            img.onerror = null;
            img.removeAttribute('src');
        }
        if (nameEl) nameEl.textContent = '';
        if (capEl) capEl.textContent = '';
        setClearButtonVisible(previewEl, false);
        if (previewEl.dataset) {
            previewEl.dataset.isNewFile = 'false';
            if (options.preserveRemoved !== true) {
                delete previewEl.dataset.removedExisting;
            }
            if (options.clearExisting !== false) {
                delete previewEl.dataset.existingUrl;
                delete previewEl.dataset.existingFilename;
                delete previewEl.dataset.existingCaption;
            }
        }
        return;
    }

    const isBlobUrl = typeof url === 'string' && url.startsWith('blob:');
    const isNewFile = Boolean(options.isNewFile ?? isBlobUrl);
    const resolvedName = filename || normalizedUrl.split('/').pop().split('\\').pop() || 'Gambar';
    const resolvedCaption = caption || 'Gambar saat ini (Kosongkan jika tidak ingin mengubah)';
    const fallbackUrl = resolvePreviewFallbackUrl(previewEl);

    if (img) {
        img.onerror = () => {
            img.onerror = null;
            img.src = fallbackUrl;
        };
        img.src = normalizedUrl;
    }
    if (nameEl) nameEl.textContent = resolvedName;
    if (capEl) capEl.textContent = resolvedCaption;
    previewEl.style.display = 'flex';
    // Only supporting image previews expose the [X] button; main images cannot be cleared.
    setClearButtonVisible(previewEl, !isMain);

    if (previewEl.dataset) {
        previewEl.dataset.isNewFile = isNewFile ? 'true' : 'false';
        delete previewEl.dataset.removedExisting;
        if (isNewFile && isBlobUrl) {
            previewEl.dataset.objectUrl = url;
        }
        if (!isNewFile) {
            previewEl.dataset.existingUrl = normalizedUrl;
            previewEl.dataset.existingFilename = resolvedName;
            previewEl.dataset.existingCaption = resolvedCaption;
        }
    }
}

export function clearFileSelection(triggerEl) {
    const group = triggerEl?.closest?.('.form-group');
    if (!group) return;

    const fileInput = group.querySelector('[data-file-input]');
    const textEl = group.querySelector('[data-file-text]');
    const previewEl = group.querySelector('[data-image-preview]');
    const modal = group.closest?.('[data-modal]');
    const scope = modal || group.closest?.('form') || group;
    const isEdit = Boolean(modal?.id?.startsWith('edit-'));
    const modalId = modal?.id || '';
    const existingUrl = previewEl?.dataset?.existingUrl || '';
    const isNewFile = previewEl?.dataset?.isNewFile === 'true';
    const stillRemoved = isEdit && previewEl?.dataset?.removedExisting === 'true';
    const isClearButton = typeof triggerEl?.matches === 'function'
        ? triggerEl.matches('[data-clear-file]')
        : Boolean(triggerEl?.dataset && 'clearFile' in triggerEl.dataset);
    const isSupportingField = fileInput?.name === 'supporting_image_1' || fileInput?.name === 'supporting_image_2';

    if (isClearButton && (fileInput?.name === 'image' || isMainPreviewElement(previewEl))) {
        setClearButtonVisible(previewEl, false);
        return;
    }

    if (isSupportingField && isClearButton) {
        const { supp1, supp2 } = getSupportingFields(scope);
        const pairFields = [supp1, supp2];
        const hasExistingPair = Boolean(supp1.preview?.dataset?.existingUrl && supp2.preview?.dataset?.existingUrl);
        const anyNewFile = pairFields.some((f) =>
            Boolean((f.input?.files && f.input.files.length > 0) || f.preview?.dataset?.isNewFile === 'true')
        );

        if (isEdit && hasExistingPair && anyNewFile) {
            pairFields.forEach((f) => {
                revokePreviewObjectUrl(f.preview);
                clearInputFiles(f.input);
                if (f.input) {
                    clearFieldError(f.input, `${modalId}-${f.input.name}-error`);
                }
                if (f.textEl) {
                    f.textEl.textContent = 'Pilih File (Biarkan kosong jika tidak diganti)';
                    f.textEl.style.color = 'var(--color-primary-300)';
                }
                setPreviewImage(
                    f.preview,
                    f.preview?.dataset?.existingUrl || '',
                    f.preview?.dataset?.existingFilename || '',
                    f.preview?.dataset?.existingCaption || '',
                    { isEditExisting: true, clearExisting: false }
                );
            });
            setSupportingStatusVisible(scope, false);
            return;
        }

        if (isEdit && hasExistingPair && !anyNewFile) {
            pairFields.forEach((f) => {
                revokePreviewObjectUrl(f.preview);
                clearInputFiles(f.input);
                if (f.input) {
                    clearFieldError(f.input, `${modalId}-${f.input.name}-error`);
                }
                if (f.preview?.dataset) {
                    f.preview.dataset.removedExisting = 'true';
                }
                setPreviewImage(f.preview, '', '', '', {
                    clearExisting: false,
                    preserveRemoved: true,
                });
                if (f.textEl) {
                    f.textEl.textContent = 'Pasangan gambar akan dihapus saat disimpan';
                    f.textEl.style.color = 'var(--color-primary-700)';
                }
            });
            setSupportingStatusVisible(scope, true);
            return;
        }

        // Add mode or Edit mode without saved pair: clear only the target supporting input.
        revokePreviewObjectUrl(previewEl);
        clearInputFiles(fileInput);
        if (fileInput) {
            clearFieldError(fileInput, `${modalId}-${fileInput.name}-error`);
        }
        setPreviewImage(previewEl, '');
        if (textEl) {
            textEl.textContent = 'Pilih File';
            textEl.style.color = 'var(--color-primary-300)';
        }
        setSupportingStatusVisible(scope, false);
        return;
    }

    revokePreviewObjectUrl(previewEl);

    if (fileInput) {
        clearInputFiles(fileInput);
        clearFieldError(fileInput, `${modalId}-${fileInput.name}-error`);
    }

    if (isEdit && existingUrl && (isNewFile || (!isClearButton && !stillRemoved))) {
        if (textEl) {
            textEl.textContent = 'Pilih File (Biarkan kosong jika tidak diganti)';
            textEl.style.color = 'var(--color-primary-300)';
        }
        setPreviewImage(
            previewEl,
            existingUrl,
            previewEl.dataset.existingFilename || '',
            previewEl.dataset.existingCaption || '',
            { isEditExisting: true, clearExisting: false }
        );
        return;
    }

    setPreviewImage(previewEl, '', '', '', {
        clearExisting: !isEdit || !existingUrl,
        preserveRemoved: stillRemoved,
    });
    if (textEl) {
        if (stillRemoved) {
            textEl.textContent = 'Pasangan gambar akan dihapus saat disimpan';
            textEl.style.color = 'var(--color-primary-700)';
        } else {
            textEl.textContent = isEdit && existingUrl
                ? 'Pilih File (Biarkan kosong jika tidak diganti)'
                : 'Pilih File';
            textEl.style.color = 'var(--color-primary-300)';
        }
    }
}

export function handleFileInputChange(fileInput) {
    if (!fileInput) return;
    const group = fileInput.closest?.('.form-group');
    const textEl = fileInput.closest?.('.form-file-group')?.querySelector('[data-file-text]');
    const previewEl = group?.querySelector('[data-image-preview]');
    const modal = fileInput.closest?.('[data-modal]');
    const scope = modal || fileInput.closest?.('form') || group;
    const modalId = modal?.id || '';
    const errorContainerId = `${modalId}-${fileInput.name}-error`;
    const isSupportingField = fileInput.name === 'supporting_image_1' || fileInput.name === 'supporting_image_2';

    clearFieldError(fileInput, errorContainerId);
    revokePreviewObjectUrl(previewEl);

    if (fileInput.files && fileInput.files.length > 0) {
        const file = fileInput.files[0];
        const validExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        const ext = file.name.split('.').pop().toLowerCase();
        const maxBytes = 2 * 1024 * 1024;

        if (!validExtensions.includes(ext)) {
            fileInput.value = '';
            clearFileSelection(fileInput);
            showFieldError(fileInput, errorContainerId, 'Format gambar tidak didukung. Gunakan JPG, JPEG, PNG, atau WEBP.');
            return;
        }

        if (file.size > maxBytes) {
            const sizeMb = (file.size / (1024 * 1024)).toFixed(1);
            const msg = `Ukuran file melebihi batas 2MB (${sizeMb} MB). Silakan pilih file yang lebih kecil.`;
            fileInput.value = '';
            clearFileSelection(fileInput);
            showFieldError(fileInput, errorContainerId, msg);
            return;
        }

        if (isSupportingField) {
            const { supp1, supp2 } = getSupportingFields(scope);
            const otherField = fileInput.name === 'supporting_image_1' ? supp2 : supp1;
            const otherWasRemoved = otherField.preview?.dataset?.removedExisting === 'true';

            setSupportingStatusVisible(scope, false);
            [supp1, supp2].forEach((f) => {
                if (f.input) {
                    clearFieldError(f.input, `${modalId}-${f.input.name}-error`);
                }
                if (f.preview?.dataset) {
                    delete f.preview.dataset.removedExisting;
                }
            });

            if (otherWasRemoved && otherField.textEl && !(otherField.input?.files?.length > 0)) {
                otherField.textEl.textContent = 'Pilih File';
                otherField.textEl.style.color = 'var(--color-primary-300)';
            }
        }

        if (textEl) {
            textEl.textContent = file.name;
            textEl.style.color = 'var(--color-primary-700)';
        }

        if (previewEl) {
            const objectUrl = (typeof URL !== 'undefined' && typeof URL.createObjectURL === 'function')
                ? URL.createObjectURL(file)
                : file.name;
            const sizeKb = Math.max(1, Math.round(file.size / 1024));
            setPreviewImage(previewEl, objectUrl, file.name, `File baru (${sizeKb} KB)`, {
                isNewFile: true,
                clearExisting: false,
                allowClear: isSupportingField,
            });
        }

        if (isSupportingField && hasSupportingImagesInModal(modal || scope)) {
            clearGalleryNotices(modal);
        }
    } else {
        clearFileSelection(fileInput);
    }
}

export function validateMainImageSelection(form, modalId, resourceType, isEdit) {
    if (isEdit || !['research', 'news', 'achievement'].includes(resourceType)) {
        return true;
    }
    const imageInput = form?.querySelector('input[name="image"]');
    if (!imageInput) return true;

    if (!imageInput.files || imageInput.files.length === 0) {
        showFieldError(imageInput, `${modalId}-image-error`, 'Gambar utama wajib diisi.');
        imageInput.focus?.();
        return false;
    }
    return true;
}

export function validateSupportingImagesSelection(form, modalId, resourceType, isEdit) {
    if (!['research', 'news', 'achievement'].includes(resourceType)) {
        return true;
    }

    const { supp1, supp2 } = getSupportingFields(form);
    if (!supp1.input || !supp2.input) {
        return true;
    }

    const hasNew1 = Boolean(supp1.input.files && supp1.input.files.length > 0);
    const hasNew2 = Boolean(supp2.input.files && supp2.input.files.length > 0);
    const pairMsg = 'Gambar pendukung harus diisi berpasangan. Pilih kedua gambar atau kosongkan keduanya.';

    if (hasNew1 !== hasNew2) {
        const missingInput = hasNew1 ? supp2.input : supp1.input;
        showFieldError(missingInput, `${modalId}-${missingInput.name}-error`, pairMsg);
        missingInput.focus?.();
        return false;
    }

    if (isEdit && !hasNew1 && !hasNew2) {
        const rem1 = supp1.preview?.dataset?.removedExisting === 'true';
        const rem2 = supp2.preview?.dataset?.removedExisting === 'true';
        if (rem1 !== rem2) {
            showFieldError(supp1.input, `${modalId}-supporting_image_1-error`, 'Gambar pendukung harus dihapus berpasangan.');
            supp1.input.focus?.();
            return false;
        }
    }

    return true;
}

function resetModalFileInputs(modal, isEdit = false) {
    if (!modal) return;
    setSupportingStatusVisible(modal, false);
    modal.querySelectorAll('[data-image-preview]').forEach((previewEl) => {
        revokePreviewObjectUrl(previewEl);
        setClearButtonVisible(previewEl, false);
        if (previewEl.dataset) {
            delete previewEl.dataset.removedExisting;
            previewEl.dataset.isNewFile = 'false';
        }
        if (!isEdit) {
            setPreviewImage(previewEl, '');
        }
    });

    const fileInputs = modal.querySelectorAll('input[type="file"]');
    fileInputs.forEach((fi) => {
        fi.value = '';
        const textEl = fi.closest('.form-file-group')?.querySelector('[data-file-text]');
        if (textEl) {
            textEl.textContent = isEdit
                ? 'Pilih File (Biarkan kosong jika tidak diganti)'
                : 'Pilih File';
            textEl.style.color = 'var(--color-primary-300)';
        }
    });
}

export function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    closeDropdown();

    if (modalId === 'research-modal') {
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            const dateEl = modal.querySelector('input[type="date"]');
            if (dateEl && !dateEl.value) {
                dateEl.value = new Date().toISOString().split('T')[0];
            }
            const statusEl = modal.querySelector('#research-modal-status');
            if (statusEl) statusEl.value = 'Sedang Berjalan';
            resetModalFileInputs(modal, false);
        }
        clearModalErrors(modal);
    }

    if (modalId === 'news-modal') {
        delete modal.dataset.editingImage;
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            const dateEl = modal.querySelector('input[type="date"]');
            if (dateEl && !dateEl.value) {
                dateEl.value = new Date().toISOString().split('T')[0];
            }
            resetModalFileInputs(modal, false);
        }
        clearModalErrors(modal);
    }

    if (modalId === 'achievement-modal') {
        delete modal.dataset.editingImage;
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            const dateEl = modal.querySelector('input[type="date"]');
            if (dateEl && !dateEl.value) {
                dateEl.value = new Date().toISOString().split('T')[0];
            }
            resetModalFileInputs(modal, false);
        }
        clearModalErrors(modal);
        const authorSelect = modal.querySelector('#achievement-modal-author');
        if (authorSelect) {
            delete authorSelect.dataset.pendingSelectedId;
            delete authorSelect.dataset.pendingSelectedName;
        }
        setupAchievementAlumniSelect(modal);
    }

    if (modalId === 'edit-achievement-modal') {
        setupAchievementAlumniSelect(modal);
    }

    if (modalId === 'publication-modal') {
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            const yearEl = modal.querySelector('#publication-modal-year');
            if (yearEl && !yearEl.value) {
                yearEl.value = new Date().getFullYear();
            }
        }
        clearModalErrors(modal);
    }

    modal.hidden = false;
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    const firstInput = modal.querySelector('input:not([type="hidden"])');
    if (firstInput) {
        setTimeout(() => firstInput.focus(), 60);
    }
}

export function closeModal(modal) {
    if (!modal) return;
    modal.hidden = true;
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';

    resetModalFileInputs(modal, false);
    clearModalErrors(modal);
}

function populateEditMainImage(modal, imageUrl) {
    const mainField = getFieldParts(modal, 'image');
    const previewEl = mainField.preview || modal?.querySelector?.(`#${modal.id}-image-preview`);
    if (mainField.textEl) {
        mainField.textEl.textContent = 'Pilih File (Biarkan kosong jika tidak diganti)';
        mainField.textEl.style.color = 'var(--color-primary-300)';
    }
    setPreviewImage(
        previewEl,
        imageUrl,
        '',
        'Gambar saat ini (Kosongkan jika tidak ingin mengubah)',
        { isEditExisting: true, allowClear: false }
    );
}

function populateEditSupportingImages(modal, supportingImage1, supportingImage2) {
    const hasPair = Boolean(supportingImage1 && supportingImage2);
    const { supp1, supp2 } = getSupportingFields(modal);
    setSupportingStatusVisible(modal, false);

    [
        { field: supp1, url: hasPair ? supportingImage1 : '', caption: 'Gambar pendukung 1 saat ini' },
        { field: supp2, url: hasPair ? supportingImage2 : '', caption: 'Gambar pendukung 2 saat ini' },
    ].forEach(({ field, url, caption }) => {
        if (field.textEl) {
            field.textEl.textContent = url
                ? 'Pilih File (Biarkan kosong jika tidak diganti)'
                : 'Pilih File';
        }
        setPreviewImage(field.preview, url, '', caption, { isEditExisting: true, allowClear: true });
    });
}

export function populateEditModal(modalId, item, editBtn) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    const targetItem = item ||
        editBtn?.closest?.('.editable-item') ||
        (typeof document !== 'undefined' ? document.querySelector?.('.editable-item') : null);

    let data = {};
    if (editBtn?.dataset?.editItem) {
        try {
            data = JSON.parse(editBtn.dataset.editItem);
        } catch (e) {}
    }

    if (targetItem) {
        data = {
            id: targetItem.dataset.itemId || data.id || '',
            title: targetItem.dataset.itemTitle || data.title || '',
            author: targetItem.dataset.itemAuthor || data.author || '',
            authorId: targetItem.dataset.itemAuthorId || data.anggota_alumni_id || data.author_id || data.authorId || '',
            date: targetItem.dataset.itemDate || data.date || '',
            status: targetItem.dataset.itemStatus || data.status || '',
            description: targetItem.dataset.itemDescription || targetItem.dataset.itemContent || data.description || '',
            journal: targetItem.dataset.itemJournal || data.journal || '',
            year: targetItem.dataset.itemYear || data.year || '',
            doi: targetItem.dataset.itemDoi || data.doi || '',
            image: targetItem.dataset.itemImage || data.image || targetItem.querySelector?.('img.research-card__image, img.news-card__image, img.achievement-card__image, .achievement-card img')?.getAttribute('src') || '',
            supportingImage1: targetItem.dataset.itemSupportingImage1 || data.supporting_image_1 || '',
            supportingImage2: targetItem.dataset.itemSupportingImage2 || data.supporting_image_2 || '',
        };
    }

    // Riset Form Modal
    if (modalId === 'edit-research-modal') {
        modal.dataset.editingId = data.id || '';
        const idInput = modal.querySelector('input[name="research_id"]');
        if (idInput) idInput.value = data.id || '';

        const titleEl = modal.querySelector('#edit-research-modal-title');
        const authorEl = modal.querySelector('#edit-research-modal-author');
        const dateEl = modal.querySelector('#edit-research-modal-date');
        const statusEl = modal.querySelector('#edit-research-modal-status');
        const descEl = modal.querySelector('#edit-research-modal-description');

        if (titleEl) titleEl.value = data.title || '';
        if (authorEl) authorEl.value = data.author || '';
        if (dateEl) dateEl.value = data.date || '';
        if (statusEl) {
            const statusVal = data.status && data.status.toLowerCase().includes('selesai') ? 'Selesai' : 'Sedang Berjalan';
            statusEl.value = statusVal;
        }
        if (descEl) descEl.value = data.description || '';

        resetModalFileInputs(modal, true);

        populateEditMainImage(modal, data.image);
        populateEditSupportingImages(modal, data.supportingImage1, data.supportingImage2);

        clearModalErrors(modal);
    }

    // Publikasi Form Modal
    if (modalId === 'edit-publication-modal') {
        modal.dataset.editingId = data.id || '';
        const idInput = modal.querySelector('input[name="publication_id"]');
        if (idInput) idInput.value = data.id || '';

        const titleEl = modal.querySelector('#edit-publication-modal-title');
        const authorEl = modal.querySelector('#edit-publication-modal-author');
        const journalEl = modal.querySelector('#edit-publication-modal-journal');
        const yearEl = modal.querySelector('#edit-publication-modal-year');
        const doiEl = modal.querySelector('#edit-publication-modal-doi');

        if (titleEl) titleEl.value = data.title || '';
        if (authorEl) authorEl.value = data.author || '';
        if (journalEl) journalEl.value = data.journal || '';
        if (yearEl) yearEl.value = data.year || '';
        if (doiEl) doiEl.value = data.doi || '';

        clearModalErrors(modal);
    }

    // Berita Form Modal
    if (modalId === 'edit-news-modal') {
        modal.dataset.editingId = data.id || '';
        modal.dataset.editingImage = data.image || '';
        const idInput = modal.querySelector('input[name="news_id"]');
        if (idInput) idInput.value = data.id || '';

        const titleEl = modal.querySelector('#edit-news-modal-title');
        const authorEl = modal.querySelector('#edit-news-modal-author');
        const dateEl = modal.querySelector('#edit-news-modal-date');
        const contentEl = modal.querySelector('#edit-news-modal-content');

        if (titleEl) titleEl.value = data.title || '';
        if (authorEl) authorEl.value = data.author || '';
        if (dateEl) dateEl.value = data.date || '';
        if (contentEl) contentEl.value = data.content || data.description || '';

        resetModalFileInputs(modal, true);

        populateEditMainImage(modal, data.image);
        populateEditSupportingImages(modal, data.supportingImage1, data.supportingImage2);

        clearModalErrors(modal);
    }

    // Prestasi Form Modal
    if (modalId === 'edit-achievement-modal') {
        modal.dataset.editingId = data.id || '';
        modal.dataset.editingImage = data.image || '';
        const idInput = modal.querySelector('input[name="achievement_id"]');
        if (idInput) idInput.value = data.id || '';

        const titleEl = modal.querySelector('#edit-achievement-modal-title');
        const authorEl = modal.querySelector('#edit-achievement-modal-author');
        const dateEl = modal.querySelector('#edit-achievement-modal-date');
        const contentEl = modal.querySelector('#edit-achievement-modal-content');

        if (titleEl) titleEl.value = data.title || '';
        if (dateEl) dateEl.value = data.date || '';
        if (contentEl) contentEl.value = data.content || data.description || '';

        if (authorEl) {
            const targetAuthorId = data.authorId || (data.author && !isNaN(data.author) ? data.author : '');
            authorEl.dataset.pendingSelectedId = targetAuthorId || '';
            authorEl.dataset.pendingSelectedName = data.author || '';
            if (targetAuthorId && authorEl.querySelector(`option[value="${targetAuthorId}"]`)) {
                authorEl.value = String(targetAuthorId);
            } else {
                authorEl.value = '';
            }
        }

        resetModalFileInputs(modal, true);

        populateEditMainImage(modal, data.image);
        populateEditSupportingImages(modal, data.supportingImage1, data.supportingImage2);

        clearModalErrors(modal);
    }
}

let activeEditingItem = null;
let activeEditModal = null;

export function initModal() {
    document.addEventListener('click', (e) => {
        // Admin edit trigger
        const editBtn = e.target.closest('.admin-edit-btn, .admin-edit-action-btn');
        if (editBtn) {
            e.preventDefault();
            e.stopPropagation();

            const modalId = editBtn.dataset.openModal;
            if (!modalId) return;

            const item = editBtn.closest('.editable-item') || document.querySelector('.editable-item');
            activeEditingItem = item;

            populateEditModal(modalId, item, editBtn);
            openModal(modalId);
            return;
        }

        // Delete confirmation trigger
        const deleteTrigger = e.target.closest('[data-delete-trigger]');
        if (deleteTrigger) {
            e.preventDefault();
            e.stopPropagation();
            activeEditModal = deleteTrigger.closest('[data-modal]');
            openModal('delete-confirm-modal');
            return;
        }

        // Confirm delete action
        const confirmDeleteBtn = e.target.closest('[data-confirm-delete]');
        if (confirmDeleteBtn) {
            e.preventDefault();
            e.stopPropagation();

            const isResearchDelete = activeEditModal?.id === 'edit-research-modal' ||
                (activeEditingItem && activeEditingItem.dataset.itemType === 'research');

            if (isResearchDelete) {
                const researchId = activeEditModal?.dataset.editingId ||
                    activeEditModal?.querySelector('input[name="research_id"]')?.value ||
                    activeEditingItem?.dataset.itemId;

                if (!researchId) {
                    alert('ID riset tidak ditemukan.');
                    return;
                }

                if (confirmDeleteBtn.disabled) return;

                const originalBtnText = confirmDeleteBtn.textContent;
                confirmDeleteBtn.disabled = true;
                confirmDeleteBtn.textContent = 'Menghapus...';

                (async () => {
                    try {
                        await deleteResearch(researchId);

                        const confirmModal = confirmDeleteBtn.closest('[data-modal]');
                        if (confirmModal) closeModal(confirmModal);

                        if (activeEditModal) {
                            closeModal(activeEditModal);
                            activeEditModal = null;
                        }

                        if (window.location.pathname.startsWith('/riset/')) {
                            window.location.href = '/riset';
                        } else {
                            if (activeEditingItem) {
                                activeEditingItem.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                                activeEditingItem.style.opacity = '0';
                                activeEditingItem.style.transform = 'scale(0.96)';
                                setTimeout(() => {
                                    activeEditingItem?.remove();
                                    activeEditingItem = null;
                                    window.location.reload();
                                }, 300);
                            } else {
                                window.location.reload();
                            }
                        }
                    } catch (err) {
                        if (err instanceof ApiError && err.status === 401) {
                            const confirmModal = confirmDeleteBtn.closest('[data-modal]');
                            if (confirmModal) closeModal(confirmModal);
                            if (activeEditModal) {
                                closeModal(activeEditModal);
                                activeEditModal = null;
                            }
                            return;
                        }
                        const errorMsg = (err instanceof ApiError && (err.data?.message || err.message))
                            ? (err.data?.message || err.message)
                            : 'Gagal menghapus riset.';
                        alert(errorMsg);
                    } finally {
                        confirmDeleteBtn.disabled = false;
                        confirmDeleteBtn.textContent = originalBtnText;
                    }
                })();

                return;
            }

            const isNewsDelete = activeEditModal?.id === 'edit-news-modal' ||
                (activeEditingItem && activeEditingItem.dataset.itemType === 'news');

            if (isNewsDelete) {
                const newsId = activeEditModal?.dataset.editingId ||
                    activeEditModal?.querySelector('input[name="news_id"]')?.value ||
                    activeEditingItem?.dataset.itemId;

                if (!newsId) {
                    alert('ID berita tidak ditemukan.');
                    return;
                }

                if (confirmDeleteBtn.disabled) return;

                const originalBtnText = confirmDeleteBtn.textContent;
                confirmDeleteBtn.disabled = true;
                confirmDeleteBtn.textContent = 'Menghapus...';

                (async () => {
                    try {
                        await deleteNews(newsId);

                        const confirmModal = confirmDeleteBtn.closest('[data-modal]');
                        if (confirmModal) closeModal(confirmModal);

                        if (activeEditModal) {
                            closeModal(activeEditModal);
                            activeEditModal = null;
                        }

                        if (window.location.pathname.startsWith('/berita/')) {
                            window.location.href = '/berita';
                        } else {
                            if (activeEditingItem) {
                                activeEditingItem.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                                activeEditingItem.style.opacity = '0';
                                activeEditingItem.style.transform = 'scale(0.96)';
                                setTimeout(() => {
                                    activeEditingItem?.remove();
                                    activeEditingItem = null;
                                    window.location.reload();
                                }, 300);
                            } else {
                                window.location.reload();
                            }
                        }
                    } catch (err) {
                        if (err instanceof ApiError && err.status === 401) {
                            const confirmModal = confirmDeleteBtn.closest('[data-modal]');
                            if (confirmModal) closeModal(confirmModal);
                            if (activeEditModal) {
                                closeModal(activeEditModal);
                                activeEditModal = null;
                            }
                            return;
                        }
                        const errorMsg = (err instanceof ApiError && (err.data?.message || err.message))
                            ? (err.data?.message || err.message)
                            : 'Gagal menghapus berita.';
                        alert(errorMsg);
                    } finally {
                        confirmDeleteBtn.disabled = false;
                        confirmDeleteBtn.textContent = originalBtnText;
                    }
                })();

                return;
            }

            const isAchievementDelete = activeEditModal?.id === 'edit-achievement-modal' ||
                (activeEditingItem && activeEditingItem.dataset.itemType === 'achievement');

            if (isAchievementDelete) {
                const achievementId = activeEditModal?.dataset.editingId ||
                    activeEditModal?.querySelector('input[name="achievement_id"]')?.value ||
                    activeEditingItem?.dataset.itemId;

                if (!achievementId) {
                    alert('ID prestasi & pencapaian tidak ditemukan.');
                    return;
                }

                if (confirmDeleteBtn.disabled) return;

                const originalBtnText = confirmDeleteBtn.textContent;
                confirmDeleteBtn.disabled = true;
                confirmDeleteBtn.textContent = 'Menghapus...';

                (async () => {
                    try {
                        await deleteAchievement(achievementId);

                        const confirmModal = confirmDeleteBtn.closest('[data-modal]');
                        if (confirmModal) closeModal(confirmModal);

                        if (activeEditModal) {
                            closeModal(activeEditModal);
                            activeEditModal = null;
                        }

                        if (window.location.pathname.startsWith('/prestasi/')) {
                            window.location.href = '/prestasi';
                        } else {
                            if (activeEditingItem) {
                                activeEditingItem.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                                activeEditingItem.style.opacity = '0';
                                activeEditingItem.style.transform = 'scale(0.96)';
                                setTimeout(() => {
                                    activeEditingItem?.remove();
                                    activeEditingItem = null;
                                    window.location.reload();
                                }, 300);
                            } else {
                                window.location.reload();
                            }
                        }
                    } catch (err) {
                        if (err instanceof ApiError && err.status === 401) {
                            const confirmModal = confirmDeleteBtn.closest('[data-modal]');
                            if (confirmModal) closeModal(confirmModal);
                            if (activeEditModal) {
                                closeModal(activeEditModal);
                                activeEditModal = null;
                            }
                            return;
                        }
                        const errorMsg = (err instanceof ApiError && (err.data?.message || err.message))
                            ? (err.data?.message || err.message)
                            : 'Gagal menghapus prestasi & pencapaian.';
                        alert(errorMsg);
                    } finally {
                        confirmDeleteBtn.disabled = false;
                        confirmDeleteBtn.textContent = originalBtnText;
                    }
                })();

                return;
            }

            const isPublicationDelete = activeEditModal?.id === 'edit-publication-modal' ||
                (activeEditingItem && activeEditingItem.dataset.itemType === 'publication');

            if (isPublicationDelete) {
                const publicationId = activeEditModal?.dataset.editingId ||
                    activeEditModal?.querySelector('input[name="publication_id"]')?.value ||
                    activeEditingItem?.dataset.itemId;

                if (!publicationId) {
                    alert('ID publikasi tidak ditemukan.');
                    return;
                }

                if (confirmDeleteBtn.disabled) return;

                const originalBtnText = confirmDeleteBtn.textContent;
                confirmDeleteBtn.disabled = true;
                confirmDeleteBtn.textContent = 'Menghapus...';

                (async () => {
                    try {
                        await deletePublication(publicationId);

                        const confirmModal = confirmDeleteBtn.closest('[data-modal]');
                        if (confirmModal) closeModal(confirmModal);

                        if (activeEditModal) {
                            closeModal(activeEditModal);
                            activeEditModal = null;
                        }

                        if (activeEditingItem) {
                            const accordionItem = activeEditingItem.closest('[data-accordion-item]');
                            const list = activeEditingItem.closest('.publication-accordion__list');

                            activeEditingItem.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            activeEditingItem.style.opacity = '0';
                            activeEditingItem.style.transform = 'scale(0.96)';

                            setTimeout(() => {
                                activeEditingItem?.remove();
                                activeEditingItem = null;

                                if (list && list.querySelectorAll('.publication-accordion__entry').length === 0 && accordionItem) {
                                    accordionItem.remove();
                                }
                                window.location.reload();
                            }, 300);
                        } else {
                            window.location.reload();
                        }
                    } catch (err) {
                        if (err instanceof ApiError && err.status === 401) {
                            const confirmModal = confirmDeleteBtn.closest('[data-modal]');
                            if (confirmModal) closeModal(confirmModal);
                            if (activeEditModal) {
                                closeModal(activeEditModal);
                                activeEditModal = null;
                            }
                            return;
                        }
                        const errorMsg = (err instanceof ApiError && (err.data?.message || err.message))
                            ? (err.data?.message || err.message)
                            : 'Gagal menghapus publikasi.';
                        alert(errorMsg);
                    } finally {
                        confirmDeleteBtn.disabled = false;
                        confirmDeleteBtn.textContent = originalBtnText;
                    }
                })();

                return;
            }

            const confirmModal = confirmDeleteBtn.closest('[data-modal]');
            if (confirmModal) closeModal(confirmModal);

            if (activeEditModal) {
                closeModal(activeEditModal);
                activeEditModal = null;
            }

            if (activeEditingItem) {
                activeEditingItem.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                activeEditingItem.style.opacity = '0';
                activeEditingItem.style.transform = 'scale(0.96)';
                setTimeout(() => {
                    activeEditingItem?.remove();
                    activeEditingItem = null;
                }, 300);
            }
            return;
        }

        // Dropdown actions to open modals
        const actionBtn = e.target.closest('[data-auth-action]');
        if (actionBtn) {
            const action = actionBtn.dataset.authAction;
            if (action === 'login') {
                e.preventDefault();
                openModal('login-modal');
                return;
            }
            if (action === 'logout') {
                e.preventDefault();
                handleLogout();
                return;
            }
        }

        // Alumni retry button
        const retryAlumniBtn = e.target.closest('[data-retry-alumni]');
        if (retryAlumniBtn) {
            e.preventDefault();
            e.stopPropagation();
            const modal = retryAlumniBtn.closest('[data-modal]');
            if (modal) {
                setupAchievementAlumniSelect(modal, true);
            }
            return;
        }

        const clearFileBtn = e.target.closest('[data-clear-file]');
        if (clearFileBtn) {
            e.preventDefault();
            clearFileSelection(clearFileBtn);
            return;
        }

        const insertGalleryBtn = e.target.closest('[data-insert-gallery]');
        if (insertGalleryBtn) {
            e.preventDefault();
            handleInsertGalleryClick(insertGalleryBtn);
            return;
        }

        // Generic open modal button
        const openBtn = e.target.closest('[data-open-modal]');
        if (openBtn) {
            e.preventDefault();
            openModal(openBtn.dataset.openModal);
            return;
        }

        // Close button or backdrop
        const closeBtn = e.target.closest('[data-modal-close]');
        if (closeBtn) {
            const modal = closeBtn.closest('[data-modal]');
            if (modal) {
                e.preventDefault();
                closeModal(modal);
            }
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('[data-modal]:not([hidden])').forEach((m) => {
                closeModal(m);
            });
            closeDropdown();
        }
    });

    // Password visibility toggle (independent per input group)
    document.addEventListener('click', (e) => {
        const toggleBtn = e.target.closest('[data-password-toggle]');
        if (!toggleBtn) return;

        const inputGroup = toggleBtn.closest('.form-input-group');
        if (!inputGroup) return;

        const input = inputGroup.querySelector('input');
        if (!input) return;

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        const eyeHidden = toggleBtn.querySelector('.password-eye-hidden');
        const eyeVisible = toggleBtn.querySelector('.password-eye-visible');

        if (eyeHidden && eyeVisible) {
            eyeHidden.style.display = isPassword ? 'none' : 'block';
            eyeVisible.style.display = isPassword ? 'block' : 'none';
        }

        toggleBtn.setAttribute('aria-label', isPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    });

    const clearInputError = (input) => {
        if (!input) return;
        if (input.matches('[data-auth-form] input, [data-admin-form] input, [data-admin-form] textarea, [data-admin-form] select')) {
            const group = input.closest('.form-group');
            const errorEl = group?.querySelector('.form-field-error');
            if (errorEl) {
                input.setAttribute('aria-invalid', 'false');
                input.removeAttribute('aria-describedby');
                errorEl.style.display = 'none';
                errorEl.setAttribute('hidden', '');
            }
        }
    };

    document.addEventListener('input', (e) => {
        clearInputError(e.target);
    });

    document.addEventListener('change', (e) => {
        clearInputError(e.target);
    });

    const loginForm = document.querySelector('[data-auth-form="login"]');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const modal = loginForm.closest('[data-modal]');
            clearModalErrors(modal);

            const emailInput = loginForm.querySelector('#login-email');
            const passwordInput = loginForm.querySelector('#login-password');
            const submitBtn = loginForm.querySelector('.btn-modal-submit');

            const email = emailInput?.value.trim() || '';
            const password = passwordInput?.value || '';
            if (!email) {
                showFieldError(emailInput, 'login-email-error', 'Email wajib diisi.');
                emailInput?.focus();
                return;
            }

            if (!password) {
                showFieldError(passwordInput, 'login-password-error', 'Kata sandi wajib diisi.');
                passwordInput?.focus();
                return;
            }

            const originalBtnText = submitBtn ? submitBtn.textContent : 'Masuk';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Memproses...';
            }

            try {
                const response = await login({ email, password });
                const token = response?.access_token;
                const admin = response?.admin;

                if (!token) {
                    showFieldError(passwordInput, 'login-password-error', 'Respon server tidak memuat token autentikasi.');
                    return;
                }

                setAuthToken(token);
                if (admin) {
                    setAdminUser(admin);
                }
                updateAuthUI(admin || { email });

                loginForm.reset();
                if (modal) {
                    closeModal(modal);
                }
            } catch (err) {
                if (err instanceof ApiError) {
                    const status = err.status;
                    const data = err.data;

                    if (status === 422 && data && data.errors) {
                        if (data.errors.email) {
                            showFieldError(emailInput, 'login-email-error', data.errors.email[0]);
                            emailInput?.focus();
                        } else if (data.errors.password) {
                            showFieldError(passwordInput, 'login-password-error', data.errors.password[0]);
                            passwordInput?.focus();
                        } else {
                            showFieldError(passwordInput, 'login-password-error', data.message || 'Validasi formulir gagal.');
                        }
                    } else if (status === 401) {
                        const msg = (data && data.message) ? data.message : 'Kredensial login tidak valid.';
                        showFieldError(passwordInput, 'login-password-error', msg);
                        passwordInput?.focus();
                    } else {
                        const msg = (data && data.message) || err.message || 'Terjadi kesalahan saat masuk.';
                        showFieldError(passwordInput, 'login-password-error', msg);
                        passwordInput?.focus();
                    }
                } else {
                    showFieldError(passwordInput, 'login-password-error', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
                }
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                }
            }
        });
    }


    document.addEventListener('change', (e) => {
        const fileInput = e.target.closest('[data-file-input]');
        if (!fileInput) return;
        handleFileInputChange(fileInput);
    });

    const validResources = ['research', 'news', 'publication', 'achievement'];

    document.addEventListener('submit', async (e) => {
        const form = e.target.closest('[data-admin-form]');
        if (!form) return;

        const resourceType = form.dataset.adminForm;
        if (!validResources.includes(resourceType)) return;

        e.preventDefault();

        const modal = form.closest('[data-modal]');
        const modalId = modal?.id;
        clearModalErrors(modal);

        const submitBtn = form.querySelector('.btn-modal-submit');
        if (submitBtn?.disabled) return;

        const originalBtnText = submitBtn ? submitBtn.textContent : 'Simpan';
        const isEdit = modalId === `edit-${resourceType}-modal`;
        if (
            !validateMainImageSelection(form, modalId, resourceType, isEdit) ||
            !validateSupportingImagesSelection(form, modalId, resourceType, isEdit)
        ) {
            return;
        }
        const editingId = modal?.dataset.editingId ||
            form.querySelector(`input[name="${resourceType}_id"]`)?.value ||
            (activeEditingItem && activeEditingItem.dataset.itemType === resourceType ? activeEditingItem.dataset.itemId : null);

        if (isEdit && !editingId) {
            const missingIdMessages = {
                research: 'ID Riset tidak ditemukan.',
                news: 'ID Berita tidak ditemukan.',
                publication: 'ID Publikasi tidak ditemukan.',
                achievement: 'ID Prestasi & pencapaian tidak ditemukan.',
            };
            showModalGeneralError(modal, modalId, missingIdMessages[resourceType] || 'ID data tidak ditemukan.');
            return;
        }

        let payload;
        if (resourceType === 'research') {
            payload = buildResearchPayload(form, isEdit);
        } else if (resourceType === 'news') {
            payload = buildNewsPayload(form, isEdit);
        } else if (resourceType === 'publication') {
            payload = buildPublicationPayload(form, isEdit);
        } else if (resourceType === 'achievement') {
            payload = buildAchievementPayload(form, isEdit);
        }

        if (resourceType !== 'achievement') {
            const checkAdminId = payload instanceof FormData ? payload.get('admin_id') : payload?.admin_id;
            if (!checkAdminId || isNaN(Number(checkAdminId))) {
                showModalGeneralError(modal, modalId, 'Sesi admin tidak valid atau ID admin tidak ditemukan. Silakan masuk kembali.');
                return;
            }
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';
        }

        try {
            if (resourceType === 'research') {
                if (isEdit) {
                    await updateResearch(editingId, payload);
                } else {
                    await createResearch(payload);
                }
            } else if (resourceType === 'news') {
                if (isEdit) {
                    await updateNews(editingId, payload);
                } else {
                    await createNews(payload);
                }
            } else if (resourceType === 'publication') {
                if (isEdit) {
                    await updatePublication(editingId, payload);
                } else {
                    await createPublication(payload);
                }
            } else if (resourceType === 'achievement') {
                if (isEdit) {
                    await updateAchievement(editingId, payload);
                } else {
                    await createAchievement(payload);
                }
            }

            closeModal(modal);
            if (typeof window !== 'undefined') {
                window.location.reload();
            }
        } catch (err) {
            if (err instanceof ApiError) {
                if (err.status === 401) {
                    closeModal(modal);
                    return;
                }
                if (err.status === 422 && err.data?.errors) {
                    handleCmsValidationErrors(form, modal, modalId, resourceType, err.data.errors);
                    return;
                }
                const msg = (err.data && err.data.message) || err.message || 'Terjadi kesalahan pada server.';
                showModalGeneralError(modal, modalId, msg);
                return;
            }
            showModalGeneralError(modal, modalId, 'Terjadi kesalahan jaringan. Silakan coba lagi.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
            }
        }
    });
}

