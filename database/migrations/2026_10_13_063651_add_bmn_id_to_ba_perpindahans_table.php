<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('ba_perpindahans') && ! Schema::hasColumn('ba_perpindahans', 'bmn_id')) {
            Schema::table('ba_perpindahans', function (Blueprint $table) {
                $table->foreignId('bmn_id')->nullable()->after('tanggal')->constrained('bmns')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ba_perpindahans') && Schema::hasColumn('ba_perpindahans', 'bmn_id')) {
            Schema::table('ba_perpindahans', function (Blueprint $table) {
                $table->dropForeign(['bmn_id']);
                $table->dropColumn('bmn_id');
            });
        }
    }
};
