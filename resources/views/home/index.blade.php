@extends('layouts.app')

@section('title', 'TP Laboratory')
@section('description', 'TP Laboratory Research Group adalah kelompok riset terapan di bidang energi terbarukan yang berfokus pada teknologi Building-Integrated Photovoltaics (BIPV) dan pengembangan panel surya.')
@section('page', 'home')

@php
    $researches = [
        (object) [
            'id' => 1,
            'title' => 'Building Integrated Photovoltaic (BIPV): Menyatukan estetika bangunan dan sinar surya – Inovasi mandiri energi hemat lahan',
            'main_image' => 'images/home/research-1.jpg',
            'status' => 'Sedang Berjalan',
            'excerpt' => 'Teknologi Building-Integrated Photovoltaics (BIPV) semakin menarik perhatian di Indonesia dan global sebagai solusi energi terbarukan yang efisien serta estetis.',
        ],
        (object) [
            'id' => 2,
            'title' => 'Building Integrated Photovoltaic (BPIV): Menyatukan estetika bangunan dan sinar surya – Inovasi mandiri energi hemat lahan',
            'main_image' => 'images/home/research-2.jpg',
            'status' => 'Selesai',
            'excerpt' => 'BIPV (Building-Integrated Photovoltaics) adalah teknologi panel surya yang terintegrasi langsung dengan elemen bangunan seperti atap, kaca, atau façade.',
        ],
    ];

    $news = [
        (object) [
            'id' => 1,
            'title' => 'Building Integrated Photovoltaic (BPIV): Menyatukan estetika bangunan dan sinar surya – Inovasi mandiri energi hemat lahan',
            'main_image' => 'images/home/news-1.jpg',
            'excerpt' => 'BIPV (Building-Integrated Photovoltaics) adalah teknologi panel surya yang terintegrasi langsung dengan elemen bangunan.',
        ],
        (object) [
            'id' => 2,
            'title' => 'Workshop Nasional Integrasi Panel Surya Gedung dan Standarisasi Green Building Indonesia',
            'main_image' => 'images/home/news-2.jpg',
            'excerpt' => 'TP Laboratory menyelenggarakan workshop teknis bersama praktisi arsitektur dan pemangku kebijakan energi terbarukan.',
        ],
    ];

    $achievements = [
        (object) [
            'id' => 1,
            'title' => 'Building Integrated Photovoltaic (BPIV): Menyatukan estetika bangunan dan sinar surya – Inovasi mandiri energi hemat lahan',
            'image_url' => 'images/home/achievement-1.jpg',
            'date' => '17 September 2026',
            'excerpt' => 'Penghargaan riset inovasi energi bersih terbaik tingkat nasional untuk perancangan fasad cerdas mandiri energi.',
        ],
        (object) [
            'id' => 2,
            'title' => 'Building Integrated Photovoltaic (BPIV): Menyatukan estetika bangunan dan sinar surya – Inovasi mandiri energi hemat lahan',
            'image_url' => 'images/home/achievement-2.jpg',
            'date' => '20 August 2026',
            'excerpt' => 'Hibah kompetitif penelitian terapan energi terbarukan dari Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi.',
        ],
    ];
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

    <div class="container home-content">
        <section class="home-section" id="hasil-riset">
            <x-section-title title="HASIL RISET" class="home-section__header" />
            <div class="home-section__list" id="home-research-list">
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
            </div>
            <div class="home-section__action">
                <x-button href="{{ url('/riset') }}" icon="images/icons/chevron-right.png">Lihat Semua Riset</x-button>
            </div>
        </section>

        <section class="home-section" id="berita-terkini">
            <x-section-title title="BERITA TERKINI" class="home-section__header" />
            <div class="home-section__list" id="home-news-list">
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
            </div>
            <div class="home-section__action">
                <x-button href="{{ url('/berita') }}" icon="images/icons/chevron-right.png">Lihat Semua Berita</x-button>
            </div>
        </section>

        <section class="home-section" id="prestasi-pencapaian">
            <x-section-title title="PRESTASI &amp; PENCAPAIAN" class="home-section__header" />
            <div class="home-achievements__grid" id="home-achievement-list">
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
            </div>
            <div class="home-section__action">
                <x-button href="{{ url('/prestasi') }}" icon="images/icons/chevron-right.png">Lihat Semua Prestasi &amp; Pencapaian</x-button>
            </div>
        </section>
    </div>
@endsection
