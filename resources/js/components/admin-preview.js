
const STORAGE_KEY = 'tp-laboratory-admin-preview';

function enableAdminPreview() {
    document.body.classList.add('is-admin');
    sessionStorage.setItem(STORAGE_KEY, 'true');

    console.info('Admin Preview aktif. Ini hanya simulasi UI lokal.');
}

function disableAdminPreview() {
    document.body.classList.remove('is-admin');
    sessionStorage.removeItem(STORAGE_KEY);

    console.info('Kembali ke mode Visitor.');
}

function initAdminPreview() {
    if (sessionStorage.getItem(STORAGE_KEY) === 'true') {
        document.body.classList.add('is-admin');
    }

    if (import.meta.env.DEV) {
        window.tpAdminPreview = {
            enable: enableAdminPreview,
            disable: disableAdminPreview,
        };
    }
}

initAdminPreview();
