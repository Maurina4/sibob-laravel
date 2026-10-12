<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_terhapus', function (Blueprint $table) {
            $table->id();

            $table->string('jenis');
            $table->unsignedBigInteger('data_id');
            $table->string('nama');

            $table->timestamp('dihapus_pada');
            $table->string('dihapus_oleh');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_terhapus');
    }
};