<?php

namespace App\Services;

use App\Models\Bmn;
use App\Models\Lokasi;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LokasiService
{
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Lokasi::query()
            ->filter($filters)
            ->orderedByGedung()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function normalizeKodeRuangan(?string $kode): ?string
    {
        if (empty($kode)) {
            return null;
        }

        $kode = trim($kode);

        // Normalize A1.5 -> A1.05
        if (preg_match('/^([A-Za-z]+)(\d+)\.(\d)$/i', $kode, $m)) {
            $kode = strtoupper($m[1]).$m[2].'.0'.$m[3];
        }
        // Normalize C.Bs.5 -> C.Bs.05 or B.BS.5 -> B.BS.05
        elseif (preg_match('/^([A-Za-z]+)\.([A-Za-z]+)\.(\d)$/i', $kode, $m)) {
            $kode = strtoupper($m[1]).'.'.ucfirst(strtolower($m[2])).'.0'.$m[3];
        } else {
            // Uppercase building prefix if simple A1.xx / B1.xx / C1.xx
            if (preg_match('/^([a-z])(\d+\..*)$/i', $kode, $m)) {
                $kode = strtoupper($m[1]).$m[2];
            }
        }

        return $kode;
    }

    public function store(array $validated): Lokasi
    {
        return Lokasi::create([
            'kode_ruangan' => $this->normalizeKodeRuangan($validated['kode_ruangan'] ?? null),
            'nama' => trim($validated['nama']),
        ]);
    }

    public function update(Lokasi $lokasi, array|string $data): bool
    {
        if (is_string($data)) {
            $data = ['nama' => $data];
        }

        $namaLama = $lokasi->nama;
        $kodeLama = $lokasi->kode_ruangan;
        $namaBaru = trim($data['nama'] ?? $namaLama);
        $kodeBaru = array_key_exists('kode_ruangan', $data)
            ? $this->normalizeKodeRuangan($data['kode_ruangan'] ?? null)
            : $lokasi->kode_ruangan;

        if ($namaLama === $namaBaru && $lokasi->kode_ruangan === $kodeBaru) {
            return false;
        }

        return DB::transaction(function () use ($lokasi, $namaLama, $kodeLama, $namaBaru, $kodeBaru) {
            $lokasi->update([
                'nama' => $namaBaru,
                'kode_ruangan' => $kodeBaru,
            ]);

            // All variations of the old location string that might have been stored in old records or imports:
            $oldVariations = array_values(array_unique(array_filter([
                $namaLama,
                $kodeLama ? "{$kodeLama} {$namaLama}" : null,
                $kodeLama ? "{$kodeLama} - {$namaLama}" : null,
                $kodeLama ? "{$kodeLama}. {$namaLama}" : null,
                $kodeLama ? "# {$lokasi->id} - {$kodeLama} - {$namaLama}" : null,
                "# {$lokasi->id} - {$namaLama}",
            ])));

            // 1. Update BMNs
            Bmn::query()->where(function ($q) use ($lokasi, $oldVariations) {
                $q->where('lokasi_id', $lokasi->id);
                if (! empty($oldVariations)) {
                    $q->orWhereIn('lokasi', $oldVariations);
                }
            })->update([
                'lokasi' => $namaBaru,
                'lokasi_id' => $lokasi->id,
            ]);

            // 2. Update BMN Lab (bmns_lab)
            DB::table('bmns_lab')->where(function ($q) use ($lokasi, $oldVariations) {
                $q->whereIn('bmn_id', function ($sub) use ($lokasi) {
                    $sub->select('id')->from('bmns')->where('lokasi_id', $lokasi->id);
                });
                if (! empty($oldVariations)) {
                    $q->orWhereIn('lokasi', $oldVariations);
                }
            })->update(['lokasi' => $namaBaru]);

            // 3. Update BMN TIK (bmns_tik)
            DB::table('bmns_tik')->where(function ($q) use ($lokasi, $oldVariations) {
                $q->whereIn('bmn_id', function ($sub) use ($lokasi) {
                    $sub->select('id')->from('bmns')->where('lokasi_id', $lokasi->id);
                });
                if (! empty($oldVariations)) {
                    $q->orWhereIn('lokasi', $oldVariations);
                }
            })->update(['lokasi' => $namaBaru]);

            // 4. Update Distribusi (distribusis)
            // Asal
            DB::table('distribusis')->where(function ($q) use ($lokasi, $oldVariations) {
                $q->where('lokasi_asal_id', $lokasi->id);
                if (! empty($oldVariations)) {
                    $q->orWhereIn('lokasi_asal', $oldVariations);
                }
            })->update([
                'lokasi_asal' => $namaBaru,
                'lokasi_asal_id' => $lokasi->id,
            ]);

            // Tujuan
            DB::table('distribusis')->where(function ($q) use ($lokasi, $oldVariations) {
                $q->where('lokasi_tujuan_id', $lokasi->id);
                if (! empty($oldVariations)) {
                    $q->orWhereIn('lokasi_tujuan', $oldVariations);
                }
            })->update([
                'lokasi_tujuan' => $namaBaru,
                'lokasi_tujuan_id' => $lokasi->id,
            ]);

            // 5. Update BA Perpindahan (ba_perpindahans)
            // Dari
            DB::table('ba_perpindahans')->where(function ($q) use ($lokasi, $oldVariations) {
                $q->where('lokasi_asal_id', $lokasi->id);
                if (! empty($oldVariations)) {
                    $q->orWhereIn('dari', $oldVariations);
                }
            })->update([
                'dari' => $namaBaru,
                'lokasi_asal_id' => $lokasi->id,
            ]);

            // Ke
            DB::table('ba_perpindahans')->where(function ($q) use ($lokasi, $oldVariations) {
                $q->where('lokasi_tujuan_id', $lokasi->id);
                if (! empty($oldVariations)) {
                    $q->orWhereIn('ke', $oldVariations);
                }
            })->update([
                'ke' => $namaBaru,
                'lokasi_tujuan_id' => $lokasi->id,
            ]);

            // 6. Update Laporan Kerusakan (laporan_kerusakans)
            DB::table('laporan_kerusakans')->where(function ($q) use ($lokasi, $oldVariations) {
                $q->where('lokasi_id', $lokasi->id);
                if (! empty($oldVariations)) {
                    $q->orWhereIn('lokasi', $oldVariations);
                }
            })->update([
                'lokasi' => $namaBaru,
                'lokasi_id' => $lokasi->id,
            ]);

            return true;
        });
    }

    public function delete(Lokasi $lokasi): array
    {
        $jumlahAset = Bmn::where(function ($q) use ($lokasi) {
            $q->where('lokasi_id', $lokasi->id)
                ->orWhere('lokasi', $lokasi->nama);
        })
            ->where('dihapus', false)
            ->count();

        if ($jumlahAset > 0) {
            return [
                'success' => false,
                'message' => 'Lokasi tidak dapat dihapus karena masih digunakan oleh '.$jumlahAset.' aset BMN.',
            ];
        }

        $lokasi->delete();

        return [
            'success' => true,
            'message' => 'Lokasi berhasil dihapus.',
        ];
    }
}
