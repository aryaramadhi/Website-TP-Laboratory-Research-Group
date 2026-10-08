<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home.index');
})->name('home');

Route::get('/riset', function () {
    return view('riset.index');
})->name('riset.index');

Route::get('/riset/{id}', function ($id) {
    return view('riset.show', [
        'research' => null,
    ]);
})->name('riset.show');

Route::get('/publikasi', function () {
    return view('publikasi.index');
})->name('publikasi.index');

Route::get('/berita', function () {
    return view('berita.index');
})->name('berita.index');

Route::get('/berita/{id}', function ($id) {
    return view('berita.show', [
        'news' => null,
    ]);
})->name('berita.show');

Route::get('/prestasi', function () {
    return view('prestasi.index');
})->name('prestasi.index');

Route::get('/prestasi/{id}', function ($id) {
    return view('prestasi.show', [
        'achievement' => null,
    ]);
})->name('prestasi.show');