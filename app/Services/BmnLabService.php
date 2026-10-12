<?php

namespace App\Services;

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
            'penanggungjawabFilters' => BmnLab::query()
                ->active()
                ->whereNotNull('penanggungjawab')
                ->where('penanggungjawab', '!=', '')
                ->distinct()
                ->orderBy('penanggungjawab')
                ->pluck('penanggungjawab'),
            'kondisiFilters' => [
                'Baik',
                'Rusak',
                'Perlu Kalibrasi',
            ],
            'statusKalibrasiFilters' => [
                'Belum Dikalibrasi',
                'Berlaku',
                'Jatuh Tempo',
            ],
            'lokasis' => Lokasi::orderBy('nama')->get(),
            'pegawais' => Pegawai::orderBy('nama')->get(),
        ];
    }

    public function store(array $validated): BmnLab
    {
        $validated['dihapus'] = false;
        $validated['dihapus_pada'] = null;
        $validated['dihapus_oleh'] = null;

        return BmnLab::create($validated);
    }

    public function update(BmnLab $bmnLab, array $validated): bool
    {
        return $bmnLab->update($validated);
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

            DataTerhapus::create([
                'jenis' => 'BMN Lab',
                'data_id' => $bmnLab->id,
                'nama' => $bmnLab->nama_barang,
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);

            return true;
        });
    }
}
