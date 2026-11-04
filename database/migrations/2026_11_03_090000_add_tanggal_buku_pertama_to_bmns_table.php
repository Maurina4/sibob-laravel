<?php

use App\Models\Lokasi;
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
        if (Schema::hasTable('bmns')) {
            Schema::table('bmns', function (Blueprint $table) {
                if (! Schema::hasColumn('bmns', 'tanggal_buku_pertama')) {
                    $table->date('tanggal_buku_pertama')->nullable()->after('tanggal_terima');
                }
            });
        }

        // Pastikan entri 'Belum berlokasi' terdaftar di tabel lokasis
        if (Schema::hasTable('lokasis')) {
            Lokasi::firstOrCreate(
                ['nama' => 'Belum berlokasi'],
                ['kode_ruangan' => 'BL']
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bmns')) {
            Schema::table('bmns', function (Blueprint $table) {
                if (Schema::hasColumn('bmns', 'tanggal_buku_pertama')) {
                    $table->dropColumn('tanggal_buku_pertama');
                }
            });
        }
    }
};
