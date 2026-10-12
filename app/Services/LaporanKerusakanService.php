<?php

namespace App\Services;

use App\Models\DataTerhapus;
use App\Models\LaporanKerusakan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LaporanKerusakanService
{
    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return LaporanKerusakan::query()
            ->active()
            ->latest()
            ->paginate($perPage);
    }

    public function store(array $validated, $user): LaporanKerusakan
    {
        $validated['pelapor_email'] = $user?->email;
        $validated['pelapor_nama'] = $user?->name;
        $validated['status'] = 'Dilaporkan';

        return LaporanKerusakan::create($validated);
    }

    public function update(LaporanKerusakan $laporanKerusakan, array $validated, $user): bool
    {
        $validated['tanggal_update'] = now();
        $validated['ditanggapi_oleh'] = $user?->email;

        return $laporanKerusakan->update($validated);
    }

    public function delete(LaporanKerusakan $laporanKerusakan, ?string $userEmail): bool
    {
        $waktuHapus = now();
        $penghapus = $userEmail ?? 'system';

        return DB::transaction(function () use ($laporanKerusakan, $waktuHapus, $penghapus) {
            $laporanKerusakan->update([
                'dihapus' => true,
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);

            DataTerhapus::create([
                'jenis' => 'Laporan Kerusakan',
                'data_id' => $laporanKerusakan->id,
                'nama' => $laporanKerusakan->nomor ?? ('Laporan #' . $laporanKerusakan->id),
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);

            return true;
        });
    }
}
