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
        Schema::table('pengembalian_items', function (Blueprint $table) {
            if (! Schema::hasColumn('pengembalian_items', 'kondisi_awal')) {
                $table->string('kondisi_awal')->nullable()->after('nama_barang_snapshot');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengembalian_items', function (Blueprint $table) {
            if (Schema::hasColumn('pengembalian_items', 'kondisi_awal')) {
                $table->dropColumn('kondisi_awal');
            }
        });
    }
};
