<?php

use App\Models\BaPerpindahan;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$baPerpindahan = new BaPerpindahan;
$baPerpindahan->id = 1;
$baPerpindahan->nomor_ba = 'BA-001/BMN/2026';
$baPerpindahan->nomor_surat = 'BA-001/BMN/2026';
$baPerpindahan->tanggal = '2026-10-20';
$baPerpindahan->pihak_pertama = 'Irma Nur Azizah, A.Md.Kes.';
$baPerpindahan->pihak_kedua = 'Ahmad Baihaki, S.Kom.';
$baPerpindahan->dari = 'Ruang Server';
$baPerpindahan->ke = 'Gudang BMN';
$baPerpindahan->alasan = 'Pemindahan aset berkala';
$baPerpindahan->catatan = '-';

$items = collect([
    (object) [
        'nama_barang' => 'Laptop ThinkPad X1 Carbon',
        'kode_barang' => '3.05.01.04.001',
        'nup' => '00012',
        'merk' => 'Lenovo',
        'jumlah' => 1,
        'tahun_perolehan' => '2022',
        'kondisi' => 'Baik',
        'lokasi_asal' => 'Ruang Server',
        'lokasi_tujuan' => 'Gudang BMN',
    ],
]);

$pihakPertama = [
    'nama' => 'Irma Nur Azizah, A.Md.Kes.',
    'nip' => '19900101 201503 2 001',
    'jabatan' => 'Pengadministrasi BMN',
    'unit_kerja' => 'Subbagian Tata Usaha',
];

$pihakKedua = [
    'nama' => 'Ahmad Baihaki, S.Kom.',
    'nip' => '19880202 201402 1 002',
    'jabatan' => 'Pranata Komputer Ahli Muda',
    'unit_kerja' => 'Subbagian Tata Usaha',
];

$size = 'a4';
$kabalaiNama = 'Drs. Leonard Duma, Apt., M.M.';
$kabalaiNip = '19671112 199303 1 001';
$kondisiGeneral = 'Baik';
$logoBase64 = '';

$logoPath = public_path('images/logo-badan-pom-hd.png');
if (file_exists($logoPath)) {
    $logoBase64 = base64_encode(file_get_contents($logoPath));
}

$view = view('ba-perpindahan.print', compact(
    'baPerpindahan',
    'size',
    'pihakPertama',
    'pihakKedua',
    'kabalaiNama',
    'kabalaiNip',
    'items',
    'kondisiGeneral',
    'logoBase64'
));

$html = $view->render();
file_put_contents('scratch/preview_ba.html', $html);
echo 'SUCCESS: scratch/preview_ba.html written ('.strlen($html)." bytes)\n";
