@extends('layouts.app')

@section('title', 'TP Laboratory')
@section('description', 'TP Laboratory Research Group adalah kelompok riset terapan di bidang energi terbarukan yang berfokus pada teknologi Building-Integrated Photovoltaics (BIPV) dan pengembangan panel surya.')
@section('page', 'home')

@php
    $hasSsrResearches = isset($researches) && is_iterable($researches) && count($researches) > 0;
    $hasSsrNews = isset($news) && is_iterable($news) && count($news) > 0;
    $hasSsrAchievements = isset($achievements) && is_iterable($achievements) && count($achievements) > 0;
@endphp

@section('content')
    <x-hero />

    <section class="home-intro">
        <div class="container">
            <div class="home-intro__content">
                <h2 class="home-intro__title">TP Laboratory Research Group</h2>
                <p class="home-intro__desc">
                    TP Laboratory Research Group adalah kelompok riset terapan di bidang energi terbarukan yang berfokus pada teknologi Building-Integrated Photovoltaics (BIPV) dan pengembangan panel surya di bawah bimbingan Dr.Eng. Tika Erna Putri
                </p>
            </div>
        </div>
    </section>

    <div class="container home-content" data-home-page>
        <section class="home-section" id="hasil-riset">
            <x-section-title title="HASIL RISET" class="home-section__header" />
            <div class="home-section__list" id="home-research-list" data-home-research-list>
                @if ($hasSsrResearches)
                    @foreach ($researches as $research)
                        <x-research-card
                            :id="$research->id"
                            :image="$research->main_image"
                            :title="$research->title"
                            :status="$research->status"
                            :reverse="$loop->even"
                            :excerpt="$research->excerpt"
                            :href="url('/riset/' . $research->id)"
                        />
                    @endforeach
                @else
                    <div class="card-loading-state" data-home-research-state="loading">
                        <div class="loading-spinner" aria-hidden="true"></div>
                        <p>Memuat hasil riset...</p>
                    </div>

                    <div class="card-empty-state" data-home-research-state="empty" style="display: none;">
                        <p>Belum ada hasil riset yang dipublikasikan saat ini.</p>
                    </div>

                    <div class="card-error-state" data-home-research-state="error" style="display: none;">
                        <p class="card-error-state__message" data-home-research-error-message>Gagal memuat hasil riset.</p>
                        <button type="button" class="button btn-retry" data-home-research-retry>Coba Lagi</button>
                    </div>

                    <noscript>
                        <div class="card-empty-state">
                            <p>Aktifkan JavaScript untuk melihat hasil riset.</p>
                        </div>
                    </noscript>
                @endif
            </div>
            <div class="home-section__action">
                <x-button href="{{ url('/riset') }}" icon="images/icons/chevron-right.png">Lihat Semua Riset</x-button>
            </div>
        </section>

        <section class="home-section" id="berita-terkini">
            <x-section-title title="BERITA TERKINI" class="home-section__header" />
            <div class="home-section__list" id="home-news-list" data-home-news-list>
                @if ($hasSsrNews)
                    @foreach ($news as $item)
                        <x-news-card
                            :id="$item->id"
                            :image="$item->main_image"
                            :title="$item->title"
                            :reverse="$loop->even"
                            :excerpt="$item->excerpt"
                            :href="url('/berita/' . $item->id)"
                            action-text="Lihat Detail Berita"
                        />
                    @endforeach
                @else
                    <div class="card-loading-state" data-home-news-state="loading">
                        <div class="loading-spinner" aria-hidden="true"></div>
                        <p>Memuat berita terkini...</p>
                    </div>

                    <div class="card-empty-state" data-home-news-state="empty" style="display: none;">
                        <p>Belum ada berita yang dipublikasikan saat ini.</p>
                    </div>

                    <div class="card-error-state" data-home-news-state="error" style="display: none;">
                        <p class="card-error-state__message" data-home-news-error-message>Gagal memuat berita terkini.</p>
                        <button type="button" class="button btn-retry" data-home-news-retry>Coba Lagi</button>
                    </div>

                    <noscript>
                        <div class="card-empty-state">
                            <p>Aktifkan JavaScript untuk melihat berita terkini.</p>
                        </div>
                    </noscript>
                @endif
            </div>
            <div class="home-section__action">
                <x-button href="{{ url('/berita') }}" icon="images/icons/chevron-right.png">Lihat Semua Berita</x-button>
            </div>
        </section>

        <section class="home-section" id="prestasi-pencapaian">
            <x-section-title title="PRESTASI & PENCAPAIAN" class="home-section__header" />
            <div class="home-achievements__grid" id="home-achievement-list" data-home-achievement-list>
                @if ($hasSsrAchievements)
                    @foreach ($achievements as $achievement)
                        <x-achievement-card
                            :id="$achievement->id"
                            :image="$achievement->image_url"
                            :title="$achievement->title"
                            :date="$achievement->date"
                            :description="$achievement->excerpt"
                            :href="url('/prestasi/' . $achievement->id)"
                        />
                    @endforeach
                @else
                    <div class="card-loading-state" data-home-achievement-state="loading">
                        <div class="loading-spinner" aria-hidden="true"></div>
                        <p>Memuat prestasi &amp; pencapaian...</p>
                    </div>

                    <div class="card-empty-state" data-home-achievement-state="empty" style="display: none;">
                        <p>Belum ada prestasi atau pencapaian yang dipublikasikan saat ini.</p>
                    </div>

                    <div class="card-error-state" data-home-achievement-state="error" style="display: none;">
                        <p class="card-error-state__message" data-home-achievement-error-message>Gagal memuat prestasi &amp; pencapaian.</p>
                        <button type="button" class="button btn-retry" data-home-achievement-retry>Coba Lagi</button>
                    </div>

                    <noscript>
                        <div class="card-empty-state">
                            <p>Aktifkan JavaScript untuk melihat prestasi &amp; pencapaian.</p>
                        </div>
                    </noscript>
                @endif
            </div>
            <div class="home-section__action">
                <x-button href="{{ url('/prestasi') }}" icon="images/icons/chevron-right.png">Lihat Semua Prestasi & Pencapaian</x-button>
            </div>
        </section>
    </div>
@endsection
