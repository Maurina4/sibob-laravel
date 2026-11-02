<?php

use App\Models\Distribusi;
use App\Models\Pengembalian;
use Illuminate\Contracts\Console\Kernel;

require 'C:/xampp/htdocs/BMN-app/sibob-laravel/vendor/autoload.php';
$app = require_once 'C:/xampp/htdocs/BMN-app/sibob-laravel/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$distribusi = Distribusi::with('bmn')->first();
if (! $distribusi) {
    $distribusi = new Distribusi;
    $distribusi->nomor = 'BA-DIST-001/2026';
    $distribusi->tanggal = now();
}
$htmlDist = view('distribusi.print', compact('distribusi'))->render();
if (strpos($htmlDist, 'kop-footer-decoration') === false) {
    throw new Exception('distribusi missing kop-footer-decoration');
}
if (strpos($htmlDist, 'margin-top: 14mm') === false) {
    throw new Exception('distribusi missing 14mm title margin');
}
if (strpos($htmlDist, 'Margin Kop: Kiri ~6.7mm, Kanan ~5.3mm') === false) {
    throw new Exception('distribusi missing updated kop');
}
echo "Distribusi print view verified OK!\n";

$pengembalian = Pengembalian::with('items.bmn')->first();
if (! $pengembalian) {
    $pengembalian = new Pengembalian;
    $pengembalian->nomor_pengajuan = 'BA-KEMBALI-001/2026';
    $pengembalian->tanggal_pengajuan = now();
}
$htmlPeng = view('pengembalian.print', compact('pengembalian'))->render();
if (strpos($htmlPeng, 'kop-footer-decoration') === false) {
    throw new Exception('pengembalian missing kop-footer-decoration');
}
if (strpos($htmlPeng, 'margin-top: 14mm') === false) {
    throw new Exception('pengembalian missing 14mm title margin');
}
if (strpos($htmlPeng, 'Margin Kop: Kiri ~6.7mm, Kanan ~5.3mm') === false) {
    throw new Exception('pengembalian missing updated kop');
}
echo "Pengembalian print view verified OK!\n";
