<?php

namespace App\Services;

use App\Models\Bmn;
use App\Models\BmnLab;
use App\Models\BmnTik;
use App\Models\LaporanKerusakan;
use App\Models\Lokasi;
use App\Models\Pegawai;
use App\Models\PengajuanPengembalian;
use App\Models\PengajuanPengembalianItem;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PengajuanPengembalianService
{
    public function getPaginatedForUser(int $userId, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return PengajuanPengembalian::query()
            ->with(['items.bmn', 'penyetujuUser'])
            ->milikhak($userId)
            ->filter($filters)
            ->latest('tanggal')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getPaginatedForAdmin(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return PengajuanPengembalian::query()
            ->with(['items.bmn', 'pemohonUser', 'penyetujuUser'])
            ->filter($filters)
            ->latest('tanggal')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Mengambil daftar BMN aktif yang tersedia untuk diajukan oleh pengguna.
     */
    public function getAvailableBmns(User $user, ?string $alasan = null, ?string $search = null): Collection
    {
        // 1. Ambil id barang yang sedang berada dalam pengajuan berstatus 'diajukan'
        $pendingBmnIds = PengajuanPengembalianItem::query()
            ->whereHas('pengajuan', fn ($q) => $q->where('status', 'diajukan'))
            ->pluck('bmn_id')
            ->toArray();

        // 2. Query BMN yang masih aktif di ruangan
        $query = Bmn::query()
            ->active()
            ->where(function ($q) {
                $q->whereNull('is_dikembalikan')
                    ->orWhere('is_dikembalikan', false);
            })
            ->where(function ($q) {
                $q->whereNull('kondisi')
                    ->orWhereRaw('LOWER(kondisi) != ?', ['rusak ringan']);
            })
            ->whereNotIn('id', $pendingBmnIds);

        // Filter alasan pengembalian biasa: hanya kondisi 'Baik'
        if ($alasan === 'pengembalian_biasa') {
            $query->where(function ($q) {
                $q->whereNull('kondisi')
                    ->orWhereRaw('LOWER(kondisi) = ?', ['baik'])
                    ->orWhere('kondisi', '');
            });
        }

        if (! empty($search)) {
            $s = trim($search);
            $query->where(function ($q) use ($s) {
                $q->where('kode_barang', 'like', "%{$s}%")
                    ->orWhere('nup', 'like', "%{$s}%")
                    ->orWhere('nama_barang', 'like', "%{$s}%")
                    ->orWhere('merek', 'like', "%{$s}%");
            });
        }

        $bmns = $query->orderBy('nama_barang')->get();

        // Cari pegawai terkait user untuk memprioritaskan penandaan kepemilikan
        $pegawai = Pegawai::where('nama', $user->name)->first();

        return $bmns->map(function ($bmn) use ($user, $pegawai) {
            $isMine = false;
            if ($pegawai && $bmn->penanggung_jawab_id === $pegawai->id) {
                $isMine = true;
            } elseif (! empty($bmn->penanggungjawab) && str_contains(strtolower($bmn->penanggungjawab), strtolower($user->name))) {
                $isMine = true;
            }

            $bmn->is_mine = $isMine;

            return $bmn;
        })->sortByDesc('is_mine')->values();
    }

    /**
     * Menyimpan formulir pengajuan pengembalian baru oleh pegawai.
     */
    public function ajukan(array $validated, User $user): PengajuanPengembalian
    {
        return DB::transaction(function () use ($validated, $user) {
            $pegawai = Pegawai::where('nama', $user->name)->first();

            $prefix = 'PGM';
            $datePart = now()->format('Ymd');
            $countToday = PengajuanPengembalian::where('nomor_pengajuan', 'like', "{$prefix}-{$datePart}-%")->count() + 1;
            $nomorPengajuan = "{$prefix}-{$datePart}-".str_pad((string) $countToday, 3, '0', STR_PAD_LEFT);
            while (PengajuanPengembalian::where('nomor_pengajuan', $nomorPengajuan)->exists()) {
                $countToday++;
                $nomorPengajuan = "{$prefix}-{$datePart}-".str_pad((string) $countToday, 3, '0', STR_PAD_LEFT);
            }

            $pengajuan = PengajuanPengembalian::create([
                'nomor_pengajuan' => $nomorPengajuan,
                'nomor_sipanda' => $validated['nomor_sipanda'] ?? null,
                'bidang' => $validated['bidang'],
                'tanggal' => $validated['tanggal'] ?? now()->toDateString(),
                'pegawai_id' => $pegawai?->id,
                'nama_pegawai' => $pegawai?->nama ?? $user->name,
                'nip_pegawai' => $pegawai?->nip ?? '-',
                'diajukan_oleh' => $user->id,
                'alasan' => $validated['alasan'],
                'status' => 'diajukan',
                'catatan_pemohon' => $validated['catatan_pemohon'] ?? null,
            ]);

            foreach ($validated['items'] as $itemData) {
                $bmn = Bmn::findOrFail($itemData['bmn_id']);

                $itemPayload = [
                    'pengajuan_id' => $pengajuan->id,
                    'bmn_id' => $bmn->id,
                    'kode_barang' => $bmn->kode_barang,
                    'nup' => (int) $bmn->nup,
                    'nama_barang' => $bmn->nama_barang,
                    'merek' => $bmn->merek,
                    'lokasi_awal' => $bmn->lokasi,
                    'kondisi_awal' => $bmn->kondisi ?: 'Baik',
                    'kondisi' => $validated['alasan'] === 'rusak_berat' ? 'Rusak Berat' : 'Baik',
                    'keterangan' => $itemData['keterangan'] ?? null,
                ];

                if ($validated['alasan'] === 'rusak_berat') {
                    $itemPayload['tanggal_temuan'] = $itemData['tanggal_temuan'] ?? $validated['tanggal'];
                    $itemPayload['deskripsi_kerusakan'] = $itemData['deskripsi_kerusakan'] ?? null;
                }

                PengajuanPengembalianItem::create($itemPayload);
            }

            return $pengajuan;
        });
    }

    /**
     * Menyetujui pengajuan dalam satu transaksi database atomik.
     */
    public function setujui(PengajuanPengembalian $pengajuan, User $adminUser): bool
    {
        return DB::transaction(function () use ($pengajuan, $adminUser) {
            /** @var PengajuanPengembalian $locked */
            $locked = PengajuanPengembalian::where('id', $pengajuan->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'diajukan') {
                throw new Exception("Pengajuan ini sudah berstatus '{$locked->status}' dan tidak dapat disetujui lagi.");
            }

            // 1. Generate Nomor Berita Acara (BA)
            $prefix = $locked->alasan === 'rusak_berat' ? 'BA-RB' : 'BA-PGM';
            $datePart = now()->format('Ymd');
            $countToday = PengajuanPengembalian::where('nomor_ba', 'like', "{$prefix}/{$datePart}/%")->count() + 1;
            $nomorBa = "{$prefix}/{$datePart}/".str_pad((string) $countToday, 3, '0', STR_PAD_LEFT);
            while (PengajuanPengembalian::where('nomor_ba', $nomorBa)->exists()) {
                $countToday++;
                $nomorBa = "{$prefix}/{$datePart}/".str_pad((string) $countToday, 3, '0', STR_PAD_LEFT);
            }

            // 2. Lokasi Gudang BMN & Penanggung Jawab Pengelola BMN
            $gudang = Lokasi::findOrCreateByName('Gudang BMN');

            // 3. Loop Item & Eksekusi Pembaruan BMN
            $items = $locked->items()->with('bmn')->get();

            foreach ($items as $item) {
                /** @var Bmn $bmn */
                $bmn = Bmn::where('id', $item->bmn_id)->lockForUpdate()->first();
                if (! $bmn) {
                    continue;
                }

                if ($locked->alasan === 'rusak_berat') {
                    // Buat Laporan Kerusakan otomatis per item
                    $countTodayLk = LaporanKerusakan::whereDate('created_at', today())->count() + 1;
                    $nomorLk = 'LK/'.date('Ymd').'/'.str_pad((string) $countTodayLk, 3, '0', STR_PAD_LEFT);
                    while (LaporanKerusakan::where('nomor', $nomorLk)->exists()) {
                        $countTodayLk++;
                        $nomorLk = 'LK/'.date('Ymd').'/'.str_pad((string) $countTodayLk, 3, '0', STR_PAD_LEFT);
                    }

                    $lk = LaporanKerusakan::create([
                        'nomor' => $nomorLk,
                        'tanggal' => $item->tanggal_temuan ?: $locked->tanggal,
                        'bmn_id' => $bmn->id,
                        'lokasi_id' => $gudang?->id,
                        'lokasi' => $gudang ? $gudang->nama : 'Gudang BMN',
                        'pelapor_id' => $locked->diajukan_oleh,
                        'pelapor_pegawai_id' => $locked->pegawai_id,
                        'pelapor_nama' => $locked->nama_pegawai,
                        'pelapor_email' => $locked->pemohonUser?->email,
                        'nama_barang' => $bmn->nama_barang,
                        'tingkat' => 'Rusak Berat',
                        'deskripsi' => $item->deskripsi_kerusakan ?: 'Barang diajukan rusak berat melalui Berita Acara '.$nomorBa,
                        'status' => 'Dilaporkan',
                        'sumber' => 'pengajuan',
                        'pengajuan_id' => $locked->id,
                    ]);

                    $item->update(['laporan_kerusakan_id' => $lk->id]);

                    // Update Master BMN
                    $bmn->update([
                        'kondisi' => 'Rusak Berat',
                        'lokasi' => $gudang ? $gudang->nama : 'Gudang BMN',
                        'lokasi_id' => $gudang?->id,
                        'penanggungjawab' => 'Pengelola BMN',
                        'penanggungjawab_nip' => '-',
                        'penanggung_jawab_id' => null,
                        'is_dikembalikan' => true,
                    ]);
                } else {
                    // Pengembalian Biasa: kondisi tetap Baik
                    $bmn->update([
                        'kondisi' => 'Baik',
                        'lokasi' => $gudang ? $gudang->nama : 'Gudang BMN',
                        'lokasi_id' => $gudang?->id,
                        'penanggungjawab' => 'Pengelola BMN',
                        'penanggungjawab_nip' => '-',
                        'penanggung_jawab_id' => null,
                        'is_dikembalikan' => true,
                    ]);
                }

                // Sinkronisasi tabel ekstensi BMN-TIK / BMN-LAB jika ada (mendukung Dual Tracking)
                if ($bmn->jenis === 'BMN-TIK' || $bmn->jenis === 'BMN-TIK & LAB' || $bmn->tik) {
                    BmnTik::where('kode_barang', $bmn->kode_barang)
                        ->where('nup', $bmn->nup)
                        ->update([
                            'lokasi' => $gudang ? $gudang->nama : 'Gudang BMN',
                            'penanggungjawab' => 'Pengelola BMN',
                            'kondisi' => $bmn->kondisi,
                            'is_dikembalikan' => true,
                        ]);
                }
                if ($bmn->jenis === 'BMN-LAB' || $bmn->jenis === 'BMN-TIK & LAB' || $bmn->lab) {
                    BmnLab::where('kode_barang', $bmn->kode_barang)
                        ->where('nup', $bmn->nup)
                        ->update([
                            'lokasi' => $gudang ? $gudang->nama : 'Gudang BMN',
                            'penanggungjawab' => 'Pengelola BMN',
                            'kondisi' => $bmn->kondisi,
                            'is_dikembalikan' => true,
                        ]);
                }
            }

            // 4. Update Header Pengajuan
            $locked->update([
                'status' => 'disetujui',
                'nomor_ba' => $nomorBa,
                'disetujui_oleh' => $adminUser->id,
                'disetujui_pada' => now(),
            ]);

            return true;
        });
    }

    /**
     * Menolak pengajuan oleh admin dengan catatan alasan penolakan.
     */
    public function tolak(PengajuanPengembalian $pengajuan, string $catatan, User $adminUser): bool
    {
        return DB::transaction(function () use ($pengajuan, $catatan, $adminUser) {
            $locked = PengajuanPengembalian::where('id', $pengajuan->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'diajukan') {
                throw new Exception("Pengajuan ini sudah berstatus '{$locked->status}' dan tidak dapat ditolak.");
            }

            $locked->update([
                'status' => 'ditolak',
                'catatan_penolakan' => $catatan,
                'disetujui_oleh' => $adminUser->id,
                'disetujui_pada' => now(),
            ]);

            return true;
        });
    }

    /**
     * Membatalkan pengajuan oleh pemohon selama berstatus 'diajukan'.
     */
    public function batalkan(PengajuanPengembalian $pengajuan, User $user): bool
    {
        return DB::transaction(function () use ($pengajuan, $user) {
            $locked = PengajuanPengembalian::where('id', $pengajuan->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->diajukan_oleh !== $user->id && $user->role !== 'Operator') {
                throw new Exception('Anda tidak memiliki hak untuk membatalkan pengajuan ini.');
            }

            if ($locked->status !== 'diajukan') {
                throw new Exception("Pengajuan berstatus '{$locked->status}' tidak dapat dibatalkan.");
            }

            $locked->update([
                'status' => 'dibatalkan',
            ]);

            return true;
        });
    }
}
