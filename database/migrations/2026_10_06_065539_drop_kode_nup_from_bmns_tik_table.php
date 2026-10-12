<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('bmns_tik', 'kode_nup')) {
            Schema::table('bmns_tik', function (Blueprint $table) {
                $table->dropColumn('kode_nup');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('bmns_tik', 'kode_nup')) {
            Schema::table('bmns_tik', function (Blueprint $table) {
                $table->string('kode_nup')->nullable();
            });
        }
    }
};