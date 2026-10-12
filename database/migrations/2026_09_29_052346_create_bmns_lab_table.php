<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bmns_lab', function (Blueprint $table) {
            $table->id();

            $table->string('kode_barang');
            $table->string('nama_barang');
            $table->string('merek')->nullable();

            $table->string('lokasi');
            $table->string('penanggungjawab');

            $table->string('kondisi')->default('Baik');

            $table->string('status_kalibrasi')->nullable();
            $table->date('tanggal_kalibrasi')->nullable();

            $table->boolean('dihapus')->default(false);
            $table->timestamp('dihapus_pada')->nullable();
            $table->string('dihapus_oleh')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bmns_lab');
    }
};