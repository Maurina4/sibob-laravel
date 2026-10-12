<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribusis', function (Blueprint $table) {
            $table->id();

            $table->string('nomor');
            $table->date('tanggal');

            $table->string('nama_barang');

            $table->integer('jumlah')->default(1);
            $table->string('satuan')->default('unit');

            $table->string('lokasi_asal');
            $table->string('lokasi_tujuan');

            $table->string('penerima');

            $table->text('keterangan')->nullable();

            $table->string('status')->default('Diajukan');

            $table->boolean('dihapus')->default(false);
            $table->timestamp('dihapus_pada')->nullable();
            $table->string('dihapus_oleh')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribusis');
    }
};