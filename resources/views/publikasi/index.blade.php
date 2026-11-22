@extends('layouts.app')

@section('title', 'Publikasi | TP Laboratory')
@section('description', 'Daftar publikasi ilmiah TP Laboratory Research Group di bidang energi terbarukan, Building-Integrated Photovoltaics (BIPV), dan teknologi panel surya.')
@section('page', 'publication')

@php
    $hasSsrData = isset($publicationsByYear) && $publicationsByYear->isNotEmpty();
@endphp

@section('content')
    <x-hero title="Publikasi" image="images/publication/hero-bg.jpg" />

    <div class="container publication-page" data-publication-page>
        <div class="publication-page__wrapper">
            <div class="admin-action-bar">
                <x-admin-add-button modal="publication-modal">Tambah Publikasi</x-admin-add-button>
            </div>

            @if ($hasSsrData)
                <x-publication-accordion :publications-by-year="$publicationsByYear" />
            @else
                <div class="card-loading-state" data-publication-state="loading">
                    <div class="loading-spinner" aria-hidden="true"></div>
                    <p>Memuat daftar publikasi dan hibah...</p>
                </div>

                <div class="card-empty-state" data-publication-state="empty" style="display: none;">
                    <p>Belum ada publikasi atau hibah yang dipublikasikan.</p>
                </div>

                <div class="card-error-state" data-publication-state="error" style="display: none;">
                    <p class="card-error-state__message" data-publication-error-message>Gagal memuat daftar publikasi.</p>
                    <button type="button" class="button btn-retry" data-publication-retry>Coba Lagi</button>
                </div>

                <noscript>
                    <div class="card-empty-state">
                        <p>Aktifkan JavaScript untuk melihat daftar publikasi secara dinamis.</p>
                    </div>
                </noscript>

                <div class="accordion publication-accordion" data-accordion data-publication-accordion style="display: none;">
                    {{-- Dynamically populated by publication.js --}}
                </div>
            @endif
        </div>
    </div>
@endsection
