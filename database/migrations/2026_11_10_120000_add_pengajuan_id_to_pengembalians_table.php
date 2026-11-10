<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengembalians', function (Blueprint $table) {
            if (! Schema::hasColumn('pengembalians', 'pengajuan_id')) {
                $table->foreignId('pengajuan_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('pengajuan_pengembalians')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengembalians', function (Blueprint $table) {
            if (Schema::hasColumn('pengembalians', 'pengajuan_id')) {
                if (DB::getDriverName() !== 'sqlite') {
                    $table->dropForeign(['pengajuan_id']);
                }
                $table->dropColumn('pengajuan_id');
            }
        });
    }
};
