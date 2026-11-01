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
        // 1. Tambah foreign key bmn_id ke bmns_tik
        if (Schema::hasTable('bmns_tik') && ! Schema::hasColumn('bmns_tik', 'bmn_id')) {
            Schema::table('bmns_tik', function (Blueprint $table) {
                $table->foreignId('bmn_id')->nullable()->after('id')->constrained('bmns')->nullOnDelete();
            });
        }

        // 2. Tambah foreign key bmn_id ke bmns_lab
        if (Schema::hasTable('bmns_lab') && ! Schema::hasColumn('bmns_lab', 'bmn_id')) {
            Schema::table('bmns_lab', function (Blueprint $table) {
                $table->foreignId('bmn_id')->nullable()->after('id')->constrained('bmns')->nullOnDelete();
            });
        }

        // 3. Sinkronisasi data lama dari bmns_tik ke bmns (agar muncul di Daftar BMN)
        if (Schema::hasTable('bmns_tik') && Schema::hasTable('bmns')) {
            $tikItems = DB::table('bmns_tik')->get();
            foreach ($tikItems as $tik) {
                $bmnId = $tik->bmn_id;

                if (! $bmnId && ! empty($tik->kode_barang) && ! empty($tik->nup)) {
                    $bmnId = DB::table('bmns')
                        ->where('kode_barang', $tik->kode_barang)
                        ->where('nup', $tik->nup)
                        ->value('id');
                }

                if (! $bmnId) {
                    $kodeBarang = $tik->kode_barang ?: ('TIK-'.$tik->id);
                    $nup = $tik->nup !== null ? $tik->nup : $tik->id;

                    // Pastikan unique kode_barang + nup di bmns
                    while (DB::table('bmns')->where('kode_barang', $kodeBarang)->where('nup', $nup)->exists()) {
                        $nup = (int) $nup + 1000;
                    }

                    $bmnId = DB::table('bmns')->insertGetId([
                        'kode_barang' => $kodeBarang,
                        'nup' => (string) $nup,
                        'nama_barang' => $tik->nama_barang,
                        'jenis' => 'BMN-TIK',
                        'lokasi' => $tik->lokasi ?: 'Ruang TIK',
                        'penanggungjawab' => $tik->penanggungjawab ?: 'Pengelola BMN',
                        'kondisi' => 'Baik',
                        'dihapus' => $tik->dihapus ?: false,
                        'dihapus_pada' => $tik->dihapus_pada,
                        'dihapus_oleh' => $tik->dihapus_oleh,
                        'created_at' => $tik->created_at ?: now(),
                        'updated_at' => $tik->updated_at ?: now(),
                    ]);
                }

                DB::table('bmns_tik')->where('id', $tik->id)->update(['bmn_id' => $bmnId]);
            }
        }

        // 4. Sinkronisasi data lama dari bmns_lab ke bmns (agar muncul di Daftar BMN)
        if (Schema::hasTable('bmns_lab') && Schema::hasTable('bmns')) {
            $labItems = DB::table('bmns_lab')->get();
            foreach ($labItems as $lab) {
                $bmnId = $lab->bmn_id;

                if (! $bmnId && ! empty($lab->kode_barang) && ! empty($lab->nup)) {
                    $bmnId = DB::table('bmns')
                        ->where('kode_barang', $lab->kode_barang)
                        ->where('nup', $lab->nup)
                        ->value('id');
                }

                if (! $bmnId) {
                    $kodeBarang = $lab->kode_barang ?: ('LAB-'.$lab->id);
                    $nup = $lab->nup !== null ? $lab->nup : $lab->id;

                    // Pastikan unique kode_barang + nup di bmns
                    while (DB::table('bmns')->where('kode_barang', $kodeBarang)->where('nup', $nup)->exists()) {
                        $nup = (int) $nup + 2000;
                    }

                    $bmnId = DB::table('bmns')->insertGetId([
                        'kode_barang' => $kodeBarang,
                        'nup' => (string) $nup,
                        'nama_barang' => $lab->nama_barang,
                        'merek' => $lab->merek,
                        'jenis' => 'BMN-LAB',
                        'lokasi' => $lab->lokasi ?: 'Laboratorium',
                        'penanggungjawab' => $lab->penanggungjawab ?: 'Pengelola BMN',
                        'kondisi' => $lab->kondisi ?: 'Baik',
                        'dihapus' => $lab->dihapus ?: false,
                        'dihapus_pada' => $lab->dihapus_pada,
                        'dihapus_oleh' => $lab->dihapus_oleh,
                        'created_at' => $lab->created_at ?: now(),
                        'updated_at' => $lab->updated_at ?: now(),
                    ]);
                }

                DB::table('bmns_lab')->where('id', $lab->id)->update(['bmn_id' => $bmnId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bmns_tik') && Schema::hasColumn('bmns_tik', 'bmn_id')) {
            Schema::table('bmns_tik', function (Blueprint $table) {
                $table->dropForeign(['bmn_id']);
                $table->dropColumn('bmn_id');
            });
        }

        if (Schema::hasTable('bmns_lab') && Schema::hasColumn('bmns_lab', 'bmn_id')) {
            Schema::table('bmns_lab', function (Blueprint $table) {
                $table->dropForeign(['bmn_id']);
                $table->dropColumn('bmn_id');
            });
        }
    }
};
