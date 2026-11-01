<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Lokasi;
use Illuminate\Contracts\Console\Kernel;

$all = Lokasi::orderedByGedung()->get(['id', 'kode_ruangan', 'nama']);
foreach ($all as $l) {
    echo "{$l->id} | {$l->kode_ruangan} | {$l->nama}".PHP_EOL;
}
