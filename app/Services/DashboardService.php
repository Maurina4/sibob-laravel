<?php

namespace App\Services;

use App\Models\BaPerpindahan;
use App\Models\Bmn;
use App\Models\BmnLab;
use App\Models\BmnTik;
use App\Models\Distribusi;
use App\Models\LaporanKerusakan;
use App\Models\Pengembalian;
use Illuminate\Support\Carbon;

class DashboardService
{
    public function getStats(): array
    {
        return [
            'jumlahBmn' => Bmn::active()->count(),
            'jumlahBmnUmum' => Bmn::active()->where(function ($query) {
                $query->where('kategori_bmn_id', 1)->orWhere('jenis', 'BMN-NON TIK');
            })->count(),
            'jumlahBmnTik' => BmnTik::active()->count(),
            'jumlahBmnLab' => BmnLab::active()->count(),
            'jumlahKondisiBaik' => Bmn::active()->where('kondisi', 'Baik')->count(),
            'jumlahPerluKalibrasi' => BmnLab::active()->perluKalibrasi()->count(),
            'jumlahKerusakan' => LaporanKerusakan::active()->belumSelesai()->count(),
            'jumlahDistribusi' => Distribusi::active()->count(),
            'jumlahBaPerpindahan' => BaPerpindahan::active()->status('Diajukan')->count(),
            'aktivitasTerkini' => $this->getRecentActivities(6),
        ];
    }

