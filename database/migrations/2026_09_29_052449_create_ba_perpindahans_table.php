<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ba_perpindahans', function (Blueprint $table) {
            $table->id();

            $table->string('nomor_ba')->nullable();
            $table->date('tanggal');

            $table->string('nama_barang');
            $table->string('dari');
            $table->string('ke')->nullable();

            $table->text('alasan');

            $table->string('pihak_kedua')->nullable();

            $table->string('pengaju_email');
            $table->string('pengaju_nama');

            $table->string('status')->default('Diajukan');

            $table->text('catatan_operator')->nullable();
            $table->date('tanggal_keputusan')->nullable();
            $table->string('diputuskan_oleh')->nullable();

            $table->boolean('dihapus')->default(false);
            $table->timestamp('dihapus_pada')->nullable();
            $table->string('dihapus_oleh')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ba_perpindahans');
    }
};
