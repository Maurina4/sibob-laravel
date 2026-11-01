<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$tables = DB::select('SHOW TABLES');
$dbName = DB::getDatabaseName();
$tableKey = 'Tables_in_'.$dbName;

foreach ($tables as $t) {
    $tableName = $t->$tableKey;
    $columns = DB::select("SHOW COLUMNS FROM `$tableName`");
    foreach ($columns as $c) {
        $colName = $c->Field;
        $type = strtolower($c->Type);
        if (str_contains($type, 'char') || str_contains($type, 'text')) {
            $cnt = DB::table($tableName)->where($colName, 'LIKE', '%Banjarmasin%')->count();
            if ($cnt > 0) {
                echo "Table: $tableName, Col: $colName -> $cnt rows with 'Banjarmasin'".PHP_EOL;
            }
        }
    }
}
