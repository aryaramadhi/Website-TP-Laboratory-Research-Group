export function closeDropdown() {
    const dropdown = document.querySelector('[data-profile-dropdown]');
    const toggle = document.querySelector('[data-profile-toggle]');
    if (dropdown) {
        dropdown.hidden = true;
        dropdown.setAttribute('aria-hidden', 'true');
    }
    if (toggle) {
        toggle.setAttribute('aria-expanded', 'false');
    }
}

export function openDropdown() {
    const dropdown = document.querySelector('[data-profile-dropdown]');
    const toggle = document.querySelector('[data-profile-toggle]');
    if (dropdown) {
        dropdown.hidden = false;
        dropdown.setAttribute('aria-hidden', 'false');
    }
    if (toggle) {
        toggle.setAttribute('aria-expanded', 'true');
    }
}

export function initDropdown() {
    const toggle = document.querySelector('[data-profile-toggle]');
    const dropdown = document.querySelector('[data-profile-dropdown]');
    if (!toggle || !dropdown) return;

    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        if (dropdown.hidden) {
            openDropdown();
        } else {
            closeDropdown();
        }
    });

    document.addEventListener('click', (e) => {
        if (!dropdown.hidden && !dropdown.contains(e.target) && !toggle.contains(e.target)) {
            closeDropdown();
        }
    });
}
