<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengembalians', function (Blueprint $table) {
            $table->id();

            $table->string('nomor_pengajuan');
            $table->date('tanggal');
            $table->string('nama_pengaju');
            $table->string('bidang')->nullable();

            $table->string('status')->default('Diajukan');

            $table->boolean('dihapus')->default(false);
            $table->timestamp('dihapus_pada')->nullable();
            $table->string('dihapus_oleh')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengembalians');
    }
};