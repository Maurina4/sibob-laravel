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
        if (Schema::hasTable('data_terhapus') && ! Schema::hasColumn('data_terhapus', 'bmn_id')) {
            Schema::table('data_terhapus', function (Blueprint $table) {
                $table->foreignId('bmn_id')->nullable()->after('data_id')->constrained('bmns')->nullOnDelete();
            });
        }

        // Sinkronisasi dan perbaiki kategori data terhapus lama
        if (Schema::hasTable('data_terhapus') && Schema::hasTable('bmns')) {
            $items = DB::table('data_terhapus')->get();
            foreach ($items as $item) {
                if ($item->jenis === 'BMN') {
                    $bmn = DB::table('bmns')->where('id', $item->data_id)->first();
                    if ($bmn) {
                        $newJenis = 'BMN';
                        if ($bmn->jenis === 'BMN-TIK') {
                            $newJenis = 'BMN TIK';
                        } elseif ($bmn->jenis === 'BMN-LAB') {
                            $newJenis = 'BMN Lab';
                        }

                        DB::table('data_terhapus')->where('id', $item->id)->update([
                            'bmn_id' => $bmn->id,
                            'jenis' => $newJenis,
                        ]);
                    }
                } elseif ($item->jenis === 'BMN TIK') {
                    $tik = DB::table('bmns_tik')->where('id', $item->data_id)->first();
                    if ($tik && $tik->bmn_id) {
                        DB::table('data_terhapus')->where('id', $item->id)->update([
                            'bmn_id' => $tik->bmn_id,
                        ]);
                    }
                } elseif ($item->jenis === 'BMN Lab') {
                    $lab = DB::table('bmns_lab')->where('id', $item->data_id)->first();
                    if ($lab && $lab->bmn_id) {
                        DB::table('data_terhapus')->where('id', $item->id)->update([
                            'bmn_id' => $lab->bmn_id,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('data_terhapus') && Schema::hasColumn('data_terhapus', 'bmn_id')) {
            Schema::table('data_terhapus', function (Blueprint $table) {
                $table->dropForeign(['bmn_id']);
                $table->dropColumn('bmn_id');
            });
        }
    }
};
