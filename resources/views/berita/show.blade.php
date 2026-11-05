@extends('layouts.app')

@php
    $hasSsrData = !empty($news);
    $itemTitle = $news?->title ?? 'Detail Berita';
    $itemDate = $news?->release_date ? $news->release_date->translatedFormat('d F Y') : '';
    $itemAuthor = $news?->author ?? 'TP Laboratory Research Group';
    $itemImage = $news?->main_image ?? 'images/news/detail-featured.jpg';
    $itemExcerpt = $news?->excerpt ?? '';
@endphp

@section('title', ($hasSsrData && $itemTitle ? $itemTitle . ' | ' : '') . 'Berita | TP Laboratory')
@section('description', $itemExcerpt ?: 'Berita dan artikel terkini seputar riset TP Laboratory Research Group.')
@section('page', 'news')

@section('content')
    <x-hero title="Berita" image="images/news/detail-hero-bg.jpg" />

    <div class="container news-detail" data-news-detail data-news-id="{{ request()->route('id') }}">
        <div class="admin-detail-action-bar" data-news-detail-action @if (!$hasSsrData) style="display: none;" @endif>
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

        @if (!$hasSsrData)
            <div class="card-loading-state" data-news-detail-loading>
                <div class="loading-spinner" aria-hidden="true"></div>
                <p>Memuat detail berita...</p>
            </div>

            <div class="card-error-state" data-news-detail-error style="display: none;">
                <p class="card-error-state__message" data-news-detail-error-message>Gagal memuat detail berita.</p>
                <button type="button" class="button btn-retry" data-news-detail-retry style="display: none;">Coba Lagi</button>
            </div>

            <noscript>
                <div class="card-empty-state">
                    <p>Aktifkan JavaScript untuk melihat detail berita.</p>
                </div>
            </noscript>
        @endif

        <article class="news-detail__card editable-item"
            data-news-detail-card
            @if (!$hasSsrData) style="display: none;" @endif
            data-item-id="{{ $news?->id }}"
            data-item-type="news"
            data-item-title="{{ $itemTitle }}"
            data-item-author="{{ $itemAuthor }}"
            data-item-date="{{ $news?->release_date?->format('Y-m-d') ?? '' }}"
            data-item-content="{{ $news?->content ?? $itemExcerpt }}"
            data-item-description="{{ $news?->content ?? $itemExcerpt }}"
            data-item-supporting-image-1=""
            data-item-supporting-image-2=""
        >
            <div class="news-detail__featured">
                <img
                    src="{{ $news?->image_url ?? asset('images/news/detail-featured.jpg') }}"
                    alt="{{ $itemTitle }}"
                    class="news-detail__featured-img"
                    data-news-detail-image
                >
            </div>

            <div class="news-detail__body" data-news-detail-content>
                <h1 class="news-detail__title" data-news-detail-title>
                    {{ $hasSsrData ? $itemTitle : '' }}
                </h1>

                <div class="news-detail__meta">
                    <div class="news-detail__meta-item">
                        <img src="{{ asset('images/icons/calendar.png') }}" alt="" class="news-detail__meta-icon" aria-hidden="true">
                        <span data-news-detail-date>{{ $itemDate }}</span>
                    </div>
                    <div class="news-detail__meta-item" data-news-detail-author-wrapper>
                        <img src="{{ asset('images/icons/author.png') }}" alt="" class="news-detail__meta-icon" aria-hidden="true">
                        <span data-news-detail-author>{{ $itemAuthor ? 'Oleh: ' . $itemAuthor : '' }}</span>
                    </div>
                </div>

                <div class="news-detail__paragraphs" data-news-detail-paragraphs>
                    @if ($news && !empty($news->content))
                        @php
                            $paragraphs = array_values(array_filter(explode("\n\n", trim($news->content)), fn($p) => trim($p) !== ''));
                        @endphp
                        @foreach ($paragraphs as $paragraph)
                            <p class="news-detail__paragraph">
                                {!! nl2br(e($paragraph)) !!}
                            </p>
                        @endforeach
                    @elseif ($news && !empty($news->excerpt))
                        <p class="news-detail__paragraph">
                            {!! nl2br(e($news->excerpt)) !!}
                        </p>
                    @endif
                </div>
            </div>
        </article>

        <div class="news-detail__back-action">
            <a href="/berita" class="news-detail__back-btn">
                Kembali
            </a>
        </div>
    </div>
@endsection
