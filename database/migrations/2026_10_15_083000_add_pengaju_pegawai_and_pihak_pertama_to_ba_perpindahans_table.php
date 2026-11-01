<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ba_perpindahans', function (Blueprint $table) {
            if (! Schema::hasColumn('ba_perpindahans', 'pengaju_pegawai_id')) {
                $table->foreignId('pengaju_pegawai_id')->nullable()->after('pengaju_id')->constrained('pegawais')->nullOnDelete();
            }
            if (! Schema::hasColumn('ba_perpindahans', 'pihak_pertama')) {
                $table->string('pihak_pertama')->nullable()->after('alasan');
            }
            if (! Schema::hasColumn('ba_perpindahans', 'pihak_pertama_id')) {
                $table->foreignId('pihak_pertama_id')->nullable()->after('pihak_pertama')->constrained('pegawais')->nullOnDelete();
            }
        });

        // Isi data awal untuk konsistensi data yang telah ada
        DB::table('ba_perpindahans')
            ->whereNull('pihak_pertama')
            ->whereNotNull('pengaju_nama')
            ->update([
                'pihak_pertama' => DB::raw('pengaju_nama'),
            ]);

        DB::table('ba_perpindahans')
            ->whereNull('pihak_pertama_id')
            ->whereNotNull('pegawai_id')
            ->update([
                'pihak_pertama_id' => DB::raw('pegawai_id'),
            ]);

        DB::table('ba_perpindahans')
            ->whereNull('pengaju_pegawai_id')
            ->whereNotNull('pegawai_id')
            ->update([
                'pengaju_pegawai_id' => DB::raw('pegawai_id'),
            ]);
    }

    public function down(): void
    {
        Schema::table('ba_perpindahans', function (Blueprint $table) {
            if (Schema::hasColumn('ba_perpindahans', 'pengaju_pegawai_id')) {
                $table->dropForeign(['pengaju_pegawai_id']);
                $table->dropColumn('pengaju_pegawai_id');
            }
            if (Schema::hasColumn('ba_perpindahans', 'pihak_pertama_id')) {
                $table->dropForeign(['pihak_pertama_id']);
                $table->dropColumn('pihak_pertama_id');
            }
            if (Schema::hasColumn('ba_perpindahans', 'pihak_pertama')) {
                $table->dropColumn('pihak_pertama');
            }
        });
    }
};
