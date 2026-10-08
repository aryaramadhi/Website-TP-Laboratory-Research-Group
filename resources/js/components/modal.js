import { closeDropdown } from './dropdown';

export function showFieldError(input, errorContainerId, message) {
    if (!input) return;
    input.setAttribute('aria-invalid', 'true');
    input.setAttribute('aria-describedby', errorContainerId);

    const errorEl = document.getElementById(errorContainerId);
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
    const errorEl = document.getElementById(errorContainerId);
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
    modal.querySelectorAll('input').forEach((input) => {
        input.setAttribute('aria-invalid', 'false');
        input.removeAttribute('aria-describedby');
    });
}

function resetModalFileInputs(modal, isEdit = false) {
    if (!modal) return;
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

    clearModalErrors(modal);
}

export function populateEditModal(modalId, item, editBtn) {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    let data = {};
    if (editBtn?.dataset.editItem) {
        try {
            data = JSON.parse(editBtn.dataset.editItem);
        } catch (e) {}
    }

    if (item) {
        data = {
            id: item.dataset.itemId || data.id || '',
            title: item.dataset.itemTitle || data.title || '',
            author: item.dataset.itemAuthor || data.author || '',
            date: item.dataset.itemDate || data.date || '',
            status: item.dataset.itemStatus || data.status || '',
            description: item.dataset.itemDescription || item.dataset.itemContent || data.description || '',
            journal: item.dataset.itemJournal || data.journal || '',
            year: item.dataset.itemYear || data.year || '',
            doi: item.dataset.itemDoi || data.doi || '',
            supportingImage1: item.dataset.itemSupportingImage1 || data.supporting_image_1 || '',
            supportingImage2: item.dataset.itemSupportingImage2 || data.supporting_image_2 || '',
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

        const supp1Input = modal.querySelector('input[name="supporting_image_1"]');
        const supp1Text = supp1Input?.closest('.form-file-group')?.querySelector('[data-file-text]');
        if (supp1Text) {
            supp1Text.textContent = data.supportingImage1
                ? 'Pilih File (Biarkan kosong jika tidak diganti)'
                : 'Pilih File';
        }

        const supp2Input = modal.querySelector('input[name="supporting_image_2"]');
        const supp2Text = supp2Input?.closest('.form-file-group')?.querySelector('[data-file-text]');
        if (supp2Text) {
            supp2Text.textContent = data.supportingImage2
                ? 'Pilih File (Biarkan kosong jika tidak diganti)'
                : 'Pilih File';
        }

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

        const supp1Input = modal.querySelector('input[name="supporting_image_1"]');
        const supp1Text = supp1Input?.closest('.form-file-group')?.querySelector('[data-file-text]');
        if (supp1Text) {
            supp1Text.textContent = data.supportingImage1
                ? 'Pilih File (Biarkan kosong jika tidak diganti)'
                : 'Pilih File';
        }

        const supp2Input = modal.querySelector('input[name="supporting_image_2"]');
        const supp2Text = supp2Input?.closest('.form-file-group')?.querySelector('[data-file-text]');
        if (supp2Text) {
            supp2Text.textContent = data.supportingImage2
                ? 'Pilih File (Biarkan kosong jika tidak diganti)'
                : 'Pilih File';
        }

        clearModalErrors(modal);
    }

    // Prestasi Form Modal
    if (modalId === 'edit-achievement-modal') {
        modal.dataset.editingId = data.id || '';
        const idInput = modal.querySelector('input[name="achievement_id"]');
        if (idInput) idInput.value = data.id || '';

        const titleEl = modal.querySelector('#edit-achievement-modal-title');
        const authorEl = modal.querySelector('#edit-achievement-modal-author');
        const dateEl = modal.querySelector('#edit-achievement-modal-date');
        const contentEl = modal.querySelector('#edit-achievement-modal-content');

        if (titleEl) titleEl.value = data.title || '';
        if (authorEl) authorEl.value = data.author || '';
        if (dateEl) dateEl.value = data.date || '';
        if (contentEl) contentEl.value = data.content || data.description || '';

        resetModalFileInputs(modal, true);

        const supp1Input = modal.querySelector('input[name="supporting_image_1"]');
        const supp1Text = supp1Input?.closest('.form-file-group')?.querySelector('[data-file-text]');
        if (supp1Text) {
            supp1Text.textContent = data.supportingImage1
                ? 'Pilih File (Biarkan kosong jika tidak diganti)'
                : 'Pilih File';
        }

        const supp2Input = modal.querySelector('input[name="supporting_image_2"]');
        const supp2Text = supp2Input?.closest('.form-file-group')?.querySelector('[data-file-text]');
        if (supp2Text) {
            supp2Text.textContent = data.supportingImage2
                ? 'Pilih File (Biarkan kosong jika tidak diganti)'
                : 'Pilih File';
        }

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

                const originalBtnText = confirmDeleteBtn.textContent;
                confirmDeleteBtn.disabled = true;
                confirmDeleteBtn.textContent = 'Menghapus...';

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                fetch(`/admin/research/${researchId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken || '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                .then(async (response) => {
                    const data = await response.json();
                    if (!response.ok) {
                        alert(data.message || 'Gagal menghapus riset.');
                        return;
                    }

                    const confirmModal = confirmDeleteBtn.closest('[data-modal]');
                    if (confirmModal) closeModal(confirmModal);

                    if (activeEditModal) {
                        closeModal(activeEditModal);
                        activeEditModal = null;
                    }

                    // If currently on detail page, redirect to research list
                    if (window.location.pathname.startsWith('/riset/')) {
                        window.location.href = data.redirect || '/riset';
                    } else {
                        // On list or home page, remove item or reload
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
                })
                .catch(() => {
                    alert('Terjadi kesalahan jaringan saat menghapus riset.');
                })
                .finally(() => {
                    confirmDeleteBtn.disabled = false;
                    confirmDeleteBtn.textContent = originalBtnText;
                });

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

                const originalBtnText = confirmDeleteBtn.textContent;
                confirmDeleteBtn.disabled = true;
                confirmDeleteBtn.textContent = 'Menghapus...';

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                fetch(`/admin/news/${newsId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken || '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                .then(async (response) => {
                    const data = await response.json();
                    if (!response.ok) {
                        alert(data.message || 'Gagal menghapus berita.');
                        return;
                    }

                    const confirmModal = confirmDeleteBtn.closest('[data-modal]');
                    if (confirmModal) closeModal(confirmModal);

                    if (activeEditModal) {
                        closeModal(activeEditModal);
                        activeEditModal = null;
                    }

                    // If currently on detail page, redirect to news list
                    if (window.location.pathname.startsWith('/berita/')) {
                        window.location.href = data.redirect || '/berita';
                    } else {
                        // On list or home page, remove item or reload
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
                })
                .catch(() => {
                    alert('Terjadi kesalahan jaringan saat menghapus berita.');
                })
                .finally(() => {
                    confirmDeleteBtn.disabled = false;
                    confirmDeleteBtn.textContent = originalBtnText;
                });

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

                const originalBtnText = confirmDeleteBtn.textContent;
                confirmDeleteBtn.disabled = true;
                confirmDeleteBtn.textContent = 'Menghapus...';

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                fetch(`/admin/achievements/${achievementId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken || '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                .then(async (response) => {
                    const data = await response.json();
                    if (!response.ok) {
                        alert(data.message || 'Gagal menghapus prestasi & pencapaian.');
                        return;
                    }

                    const confirmModal = confirmDeleteBtn.closest('[data-modal]');
                    if (confirmModal) closeModal(confirmModal);

                    if (activeEditModal) {
                        closeModal(activeEditModal);
                        activeEditModal = null;
                    }

                    // If currently on detail page, redirect to achievement list
                    if (window.location.pathname.startsWith('/prestasi/')) {
                        window.location.href = data.redirect || '/prestasi';
                    } else {
                        // On list or home page, remove item or reload
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
                })
                .catch(() => {
                    alert('Terjadi kesalahan jaringan saat menghapus prestasi & pencapaian.');
                })
                .finally(() => {
                    confirmDeleteBtn.disabled = false;
                    confirmDeleteBtn.textContent = originalBtnText;
                });

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

                const originalBtnText = confirmDeleteBtn.textContent;
                confirmDeleteBtn.disabled = true;
                confirmDeleteBtn.textContent = 'Menghapus...';

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                fetch(`/admin/publications/${publicationId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken || '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                .then(async (response) => {
                    const data = await response.json();
                    if (!response.ok) {
                        alert(data.message || 'Gagal menghapus publikasi.');
                        return;
                    }

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
                })
                .catch(() => {
                    alert('Terjadi kesalahan jaringan saat menghapus publikasi.');
                })
                .finally(() => {
                    confirmDeleteBtn.disabled = false;
                    confirmDeleteBtn.textContent = originalBtnText;
                });

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
            } else if (action === 'register') {
                e.preventDefault();
                openModal('register-modal');
                return;
            }
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

    document.addEventListener('input', (e) => {
        const input = e.target;
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

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const originalBtnText = submitBtn ? submitBtn.textContent : 'Masuk';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Memproses...';
            }

            try {
                const response = await fetch('/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ email, password }),
                });

                const data = await response.json();

                if (!response.ok) {
                    if (data.errors) {
                        if (data.errors.email) {
                            showFieldError(emailInput, 'login-email-error', data.errors.email[0]);
                            emailInput?.focus();
                        } else if (data.errors.password) {
                            showFieldError(passwordInput, 'login-password-error', data.errors.password[0]);
                            passwordInput?.focus();
                        }
                    } else if (data.message) {
                        showFieldError(passwordInput, 'login-password-error', data.message);
                        passwordInput?.focus();
                    }
                    return;
                }

                // Authentication succeeded: redirect or reload to apply server session
                window.location.href = data.redirect || '/';
            } catch (err) {
                showFieldError(passwordInput, 'login-password-error', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                }
            }
        });
    }

    // Handle Register Form Submission
    const registerForm = document.querySelector('[data-auth-form="register"]');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const modal = registerForm.closest('[data-modal]');
            clearModalErrors(modal);

            const emailInput = registerForm.querySelector('#register-email');
            const passwordInput = registerForm.querySelector('#register-password');
            const confirmInput = registerForm.querySelector('#register-password-confirm');

            const email = emailInput?.value.trim() || '';
            const password = passwordInput?.value || '';
            const confirm = confirmInput?.value || '';

            if (!email) {
                showFieldError(emailInput, 'register-email-error', 'Email belum terdaftar');
                emailInput?.focus();
                return;
            }

            if (!password) {
                showFieldError(passwordInput, 'register-password-error', 'Kata sandi salah');
                passwordInput?.focus();
                return;
            }

            if (password !== confirm) {
                showFieldError(confirmInput, 'register-password-confirm-error', 'Kata sandi tidak sesuai');
                confirmInput?.focus();
                return;
            }

            closeModal(modal);
            registerForm.reset();
        });
    }

    document.addEventListener('change', (e) => {
        const fileInput = e.target.closest('[data-file-input]');
        if (!fileInput) return;
        const group = fileInput.closest('.form-file-group');
        const textEl = group?.querySelector('[data-file-text]');
        if (textEl) {
            if (fileInput.files && fileInput.files.length > 0) {
                if (fileInput.files.length === 1) {
                    textEl.textContent = fileInput.files[0].name;
                } else {
                    textEl.textContent = `${fileInput.files.length} file dipilih`;
                }
                textEl.style.color = 'var(--color-primary-700)';
            } else {
                const modal = fileInput.closest('[data-modal]');
                const isEdit = Boolean(modal?.id.startsWith('edit-'));
                textEl.textContent = isEdit
                    ? 'Pilih File (Biarkan kosong jika tidak diganti)'
                    : 'Pilih File';
                textEl.style.color = 'var(--color-primary-300)';
            }
        }
    });

    const ADMIN_RESOURCES = {
        research: {
            basePath: '/admin/research',
            missingIdMessage: 'ID Riset tidak ditemukan.',
        },
        news: {
            basePath: '/admin/news',
            missingIdMessage: 'ID Berita tidak ditemukan.',
        },
        achievement: {
            basePath: '/admin/achievements',
            missingIdMessage: 'ID Prestasi & pencapaian tidak ditemukan.',
        },
        publication: {
            basePath: '/admin/publications',
            missingIdMessage: 'ID Publikasi tidak ditemukan.',
        },
    };

    function showModalGeneralError(modal, modalId, message) {
        const genErr = modal?.querySelector(`#${modalId}-general-error`);
        if (genErr) {
            const textEl = genErr.querySelector('.form-error-text');
            if (textEl && message) {
                textEl.textContent = message;
            }
            genErr.style.display = 'flex';
            genErr.removeAttribute('hidden');
        }
    }

    document.addEventListener('submit', async (e) => {
        const form = e.target.closest('[data-admin-form]');
        if (!form) return;

        const resourceType = form.dataset.adminForm;
        const config = ADMIN_RESOURCES[resourceType];
        if (!config) return;

        e.preventDefault();

        const modal = form.closest('[data-modal]');
        const modalId = modal?.id;
        clearModalErrors(modal);

        const submitBtn = form.querySelector('.btn-modal-submit');
        const originalBtnText = submitBtn ? submitBtn.textContent : 'Simpan';

        const isEdit = modalId === `edit-${resourceType}-modal`;
        const editingId = modal?.dataset.editingId || form.querySelector(`input[name="${resourceType}_id"]`)?.value;

        if (isEdit && !editingId) {
            showModalGeneralError(modal, modalId, config.missingIdMessage);
            return;
        }

        const url = isEdit ? `${config.basePath}/${editingId}` : config.basePath;
        const formData = new FormData(form);

        if (isEdit) {
            formData.set('_method', 'PUT');
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                if (response.status === 422 && data.errors) {
                    for (const [field, messages] of Object.entries(data.errors)) {
                        const message = messages[0];
                        let inputField = form.querySelector(`[name="${field}"]`);
                        let errorId = `${modalId}-${field}-error`;

                        if (field.startsWith('gallery.')) {
                            inputField = form.querySelector('[name="gallery[]"]');
                            errorId = `${modalId}-gallery-error`;
                        }

                        if (inputField) {
                            showFieldError(inputField, errorId, message);
                        } else {
                            showModalGeneralError(modal, modalId, message);
                        }
                    }
                    const firstInvalid = form.querySelector('[aria-invalid="true"]');
                    firstInvalid?.focus();
                    return;
                }

                showModalGeneralError(modal, modalId, data.message || 'Terjadi kesalahan pada server.');
                return;
            }

            // Success: Close modal and navigate/reload to reflect updated data
            closeModal(modal);
            window.location.href = data.redirect || window.location.href;
        } catch (err) {
            showModalGeneralError(modal, modalId, 'Terjadi kesalahan jaringan. Silakan coba lagi.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
            }
        }
    });
}

