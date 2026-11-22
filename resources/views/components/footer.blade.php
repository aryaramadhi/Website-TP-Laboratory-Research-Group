<footer class="footer">
    <div class="footer__container">
        <div class="footer__main">
            <div class="footer__col footer__col--brand">
                <h2 class="footer__brand-title">TP Laboratory</h2>
                <p class="footer__brand-desc">
                    TP Laboratory adalah kelompok riset terapan di bidang energi terbarukan (fokus pada teknologi Building-Integrated Photovoltaics / BIPV dan panel surya) di bawah bimbingan Dr.Eng. Tika Erna Putri.
                </p>
            </div>

            <div class="footer__col footer__col--nav">
                <div class="footer__heading-group">
                    <h3 class="footer__heading">NAVIGASI</h3>
                    <div class="footer__heading-bar" aria-hidden="true"></div>
                </div>

                <ul class="footer__nav-list">
                    <li class="footer__nav-item">
                        <a href="{{ url('/') }}" class="footer__nav-link">
                            <img src="{{ asset('images/icons/nav-chevron.png') }}" alt="" class="footer__nav-icon" aria-hidden="true">
                            <span>Beranda</span>
                        </a>
                    </li>
                    <li class="footer__nav-item">
                        <a href="{{ url('/riset') }}" class="footer__nav-link">
                            <img src="{{ asset('images/icons/nav-chevron.png') }}" alt="" class="footer__nav-icon" aria-hidden="true">
                            <span>Katalog Riset</span>
                        </a>
                    </li>
                    <li class="footer__nav-item">
                        <a href="{{ url('/publikasi') }}" class="footer__nav-link">
                            <img src="{{ asset('images/icons/nav-chevron.png') }}" alt="" class="footer__nav-icon" aria-hidden="true">
                            <span>Publikasi</span>
                        </a>
                    </li>
                    <li class="footer__nav-item">
                        <a href="{{ url('/berita') }}" class="footer__nav-link">
                            <img src="{{ asset('images/icons/nav-chevron.png') }}" alt="" class="footer__nav-icon" aria-hidden="true">
                            <span>Berita</span>
                        </a>
                    </li>
                    <li class="footer__nav-item">
                        <a href="{{ url('/prestasi') }}" class="footer__nav-link">
                            <img src="{{ asset('images/icons/nav-chevron.png') }}" alt="" class="footer__nav-icon" aria-hidden="true">
                            <span>Prestasi & Pencapaian</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="footer__col footer__col--contact">
                <div class="footer__heading-group">
                    <h3 class="footer__heading">KONTAK</h3>
                    <div class="footer__heading-bar" aria-hidden="true"></div>
                </div>

                <ul class="footer__contact-list">
                    <li class="footer__contact-item">
                        <img src="{{ asset('images/icons/mail.png') }}" alt="Email" class="footer__contact-icon">
                        <a href="mailto:tika.erna.p@mail.ugm.ac.id" class="footer__contact-link">
                            tika.erna.p@mail.ugm.ac.id
                        </a>
                    </li>
                    <li class="footer__contact-item">
                        <img src="{{ asset('images/icons/phone.png') }}" alt="Telepon" class="footer__contact-icon">
                        <a href="tel:+6201234567890" class="footer__contact-link">
                            +62 01234567890
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="footer__divider" aria-hidden="true"></div>

    <div class="footer__bottom">
        <p class="footer__copyright">
            &copy; 2026 TP Laboratory Research Group. All Rights Reserved.
        </p>
    </div>
</footer>
