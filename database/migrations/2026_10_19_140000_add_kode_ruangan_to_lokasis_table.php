<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lokasis', function (Blueprint $table) {
            if (! Schema::hasColumn('lokasis', 'kode_ruangan')) {
                $table->string('kode_ruangan', 50)->nullable()->after('id');
            }
        });

        // Set default kode ruangan untuk master lokasi yang sudah ada
        $defaults = [
            'Banjarbaru' => 'BJB',
            'BPOM' => 'BPOM',
            'BJM' => 'BJM',
            'Ruang Tata Usaha' => 'R.TU',
            'Ruang Kepala Balai' => 'R.KB',
            'Laboratorium Pengujian Kimia' => 'LAB.KIM',
            'Laboratorium Pengujian Mikrobiologi' => 'LAB.MIK',
            'Laboratorium Kosmetik & Obat Bahan Alam' => 'LAB.KOBA',
            'Ruang Server & TIK' => 'R.TIK',
            'Gudang BMN Banjarbaru' => 'GDG.BJB',
            'Ruang Rapat Pimpinan' => 'R.RAPIM',
            'Ruang Pelayanan Publik / Sertifikasi' => 'R.YANLUK',
            'Ruang Pengarsipan Dokumen' => 'R.ARSIP',
            'Gudang BMN' => 'GDG.BMN',
        ];

        foreach ($defaults as $nama => $kode) {
            DB::table('lokasis')
                ->where('nama', $nama)
                ->whereNull('kode_ruangan')
                ->update(['kode_ruangan' => $kode]);
        }

        // Untuk lokasi lain yang belum memiliki kode, generate kode sederhana
        $remaining = DB::table('lokasis')->whereNull('kode_ruangan')->get();
        foreach ($remaining as $lok) {
            DB::table('lokasis')
                ->where('id', $lok->id)
                ->update(['kode_ruangan' => 'R.'.str_pad((string) $lok->id, 2, '0', STR_PAD_LEFT)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lokasis', function (Blueprint $table) {
            if (Schema::hasColumn('lokasis', 'kode_ruangan')) {
                $table->dropColumn('kode_ruangan');
            }
        });
    }
};
