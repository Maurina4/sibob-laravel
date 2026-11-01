<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bmns', function (Blueprint $table) {
            $table->id();

            $table->string('kode_barang');
            $table->string('nup');
            $table->string('nama_barang');

            $table->string('merek')->nullable();
            $table->date('tanggal_terima')->nullable();
            $table->string('jenis')->nullable();
            $table->string('satuan')->nullable();

            $table->string('lokasi');
            $table->string('penanggungjawab');
            $table->string('penanggungjawab_nip')->nullable();

            $table->string('kondisi')->default('Baik');
            $table->date('jadwal_pemeliharaan')->nullable();

            $table->text('spesifikasi')->nullable();

            $table->boolean('dihapus')->default(false);
            $table->timestamp('dihapus_pada')->nullable();
            $table->string('dihapus_oleh')->nullable();

            $table->timestamps();

            $table->unique(['kode_barang', 'nup']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bmns');
    }
};
