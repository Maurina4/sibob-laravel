<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Lokasi;
use Illuminate\Contracts\Console\Kernel;

$q = Lokasi::query()
    ->orderByRaw("
        CASE
            WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE 'A%' OR UPPER(nama) LIKE '%GEDUNG A%' THEN 1
            WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE 'B%' OR UPPER(nama) LIKE '%GEDUNG B%' THEN 2
            WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE 'C%' OR UPPER(nama) LIKE '%GEDUNG C%' THEN 3
            ELSE 4
        END ASC
    ")
    ->orderByRaw("
        CASE
            WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE '_1.%' THEN 1
            WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE '_2.%' THEN 2
            WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE '_3.%' THEN 3
            WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE '%.BS%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '%BS.%' THEN 4
            ELSE 5
        END ASC
    ")
    ->orderBy('kode_ruangan')
    ->orderBy('id');

$results = $q->get();
echo 'Total: '.$results->count().PHP_EOL;

echo '--- GEDUNG A (first 3 & last 3) ---'.PHP_EOL;
$ga = $results->filter(fn ($l) => str_starts_with(strtoupper($l->kode_ruangan ?? ''), 'A'));
echo 'Count GA: '.$ga->count().PHP_EOL;
foreach ($ga->take(3) as $l) {
    echo "  {$l->id} - {$l->kode_ruangan} - {$l->nama}".PHP_EOL;
}
echo '  ...'.PHP_EOL;
foreach ($ga->slice(-3) as $l) {
    echo "  {$l->id} - {$l->kode_ruangan} - {$l->nama}".PHP_EOL;
}

echo '--- GEDUNG B (first 3 & last 3) ---'.PHP_EOL;
$gb = $results->filter(fn ($l) => str_starts_with(strtoupper($l->kode_ruangan ?? ''), 'B'));
echo 'Count GB: '.$gb->count().PHP_EOL;
foreach ($gb->take(3) as $l) {
    echo "  {$l->id} - {$l->kode_ruangan} - {$l->nama}".PHP_EOL;
}
echo '  ...'.PHP_EOL;
foreach ($gb->slice(-3) as $l) {
    echo "  {$l->id} - {$l->kode_ruangan} - {$l->nama}".PHP_EOL;
}

echo '--- GEDUNG C (first 3 & last 3) ---'.PHP_EOL;
$gc = $results->filter(fn ($l) => str_starts_with(strtoupper($l->kode_ruangan ?? ''), 'C'));
echo 'Count GC: '.$gc->count().PHP_EOL;
foreach ($gc->take(3) as $l) {
    echo "  {$l->id} - {$l->kode_ruangan} - {$l->nama}".PHP_EOL;
}
echo '  ...'.PHP_EOL;
foreach ($gc->slice(-3) as $l) {
    echo "  {$l->id} - {$l->kode_ruangan} - {$l->nama}".PHP_EOL;
}
