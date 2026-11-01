<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Lokasi;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();
try {
    $inserted = [
        Lokasi::create(['kode_ruangan' => 'A1.21', 'nama' => 'RUANG TESTING SISIPAN A1']),
        Lokasi::create(['kode_ruangan' => 'A1.36', 'nama' => 'RUANG TESTING AKHIR A1']),
        Lokasi::create(['kode_ruangan' => 'A2.26', 'nama' => 'RUANG TESTING AKHIR A2']),
        Lokasi::create(['kode_ruangan' => 'B1.14', 'nama' => 'RUANG TESTING AKHIR B1']),
        Lokasi::create(['kode_ruangan' => 'B2.11', 'nama' => 'RUANG TESTING AKHIR B2']),
        Lokasi::create(['kode_ruangan' => 'B.BS.02', 'nama' => 'RUANG TESTING AKHIR B.BS']),
        Lokasi::create(['kode_ruangan' => 'C1.18', 'nama' => 'RUANG TESTING AKHIR C1']),
        Lokasi::create(['kode_ruangan' => 'C2.10', 'nama' => 'RUANG TESTING AKHIR C2']),
        Lokasi::create(['kode_ruangan' => 'C.Bs.23', 'nama' => 'RUANG TESTING AKHIR C.BS']),
    ];

    $driver = DB::getDriverName();
    $orderBuilding = "
        CASE
            WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE 'A%' OR UPPER(nama) LIKE '%GEDUNG A%' THEN 1
            WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE 'B%' OR UPPER(nama) LIKE '%GEDUNG B%' THEN 2
            WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE 'C%' OR UPPER(nama) LIKE '%GEDUNG C%' THEN 3
            ELSE 4
        END ASC
    ";

    $orderFloor = "
        CASE
            WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE '%BS%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '%.BS%' OR UPPER(nama) LIKE '%BASEMENT%' THEN 10
            WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE '_1.%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_1-%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_1 %' OR UPPER(nama) LIKE '%LANTAI 1%' OR UPPER(nama) LIKE '%LT 1%' THEN 1
            WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE '_2.%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_2-%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_2 %' OR UPPER(nama) LIKE '%LANTAI 2%' OR UPPER(nama) LIKE '%LT 2%' THEN 2
            WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE '_3.%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_3-%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_3 %' OR UPPER(nama) LIKE '%LANTAI 3%' OR UPPER(nama) LIKE '%LT 3%' THEN 3
            WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE '_4.%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_4-%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_4 %' OR UPPER(nama) LIKE '%LANTAI 4%' OR UPPER(nama) LIKE '%LT 4%' THEN 4
            ELSE 5
        END ASC
    ";

    $orderRoomNum = $driver === 'mysql'
        ? "CAST(SUBSTRING_INDEX(COALESCE(kode_ruangan, '0'), '.', -1) AS UNSIGNED) ASC"
        : 'kode_ruangan ASC';

    $all = Lokasi::query()
        ->orderByRaw($orderBuilding)
        ->orderByRaw($orderFloor)
        ->orderByRaw($orderRoomNum)
        ->orderBy('kode_ruangan')
        ->orderBy('id')
        ->get(['id', 'kode_ruangan', 'nama']);

    echo 'Total with additions: '.$all->count().PHP_EOL;

    // Check positions
    $pos = function ($code) use ($all) {
        $idx = $all->search(fn ($l) => $l->kode_ruangan === $code);
        $prev = $idx > 0 ? $all[$idx - 1]->kode_ruangan : '[START]';
        $next = $idx < $all->count() - 1 ? $all[$idx + 1]->kode_ruangan : '[END]';
        echo "Position of $code (index $idx): after $prev, before $next".PHP_EOL;
    };

    $pos('A1.21');
    $pos('A1.36');
    $pos('A2.26');
    $pos('B1.14');
    $pos('B2.11');
    $pos('B.BS.02');
    $pos('C1.18');
    $pos('C2.10');
    $pos('C.Bs.23');

} finally {
    DB::rollBack();
}
