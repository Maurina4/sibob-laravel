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
            ->orderBy('nama')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function store(array $validated): Lokasi
    {
        return Lokasi::create([
            'nama' => $validated['nama'],
        ]);
    }

    public function update(Lokasi $lokasi, string $namaBaru): bool
    {
        $namaLama = $lokasi->nama;

        if ($namaLama === $namaBaru) {
            return false;
        }

        return DB::transaction(function () use ($lokasi, $namaLama, $namaBaru) {
            $lokasi->update(['nama' => $namaBaru]);

            Bmn::where('lokasi', $namaLama)->update(['lokasi' => $namaBaru]);

            return true;
        });
    }

    public function delete(Lokasi $lokasi): array
    {
        $jumlahAset = Bmn::where('lokasi', $lokasi->nama)
            ->where('dihapus', false)
            ->count();

        if ($jumlahAset > 0) {
            return [
                'success' => false,
                'message' => 'Lokasi tidak dapat dihapus karena masih digunakan oleh ' . $jumlahAset . ' aset BMN.',
            ];
        }

        $lokasi->delete();

        return [
            'success' => true,
            'message' => 'Lokasi berhasil dihapus.',
        ];
    }
}
