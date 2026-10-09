@extends('layouts.app')

@section('title', 'Katalog Riset | TP Laboratory')
@section('description', 'Katalog Riset TP Laboratory Research Group di bidang energi terbarukan, Building-Integrated Photovoltaics (BIPV), dan teknologi panel surya.')
@section('page', 'research')

@php
    $researches = [
        (object) [
            'id' => 1,
            'title' => 'Building Integrated Photovoltaic (BPIV): Menyatukan estetika bangunan dan sinar surya – Inovasi mandiri energi hemat lahan',
            'main_image' => 'images/research/research-1.jpg',
            'status' => 'Selesai',
            'excerpt' => 'BIPV (Building-Integrated Photovoltaics) adalah teknologi panel surya yang terintegrasi langsung dengan elemen bangunan seperti atap, kaca, atau façade. Teknologi ini menghasilkan listrik sekaligus berfungsi sebagai bagian dari bangunan.',
        ],
        (object) [
            'id' => 2,
            'title' => 'Building Integrated Photovoltaic (BPIV): Menyatukan estetika bangunan dan sinar surya – Inovasi mandiri energi hemat lahan',
            'main_image' => 'images/research/research-1.jpg',
            'status' => 'Sedang Berjalan',
            'excerpt' => 'BIPV (Building-Integrated Photovoltaics) adalah teknologi panel surya yang terintegrasi langsung dengan elemen bangunan seperti atap, kaca, atau façade. Teknologi ini menghasilkan listrik sekaligus berfungsi sebagai bagian dari bangunan.',
        ],
        (object) [
            'id' => 3,
            'title' => 'Building Integrated Photovoltaic (BPIV): Menyatukan estetika bangunan dan sinar surya – Inovasi mandiri energi hemat lahan',
            'main_image' => 'images/research/research-1.jpg',
            'status' => 'Sedang Berjalan',
            'excerpt' => 'BIPV (Building-Integrated Photovoltaics) adalah teknologi panel surya yang terintegrasi langsung dengan elemen bangunan seperti atap, kaca, atau façade. Teknologi ini menghasilkan listrik sekaligus berfungsi sebagai bagian dari bangunan.',
        ],
    ];
@endphp

@section('content')
    <x-hero title="Katalog Riset" image="images/research/hero-bg.jpg" />

    <div class="container research-catalog">
        <div class="admin-action-bar">
            <x-admin-add-button modal="research-modal">Tambah Riset</x-admin-add-button>
        </div>

        <div class="research-catalog__list">
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
    </div>
@endsection
