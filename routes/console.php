<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bmn:reset-data', function () {
    $tablesToTruncate = [
        'bmn_dokumens',
        'bmn_lab_details',
        'bmns_tik',
        'bmns_lab',
        'bmns_angkutan',
        'ba_perpindahan_items',
        'ba_perpindahans',
        'distribusi_items',
        'distribusis',
        'pengajuan_pengembalian_items',
        'pengajuan_pengembalians',
        'pengembalian_items',
        'pengembalians',
        'laporan_kerusakans',
        'data_terhapus',
        'import_logs',
        'penanggung_jawabs',
        'bmns',
    ];

    $this->info('Menghapus seluruh data aset BMN dan transaksi...');

    Schema::disableForeignKeyConstraints();
    foreach ($tablesToTruncate as $table) {
        if (Schema::hasTable($table)) {
            DB::table($table)->truncate();
            $this->line("Truncated: {$table}");
        }
    }
    Schema::enableForeignKeyConstraints();

    $this->info('Selesai! Master Lokasi (' . DB::table('lokasis')->count() . ' lokasi), Users (' . DB::table('users')->count() . ' users), dan Pegawai (' . DB::table('pegawais')->count() . ' pegawai) tetap tersimpan aman.');
})->purpose('Reset semua data aset BMN dan transaksi, mempertahankan Lokasi, User, dan Pegawai');

