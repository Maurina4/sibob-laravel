<?php

use App\Http\Controllers\Admin\PengajuanPengembalianController as AdminPengajuanPengembalianController;
use App\Http\Controllers\PengajuanPengembalianController;
use Illuminate\Support\Facades\Route;

if (config('fitur.pengajuan_pengembalian', true)) {
    // 1. Rute Pegawai / User
    Route::prefix('pengajuan-pengembalian')->name('pengajuan-pengembalian.')->group(function () {
        Route::get('/api/search-bmn', [PengajuanPengembalianController::class, 'apiSearchBmn'])->name('api-search-bmn');
        Route::get('/', [PengajuanPengembalianController::class, 'index'])->name('index');
        Route::get('/create', [PengajuanPengembalianController::class, 'create'])->name('create');
        Route::post('/', [PengajuanPengembalianController::class, 'store'])->name('store');
        Route::get('/{pengajuanPengembalian}', [PengajuanPengembalianController::class, 'show'])->name('show');
        Route::post('/{pengajuanPengembalian}/batalkan', [PengajuanPengembalianController::class, 'batalkan'])->name('batalkan');
        Route::get('/{pengajuanPengembalian}/cetak-ba', [PengajuanPengembalianController::class, 'cetakBa'])->name('cetak-ba');
    });

    // 2. Rute Admin / Pengelola BMN (Khusus Operator)
    Route::middleware(['role:Operator'])->prefix('admin/pengajuan-pengembalian')->name('admin.pengajuan-pengembalian.')->group(function () {
        Route::get('/', [AdminPengajuanPengembalianController::class, 'index'])->name('index');
        Route::get('/{pengajuanPengembalian}', [AdminPengajuanPengembalianController::class, 'show'])->name('show');
        Route::post('/{pengajuanPengembalian}/setujui', [AdminPengajuanPengembalianController::class, 'setujui'])->name('setujui');
        Route::post('/{pengajuanPengembalian}/tolak', [AdminPengajuanPengembalianController::class, 'tolak'])->name('tolak');
        Route::get('/{pengajuanPengembalian}/cetak-ba', [AdminPengajuanPengembalianController::class, 'cetakBa'])->name('cetak-ba');
    });
}
