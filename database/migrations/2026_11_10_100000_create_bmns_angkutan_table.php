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
        // 1. Tambah kolom no_polisi ke tabel bmns jika belum ada (nullable)
        if (Schema::hasTable('bmns') && ! Schema::hasColumn('bmns', 'no_polisi')) {
            Schema::table('bmns', function (Blueprint $table) {
                $table->string('no_polisi')->nullable()->after('no_seri');
            });
        }

        // 2. Buat tabel bmns_angkutan untuk modul khusus Alat Angkutan Bermotor
        Schema::create('bmns_angkutan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bmn_id')->nullable()->constrained('bmns')->nullOnDelete();

            $table->string('kode_barang')->index();
            $table->integer('nup');
            $table->string('nama_barang');
            $table->string('merek')->nullable();
            $table->string('no_polisi'); // Plat Nomor Kendaraan (khusus form ini)

            $table->integer('tahun_perolehan')->nullable();
            $table->double('nilai_perolehan', 15, 2)->nullable();

            $table->string('lokasi');
            $table->string('penanggungjawab');

            $table->string('kondisi')->default('Baik');
            $table->boolean('is_dikembalikan')->default(false);

            $table->boolean('dihapus')->default(false);
            $table->timestamp('dihapus_pada')->nullable();
            $table->string('dihapus_oleh')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bmns_angkutan');

        if (Schema::hasTable('bmns') && Schema::hasColumn('bmns', 'no_polisi')) {
            Schema::table('bmns', function (Blueprint $table) {
                $table->dropColumn('no_polisi');
            });
        }
    }
};
