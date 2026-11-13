@extends('layouts.app')

@php
    $hasSsrData = !empty($research);
    $itemTitle = $research?->title ?? 'Detail Riset';
    $itemDate = $research?->start_date ? $research->start_date->translatedFormat('d F Y') : '';
    $itemAuthor = $research?->author ?? 'TP Laboratory Research Group';
    $itemImage = $research?->main_image ?? 'images/research/detail-featured.jpg';
    $itemStatus = $research?->status ?? 'Sedang Berjalan';
    $itemExcerpt = $research?->excerpt ?? '';
@endphp

@section('title', ($hasSsrData && $itemTitle ? $itemTitle . ' | ' : '') . 'Katalog Riset | TP Laboratory')
@section('description', $itemExcerpt ?: 'Detail proyek riset TP Laboratory Research Group.')
@section('page', 'research')

@section('content')
    <x-hero title="Katalog Riset" image="images/research/detail-hero-bg.jpg" />

    <div class="container research-detail" data-research-detail data-research-id="{{ request()->route('id') }}">
        <div class="admin-detail-action-bar" data-research-detail-action @if (!$hasSsrData) style="display: none;" @endif>
            <button
                type="button"
                class="button admin-edit-action-btn"
                data-open-modal="edit-research-modal"
                aria-label="Edit Riset"
            >
                <span>Edit Riset</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                    <path d="M15 5l4 4"></path>
                </svg>
            </button>
        </div>

        @if (!$hasSsrData)
            <div class="card-loading-state" data-research-detail-loading>
                <div class="loading-spinner" aria-hidden="true"></div>
                <p>Memuat detail proyek riset...</p>
            </div>

            <div class="card-error-state" data-research-detail-error style="display: none;">
                <p class="card-error-state__message" data-research-detail-error-message>Gagal memuat detail riset.</p>
                <button type="button" class="button btn-retry" data-research-detail-retry style="display: none;">Coba Lagi</button>
            </div>

            <noscript>
                <div class="card-empty-state">
                    <p>Aktifkan JavaScript untuk melihat detail proyek riset.</p>
                </div>
            </noscript>
        @endif

        <article class="research-detail__card editable-item"
            data-research-detail-card
            @if (!$hasSsrData) style="display: none;" @endif
            data-item-id="{{ $research?->id }}"
            data-item-type="research"
            data-item-title="{{ $itemTitle }}"
            data-item-author="{{ $itemAuthor }}"
            data-item-date="{{ $research?->start_date?->format('Y-m-d') ?? '' }}"
            data-item-status="{{ $itemStatus }}"
            data-item-description="{{ $research?->content ?? $itemExcerpt }}"
            data-item-image="{{ $research?->main_image ?? '' }}"
            data-item-supporting-image-1="{{ $research?->gambar_pendukung_1 ?? '' }}"
            data-item-supporting-image-2="{{ $research?->gambar_pendukung_2 ?? '' }}"
        >
            <div class="research-detail__featured">
                <img
                    src="{{ $research?->image_url ?? asset('images/research/detail-featured.jpg') }}"
                    alt="{{ $itemTitle }}"
                    class="research-detail__featured-img"
                    data-research-detail-image
                    onerror="this.onerror=null;this.src='{{ asset('images/research/detail-featured.jpg') }}';"
                >
            </div>

            <div class="research-detail__body" data-research-detail-content>
                <h1 class="research-detail__title" data-research-detail-title>
                    {{ $hasSsrData ? $itemTitle : '' }}
                </h1>

                <div class="research-detail__meta">
                    <div class="research-detail__meta-item">
                        <img src="{{ asset('images/icons/calendar.png') }}" alt="" class="research-detail__meta-icon" aria-hidden="true">
                        <span data-research-detail-date>{{ $itemDate }}</span>
                    </div>
                    <div class="research-detail__meta-item" data-research-detail-author-wrapper>
                        <img src="{{ asset('images/icons/author.png') }}" alt="" class="research-detail__meta-icon" aria-hidden="true">
                        <span data-research-detail-author>{{ $itemAuthor ? 'Oleh: ' . $itemAuthor : '' }}</span>
                    </div>
                </div>

                <div class="research-detail__paragraphs" data-research-detail-paragraphs>
                    @if ($research && !empty($research->content))
                        @php
                            $paragraphs = array_values(array_filter(explode("\n\n", trim($research->content)), fn($p) => trim($p) !== ''));
                        @endphp
                        @foreach ($paragraphs as $paragraph)
                            <p class="research-detail__paragraph">
                                {!! nl2br(e($paragraph)) !!}
                            </p>
                        @endforeach
                    @elseif ($research && !empty($research->excerpt))
                        <p class="research-detail__paragraph">
                            {!! nl2br(e($research->excerpt)) !!}
                        </p>
                    @endif
                </div>
            </div>
        </article>

        <div class="research-detail__back-action">
            <a href="/riset" class="research-detail__back-btn">
                Kembali
            </a>
        </div>
    </div>
@endsection
