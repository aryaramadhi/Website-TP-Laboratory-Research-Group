@extends('layouts.app')

@section('title', 'Prestasi & Pencapaian | TP Laboratory')
@section('description', 'Prestasi, penghargaan, dan pencapaian TP Laboratory Research Group di bidang energi terbarukan dan Building-Integrated Photovoltaics.')
@section('page', 'achievement')

@php
    $hasSsrData = isset($achievements) && is_iterable($achievements) && count($achievements) > 0;
@endphp

@section('content')
    <x-hero title="Prestasi & Pencapaian" image="images/achievements/hero-bg.jpg" />

    <div class="container achievement-page" data-achievement-page>
        <div class="admin-action-bar">
            <x-admin-add-button modal="achievement-modal">
                Tambah Prestasi &amp; Pencapaian
            </x-admin-add-button>
        </div>

        <div class="achievement-grid" data-achievement-grid id="achievement-grid">
            @if ($hasSsrData)
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
                <div class="card-loading-state" data-achievement-state="loading">
                    <div class="loading-spinner" aria-hidden="true"></div>
                    <p>Memuat prestasi &amp; pencapaian...</p>
                </div>

                <div class="card-empty-state" data-achievement-state="empty" style="display: none;">
                    <p>Belum ada prestasi atau pencapaian yang dipublikasikan saat ini.</p>
                </div>

                <div class="card-error-state" data-achievement-state="error" style="display: none;">
                    <p class="card-error-state__message" data-achievement-error-message>Gagal memuat prestasi &amp; pencapaian.</p>
                    <button type="button" class="button btn-retry" data-achievement-retry>Coba Lagi</button>
                </div>

                <noscript>
                    <div class="card-empty-state">
                        <p>Aktifkan JavaScript untuk melihat daftar prestasi &amp; pencapaian.</p>
                    </div>
                </noscript>
            @endif
        </div>
    </div>
@endsection
