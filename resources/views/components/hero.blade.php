@props([
    'title' => null,
    'subtitle' => null,
    'image' => null,
    'slides' => null,
])

@if ($title)
    <section {{ $attributes->class(['hero', 'hero--banner']) }} style="{{ $image ? 'background-image: url(' . asset($image) . ');' : '' }}">
        <div class="hero__overlay"></div>
        <div class="hero__container">
            <div class="hero__text">
                @if ($subtitle)
                    <span class="hero__subtitle">{{ $subtitle }}</span>
                @endif
                <h1 class="hero__title">{{ $title }}</h1>
            </div>
        </div>
    </section>
@else
    @php
        $defaultSlides = [
            [
                'image' => 'images/home/hero-bg.jpg',
                'subtitle' => 'Selamat Datang di',
                'title' => 'TP Laboratory',
                'description' => null,
                'cta' => null,
                'cta_url' => null,
            ],
            [
                'image' => 'images/research/hero-bg.jpg',
                'subtitle' => null,
                'title' => 'Katalog Riset',
                'description' => 'Telusuri penelitian yang sedang dan telah diselesaikan oleh grup kami.',
                'cta' => 'Kunjungi Katalog Riset',
                'cta_url' => url('/riset'),
            ],
            [
                'image' => 'images/publication/hero-bg.jpg',
                'subtitle' => null,
                'title' => 'Publikasi',
                'description' => 'Beberapa artikel jurnal dari hasil riset TP Laboratory yang dikelompokkan per tahun.',
                'cta' => 'Kunjungi Publikasi',
                'cta_url' => url('/publikasi'),
            ],
            [
                'image' => 'images/news/hero-bg.jpg',
                'subtitle' => null,
                'title' => 'Berita',
                'description' => 'Kabar terbaru mengenai kegiatan, seminar, kolaborasi, dan perkembangan di bidang energi terbarukan.',
                'cta' => 'Baca Berita',
                'cta_url' => url('/berita'),
            ],
            [
                'image' => 'images/achievements/hero-bg.jpg',
                'subtitle' => null,
                'title' => 'Prestasi & Pencapaian',
                'description' => 'Penghargaan, hibah, dan capaian tim TP Laboratory di tingkat nasional maupun internasional.',
                'cta' => 'Lihat Prestasi',
                'cta_url' => url('/prestasi'),
            ],
        ];

        $slidesList = $slides ?? $defaultSlides;
        $totalSlides = count($slidesList);
    @endphp

    <section {{ $attributes->class('hero') }} data-hero-slider aria-roledescription="carousel" aria-label="Hero Slider">
        <div class="hero__track" data-slider-track>
            @foreach ($slidesList as $index => $slide)
                <div
                    class="hero__slide {{ $index === 0 ? 'hero__slide--active' : '' }}"
                    style="background-image: url('{{ asset($slide['image']) }}');"
                    role="group"
                    aria-roledescription="slide"
                    aria-label="Slide {{ $index + 1 }} dari {{ $totalSlides }}"
                    data-slide-index="{{ $index }}"
                >
                    <div class="hero__overlay"></div>
                    <div class="hero__container">
                        <div class="hero__text">
                            @if (!empty($slide['subtitle']))
                                <span class="hero__subtitle">{{ $slide['subtitle'] }}</span>
                            @endif
                            <h2 class="hero__title">{{ $slide['title'] }}</h2>
                            @if (!empty($slide['description']))
                                <p class="hero__desc">{{ $slide['description'] }}</p>
                            @endif
                        </div>
                        @if (!empty($slide['cta']) && !empty($slide['cta_url']))
                            <div class="hero__action">
                                <a href="{{ $slide['cta_url'] }}" class="hero__cta">
                                    {{ $slide['cta'] }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <button type="button" class="hero__control hero__control--prev" aria-label="Slide sebelumnya" data-slider-prev>
            <img src="{{ asset('images/icons/hero-prev.png') }}" alt="" class="hero__control-icon" aria-hidden="true">
        </button>

        <button type="button" class="hero__control hero__control--next" aria-label="Slide selanjutnya" data-slider-next>
            <img src="{{ asset('images/icons/hero-next.png') }}" alt="" class="hero__control-icon" aria-hidden="true">
        </button>

        <div class="hero__pagination" data-slider-dots role="tablist" aria-label="Slide pagination">
            @foreach ($slidesList as $index => $slide)
                <button
                    type="button"
                    class="hero__dot {{ $index === 0 ? 'hero__dot--active' : '' }}"
                    role="tab"
                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                    aria-label="Ke slide {{ $index + 1 }}"
                    data-slide="{{ $index }}"
                ></button>
            @endforeach
        </div>
    </section>
@endif
