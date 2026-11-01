<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\BaPerpindahan;
use App\Models\Bmn;
use App\Models\BmnLab;
use App\Models\BmnTik;
use App\Models\Distribusi;
use App\Models\LaporanKerusakan;
use App\Models\Pengembalian;
use Illuminate\Contracts\Console\Kernel;

echo 'BMN count: '.Bmn::count().PHP_EOL;
echo 'BMN TIK count: '.BmnTik::count().PHP_EOL;
echo 'BMN Lab count: '.BmnLab::count().PHP_EOL;
echo 'Distribusi count: '.Distribusi::count().PHP_EOL;
echo 'BA Perpindahan count: '.BaPerpindahan::count().PHP_EOL;
echo 'Laporan Kerusakan count: '.LaporanKerusakan::count().PHP_EOL;
echo 'Pengembalian count: '.Pengembalian::count().PHP_EOL;

echo PHP_EOL.'--- BMN LIST ---'.PHP_EOL;
foreach (Bmn::all() as $b) {
    echo "ID: {$b->id} | {$b->kode_barang} NUP: {$b->nup} | {$b->nama_barang} | {$b->jenis} | Lokasi: {$b->lokasi} | PJ: {$b->penanggungjawab}".PHP_EOL;
}
