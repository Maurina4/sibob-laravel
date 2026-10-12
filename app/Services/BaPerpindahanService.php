<?php

namespace App\Services;

use App\Models\BaPerpindahan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BaPerpindahanService
{
    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return BaPerpindahan::query()
            ->active()
            ->latest()
            ->paginate($perPage);
    }

    public function store(array $validated, $user): BaPerpindahan
    {
        $validated['pengaju_email'] = $user?->email;
        $validated['pengaju_nama'] = $user?->name;
        $validated['status'] = 'Diajukan';

        return BaPerpindahan::create($validated);
    }

    public function update(BaPerpindahan $baPerpindahan, array $validated): bool
    {
        return $baPerpindahan->update($validated);
    }

    public function delete(BaPerpindahan $baPerpindahan, ?string $userEmail): bool
    {
        return $baPerpindahan->update([
            'dihapus' => true,
            'dihapus_pada' => now(),
            'dihapus_oleh' => $userEmail ?? 'system',
        ]);
    }

    public function approve(BaPerpindahan $baPerpindahan, $user, ?string $catatan = null): bool
    {
        return $baPerpindahan->update([
            'status' => 'Disetujui',
            'catatan_operator' => $catatan,
            'tanggal_keputusan' => now(),
            'diputuskan_oleh' => $user?->name ?? 'Operator',
        ]);
    }

    public function reject(BaPerpindahan $baPerpindahan, $user, ?string $catatan = null): bool
    {
        return $baPerpindahan->update([
            'status' => 'Ditolak',
            'catatan_operator' => $catatan,
            'tanggal_keputusan' => now(),
            'diputuskan_oleh' => $user?->name ?? 'Operator',
        ]);
    }
}
