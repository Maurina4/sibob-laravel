<?php

namespace App\Services;

use App\Models\Bmn;
use App\Models\BmnTik;
use App\Models\DataTerhapus;
use App\Models\Lokasi;
use App\Models\Pegawai;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BmnTikService
{
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return BmnTik::query()
            ->active()
            ->filter($filters)
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getFilterData(): array
    {
        return [
            'jenisFilters' => BmnTik::query()
                ->active()
                ->whereNotNull('jenis')
                ->where('jenis', '!=', '')
                ->select('jenis')
                ->distinct()
                ->orderBy('jenis')
                ->pluck('jenis'),
            'lokasiFilters' => BmnTik::query()
                ->active()
                ->whereNotNull('lokasi')
                ->where('lokasi', '!=', '')
                ->select('lokasi')
                ->distinct()
                ->orderBy('lokasi')
                ->pluck('lokasi'),
            'lokasis' => Lokasi::orderedByGedung()->get(),
            'penanggungjawabFilters' => BmnTik::query()
                ->active()
                ->whereNotNull('penanggungjawab')
                ->where('penanggungjawab', '!=', '')
                ->select('penanggungjawab')
                ->distinct()
                ->orderBy('penanggungjawab')
                ->pluck('penanggungjawab'),
            'kondisiFilters' => BmnTik::query()
                ->active()
                ->whereNotNull('kondisi')
                ->where('kondisi', '!=', '')
                ->select('kondisi')
                ->distinct()
                ->orderBy('kondisi')
                ->pluck('kondisi'),
            'lokasis' => Lokasi::orderedByGedung()->get(),
            'pegawais' => Pegawai::orderBy('nama')->get(),
        ];
    }

    public function store(array $validated): BmnTik
    {
        return DB::transaction(function () use ($validated) {
            $validated['dihapus'] = false;

            // Pastikan unique kode_barang + nup
            $kodeBarang = $validated['kode_barang'] ?: ('TIK-'.rand(1000, 9999));
            $nup = isset($validated['nup']) && $validated['nup'] !== '' ? (string) $validated['nup'] : (string) rand(100, 999);

            while (Bmn::where('kode_barang', $kodeBarang)->where('nup', $nup)->exists()) {
                $nup = (string) ((int) $nup + 1);
            }

            $kondisi = $validated['kondisi'] ?? 'Baik';
            $lokasiName = trim($validated['lokasi'] ?? 'Ruang TIK');
            $lokasiModel = $lokasiName !== '' ? Lokasi::findOrCreateByName($lokasiName) : null;

            $bmn = Bmn::create([
                'kode_barang' => $kodeBarang,
                'nup' => $nup,
                'no_seri' => $validated['no_seri'] ?? null,
                'nama_barang' => $validated['nama_barang'],
                'tahun_perolehan' => $validated['tahun_perolehan'] ?? null,
                'nilai_perolehan' => $validated['nilai_perolehan'] ?? null,
                'jenis' => 'BMN-TIK',
                'lokasi' => $lokasiModel ? $lokasiModel->nama : $lokasiName,
                'lokasi_id' => $lokasiModel?->id,
                'penanggungjawab' => $validated['penanggungjawab'] ?? 'Pengelola BMN',
                'kondisi' => $kondisi,
                'dihapus' => false,
            ]);

            $validated['bmn_id'] = $bmn->id;
            $validated['kode_barang'] = $kodeBarang;
            $validated['nup'] = (int) $nup;
            $validated['kondisi'] = $kondisi;
            $validated['lokasi'] = $lokasiModel ? $lokasiModel->nama : $lokasiName;

            app(BmnService::class)->createAutomaticDistribusiIfNeeded($bmn);

            return BmnTik::create($validated);
        });
    }

    public function update(BmnTik $bmnTik, array $validated): bool
    {
        return DB::transaction(function () use ($bmnTik, $validated) {
            $lokasiId = null;
            if (! empty($validated['lokasi'])) {
                $lokasiModel = Lokasi::findOrCreateByName($validated['lokasi']);
                $validated['lokasi'] = $lokasiModel->nama;
                $lokasiId = $lokasiModel->id;
            }

            $bmnTik->update($validated);

            if ($bmnTik->bmn_id && $bmn = Bmn::find($bmnTik->bmn_id)) {
                $bmnUpdate = array_filter([
                    'kode_barang' => $validated['kode_barang'] ?? $bmn->kode_barang,
                    'nup' => isset($validated['nup']) ? (string) $validated['nup'] : $bmn->nup,
                    'no_seri' => array_key_exists('no_seri', $validated) ? $validated['no_seri'] : $bmn->no_seri,
                    'nama_barang' => $validated['nama_barang'] ?? $bmn->nama_barang,
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

    public function delete(BmnTik $bmnTik, ?string $userEmail): bool
    {
        $waktuHapus = now();
        $penghapus = $userEmail ?? 'system';

        return DB::transaction(function () use ($bmnTik, $waktuHapus, $penghapus) {
            $bmnTik->update([
                'dihapus' => true,
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);

            if ($bmnTik->bmn_id) {
                Bmn::where('id', $bmnTik->bmn_id)->update([
                    'dihapus' => true,
                    'dihapus_pada' => $waktuHapus,
                    'dihapus_oleh' => $penghapus,
                ]);
            }

            DataTerhapus::create([
                'jenis' => 'BMN TIK',
                'data_id' => $bmnTik->id,
                'bmn_id' => $bmnTik->bmn_id,
                'nama' => $bmnTik->nama_barang,
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);

            return true;
        });
    }
}
