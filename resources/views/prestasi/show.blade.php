@extends('layouts.app')

@php
    $hasSsrData = !empty($achievement);
    $itemTitle = $achievement?->title ?? 'Detail Prestasi & Pencapaian';
    $itemDate = $achievement?->date ? $achievement->date->translatedFormat('d F Y') : '';
    $itemAuthor = $achievement?->author ?? 'TP Laboratory Research Group';
    $itemImage = $achievement?->main_image ?? 'images/achievements/detail-featured.jpg';
    $itemExcerpt = $achievement?->excerpt ?? '';
@endphp

@section('title', ($hasSsrData && $itemTitle ? $itemTitle . ' | ' : '') . 'Prestasi & Pencapaian | TP Laboratory')
@section('description', $itemExcerpt ?: 'Prestasi, penghargaan, dan pencapaian TP Laboratory Research Group.')
@section('page', 'achievement')

@section('content')
    <x-hero title="Prestasi & Pencapaian" image="images/achievements/detail-hero-bg.jpg" />

    <div class="container achievement-detail" data-achievement-detail data-achievement-id="{{ request()->route('id') }}">
        <div class="admin-detail-action-bar" data-achievement-detail-action @if (!$hasSsrData) style="display: none;" @endif>
            <button
                type="button"
                class="button admin-edit-action-btn"
                data-open-modal="edit-achievement-modal"
                aria-label="Edit Prestasi & Pencapaian"
            >
                <span>Edit Prestasi &amp; Pencapaian</span>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                    <path d="M15 5l4 4"></path>
                </svg>
            </button>
        </div>

        @if (!$hasSsrData)
            <div class="card-loading-state" data-achievement-detail-loading>
                <div class="loading-spinner" aria-hidden="true"></div>
                <p>Memuat detail prestasi &amp; pencapaian...</p>
            </div>

            <div class="card-error-state" data-achievement-detail-error style="display: none;">
                <p class="card-error-state__message" data-achievement-detail-error-message>Gagal memuat detail prestasi.</p>
                <button type="button" class="button btn-retry" data-achievement-detail-retry style="display: none;">Coba Lagi</button>
            </div>

            <noscript>
                <div class="card-empty-state">
                    <p>Aktifkan JavaScript untuk melihat detail prestasi &amp; pencapaian.</p>
                </div>
            </noscript>
        @endif

        <article class="achievement-detail__card editable-item"
            data-achievement-detail-card
            @if (!$hasSsrData) style="display: none;" @endif
            data-item-id="{{ $achievement?->id }}"
            data-item-type="achievement"
            data-item-title="{{ $itemTitle }}"
            data-item-author="{{ $itemAuthor }}"
            data-item-date="{{ $achievement?->date?->format('Y-m-d') ?? '' }}"
            data-item-content="{{ $achievement?->content ?? $itemExcerpt }}"
            data-item-description="{{ $achievement?->content ?? $itemExcerpt }}"
            data-item-supporting-image-1=""
            data-item-supporting-image-2=""
        >
            <div class="achievement-detail__featured">
                <img
                    src="{{ $achievement?->image_url ?? asset('images/achievements/detail-featured.jpg') }}"
                    alt="{{ $itemTitle }}"
                    class="achievement-detail__featured-img"
                    data-achievement-detail-image
                >
            </div>

            <div class="achievement-detail__body" data-achievement-detail-content>
                <h1 class="achievement-detail__title" data-achievement-detail-title>
                    {{ $hasSsrData ? $itemTitle : '' }}
                </h1>

                <div class="achievement-detail__meta">
                    <div class="achievement-detail__meta-item">
                        <img src="{{ asset('images/icons/calendar.png') }}" alt="" class="achievement-detail__meta-icon" aria-hidden="true">
                        <span data-achievement-detail-date>{{ $itemDate }}</span>
                    </div>
                    <div class="achievement-detail__meta-item" data-achievement-detail-author-wrapper>
                        <img src="{{ asset('images/icons/author.png') }}" alt="" class="achievement-detail__meta-icon" aria-hidden="true">
                        <span data-achievement-detail-author>{{ $itemAuthor ? 'Oleh: ' . $itemAuthor : '' }}</span>
                    </div>
                </div>

                <div class="achievement-detail__paragraphs" data-achievement-detail-paragraphs>
                    @if ($achievement && !empty($achievement->content))
                        @php
                            $paragraphs = array_values(array_filter(explode("\n\n", trim($achievement->content)), fn($p) => trim($p) !== ''));
                        @endphp
                        @foreach ($paragraphs as $paragraph)
                            <p class="achievement-detail__paragraph">
                                {!! nl2br(e($paragraph)) !!}
                            </p>
                        @endforeach
                    @elseif ($achievement && !empty($achievement->excerpt))
                        <p class="achievement-detail__paragraph">
                            {!! nl2br(e($achievement->excerpt)) !!}
                        </p>
                    @endif
                </div>
            </div>
        </article>

        <div class="achievement-detail__back-action">
            <a href="/prestasi" class="achievement-detail__back-btn">
                Kembali
            </a>
        </div>
    </div>
@endsection
