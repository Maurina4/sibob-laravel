<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bmns_tik', function (Blueprint $table) {
            $table->id();

            $table->string('kode_nup');
            $table->string('nama_barang');
            $table->string('jenis')->nullable();

            $table->string('lokasi');
            $table->string('penanggungjawab');

            $table->boolean('dihapus')->default(false);
            $table->timestamp('dihapus_pada')->nullable();
            $table->string('dihapus_oleh')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bmns_tik');
    }
};