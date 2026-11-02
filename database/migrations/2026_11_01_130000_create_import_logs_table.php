<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('nama_file_asli');
            $table->string('nama_file_hash');
            $table->integer('total_baris')->default(0);
            $table->integer('jumlah_sukses')->default(0);
            $table->integer('jumlah_gagal')->default(0);
            $table->string('status')->default('preview'); // preview, berhasil, gagal
            $table->json('error_summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
