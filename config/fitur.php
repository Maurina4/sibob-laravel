<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Flag: Pengajuan Pengembalian
    |--------------------------------------------------------------------------
    |
    | Menentukan apakah modul baru Pengajuan Pengembalian (pengembalian biasa
    | dan pelaporan rusak berat oleh pegawai) aktif atau nonaktif.
    |
    */
    'pengajuan_pengembalian' => env('FEATURE_PENGAJUAN_PENGEMBALIAN', true),
];
