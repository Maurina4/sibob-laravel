<?php

namespace App\Services;

use App\Models\Bmn;
use App\Models\BmnLab;
use App\Models\DataTerhapus;
use App\Models\Lokasi;
use App\Models\Pegawai;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BmnLabService
{
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return BmnLab::query()
            ->active()
            ->filter($filters)
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getFilterData(): array
    {
        return [
            'lokasiFilters' => BmnLab::query()
                ->active()
                ->whereNotNull('lokasi')
                ->where('lokasi', '!=', '')
                ->distinct()
                ->orderBy('lokasi')
                ->pluck('lokasi'),
            'lokasis' => Lokasi::orderedByGedung()->get(),
            'penanggungjawabFilters' => BmnLab::query()
                ->active()
                ->whereNotNull('penanggungjawab')
                ->where('penanggungjawab', '!=', '')
                ->distinct()
                ->orderBy('penanggungjawab')
                ->pluck('penanggungjawab'),
            'kondisiFilters' => [
                'Baik',
                'Rusak Berat',
                'Perlu Kalibrasi',
            ],
            'statusKalibrasiFilters' => [
                'Belum Dikalibrasi',
                'Berlaku',
                'Jatuh Tempo',
            ],
            'lokasis' => Lokasi::orderedByGedung()->get(),
            'pegawais' => Pegawai::orderBy('nama')->get(),
            'penanggungjawabs' => Pegawai::orderBy('nama')->get(),
        ];
    }

    public function store(array $validated): BmnLab
    {
        return DB::transaction(function () use ($validated) {
            $validated['dihapus'] = false;
            $validated['dihapus_pada'] = null;
            $validated['dihapus_oleh'] = null;

            // Pastikan unique kode_barang + nup
            $kodeBarang = $validated['kode_barang'] ?: ('LAB-'.rand(1000, 9999));
            $nup = isset($validated['nup']) && $validated['nup'] !== '' ? (string) $validated['nup'] : (string) rand(100, 999);

            while (Bmn::where('kode_barang', $kodeBarang)->where('nup', $nup)->exists()) {
                $nup = (string) ((int) $nup + 1);
            }

            $lokasiName = trim($validated['lokasi'] ?? 'Laboratorium');
            $lokasiModel = $lokasiName !== '' ? Lokasi::findOrCreateByName($lokasiName) : null;

            $bmn = Bmn::create([
                'kode_barang' => $kodeBarang,
                'nup' => $nup,
                'no_seri' => $validated['no_seri'] ?? null,
                'nama_barang' => $validated['nama_barang'],
                'merek' => $validated['merek'] ?? null,
                'tahun_perolehan' => $validated['tahun_perolehan'] ?? null,
                'nilai_perolehan' => $validated['nilai_perolehan'] ?? null,
                'jenis' => 'BMN-LAB',
                'lokasi' => $lokasiModel ? $lokasiModel->nama : $lokasiName,
                'lokasi_id' => $lokasiModel?->id,
                'penanggungjawab' => $validated['penanggungjawab'] ?? 'Pengelola BMN',
                'kondisi' => $validated['kondisi'] ?? 'Baik',
                'dihapus' => false,
            ]);

            $validated['bmn_id'] = $bmn->id;
            $validated['kode_barang'] = $kodeBarang;
            $validated['nup'] = (int) $nup;
            $validated['lokasi'] = $lokasiModel ? $lokasiModel->nama : $lokasiName;

            app(BmnService::class)->createAutomaticDistribusiIfNeeded($bmn);

            return BmnLab::create($validated);
        });
    }

    public function update(BmnLab $bmnLab, array $validated): bool
    {
        return DB::transaction(function () use ($bmnLab, $validated) {
            $lokasiId = null;
            if (! empty($validated['lokasi'])) {
                $lokasiModel = Lokasi::findOrCreateByName($validated['lokasi']);
                $validated['lokasi'] = $lokasiModel->nama;
                $lokasiId = $lokasiModel->id;
            }

            $bmnLab->update($validated);

            if ($bmnLab->bmn_id && $bmn = Bmn::find($bmnLab->bmn_id)) {
                $bmnUpdate = array_filter([
                    'kode_barang' => $validated['kode_barang'] ?? $bmn->kode_barang,
                    'nup' => isset($validated['nup']) ? (string) $validated['nup'] : $bmn->nup,
                    'no_seri' => array_key_exists('no_seri', $validated) ? $validated['no_seri'] : $bmn->no_seri,
                    'nama_barang' => $validated['nama_barang'] ?? $bmn->nama_barang,
                    'merek' => $validated['merek'] ?? $bmn->merek,
                    'tahun_perolehan' => array_key_exists('tahun_perolehan', $validated) ? $validated['tahun_perolehan'] : $bmn->tahun_perolehan,
                    'nilai_perolehan' => array_key_exists('nilai_perolehan', $validated) ? $validated['nilai_perolehan'] : $bmn->nilai_perolehan,
                    'lokasi' => $validated['lokasi'] ?? $bmn->lokasi,
                    'penanggungjawab' => $validated['penanggungjawab'] ?? $bmn->penanggungjawab,
                    'kondisi' => $validated['kondisi'] ?? $bmn->kondisi,
                ], fn ($v) => $v !== null);

                if ($lokasiId !== null) {
                    $bmnUpdate['lokasi_id'] = $lokasiId;
                }

                $bmn->update($bmnUpdate);
            }

            return true;
        });
    }

    public function delete(BmnLab $bmnLab, ?string $userEmail): bool
    {
        $waktuHapus = now();
        $penghapus = $userEmail ?? 'system';

        return DB::transaction(function () use ($bmnLab, $waktuHapus, $penghapus) {
            $bmnLab->update([
                'dihapus' => true,
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);

            if ($bmnLab->bmn_id) {
                Bmn::where('id', $bmnLab->bmn_id)->update([
                    'dihapus' => true,
                    'dihapus_pada' => $waktuHapus,
                    'dihapus_oleh' => $penghapus,
                ]);
            }

            DataTerhapus::create([
                'jenis' => 'BMN Lab',
                'data_id' => $bmnLab->id,
                'bmn_id' => $bmnLab->bmn_id,
                'nama' => $bmnLab->nama_barang,
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);

            return true;
        });
    }
}
