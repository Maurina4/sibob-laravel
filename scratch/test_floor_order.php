<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Lokasi;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

// Let's test the ordering logic
$driver = DB::getDriverName();

$orderBuilding = "
    CASE
        WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE 'A%' OR UPPER(nama) LIKE '%GEDUNG A%' THEN 1
        WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE 'B%' OR UPPER(nama) LIKE '%GEDUNG B%' THEN 2
        WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE 'C%' OR UPPER(nama) LIKE '%GEDUNG C%' THEN 3
        ELSE 4
    END ASC
";

// Floor logic:
// 1 = Lantai 1 (A1, B1, C1)
// 2 = Lantai 2 (A2, B2, C2)
// 3 = Lantai 3 (A3, B3, C3)
// 4 = Lantai 4 (A4, B4, C4)
// 10 = Basement (BS, Bs)
$orderFloor = "
    CASE
        WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE '%BS%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '%.BS%' OR UPPER(nama) LIKE '%BASEMENT%' THEN 10
        WHEN UPPER(COALESCE(kode_ruangan, '')) REGEXP '^[A-Z]1[.-]' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_1.%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_1-%' OR UPPER(nama) LIKE '%LANTAI 1%' OR UPPER(nama) LIKE '%LT 1%' THEN 1
        WHEN UPPER(COALESCE(kode_ruangan, '')) REGEXP '^[A-Z]2[.-]' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_2.%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_2-%' OR UPPER(nama) LIKE '%LANTAI 2%' OR UPPER(nama) LIKE '%LT 2%' THEN 2
        WHEN UPPER(COALESCE(kode_ruangan, '')) REGEXP '^[A-Z]3[.-]' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_3.%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_3-%' OR UPPER(nama) LIKE '%LANTAI 3%' OR UPPER(nama) LIKE '%LT 3%' THEN 3
        WHEN UPPER(COALESCE(kode_ruangan, '')) REGEXP '^[A-Z]4[.-]' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_4.%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_4-%' OR UPPER(nama) LIKE '%LANTAI 4%' OR UPPER(nama) LIKE '%LT 4%' THEN 4
        ELSE 5
    END ASC
";

// Room numeric logic:
$orderRoomNum = $driver === 'mysql'
    ? "CAST(SUBSTRING_INDEX(COALESCE(kode_ruangan, '0'), '.', -1) AS UNSIGNED) ASC"
    : 'kode_ruangan ASC';

echo "Running test on $driver...".PHP_EOL;

$sampleQuery = Lokasi::query()
    ->orderByRaw($orderBuilding)
    ->orderByRaw($orderFloor)
    ->orderByRaw($orderRoomNum)
    ->orderBy('kode_ruangan')
    ->orderBy('id');

$results = $sampleQuery->get();
echo 'Total existing: '.$results->count().PHP_EOL;
echo 'First 5 (A1):'.PHP_EOL;
foreach ($results->take(5) as $l) {
    echo "  {$l->kode_ruangan} - {$l->nama}".PHP_EOL;
}
echo 'A1 end & A2 start (around index 33-37):'.PHP_EOL;
foreach ($results->slice(33, 5) as $l) {
    echo "  {$l->kode_ruangan} - {$l->nama}".PHP_EOL;
}
echo 'A2 end & B1 start (around index 56-60):'.PHP_EOL;
foreach ($results->slice(56, 5) as $l) {
    echo "  {$l->kode_ruangan} - {$l->nama}".PHP_EOL;
}
echo 'B1 end & B2 start (around index 69-72):'.PHP_EOL;
foreach ($results->slice(69, 5) as $l) {
    echo "  {$l->kode_ruangan} - {$l->nama}".PHP_EOL;
}
echo 'B2 end & B.BS start (around index 78-81):'.PHP_EOL;
foreach ($results->slice(78, 4) as $l) {
    echo "  {$l->kode_ruangan} - {$l->nama}".PHP_EOL;
}
echo 'B.BS end & C1 start (around index 79-83):'.PHP_EOL;
foreach ($results->slice(79, 4) as $l) {
    echo "  {$l->kode_ruangan} - {$l->nama}".PHP_EOL;
}
echo 'C2 end & C.Bs start (around index 104-108):'.PHP_EOL;
foreach ($results->slice(104, 5) as $l) {
    echo "  {$l->kode_ruangan} - {$l->nama}".PHP_EOL;
}
