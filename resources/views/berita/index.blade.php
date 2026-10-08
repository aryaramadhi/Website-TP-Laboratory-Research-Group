@extends('layouts.app')

@section('title', 'Berita | TP Laboratory')
@section('description', 'Berita dan artikel terkini seputar riset Building-Integrated Photovoltaics (BIPV) dan energi terbarukan oleh TP Laboratory.')
@section('page', 'news')

@php
    $news = [
        (object) ['id' => 1, 'title' => 'Building Integrated Photovoltaic (BPIV): Menyatukan estetika bangunan dan sinar surya – Inovasi mandiri energi hemat lahan', 'main_image' => 'images/news/news-1.jpg', 'excerpt' => 'BIPV (Building-Integrated Photovoltaics) adalah teknologi panel surya yang terintegrasi langsung dengan elemen bangunan.'],
        (object) ['id' => 2, 'title' => 'Building Integrated Photovoltaic (BPIV): Menyatukan estetika bangunan dan sinar surya – Inovasi mandiri energi hemat lahan', 'main_image' => 'images/news/news-2.jpg', 'excerpt' => 'BIPV (Building-Integrated Photovoltaics) adalah teknologi panel surya yang terintegrasi langsung dengan elemen bangunan.'],
        (object) ['id' => 3, 'title' => 'Workshop Nasional Integrasi Panel Surya Gedung dan Standarisasi Green Building Indonesia', 'main_image' => 'images/news/news-3.jpg', 'excerpt' => 'TP Laboratory menyelenggarakan workshop teknis bersama praktisi arsitektur dan pemangku kebijakan energi terbarukan membahas percepatan adopsi BIPV.'],
    ];
@endphp

@section('content')
    <x-hero title="Berita" image="images/news/hero-bg.jpg" />

    <div class="container news-page">
        <div class="admin-action-bar">
            <x-admin-add-button modal="news-modal">Tambah Berita</x-admin-add-button>
        </div>

        <div class="news-page__list">
            @foreach ($news as $item)
                <x-news-card
                    :id="$item->id"
                    :image="$item->main_image"
                    :title="$item->title"
                    :excerpt="$item->excerpt"
                    :href="url('/berita/' . $item->id)"
                    :reverse="$loop->odd"
                    action-text="Lihat Detail Berita"
                />
            @endforeach
        </div>
    </div>
@endsection
