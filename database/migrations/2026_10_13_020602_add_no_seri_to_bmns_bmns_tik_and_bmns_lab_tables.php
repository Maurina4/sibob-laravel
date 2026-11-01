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
        if (Schema::hasTable('bmns') && ! Schema::hasColumn('bmns', 'no_seri')) {
            Schema::table('bmns', function (Blueprint $table) {
                $table->string('no_seri')->nullable()->after('nup');
            });
        }

        if (Schema::hasTable('bmns_tik') && ! Schema::hasColumn('bmns_tik', 'no_seri')) {
            Schema::table('bmns_tik', function (Blueprint $table) {
                $table->string('no_seri')->nullable()->after('nup');
            });
        }

        if (Schema::hasTable('bmns_lab') && ! Schema::hasColumn('bmns_lab', 'no_seri')) {
            Schema::table('bmns_lab', function (Blueprint $table) {
                $table->string('no_seri')->nullable()->after('nup');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bmns') && Schema::hasColumn('bmns', 'no_seri')) {
            Schema::table('bmns', function (Blueprint $table) {
                $table->dropColumn('no_seri');
            });
        }

        if (Schema::hasTable('bmns_tik') && Schema::hasColumn('bmns_tik', 'no_seri')) {
            Schema::table('bmns_tik', function (Blueprint $table) {
                $table->dropColumn('no_seri');
            });
        }

        if (Schema::hasTable('bmns_lab') && Schema::hasColumn('bmns_lab', 'no_seri')) {
            Schema::table('bmns_lab', function (Blueprint $table) {
                $table->dropColumn('no_seri');
            });
        }
    }
};
