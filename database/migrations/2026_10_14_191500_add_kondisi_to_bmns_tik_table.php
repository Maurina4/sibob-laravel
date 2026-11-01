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
        if (Schema::hasTable('bmns_tik') && ! Schema::hasColumn('bmns_tik', 'kondisi')) {
            Schema::table('bmns_tik', function (Blueprint $table) {
                $table->string('kondisi')->default('Baik')->after('penanggungjawab');
            });
        }

        // Sinkronisasi data kondisi awal dari master bmns jika ada
        if (Schema::hasTable('bmns_tik') && Schema::hasTable('bmns')) {
            $tiks = DB::table('bmns_tik')->get();
            foreach ($tiks as $tik) {
                $kondisi = 'Baik';
                if (! empty($tik->bmn_id)) {
                    $bmnKondisi = DB::table('bmns')->where('id', $tik->bmn_id)->value('kondisi');
                    if (! empty($bmnKondisi)) {
                        $kondisi = $bmnKondisi;
                    }
                } elseif (! empty($tik->kode_barang) && ! empty($tik->nup)) {
                    $bmnKondisi = DB::table('bmns')
                        ->where('kode_barang', $tik->kode_barang)
                        ->where('nup', $tik->nup)
                        ->value('kondisi');
                    if (! empty($bmnKondisi)) {
                        $kondisi = $bmnKondisi;
                    }
                }

                DB::table('bmns_tik')->where('id', $tik->id)->update(['kondisi' => $kondisi]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bmns_tik') && Schema::hasColumn('bmns_tik', 'kondisi')) {
            Schema::table('bmns_tik', function (Blueprint $table) {
                $table->dropColumn('kondisi');
            });
        }
    }
};
