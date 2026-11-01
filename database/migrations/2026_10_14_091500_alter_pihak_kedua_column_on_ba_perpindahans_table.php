<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'ba_perpindahans' 
                  AND COLUMN_NAME = 'pihak_kedua' 
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            foreach ($foreignKeys as $fk) {
                DB::statement("ALTER TABLE `ba_perpindahans` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            }

            DB::statement('ALTER TABLE `ba_perpindahans` MODIFY `pihak_kedua` VARCHAR(255) NULL');
        } else {
            Schema::table('ba_perpindahans', function (Blueprint $table) {
                $table->string('pihak_kedua')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // No-op to preserve string data
    }
};
