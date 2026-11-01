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
        if (! Schema::hasColumn('laporan_kerusakans', 'pelapor_pegawai_id')) {
            Schema::table('laporan_kerusakans', function (Blueprint $table) {
                $table->foreignId('pelapor_pegawai_id')
                    ->nullable()
                    ->after('pelapor_id')
                    ->constrained('pegawais')
                    ->nullOnDelete();
            });
        }

        // Backfill existing reports if pelapor_nama matches pegawais
        $pegawais = DB::table('pegawais')->get(['id', 'nama']);
        foreach ($pegawais as $pegawai) {
            if (! empty($pegawai->nama)) {
                DB::table('laporan_kerusakans')
                    ->whereNull('pelapor_pegawai_id')
                    ->where('pelapor_nama', $pegawai->nama)
                    ->update(['pelapor_pegawai_id' => $pegawai->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('laporan_kerusakans', 'pelapor_pegawai_id')) {
            Schema::table('laporan_kerusakans', function (Blueprint $table) {
                $table->dropConstrainedForeignId('pelapor_pegawai_id');
            });
        }
    }
};
