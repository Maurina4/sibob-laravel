<?php

namespace App\Services;

use App\Models\Bmn;
use App\Models\BmnAngkutan;
use App\Models\DataTerhapus;
use App\Models\Lokasi;
use App\Models\Pegawai;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BmnAngkutanService
{
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return BmnAngkutan::query()
            ->active()
            ->filter($filters)
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getFilterData(): array
    {
        return [
            'lokasiFilters' => BmnAngkutan::query()
                ->active()
                ->whereNotNull('lokasi')
                ->where('lokasi', '!=', '')
                ->distinct()
                ->orderBy('lokasi')
                ->pluck('lokasi'),
            'lokasis' => Lokasi::orderedByGedung()->get(),
            'penanggungjawabFilters' => BmnAngkutan::query()
                ->active()
                ->whereNotNull('penanggungjawab')
                ->where('penanggungjawab', '!=', '')
                ->distinct()
                ->orderBy('penanggungjawab')
                ->pluck('penanggungjawab'),
            'kondisiFilters' => [
                'Baik',
                'Rusak Berat',
            ],
            'pegawais' => Pegawai::orderBy('nama')->get(),
        ];
    }

    public function store(array $validated): BmnAngkutan
    {
        return DB::transaction(function () use ($validated) {
            $validated['dihapus'] = false;
            $validated['dihapus_pada'] = null;
            $validated['dihapus_oleh'] = null;

            // Pastikan unique kode_barang + nup
            $kodeBarang = $validated['kode_barang'] ?: ('ANG-'.rand(1000, 9999));
            $nup = isset($validated['nup']) && $validated['nup'] !== '' ? (string) $validated['nup'] : (string) rand(100, 999);

            while (Bmn::where('kode_barang', $kodeBarang)->where('nup', $nup)->exists()) {
                $nup = (string) ((int) $nup + 1);
            }

            $lokasiName = trim($validated['lokasi'] ?? 'Garasi / Parkir BMN');
            $lokasiModel = $lokasiName !== '' ? Lokasi::findOrCreateByName($lokasiName) : null;

            $bmn = Bmn::create([
                'kode_barang' => $kodeBarang,
                'nup' => $nup,
                'nama_barang' => $validated['nama_barang'],
                'merek' => $validated['merek'] ?? null,
                'no_polisi' => $validated['no_polisi'] ?? null,
                'tahun_perolehan' => $validated['tahun_perolehan'] ?? null,
                'nilai_perolehan' => $validated['nilai_perolehan'] ?? null,
                'jenis' => 'BMN-ANGKUTAN',
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

            return BmnAngkutan::create($validated);
        });
    }

    public function update(BmnAngkutan $bmnAngkutan, array $validated): bool
    {
        return DB::transaction(function () use ($bmnAngkutan, $validated) {
            $lokasiId = null;
            if (! empty($validated['lokasi'])) {
                $lokasiModel = Lokasi::findOrCreateByName($validated['lokasi']);
                $validated['lokasi'] = $lokasiModel->nama;
                $lokasiId = $lokasiModel->id;
            }

            $bmnAngkutan->update($validated);

            if ($bmnAngkutan->bmn_id && $bmn = Bmn::find($bmnAngkutan->bmn_id)) {
                $bmnUpdate = array_filter([
                    'kode_barang' => $validated['kode_barang'] ?? $bmn->kode_barang,
                    'nup' => isset($validated['nup']) ? (string) $validated['nup'] : $bmn->nup,
                    'nama_barang' => $validated['nama_barang'] ?? $bmn->nama_barang,
                    'merek' => $validated['merek'] ?? $bmn->merek,
                    'no_polisi' => $validated['no_polisi'] ?? $bmn->no_polisi,
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

    public function delete(BmnAngkutan $bmnAngkutan, ?string $userEmail): bool
    {
        $waktuHapus = now();
        $penghapus = $userEmail ?? 'system';

        return DB::transaction(function () use ($bmnAngkutan, $waktuHapus, $penghapus) {
            $bmnAngkutan->update([
                'dihapus' => true,
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);

            if ($bmnAngkutan->bmn_id) {
                Bmn::where('id', $bmnAngkutan->bmn_id)->update([
                    'dihapus' => true,
                    'dihapus_pada' => $waktuHapus,
                    'dihapus_oleh' => $penghapus,
                ]);
            }

            DataTerhapus::create([
                'jenis' => 'Alat Angkutan Bermotor',
                'data_id' => $bmnAngkutan->id,
                'bmn_id' => $bmnAngkutan->bmn_id,
                'nama' => $bmnAngkutan->nama_barang . ' (' . $bmnAngkutan->no_polisi . ')',
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);

            return true;
        });
    }
}
