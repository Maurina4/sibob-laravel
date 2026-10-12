<?php

namespace App\Services;

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
            'penanggungjawabFilters' => BmnTik::query()
                ->active()
                ->whereNotNull('penanggungjawab')
                ->where('penanggungjawab', '!=', '')
                ->select('penanggungjawab')
                ->distinct()
                ->orderBy('penanggungjawab')
                ->pluck('penanggungjawab'),
            'lokasis' => Lokasi::orderBy('nama')->get(),
            'pegawais' => Pegawai::orderBy('nama')->get(),
        ];
    }

    public function store(array $validated): BmnTik
    {
        $validated['dihapus'] = false;
        return BmnTik::create($validated);
    }

    public function update(BmnTik $bmnTik, array $validated): bool
    {
        return $bmnTik->update($validated);
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

            DataTerhapus::create([
                'jenis' => 'BMN TIK',
                'data_id' => $bmnTik->id,
                'nama' => $bmnTik->nama_barang,
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);

            return true;
        });
    }
}
