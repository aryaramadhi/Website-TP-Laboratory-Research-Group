@extends('layouts.app')

@section('title', 'Publikasi | TP Laboratory')
@section('description', 'Daftar publikasi ilmiah TP Laboratory Research Group di bidang energi terbarukan, Building-Integrated Photovoltaics (BIPV), dan teknologi panel surya.')
@section('page', 'publication')

@php
    $publicationsByYear = collect([
        2026 => collect([
            (object) ['id' => 1, 'title' => 'Design Optimization and Energy Potential of BIPV Facades in Tropical Climate Regimes', 'author' => 'Dr.Eng. Tika Erna Putri, S.Si., M.Sc., Budi Santoso', 'journal' => 'IEEE International Conference on Energy Conversion and Applied Technology', 'year' => 2026, 'link' => '#', 'doi' => null, 'description' => 'Publikasi mengenai optimasi desain dan potensi energi fasad BIPV pada iklim tropis.'],
            (object) ['id' => 2, 'title' => 'Building Integrated Photovoltaic (BIPV): Simulation and Performance Analysis on Teaching Industry Learning Center Building', 'author' => 'Dr.Eng. Tika Erna Putri, S.Si., M.Sc., Ahmad Fauzi, Nurul Hidayah', 'journal' => 'International Journal of Renewable Energy Development (IJRED)', 'year' => 2026, 'link' => '#', 'doi' => null, 'description' => 'Analisis simulasi dan performa sistem BIPV pada Teaching Industry Learning Center.'],
        ]),
        2025 => collect([
            (object) ['id' => 3, 'title' => 'Comparative Lifecycle Cost and Payback Period Analysis of Rooftop and Facade PV Systems', 'author' => 'Dr.Eng. Tika Erna Putri, S.Si., M.Sc., Hendra Wijaya', 'journal' => 'Renewable and Sustainable Energy Reviews', 'year' => 2025, 'link' => '#', 'doi' => null, 'description' => 'Perbandingan biaya siklus hidup dan periode pengembalian sistem PV.'],
            (object) ['id' => 4, 'title' => 'Thermal-Electrical Modeling of Building-Integrated Photovoltaic Systems under High Solar Irradiance', 'author' => 'Dr.Eng. Tika Erna Putri, S.Si., M.Sc., Siti Rahmawati', 'journal' => 'Solar Energy Materials and Solar Cells', 'year' => 2025, 'link' => '#', 'doi' => null, 'description' => 'Pemodelan termal-elektrik sistem BIPV pada iradiasi matahari tinggi.'],
        ]),
        2024 => collect([
            (object) ['id' => 5, 'title' => 'Experimental Investigation of Dust Accumulation and Mitigation Strategies on Colored Glass BIPV', 'author' => 'Dr.Eng. Tika Erna Putri, S.Si., M.Sc., Rian Ardiansyah', 'journal' => 'Applied Energy', 'year' => 2024, 'link' => '#', 'doi' => null, 'description' => 'Investigasi eksperimental akumulasi debu dan strategi mitigasi pada BIPV kaca berwarna.'],
        ]),
    ]);
@endphp

@section('content')
    <x-hero title="Publikasi" image="images/publication/hero-bg.jpg" />

    <div class="container publication-page">
        <div class="publication-page__wrapper">
            <div class="admin-action-bar">
                <x-admin-add-button modal="publication-modal">Tambah Publikasi</x-admin-add-button>
            </div>
            <x-publication-accordion :publications-by-year="$publicationsByYear" />
        </div>
    </div>
@endsection
