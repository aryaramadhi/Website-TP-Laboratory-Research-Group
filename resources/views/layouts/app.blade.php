<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description', 'TP Laboratory Research Group adalah kelompok riset terapan di bidang energi terbarukan yang berfokus pada teknologi Building-Integrated Photovoltaics (BIPV).')">
    <title>@yield('title', 'TP Laboratory')</title>

    <meta property="og:title" content="@yield('title', 'TP Laboratory')">
    <meta property="og:description" content="@yield('description', 'TP Laboratory Research Group adalah kelompok riset terapan di bidang energi terbarukan yang berfokus pada teknologi Building-Integrated Photovoltaics (BIPV).')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-page="@yield('page')">
    <x-navbar />

    <main class="main">
        @yield('content')
    </main>

    <x-footer />

    <x-login-modal />
    <x-register-modal />
    <x-research-form id="research-modal" />
    <x-research-form id="edit-research-modal" :isEdit="true" />
    <x-publication-form id="publication-modal" />
    <x-publication-form id="edit-publication-modal" :isEdit="true" />
    <x-news-form id="news-modal" />
    <x-news-form id="edit-news-modal" :isEdit="true" />
    <x-achievement-form id="achievement-modal" />
    <x-achievement-form id="edit-achievement-modal" :isEdit="true" />
    <x-delete-confirm-modal id="delete-confirm-modal" />

    @stack('modals')
</body>
</html>
