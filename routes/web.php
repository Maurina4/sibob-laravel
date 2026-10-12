<?php

use App\Http\Controllers\BaPerpindahanController;
use App\Http\Controllers\BmnController;
use App\Http\Controllers\BmnLabController;
use App\Http\Controllers\BmnTikController;
use App\Http\Controllers\DataTerhapusController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistribusiController;
use App\Http\Controllers\LaporanKerusakanController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\PengembalianController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| HALAMAN UTAMA
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});


/*
|--------------------------------------------------------------------------
| SEMUA ROUTE YANG MEMERLUKAN LOGIN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    |
    | Operator dan Pegawai/User sama-sama dapat membuka Beranda.
    |
    */

    Route::get(
        '/dashboard',
        [
            DashboardController::class,
            'index',
        ]
    )->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | MODUL OPERATOR
    |--------------------------------------------------------------------------
    |
    | Modul berikut hanya dapat diakses oleh Operator.
    |
    */

    Route::middleware(['role:Operator'])->group(function () {

        /*
        |--------------------------------------------------------------------------
        | DAFTAR BMN
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'bmns',
            BmnController::class
        );


        /*
        |--------------------------------------------------------------------------
        | UPDATE LOKASI BMN
        |--------------------------------------------------------------------------
        */

        Route::patch(
            '/bmns/{bmn}/lokasi',
            [
                BmnController::class,
                'updateLokasi',
            ]
        )->name('bmns.update-lokasi');


        /*
        |--------------------------------------------------------------------------
        | BMN TIK
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'bmns-tik',
            BmnTikController::class
        )->parameters([
            'bmns-tik' => 'bmnTik',
        ]);


        /*
        |--------------------------------------------------------------------------
        | BMN LAB
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'bmns-lab',
            BmnLabController::class
        )->parameters([
            'bmns-lab' => 'bmnLab',
        ]);


        /*
        |--------------------------------------------------------------------------
        | PENGEMBALIAN BMN
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/pengembalians/{pengembalian}/print',
            [
                PengembalianController::class,
                'print',
            ]
        )->name('pengembalians.print');


        Route::resource(
            'pengembalians',
            PengembalianController::class
        );


        /*
        |--------------------------------------------------------------------------
        | DISTRIBUSI BMN
        |--------------------------------------------------------------------------
        |
        | Controller:
        | App\Http\Controllers\DistribusiController
        |
        | Nama route:
        | distribusi.index
        | distribusi.create
        | distribusi.store
        | distribusi.show
        | distribusi.edit
        | distribusi.update
        | distribusi.destroy
        |
        */

        Route::resource(
            'distribusi',
            DistribusiController::class
        );


        /*
        |--------------------------------------------------------------------------
        | LOKASI
        |--------------------------------------------------------------------------
        |
        | Controller:
        | App\Http\Controllers\LokasiController
        |
        */

        Route::resource(
            'lokasis',
            LokasiController::class
        );


        /*
        |--------------------------------------------------------------------------
        | DATA TERHAPUS
        |--------------------------------------------------------------------------
        |
        | Data Terhapus hanya mempunyai:
        |
        | GET  /data-terhapus
        | POST /data-terhapus/{dataTerhapus}/restore
        |
        */

        Route::get(
            '/data-terhapus',
            [
                DataTerhapusController::class,
                'index',
            ]
        )->name('data-terhapus.index');


        Route::post(
            '/data-terhapus/{dataTerhapus}/restore',
            [
                DataTerhapusController::class,
                'restore',
            ]
        )->name('data-terhapus.restore');

    });


    /*
    |--------------------------------------------------------------------------
    | BA PERPINDAHAN
    |--------------------------------------------------------------------------
    |
    | Operator dan Pegawai/User dapat mengakses BA Perpindahan.
    |
    */

    Route::get(
        '/ba-perpindahan/{baPerpindahan}/print',
        [
            BaPerpindahanController::class,
            'print',
        ]
    )->name('ba-perpindahan.print');


    /*
    |--------------------------------------------------------------------------
    | APPROVE BA PERPINDAHAN
    |--------------------------------------------------------------------------
    |
    | Hanya Operator yang boleh menyetujui.
    |
    */

    Route::patch(
        '/ba-perpindahan/{baPerpindahan}/approve',
        [
            BaPerpindahanController::class,
            'approve',
        ]
    )
        ->middleware('role:Operator')
        ->name('ba-perpindahan.approve');


    /*
    |--------------------------------------------------------------------------
    | REJECT BA PERPINDAHAN
    |--------------------------------------------------------------------------
    |
    | Hanya Operator yang boleh menolak.
    |
    */

    Route::patch(
        '/ba-perpindahan/{baPerpindahan}/reject',
        [
            BaPerpindahanController::class,
            'reject',
        ]
    )
        ->middleware('role:Operator')
        ->name('ba-perpindahan.reject');


    /*
    |--------------------------------------------------------------------------
    | RESOURCE BA PERPINDAHAN
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'ba-perpindahan',
        BaPerpindahanController::class
    )->parameters([
        'ba-perpindahan' => 'baPerpindahan',
    ]);


    /*
    |--------------------------------------------------------------------------
    | LAPORAN KERUSAKAN
    |--------------------------------------------------------------------------
    |
    | Operator dan Pegawai/User dapat membuka modul ini.
    |
    | Controller:
    | LaporanKerusakanController
    |
    */

    Route::resource(
        'laporan-kerusakan',
        LaporanKerusakanController::class
    )->parameters([
        'laporan-kerusakan' => 'laporanKerusakan',
    ]);


    /*
    |--------------------------------------------------------------------------
    | PROFIL PENGGUNA
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});


/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
|
| Login, logout, register, reset password, dan route authentication
| lainnya disediakan oleh Laravel Breeze.
|
*/

require __DIR__ . '/auth.php';