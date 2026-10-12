<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom NUP ke tabel BMN Lab.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('bmns_lab', 'nup')) {
            Schema::table('bmns_lab', function (Blueprint $table) {
                $table->unsignedInteger('nup')
                    ->nullable()
                    ->after('kode_barang');
            });
        }
    }

    /**
     * Menghapus kolom NUP jika migration dibatalkan.
     */
    public function down(): void
    {
        if (Schema::hasColumn('bmns_lab', 'nup')) {
            Schema::table('bmns_lab', function (Blueprint $table) {
                $table->dropColumn('nup');
            });
        }
    }
};