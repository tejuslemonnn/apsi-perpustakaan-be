<?php

use App\Http\Controllers\Api\AnggotaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BukuController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DendaController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\PeminjamanController;
use App\Http\Controllers\Api\PengembalianController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public auth routes (no auth middleware)
Route::post('/login', [AuthController::class, 'login']);

// Protected auth routes (require web guard session)
Route::middleware(['auth:web'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/my-fines', [DendaController::class, 'myFines']);
    Route::post('/denda/{denda}/confirm-pay', [DendaController::class, 'confirmPay']);
});

// Read buku: admin + anggota
Route::middleware(['auth:web', 'role:admin,anggota'])->group(function () {
    Route::get('buku',        [BukuController::class, 'index']);
    Route::get('buku/{buku}', [BukuController::class, 'show']);
});

// Read kategori: admin + anggota
Route::middleware(['auth:web', 'role:admin,anggota'])->group(function () {
    Route::get('kategori',           [KategoriController::class, 'index']);
    Route::get('kategori/{kategori}', [KategoriController::class, 'show']);
});

// Admin-only resource routes
Route::middleware(['auth:web', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index']);

    Route::post  ('buku',                [BukuController::class, 'store']);
    Route::put   ('buku/{buku}',         [BukuController::class, 'update']);
    Route::patch ('buku/{buku}',         [BukuController::class, 'update']);
    Route::delete('buku/{buku}',         [BukuController::class, 'destroy']);

    Route::post  ('kategori',              [KategoriController::class, 'store']);
    Route::put   ('kategori/{kategori}',   [KategoriController::class, 'update']);
    Route::delete('kategori/{kategori}',   [KategoriController::class, 'destroy']);

    Route::apiResource('anggota', AnggotaController::class);
    Route::get('/admin/fines', [DendaController::class, 'adminFines']);
    Route::post('/denda/{denda}/verify-pay', [DendaController::class, 'verifyPay']);
    Route::get('/admin/loans', [PengembalianController::class, 'adminLoans']);
    Route::post('/peminjaman/{peminjaman}/process-return', [PengembalianController::class, 'processReturn']);
});

// Member-only routes
Route::middleware(['auth:web', 'role:anggota'])->group(function () {
    Route::get('/peminjaman/my', [PeminjamanController::class, 'myLoans']);
    Route::post('/peminjaman', [PeminjamanController::class, 'store']);
    Route::get('/peminjaman/{peminjaman}', [PeminjamanController::class, 'show']);
    Route::post('/peminjaman/{peminjaman}/request-return', [PeminjamanController::class, 'requestReturn']);
});
