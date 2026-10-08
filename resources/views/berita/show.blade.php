@extends('layouts.app')

@php
    $itemTitle = $news?->title ?? 'Building Integrated Photovoltaic (BPIV): Menyatukan estetika bangunan dan sinar surya – Inovasi mandiri energi hemat lahan';
    $itemDate = $news?->release_date ? $news->release_date->translatedFormat('d F Y') : '17 September 2026';
    $itemAuthor = $news?->author ?? 'Dr. Eng. Tika Erna Putri, S.Si., M.Sc.';
    $itemImage = $news?->main_image ?? 'images/news/detail-featured.jpg';
    $itemExcerpt = $news?->excerpt ?? 'Berita lengkap mengenai inovasi Building Integrated Photovoltaic (BPIV) untuk Gedung TILC oleh kelompok riset TP Laboratory.';

    // G1: Main Image
    $g1Url = $news?->image_url ?? asset($itemImage);

    // G2 & G3 resolution with existing data fallback:
    $g2Image = $news?->images->firstWhere('sort_order', 1);
    $g3Image = $news?->images->firstWhere('sort_order', 2);
    if (!$g2Image && !$g3Image && $news && $news->images->isNotEmpty()) {
        $g2Image = $news->images->get(0);
        $g3Image = $news->images->get(1);
    }

    $g2Url = $g2Image?->image_url;
    $g3Url = $g3Image?->image_url;

    // Fallback matrix:
    // Slot Left: always G2 if available, else G1
    // Slot Right: always G3 if available, else G1
    $slotLeft = !empty($g2Url) ? $g2Url : $g1Url;
    $slotLeftAlt = !empty($g2Url) ? ($g2Image->caption ?? ($itemTitle . ' - Gambar Pendukung 1')) : $itemTitle;

    $slotRight = !empty($g3Url) ? $g3Url : $g1Url;
    $slotRightAlt = !empty($g3Url) ? ($g3Image->caption ?? ($itemTitle . ' - Gambar Pendukung 2')) : $itemTitle;
@endphp

@section('title', $itemTitle . ' | TP Laboratory')
@section('description', $itemExcerpt)
@section('page', 'news')

