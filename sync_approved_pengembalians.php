<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PengajuanPengembalian;
use App\Models\Pengembalian;

$approved = PengajuanPengembalian::where('status', 'disetujui')->with('items')->get();
echo "Found " . $approved->count() . " approved pengajuan.\n";

foreach ($approved as $p) {
    $nomorBa = $p->nomor_ba ?: $p->nomor_pengajuan;
    $pengembalian = Pengembalian::updateOrCreate(
        ['pengajuan_id' => $p->id],
        [
            'nomor_pengajuan' => $nomorBa,
            'tanggal' => $p->tanggal,
            'pengaju_id' => $p->pegawai_id,
            'nama_pengaju' => $p->nama_pegawai,
            'bidang' => $p->bidang,
            'kondisi_pengembalian' => $p->alasan === 'rusak_berat' ? 'Rusak Berat' : 'Baik',
            'status' => 'Disetujui',
            'dihapus' => false,
            'dihapus_pada' => null,
            'dihapus_oleh' => null,
        ]
    );

    $pengembalian->items()->delete();
    foreach ($p->items as $item) {
        $pengembalian->items()->create([
            'bmn_id' => $item->bmn_id,
            'nama_barang_snapshot' => $item->nama_barang,
            'kondisi_awal' => $item->kondisi_awal ?: 'Baik',
            'kondisi' => $item->kondisi ?: ($p->alasan === 'rusak_berat' ? 'Rusak Berat' : 'Baik'),
            'keterangan' => $item->keterangan,
        ]);
    }
    echo "Synced Pengajuan #{$p->id} (BA: {$nomorBa}) to Pengembalian #{$pengembalian->id}\n";
}
echo "Done.\n";
