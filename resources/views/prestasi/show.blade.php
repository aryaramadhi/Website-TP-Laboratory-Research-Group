@extends('layouts.app')

@php
    $itemTitle = $achievement?->title ?? 'Building Integrated Photovoltaic (BPIV): Menyatukan estetika bangunan dan sinar surya – Inovasi mandiri energi hemat lahan';
    $itemDate = $achievement?->date ? $achievement->date->translatedFormat('d F Y') : '17 September 2026';
    $itemAuthor = $achievement?->author ?? 'Dr. Eng. Tika Erna Putri, S.Si., M.Sc.';
    $itemImage = $achievement?->main_image ?? 'images/achievements/detail-featured.jpg';
    $itemExcerpt = $achievement?->excerpt ?? 'Prestasi dan capaian riset mengenai implementasi teknologi Building-Integrated Photovoltaics (BIPV) oleh kelompok riset TP Laboratory.';

    // G1: Main Image
    $g1Url = $achievement?->image_url ?? asset($itemImage);

    // G2 & G3 resolution with existing data fallback:
    $g2Image = $achievement?->images->firstWhere('sort_order', 1);
    $g3Image = $achievement?->images->firstWhere('sort_order', 2);
    if (!$g2Image && !$g3Image && $achievement && $achievement->images->isNotEmpty()) {
        $g2Image = $achievement->images->get(0);
        $g3Image = $achievement->images->get(1);
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
@section('page', 'achievement')

@section('content')
    <x-hero title="Prestasi & Pencapaian" image="images/achievements/detail-hero-bg.jpg" />

    <div class="container achievement-detail">
        <div class="admin-detail-action-bar">
            <button
                type="button"
                class="button admin-edit-action-btn"
                data-open-modal="edit-achievement-modal"
                aria-label="Edit Prestasi & Pencapaian"
            >
                <span>Edit Prestasi & Pencapaian</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                    <path d="M15 5l4 4"></path>
                </svg>
            </button>
        </div>

        <article class="achievement-detail__card editable-item"
            data-item-id="{{ $achievement?->id }}"
            data-item-type="achievement"
            data-item-title="{{ $itemTitle }}"
            data-item-author="{{ $itemAuthor }}"
            data-item-date="{{ $achievement?->date?->format('Y-m-d') ?? '2026-09-17' }}"
            data-item-content="{{ $achievement?->content ?? $itemExcerpt }}"
            data-item-description="{{ $achievement?->content ?? $itemExcerpt }}"
            data-item-supporting-image-1="{{ $g2Image ? $g2Image->image_url : '' }}"
            data-item-supporting-image-2="{{ $g3Image ? $g3Image->image_url : '' }}"
        >
            <div class="achievement-detail__featured">
                <img
                    src="{{ $achievement?->image_url ?? asset($itemImage) }}"
                    alt="{{ $itemTitle }}"
                    class="achievement-detail__featured-img"
                >
            </div>

            <div class="achievement-detail__body">
                <h1 class="achievement-detail__title">
                    {{ $itemTitle }}
                </h1>

                <div class="achievement-detail__meta">
                    <div class="achievement-detail__meta-item">
                        <img src="{{ asset('images/icons/calendar.png') }}" alt="" class="achievement-detail__meta-icon" aria-hidden="true">
                        <span>{{ $itemDate }}</span>
                    </div>
                    <div class="achievement-detail__meta-item">
                        <img src="{{ asset('images/icons/author.png') }}" alt="" class="achievement-detail__meta-icon" aria-hidden="true">
                        <span>Oleh: {{ $itemAuthor }}</span>
                    </div>
                </div>

                @if ($achievement && !empty($achievement->content))
                    @php
                        $hasMarker = str_contains($achievement->content, '[[ACHIEVEMENT_IMAGES]]');
                    @endphp

                    @if ($hasMarker)
                        @php
                            [$beforeMarker, $afterMarker] = explode('[[ACHIEVEMENT_IMAGES]]', $achievement->content, 2);
                            $beforeParagraphs = array_values(array_filter(explode("\n\n", trim($beforeMarker)), fn($p) => trim($p) !== ''));
                            $afterParagraphs = array_values(array_filter(explode("\n\n", trim($afterMarker)), fn($p) => trim($p) !== ''));
                        @endphp

                        @foreach ($beforeParagraphs as $paragraph)
                            <p class="achievement-detail__paragraph">
                                {!! nl2br(e($paragraph)) !!}
                            </p>
                        @endforeach

                        <div class="achievement-detail__gallery">
                            <div class="achievement-detail__gallery-item achievement-detail__gallery-item--wide">
                                <img
                                    src="{{ $slotLeft }}"
                                    alt="{{ $slotLeftAlt }}"
                                    class="achievement-detail__gallery-img"
                                >
                            </div>
                            <div class="achievement-detail__gallery-item achievement-detail__gallery-item--narrow">
                                <img
                                    src="{{ $slotRight }}"
                                    alt="{{ $slotRightAlt }}"
                                    class="achievement-detail__gallery-img"
                                >
                            </div>
                        </div>

                        @foreach ($afterParagraphs as $paragraph)
                            <p class="achievement-detail__paragraph">
                                {!! nl2br(e($paragraph)) !!}
                            </p>
                        @endforeach
                    @else
                        @php
                            $paragraphs = array_values(array_filter(explode("\n\n", trim($achievement->content)), fn($p) => trim($p) !== ''));
                        @endphp

                        @foreach ($paragraphs as $index => $paragraph)
                            <p class="achievement-detail__paragraph">
                                {!! nl2br(e($paragraph)) !!}
                            </p>

                            @if ($index === 0)
                                <div class="achievement-detail__gallery">
                                    <div class="achievement-detail__gallery-item achievement-detail__gallery-item--wide">
                                        <img
                                            src="{{ $slotLeft }}"
                                            alt="{{ $slotLeftAlt }}"
                                            class="achievement-detail__gallery-img"
                                        >
                                    </div>
                                    <div class="achievement-detail__gallery-item achievement-detail__gallery-item--narrow">
                                        <img
                                            src="{{ $slotRight }}"
                                            alt="{{ $slotRightAlt }}"
                                            class="achievement-detail__gallery-img"
                                        >
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if (empty($paragraphs))
                            <div class="achievement-detail__gallery">
                                <div class="achievement-detail__gallery-item achievement-detail__gallery-item--wide">
                                    <img
                                        src="{{ $slotLeft }}"
                                        alt="{{ $slotLeftAlt }}"
                                        class="achievement-detail__gallery-img"
                                    >
                                </div>
                                <div class="achievement-detail__gallery-item achievement-detail__gallery-item--narrow">
                                    <img
                                        src="{{ $slotRight }}"
                                        alt="{{ $slotRightAlt }}"
                                        class="achievement-detail__gallery-img"
                                    >
                                </div>
                            </div>
                        @endif
                    @endif
                @else
                    <p class="achievement-detail__paragraph">
                        Teknologi Building-Integrated Photovoltaics (BIPV) semakin menarik perhatian di Indonesia dan global sebagai solusi energi terbarukan yang efisien serta estetis. Beberapa kemajuan terbaru menunjukkan implementasi nyata dan potensi pasar yang besar. Lalu, apa sebenarnya BIPV? Bulding Integrated Photovoltaic atau biasa dikenal dengan BIPV adalah sistem PV yang bukan hanya dipasang di atas bangunan, <strong>melainkan menggantikan atau menjadi bagian</strong> dari elemen bangunan seperti atap, kaca façade, dinding.
                    </p>

                    <div class="achievement-detail__gallery">
                        <div class="achievement-detail__gallery-item achievement-detail__gallery-item--wide">
                            <img
                                src="{{ $slotLeft }}"
                                alt="{{ $slotLeftAlt }}"
                                class="achievement-detail__gallery-img"
                            >
                        </div>
                        <div class="achievement-detail__gallery-item achievement-detail__gallery-item--narrow">
                            <img
                                src="{{ $slotRight }}"
                                alt="{{ $slotRightAlt }}"
                                class="achievement-detail__gallery-img"
                            >
                        </div>
                    </div>

                    <p class="achievement-detail__paragraph">
                        Di Indonesia sendiri, teknologi BIPV belum begitu populer, namun inovasi ini mulai dikembangkan dengan adanya Salah satu proyek penting: PT PP (Persero) Tbk (PTPP), perusahaan konstruksi BUMN, mencatat “rekor nasional” untuk pembangunan gedung dengan teknologi BIPV di kawasan ibukota baru Indonesia.
                    </p>
                @endif
            </div>
        </article>

        <div class="achievement-detail__back-action">
            <a href="/prestasi" class="achievement-detail__back-btn">
                Kembali
            </a>
        </div>
    </div>
@endsection
