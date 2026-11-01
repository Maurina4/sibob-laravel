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
        if (Schema::hasTable('bmns') && ! Schema::hasColumn('bmns', 'is_dikembalikan')) {
            Schema::table('bmns', function (Blueprint $table) {
                $table->boolean('is_dikembalikan')->default(false)->after('kondisi');
            });
        }

        if (Schema::hasTable('bmns_tik') && ! Schema::hasColumn('bmns_tik', 'is_dikembalikan')) {
            Schema::table('bmns_tik', function (Blueprint $table) {
                $table->boolean('is_dikembalikan')->default(false)->after('penanggungjawab');
            });
        }

        if (Schema::hasTable('bmns_lab') && ! Schema::hasColumn('bmns_lab', 'is_dikembalikan')) {
            Schema::table('bmns_lab', function (Blueprint $table) {
                $table->boolean('is_dikembalikan')->default(false)->after('kondisi');
            });
        }

        // Backfill: Set is_dikembalikan = true untuk barang yang penanggung jawabnya Pengelola BMN
        // atau tercatat di pengembalian_items
        if (Schema::hasTable('bmns') && Schema::hasColumn('bmns', 'is_dikembalikan')) {
            DB::table('bmns')
                ->where('penanggungjawab', 'Pengelola BMN')
                ->update(['is_dikembalikan' => true]);

            if (Schema::hasTable('pengembalian_items') && Schema::hasColumn('pengembalian_items', 'bmn_id')) {
                $returnedIds = DB::table('pengembalian_items')->whereNotNull('bmn_id')->pluck('bmn_id')->toArray();
                if (! empty($returnedIds)) {
                    DB::table('bmns')
                        ->whereIn('id', $returnedIds)
                        ->where('penanggungjawab', 'Pengelola BMN')
                        ->update(['is_dikembalikan' => true]);
                }
            }
        }

        if (Schema::hasTable('bmns_tik') && Schema::hasColumn('bmns_tik', 'is_dikembalikan')) {
            DB::table('bmns_tik')
                ->where('penanggungjawab', 'Pengelola BMN')
                ->update(['is_dikembalikan' => true]);
        }

        if (Schema::hasTable('bmns_lab') && Schema::hasColumn('bmns_lab', 'is_dikembalikan')) {
            DB::table('bmns_lab')
                ->where('penanggungjawab', 'Pengelola BMN')
                ->update(['is_dikembalikan' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bmns') && Schema::hasColumn('bmns', 'is_dikembalikan')) {
            Schema::table('bmns', function (Blueprint $table) {
                $table->dropColumn('is_dikembalikan');
            });
        }

        if (Schema::hasTable('bmns_tik') && Schema::hasColumn('bmns_tik', 'is_dikembalikan')) {
            Schema::table('bmns_tik', function (Blueprint $table) {
                $table->dropColumn('is_dikembalikan');
            });
        }

        if (Schema::hasTable('bmns_lab') && Schema::hasColumn('bmns_lab', 'is_dikembalikan')) {
            Schema::table('bmns_lab', function (Blueprint $table) {
                $table->dropColumn('is_dikembalikan');
            });
        }
    }
};
