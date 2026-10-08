@extends('layouts.app')

@section('title', 'Prestasi & Pencapaian | TP Laboratory')
@section('description', 'Prestasi, penghargaan, dan pencapaian TP Laboratory Research Group di bidang energi terbarukan dan Building-Integrated Photovoltaics.')
@section('page', 'achievement')

@php
    $achievements = [
        (object) ['id' => 1, 'title' => 'Building Integrated Photovoltaic (BPIV): Menyatukan estetika bangunan dan sinar surya – Inovasi mandiri energi hemat lahan', 'image_url' => 'images/achievements/achievement-1.jpg', 'date' => '17 SEPTEMBER 2026', 'excerpt' => 'Penghargaan riset inovasi energi bersih terbaik tingkat nasional untuk perancangan fasad cerdas mandiri energi.'],
        (object) ['id' => 2, 'title' => 'Building Integrated Photovoltaic (BPIV): Menyatukan estetika bangunan dan sinar surya – Inovasi mandiri energi hemat lahan', 'image_url' => 'images/achievements/achievement-2.jpg', 'date' => '20 AUGUST 2026', 'excerpt' => 'Hibah kompetitif penelitian terapan energi terbarukan dari Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi.'],
        (object) ['id' => 3, 'title' => 'Best Paper Award pada International Conference', 'image_url' => 'images/achievements/achievement-3.jpg', 'date' => '25 JULY 2026', 'excerpt' => 'Pengakuan ilmiah internasional atas publikasi hasil simulasi komprehensif sistem BIPV TILC TP Laboratory.'],
    ];
@endphp

@section('content')
    <x-hero title="Prestasi &amp; Pencapaian" image="images/achievements/hero-bg.jpg" />

    <div class="container achievement-catalog">
        <div class="admin-action-bar">
            <x-admin-add-button modal="achievement-modal">Tambah Prestasi &amp; Pencapaian</x-admin-add-button>
        </div>

        <div class="achievement-catalog__grid">
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
    </div>
@endsection
