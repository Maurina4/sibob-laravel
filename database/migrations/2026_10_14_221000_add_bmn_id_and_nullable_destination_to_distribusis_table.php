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
        if (Schema::hasTable('distribusis')) {
            // 1. Tambah kolom bmn_id jika belum ada
            if (! Schema::hasColumn('distribusis', 'bmn_id')) {
                Schema::table('distribusis', function (Blueprint $table) {
                    $table->foreignId('bmn_id')->nullable()->after('tanggal')->constrained('bmns')->nullOnDelete();
                });
            }

            // 2. Buat kolom lokasi_tujuan dan penerima menjadi nullable
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE `distribusis` MODIFY `lokasi_tujuan` VARCHAR(255) NULL');
                DB::statement('ALTER TABLE `distribusis` MODIFY `penerima` VARCHAR(255) NULL');
            } else {
                Schema::table('distribusis', function (Blueprint $table) {
                    $table->string('lokasi_tujuan')->nullable()->change();
                    $table->string('penerima')->nullable()->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('distribusis') && Schema::hasColumn('distribusis', 'bmn_id')) {
            Schema::table('distribusis', function (Blueprint $table) {
                $table->dropForeign(['bmn_id']);
                $table->dropColumn('bmn_id');
            });
        }
    }
};
