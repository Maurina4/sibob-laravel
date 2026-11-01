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
        if (Schema::hasTable('distribusis') && Schema::hasColumn('distribusis', 'status')) {
            Schema::table('distribusis', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('distribusis') && ! Schema::hasColumn('distribusis', 'status')) {
            Schema::table('distribusis', function (Blueprint $table) {
                $table->string('status')->nullable()->default('Diajukan');
            });
        }
    }
};
