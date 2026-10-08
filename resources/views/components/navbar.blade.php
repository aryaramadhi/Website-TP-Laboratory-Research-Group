<header class="navbar">
    <div class="navbar__container">
        <a href="{{ url('/') }}" class="navbar__brand">
            TP Laboratory
        </a>

        <button type="button" class="navbar__toggle" aria-label="Toggle navigation" aria-expanded="false" data-nav-toggle>
            <span class="navbar__toggle-bar"></span>
            <span class="navbar__toggle-bar"></span>
            <span class="navbar__toggle-bar"></span>
        </button>

        <nav class="navbar__nav" data-nav-menu>
            <ul class="navbar__list">
                <li class="navbar__item">
                    <a href="{{ url('/') }}" class="navbar__link {{ request()->is('/') ? 'navbar__link--active' : '' }}">Beranda</a>
                </li>
                <li class="navbar__item">
                    <a href="{{ url('/riset') }}" class="navbar__link {{ request()->is('riset*') ? 'navbar__link--active' : '' }}">Katalog Riset</a>
                </li>
                <li class="navbar__item">
                    <a href="{{ url('/publikasi') }}" class="navbar__link {{ request()->is('publikasi*') ? 'navbar__link--active' : '' }}">Publikasi</a>
                </li>
                <li class="navbar__item">
                    <a href="{{ url('/berita') }}" class="navbar__link {{ request()->is('berita*') ? 'navbar__link--active' : '' }}">Berita</a>
                </li>
                <li class="navbar__item">
                    <a href="{{ url('/prestasi') }}" class="navbar__link {{ request()->is('prestasi*') ? 'navbar__link--active' : '' }}">Prestasi &amp; Pencapaian</a>
                </li>
            </ul>

            <div class="navbar__actions">
                <button type="button" class="navbar__user-btn" aria-label="Profil Pengguna" aria-haspopup="true" aria-expanded="false" data-profile-toggle>
                    <img src="{{ asset('images/icons/user.png') }}" alt="User" class="navbar__user-icon">
                </button>
                <x-profile-dropdown />
            </div>
        </nav>
    </div>
</header>
