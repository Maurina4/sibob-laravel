<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_kerusakans', function (Blueprint $table) {
            $table->id();

            $table->string('nomor');
            $table->date('tanggal');

            $table->string('nama_barang');
            $table->string('lokasi');

            $table->string('tingkat');
            $table->text('deskripsi');

            $table->string('pelapor_email');
            $table->string('pelapor_nama');

            $table->string('status')->default('Dilaporkan');

            $table->text('tanggapan')->nullable();

            $table->date('tanggal_update')->nullable();
            $table->string('ditanggapi_oleh')->nullable();

            $table->boolean('dihapus')->default(false);
            $table->timestamp('dihapus_pada')->nullable();
            $table->string('dihapus_oleh')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_kerusakans');
    }
};