@section('content')
    <x-hero title="Berita" image="images/news/detail-hero-bg.jpg" />

    <div class="container news-detail">
        <div class="admin-detail-action-bar">
            <button
                type="button"
                class="button admin-edit-action-btn"
                data-open-modal="edit-news-modal"
                aria-label="Edit Berita"
            >
                <span>Edit Berita</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                    <path d="M15 5l4 4"></path>
                </svg>
            </button>
        </div>

        <article class="news-detail__card editable-item"
            data-item-id="{{ $news?->id }}"
            data-item-type="news"
            data-item-title="{{ $itemTitle }}"
            data-item-author="{{ $itemAuthor }}"
            data-item-date="{{ $news?->release_date?->format('Y-m-d') ?? '2026-09-17' }}"
            data-item-content="{{ $news?->content ?? $itemExcerpt }}"
            data-item-description="{{ $news?->content ?? $itemExcerpt }}"
            data-item-supporting-image-1="{{ $g2Image ? $g2Image->image_url : '' }}"
            data-item-supporting-image-2="{{ $g3Image ? $g3Image->image_url : '' }}"
        >
            <div class="news-detail__featured">
                <img
                    src="{{ $news?->image_url ?? asset($itemImage) }}"
                    alt="{{ $itemTitle }}"
                    class="news-detail__featured-img"
                >
            </div>

            <div class="news-detail__body">
                <h1 class="news-detail__title">
                    {{ $itemTitle }}
                </h1>

                <div class="news-detail__meta">
                    <div class="news-detail__meta-item">
                        <img src="{{ asset('images/icons/calendar.png') }}" alt="" class="news-detail__meta-icon" aria-hidden="true">
                        <span>{{ $itemDate }}</span>
                    </div>
                    <div class="news-detail__meta-item">
                        <img src="{{ asset('images/icons/author.png') }}" alt="" class="news-detail__meta-icon" aria-hidden="true">
                        <span>Oleh: {{ $itemAuthor }}</span>
                    </div>
                </div>

                @if ($news && !empty($news->content))
                    @php
                        $hasMarker = str_contains($news->content, '[[NEWS_IMAGES]]');
                    @endphp

                    @if ($hasMarker)
                        @php
                            [$beforeMarker, $afterMarker] = explode('[[NEWS_IMAGES]]', $news->content, 2);
                            $beforeParagraphs = array_values(array_filter(explode("\n\n", trim($beforeMarker)), fn($p) => trim($p) !== ''));
                            $afterParagraphs = array_values(array_filter(explode("\n\n", trim($afterMarker)), fn($p) => trim($p) !== ''));
                        @endphp

                        @foreach ($beforeParagraphs as $paragraph)
                            <p class="news-detail__paragraph">
                                {!! nl2br(e($paragraph)) !!}
                            </p>
                        @endforeach

                        <div class="news-detail__gallery">
                            <div class="news-detail__gallery-item news-detail__gallery-item--wide">
                                <img
                                    src="{{ $slotLeft }}"
                                    alt="{{ $slotLeftAlt }}"
                                    class="news-detail__gallery-img"
                                >
                            </div>
                            <div class="news-detail__gallery-item news-detail__gallery-item--narrow">
                                <img
                                    src="{{ $slotRight }}"
                                    alt="{{ $slotRightAlt }}"
                                    class="news-detail__gallery-img"
                                >
                            </div>
                        </div>

                        @foreach ($afterParagraphs as $paragraph)
                            <p class="news-detail__paragraph">
                                {!! nl2br(e($paragraph)) !!}
                            </p>
                        @endforeach
                    @else
                        @php
                            $paragraphs = array_values(array_filter(explode("\n\n", trim($news->content)), fn($p) => trim($p) !== ''));
                        @endphp

                        @foreach ($paragraphs as $index => $paragraph)
                            <p class="news-detail__paragraph">
                                {!! nl2br(e($paragraph)) !!}
                            </p>

                            @if ($index === 0)
                                <div class="news-detail__gallery">
                                    <div class="news-detail__gallery-item news-detail__gallery-item--wide">
                                        <img
                                            src="{{ $slotLeft }}"
                                            alt="{{ $slotLeftAlt }}"
                                            class="news-detail__gallery-img"
                                        >
                                    </div>
                                    <div class="news-detail__gallery-item news-detail__gallery-item--narrow">
                                        <img
                                            src="{{ $slotRight }}"
                                            alt="{{ $slotRightAlt }}"
                                            class="news-detail__gallery-img"
                                        >
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if (empty($paragraphs))
                            <div class="news-detail__gallery">
                                <div class="news-detail__gallery-item news-detail__gallery-item--wide">
                                    <img
                                        src="{{ $slotLeft }}"
                                        alt="{{ $slotLeftAlt }}"
                                        class="news-detail__gallery-img"
                                    >
                                </div>
                                <div class="news-detail__gallery-item news-detail__gallery-item--narrow">
                                    <img
                                        src="{{ $slotRight }}"
                                        alt="{{ $slotRightAlt }}"
                                        class="news-detail__gallery-img"
                                    >
                                </div>
                            </div>
                        @endif
                    @endif
                @else
                    <p class="news-detail__paragraph">
                        Teknologi Building-Integrated Photovoltaics (BIPV) semakin menarik perhatian di Indonesia dan global sebagai solusi energi terbarukan yang efisien serta estetis. Beberapa kemajuan terbaru menunjukkan implementasi nyata dan potensi pasar yang besar. Lalu, apa sebenarnya BIPV? Bulding Integrated Photovoltaic atau biasa dikenal dengan BIPV adalah sistem PV yang bukan hanya dipasang di atas bangunan, <strong>melainkan menggantikan atau menjadi bagian</strong> dari elemen bangunan seperti atap, kaca façade, dinding. Dengan inovasi tersebut, BIPV memiliki fungsi ganda, yaitu menghasilkan listrik dan menjalankan fungsi bangunan seperti atap/penutup façade.
                    </p>

                    <div class="news-detail__gallery">
                        <div class="news-detail__gallery-item news-detail__gallery-item--wide">
                            <img
                                src="{{ $slotLeft }}"
                                alt="{{ $slotLeftAlt }}"
                                class="news-detail__gallery-img"
                            >
                        </div>
                        <div class="news-detail__gallery-item news-detail__gallery-item--narrow">
                            <img
                                src="{{ $slotRight }}"
                                alt="{{ $slotRightAlt }}"
                                class="news-detail__gallery-img"
                            >
                        </div>
                    </div>

                    <p class="news-detail__paragraph">
                        Di Indonesia sendiri, teknologi BIPV belum begitu populer, namun inovasi ini mulai dikembangkan dengan adanya Salah satu proyek penting: PT PP (Persero) Tbk (PTPP), perusahaan konstruksi BUMN, mencatat “rekor nasional” untuk pembangunan gedung dengan teknologi BIPV di kawasan ibukota baru Indonesia.
                    </p>
                @endif
            </div>
        </article>

        <div class="news-detail__back-action">
            <a href="/berita" class="news-detail__back-btn">
                Kembali
            </a>
        </div>
    </div>
@endsection
