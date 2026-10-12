<?php

namespace App\Services;

use App\Models\DataTerhapus;
use App\Models\Pegawai;
use App\Models\Pengembalian;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PengembalianService
{
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Pengembalian::query()
            ->active()
            ->filter($filters)
            ->latest('tanggal')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getFilterData(): array
    {
        return [
            'bidangs' => [
                'Informasi dan Komunikasi',
                'Pengujian',
                'Pemeriksaan',
                'Tata Usaha',
            ],
            'pegawais' => Pegawai::orderBy('nama')->get(),
        ];
    }

    public function store(array $validated): Pengembalian
    {
        $validated['status'] = 'Diajukan';
        $validated['dihapus'] = false;
        $validated['dihapus_pada'] = null;
        $validated['dihapus_oleh'] = null;

        return Pengembalian::create($validated);
    }

    public function update(Pengembalian $pengembalian, array $validated): bool
    {
        return $pengembalian->update($validated);
    }

    public function delete(Pengembalian $pengembalian, ?string $userEmail): bool
    {
        $waktuHapus = now();
        $penghapus = $userEmail ?? 'system';

        return DB::transaction(function () use ($pengembalian, $waktuHapus, $penghapus) {
            $pengembalian->update([
                'dihapus' => true,
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);

            DataTerhapus::create([
                'jenis' => 'Pengembalian BMN',
                'data_id' => $pengembalian->id,
                'nama' => $pengembalian->nomor_pengajuan,
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);

            return true;
        });
    }
}
