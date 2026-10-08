<x-modal id="login-modal" class="auth-modal">
    <div class="modal__header">
        <h2 class="modal__title">Masuk</h2>
        <button type="button" class="modal__close" aria-label="Tutup" data-modal-close>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <div class="modal__divider" aria-hidden="true"></div>

    <form class="modal__form" data-auth-form="login" action="#" method="POST" novalidate>
        @csrf
        <div class="modal__body">
            <div class="form-group">
                <label for="login-email" class="form-label">Email</label>
                <input
                    type="email"
                    id="login-email"
                    name="email"
                    class="form-input"
                    placeholder="nama@gmail.com"
                    autocomplete="email"
                    required
                >
                <div class="form-field-error" id="login-email-error" role="alert" style="display: none;">
                    <svg class="form-error-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span class="form-error-text">Email belum terdaftar</span>
                </div>
            </div>

            <div class="form-group">
                <label for="login-password" class="form-label">Kata sandi</label>
                <div class="form-input-group">
                    <input
                        type="password"
                        id="login-password"
                        name="password"
                        class="form-input"
                        placeholder="********"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="form-password-toggle" aria-label="Tampilkan kata sandi" data-password-toggle>
                        <svg class="password-eye-hidden" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M2 10s3.5 5 10 5 10-5 10-5"></path>
                            <path d="M4 14l-2 3"></path>
                            <path d="M12 15v3"></path>
                            <path d="M20 14l2 3"></path>
                        </svg>
                        <svg class="password-eye-visible" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display: none;">
                            <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
                <div class="form-field-error" id="login-password-error" role="alert" style="display: none;">
                    <svg class="form-error-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span class="form-error-text">Kata sandi salah</span>
                </div>
            </div>

            <div class="modal__actions">
                <button type="button" class="btn-modal-cancel" data-modal-close>
                    Batal
                </button>
                <button type="submit" class="btn-modal-submit">
                    Masuk
                </button>
            </div>
        </div>
    </form>
</x-modal>
