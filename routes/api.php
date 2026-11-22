<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProyekRisetController;
use App\Http\Controllers\PublikasiHibahController;
use App\Http\Controllers\BeritaIsuController;
use App\Http\Controllers\PrestasiPencapaianController;

// Rute Publik
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::get('/proyek-riset', [ProyekRisetController::class, 'index']);
Route::get('/proyek-riset/{id}', [ProyekRisetController::class, 'show']);

Route::get('/publikasi-hibah', [PublikasiHibahController::class, 'index']);
Route::get('/publikasi-hibah/{id}', [PublikasiHibahController::class, 'show']);

Route::get('/berita-isu', [BeritaIsuController::class, 'index']);
Route::get('/berita-isu/{id}', [BeritaIsuController::class, 'show']);

Route::get('/prestasi-pencapaian', [PrestasiPencapaianController::class, 'index']);
Route::get('/prestasi-pencapaian/{id}', [PrestasiPencapaianController::class, 'show']);

// Rute Terlindungi (Admin Authentication via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('admins', AdminController::class);

    // Proyek Riset
    Route::post('/proyek-riset', [ProyekRisetController::class, 'store']);
    Route::match(['put', 'post'], '/proyek-riset/{id}', [ProyekRisetController::class, 'update']);
    Route::delete('/proyek-riset/{id}', [ProyekRisetController::class, 'destroy']);

    // Publikasi Hibah
    Route::post('/publikasi-hibah', [PublikasiHibahController::class, 'store']);
    Route::match(['put', 'post'], '/publikasi-hibah/{id}', [PublikasiHibahController::class, 'update']);
    Route::delete('/publikasi-hibah/{id}', [PublikasiHibahController::class, 'destroy']);

    // Berita Isu
    Route::post('/berita-isu', [BeritaIsuController::class, 'store']);
    Route::match(['put', 'post'], '/berita-isu/{id}', [BeritaIsuController::class, 'update']);
    Route::delete('/berita-isu/{id}', [BeritaIsuController::class, 'destroy']);

    // Prestasi Pencapaian
    Route::post('/prestasi-pencapaian', [PrestasiPencapaianController::class, 'store']);
    Route::match(['put', 'post'], '/prestasi-pencapaian/{id}', [PrestasiPencapaianController::class, 'update']);
    Route::delete('/prestasi-pencapaian/{id}', [PrestasiPencapaianController::class, 'destroy']);
});
