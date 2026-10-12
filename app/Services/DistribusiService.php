<?php

namespace App\Services;

use App\Models\Distribusi;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DistribusiService
{
    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return Distribusi::query()
            ->active()
            ->latest()
            ->paginate($perPage);
    }

    public function store(array $validated): Distribusi
    {
        $validated['status'] = 'Diajukan';

        return Distribusi::create($validated);
    }

    public function update(Distribusi $distribusi, array $validated): bool
    {
        return $distribusi->update($validated);
    }

    public function delete(Distribusi $distribusi, ?string $userEmail): bool
    {
        return $distribusi->update([
            'dihapus' => true,
            'dihapus_pada' => now(),
            'dihapus_oleh' => $userEmail ?? 'system',
        ]);
    }
}
