<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Table bmns
        if (Schema::hasTable('bmns')) {
            Schema::table('bmns', function (Blueprint $table) {
                if (! Schema::hasColumn('bmns', 'tahun_perolehan')) {
                    $table->integer('tahun_perolehan')->nullable()->after('merek');
                }
                if (! Schema::hasColumn('bmns', 'nilai_perolehan')) {
                    $table->decimal('nilai_perolehan', 15, 2)->nullable()->after('tahun_perolehan');
                }
            });
        }

        // 2. Table bmns_tik
        if (Schema::hasTable('bmns_tik')) {
            Schema::table('bmns_tik', function (Blueprint $table) {
                if (! Schema::hasColumn('bmns_tik', 'tahun_perolehan')) {
                    $table->integer('tahun_perolehan')->nullable()->after('nama_barang');
                }
                if (! Schema::hasColumn('bmns_tik', 'nilai_perolehan')) {
                    $table->decimal('nilai_perolehan', 15, 2)->nullable()->after('tahun_perolehan');
                }
            });
        }

        // 3. Table bmns_lab
        if (Schema::hasTable('bmns_lab')) {
            Schema::table('bmns_lab', function (Blueprint $table) {
                if (! Schema::hasColumn('bmns_lab', 'tahun_perolehan')) {
                    $table->integer('tahun_perolehan')->nullable()->after('merek');
                }
                if (! Schema::hasColumn('bmns_lab', 'nilai_perolehan')) {
                    $table->decimal('nilai_perolehan', 15, 2)->nullable()->after('tahun_perolehan');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bmns_lab')) {
            Schema::table('bmns_lab', function (Blueprint $table) {
                if (Schema::hasColumn('bmns_lab', 'nilai_perolehan')) {
                    $table->dropColumn('nilai_perolehan');
                }
                if (Schema::hasColumn('bmns_lab', 'tahun_perolehan')) {
                    $table->dropColumn('tahun_perolehan');
                }
            });
        }

        if (Schema::hasTable('bmns_tik')) {
            Schema::table('bmns_tik', function (Blueprint $table) {
                if (Schema::hasColumn('bmns_tik', 'nilai_perolehan')) {
                    $table->dropColumn('nilai_perolehan');
                }
                if (Schema::hasColumn('bmns_tik', 'tahun_perolehan')) {
                    $table->dropColumn('tahun_perolehan');
                }
            });
        }

        if (Schema::hasTable('bmns')) {
            Schema::table('bmns', function (Blueprint $table) {
                if (Schema::hasColumn('bmns', 'nilai_perolehan')) {
                    $table->dropColumn('nilai_perolehan');
                }
                if (Schema::hasColumn('bmns', 'tahun_perolehan')) {
                    $table->dropColumn('tahun_perolehan');
                }
            });
        }
    }
};