    /**
     * Mengumpulkan riwayat aktivitas pemeliharaan, kalibrasi, perpindahan, dan transaksi aset terkini.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRecentActivities(int $limit = 6): array
    {
        $activities = collect();

        // 1. Berita Acara Perpindahan Aset
        try {
            $baList = BaPerpindahan::active()
                ->with('bmn')
                ->latest('created_at')
                ->take(3)
                ->get();

            foreach ($baList as $ba) {
                $namaBarang = $ba->nama_barang ?: ($ba->bmn?->nama_barang ?: 'Barang BMN');
                $tgl = $ba->created_at ?? ($ba->tanggal ? Carbon::parse($ba->tanggal) : now());
                $diffDays = (int) now()->startOfDay()->diffInDays($tgl->copy()->startOfDay(), false);
                $badge = $diffDays < 0 ? abs($diffDays).' hari lalu' : ($diffDays === 0 ? 'Hari ini' : "{$diffDays} hari lagi");

                $activities->push([
                    'tipe' => 'perpindahan',
                    'judul' => "Pengajuan Berita Acara Perpindahan {$namaBarang}",
                    'subjudul' => ($ba->pengaju_nama ? "Diajukan oleh {$ba->pengaju_nama}" : 'Dari '.($ba->dari ?: 'Asal').' ke '.($ba->ke ?: 'Tujuan'))." • Status: {$ba->status}",
                    'badge' => $badge,
                    'badge_class' => 'text-[11px] font-mono text-[#62756A] shrink-0',
                    'icon_bg' => 'bg-[#F7FAF8] text-[#163828] border border-[#E3ECE6]',
                    'icon_type' => 'document',
                    'url' => route('ba-perpindahan.index'),
                    'timestamp' => $tgl->timestamp,
                ]);
            }
        } catch (\Throwable) {
        }

        // 3. Distribusi Barang
        try {
            $distribusiList = Distribusi::active()
                ->latest('created_at')
                ->take(3)
                ->get();

            foreach ($distribusiList as $dist) {
                $tgl = $dist->created_at ?? ($dist->tanggal ? Carbon::parse($dist->tanggal) : now());
                $diffDays = (int) now()->startOfDay()->diffInDays($tgl->copy()->startOfDay(), false);
                $badge = $diffDays < 0 ? abs($diffDays).' hari lalu' : ($diffDays === 0 ? 'Hari ini' : "{$diffDays} hari lagi");

                $activities->push([
                    'tipe' => 'distribusi',
                    'judul' => "Distribusi {$dist->nama_barang} ke ".($dist->lokasi_tujuan ?: 'Unit Kerja').' diajukan',
                    'subjudul' => 'Penerima: '.($dist->penerima ?: 'Petugas').($dist->jumlah ? " ({$dist->jumlah} ".($dist->satuan ?: 'unit').')' : ''),
                    'badge' => $badge,
                    'badge_class' => 'text-[11px] font-mono text-[#62756A] shrink-0',
                    'icon_bg' => 'bg-[#EAF2ED] text-[#1E5E38]',
                    'icon_type' => 'exchange',
                    'url' => route('distribusi.index'),
                    'timestamp' => $tgl->timestamp,
                ]);
            }
        } catch (\Throwable) {
        }

        // 4. Laporan Kerusakan Aset
        try {
            $laporanList = LaporanKerusakan::active()
                ->latest('created_at')
                ->take(3)
                ->get();

            foreach ($laporanList as $lapor) {
                $tgl = $lapor->created_at ?? ($lapor->tanggal ? Carbon::parse($lapor->tanggal) : now());
                $diffDays = (int) now()->startOfDay()->diffInDays($tgl->copy()->startOfDay(), false);
                $badge = $diffDays < 0 ? abs($diffDays).' hari lalu' : ($diffDays === 0 ? 'Hari ini' : "{$diffDays} hari lagi");

                $activities->push([
                    'tipe' => 'kerusakan',
                    'judul' => "Laporan Kerusakan {$lapor->nama_barang}".($lapor->tingkat ? " ({$lapor->tingkat})" : ''),
                    'subjudul' => 'Lokasi: '.($lapor->lokasi ?: 'Balai')." • Status: {$lapor->status}",
                    'badge' => $badge,
                    'badge_class' => 'text-[11px] font-mono text-[#9B1C1C] px-2 py-0.5 rounded bg-[#FDF2F2] shrink-0',
                    'icon_bg' => 'bg-[#FDF2F2] text-[#9B1C1C]',
                    'icon_type' => 'alert',
                    'url' => route('laporan-kerusakan.index'),
                    'timestamp' => $tgl->timestamp,
                ]);
            }
        } catch (\Throwable) {
        }

        // 5. Pengembalian BMN
        try {
            $pengembalianList = Pengembalian::active()
                ->with('items')
                ->latest('created_at')
                ->take(3)
                ->get();

            foreach ($pengembalianList as $peng) {
                $tgl = $peng->created_at ?? ($peng->tanggal ? Carbon::parse($peng->tanggal) : now());
                $diffDays = (int) now()->startOfDay()->diffInDays($tgl->copy()->startOfDay(), false);
                $badge = $diffDays < 0 ? abs($diffDays).' hari lalu' : ($diffDays === 0 ? 'Hari ini' : "{$diffDays} hari lagi");

                $itemFirst = $peng->items->first();
                $namaBarang = $itemFirst?->nama_barang_snapshot ?: 'Barang BMN';
                if ($peng->items->count() > 1) {
                    $namaBarang .= ' (+'.($peng->items->count() - 1).' barang lainnya)';
                }

                $activities->push([
                    'tipe' => 'pengembalian',
                    'judul' => "Pengembalian {$namaBarang} oleh ".($peng->nama_pengaju ?: 'Pegawai'),
                    'subjudul' => ($peng->bidang ? "Bidang: {$peng->bidang} • " : '')."Status: {$peng->status}",
                    'badge' => $badge,
                    'badge_class' => 'text-[11px] font-mono text-[#62756A] shrink-0',
                    'icon_bg' => 'bg-[#EFF6FF] text-[#1E40AF]',
                    'icon_type' => 'return',
                    'url' => route('pengembalian.index'),
                    'timestamp' => $tgl->timestamp,
                ]);
            }
        } catch (\Throwable) {
        }

        return $activities
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values()
            ->all();
    }
}
