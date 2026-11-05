
const STORAGE_KEY = 'tp-laboratory-admin-preview';

export function isPreviewActive() {
    if (typeof window === 'undefined' || !window.sessionStorage) return false;
    return window.sessionStorage.getItem(STORAGE_KEY) === 'true';
}

export function enableAdminPreview() {
    if (typeof window === 'undefined' || !window.sessionStorage) return;
    document.body.classList.add('is-admin');
    window.sessionStorage.setItem(STORAGE_KEY, 'true');
    console.info('Admin Preview aktif (Simulasi visual lokal, bukan autentikasi API).');
}

export function disableAdminPreview() {
    if (typeof window === 'undefined' || !window.sessionStorage) return;
    document.body.classList.remove('is-admin');
    window.sessionStorage.removeItem(STORAGE_KEY);
    console.info('Admin Preview nonaktif.');
}

export function initAdminPreview() {
    // Mode preview tidak otomatis mengaktifkan .is-admin pada startup
    // Status admin dikendalikan oleh sesi API Sanctum
    if (import.meta.env && import.meta.env.DEV) {
        if (typeof window !== 'undefined') {
            window.tpAdminPreview = {
                enable: enableAdminPreview,
                disable: disableAdminPreview,
                isActive: isPreviewActive,
            };
        }
    }
}

initAdminPreview();

export default {
    isPreviewActive,
    enableAdminPreview,
    disableAdminPreview,
    initAdminPreview,
};
