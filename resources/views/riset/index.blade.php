@extends('layouts.app')

@section('title', 'Katalog Riset | TP Laboratory')
@section('description', 'Katalog Riset TP Laboratory Research Group di bidang energi terbarukan, Building-Integrated Photovoltaics (BIPV), dan teknologi panel surya.')
@section('page', 'research')

@section('content')
    <x-hero title="Katalog Riset" image="images/research/hero-bg.jpg" />

    <div class="container research-catalog">
        <div class="admin-action-bar">
            <x-admin-add-button modal="research-modal">Tambah Riset</x-admin-add-button>
        </div>

        <div class="research-catalog__list" data-research-catalog-list id="research-catalog-list">
            <div class="card-loading-state" data-research-state="loading">
                <div class="loading-spinner" aria-hidden="true"></div>
                <p>Memuat data katalog riset...</p>
            </div>

            <div class="card-empty-state" data-research-state="empty" style="display: none;">
                <p>Belum ada proyek riset yang dipublikasikan.</p>
            </div>

            <div class="card-error-state" data-research-state="error" style="display: none;">
                <p class="card-error-state__message" data-research-error-message>Gagal memuat katalog riset.</p>
                <button type="button" class="button btn-retry" data-research-retry>Coba Lagi</button>
            </div>

            <noscript>
                <div class="card-empty-state">
                    <p>Aktifkan JavaScript untuk melihat katalog riset secara dinamis.</p>
                </div>
            </noscript>
        </div>
    </div>
@endsection
