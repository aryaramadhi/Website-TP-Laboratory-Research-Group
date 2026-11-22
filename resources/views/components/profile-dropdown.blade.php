<div {{ $attributes->class(['dropdown', 'profile-dropdown']) }} data-profile-dropdown hidden aria-hidden="true">
    <div class="profile-dropdown__state profile-dropdown__state--guest" data-auth-guest>
        <button type="button" class="profile-dropdown__btn" data-auth-action="login">
            Masuk
        </button>
    </div>

    <div class="profile-dropdown__state profile-dropdown__state--auth" data-auth-authenticated style="display: none;">
        <div class="profile-dropdown__user-info">
            <svg class="profile-dropdown__user-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span class="profile-dropdown__email" data-admin-email></span>
        </div>
        <button type="button" class="profile-dropdown__btn" data-auth-action="logout">
            Keluar
        </button>
    </div>
</div>
