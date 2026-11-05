@extends('layouts.app')

@section('title', 'Berita | TP Laboratory')
@section('description', 'Berita dan artikel terkini seputar riset Building-Integrated Photovoltaics (BIPV) dan energi terbarukan oleh TP Laboratory.')
@section('page', 'news')

@php
    $hasSsrData = isset($news) && is_iterable($news) && count($news) > 0;
@endphp

@section('content')
    <x-hero title="Berita" image="images/news/hero-bg.jpg" />

    <div class="container news-page" data-news-page>
        <div class="admin-action-bar">
            <x-admin-add-button modal="news-modal">Tambah Berita</x-admin-add-button>
        </div>

        <div class="news-page__list" data-news-list id="news-list">
            @if ($hasSsrData)
                @foreach ($news as $item)
                    <x-news-card
                        :id="$item->id"
                        :image="$item->main_image"
                        :title="$item->title"
                        :excerpt="$item->excerpt"
                        :href="url('/berita/' . $item->id)"
                        :reverse="$loop->even"
                        action-text="Lihat Detail Berita"
                    />
                @endforeach
            @else
                <div class="card-loading-state" data-news-state="loading">
                    <div class="loading-spinner" aria-hidden="true"></div>
                    <p>Memuat daftar berita...</p>
                </div>

                <div class="card-empty-state" data-news-state="empty" style="display: none;">
                    <p>Belum ada berita yang dipublikasikan.</p>
                </div>

                <div class="card-error-state" data-news-state="error" style="display: none;">
                    <p class="card-error-state__message" data-news-error-message>Gagal memuat daftar berita.</p>
                    <button type="button" class="button btn-retry" data-news-retry>Coba Lagi</button>
                </div>

                <noscript>
                    <div class="card-empty-state">
                        <p>Aktifkan JavaScript untuk melihat daftar berita secara dinamis.</p>
                    </div>
                </noscript>
            @endif
        </div>
    </div>
@endsection
