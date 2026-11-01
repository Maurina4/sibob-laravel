<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ba_perpindahans', function (Blueprint $table) {
            if (! Schema::hasColumn('ba_perpindahans', 'pegawai_id')) {
                $table->foreignId('pegawai_id')->nullable()->after('bmn_id')->constrained('pegawais')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ba_perpindahans', function (Blueprint $table) {
            if (Schema::hasColumn('ba_perpindahans', 'pegawai_id')) {
                $table->dropForeign(['pegawai_id']);
                $table->dropColumn('pegawai_id');
            }
        });
    }
};
