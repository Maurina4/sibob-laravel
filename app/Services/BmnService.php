<?php

namespace App\Services;

use App\Models\Bmn;
use App\Models\BmnDokumen;
use App\Models\DataTerhapus;
use App\Models\Lokasi;
use App\Models\Pegawai;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BmnService
{
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Bmn::query()
            ->active()
            ->filter($filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getFilterData(): array
    {
        return [
            'jenisFilters' => [
                'BMN-TIK',
                'BMN-LAB',
                'BMN-NON TIK',
            ],
            'kondisis' => Bmn::query()
                ->active()
                ->whereNotNull('kondisi')
                ->where('kondisi', '!=', '')
                ->distinct()
                ->orderBy('kondisi')
                ->pluck('kondisi'),
            'lokasiFilters' => Bmn::query()
                ->active()
                ->whereNotNull('lokasi')
                ->where('lokasi', '!=', '')
                ->distinct()
                ->orderBy('lokasi')
                ->pluck('lokasi'),
            'lokasis' => Lokasi::orderBy('nama')->get(),
            'penanggungjawabs' => Pegawai::orderBy('nama')->get(),
        ];
    }

    public function store(array $validated, Request $request): Bmn
    {
        return DB::transaction(function () use ($validated, $request) {
            $dataBmn = [
                'kode_barang' => $validated['kode_barang'],
                'nup' => $validated['nup'],
                'nama_barang' => $validated['nama_barang'],
                'merek' => $validated['merek'] ?? null,
                'tanggal_terima' => $validated['tanggal_terima'] ?? null,
                'jenis' => $validated['jenis'],
                'satuan' => $validated['satuan'] ?? null,
                'lokasi' => $validated['lokasi'],
                'penanggungjawab' => $validated['penanggungjawab'],
                'penanggungjawab_nip' => $validated['penanggungjawab_nip'] ?? null,
                'kondisi' => $validated['kondisi'],
                'jadwal_pemeliharaan' => $validated['jadwal_pemeliharaan'] ?? null,
                'spesifikasi' => $validated['spesifikasi'] ?? null,
                'dihapus' => false,
                'dihapus_pada' => null,
                'dihapus_oleh' => null,
            ];

            $bmn = Bmn::create($dataBmn);

            foreach (['manual_pengguna', 'panduan_perbaikan', 'instruksi_kerja', 'foto_barang'] as $jenisDoc) {
                if ($request->hasFile($jenisDoc)) {
                    $this->simpanDokumen($bmn, $request->file($jenisDoc), $jenisDoc);
                }
            }

            return $bmn;
        });
    }

    public function update(Bmn $bmn, array $validated): bool
    {
        return $bmn->update($validated);
    }

    public function updateLokasi(Bmn $bmn, string $lokasi): bool
    {
        return $bmn->update(['lokasi' => $lokasi]);
    }

    public function delete(Bmn $bmn, ?string $userEmail): bool
    {
        $waktuHapus = now();
        $penghapus = $userEmail ?? 'system';

        return DB::transaction(function () use ($bmn, $waktuHapus, $penghapus) {
            $bmn->update([
                'dihapus' => true,
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);

            DataTerhapus::create([
                'jenis' => 'BMN',
                'data_id' => $bmn->id,
                'nama' => $bmn->nama_barang,
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);

            return true;
        });
    }

    public function simpanDokumen(Bmn $bmn, $file, string $jenis): BmnDokumen
    {
        $namaFileAsli = $file->getClientOriginalName();
        $namaFileAman = preg_replace('/[^A-Za-z0-9.\_-]/', '_', $namaFileAsli);
        $namaFile = time() . '_' . $namaFileAman;

        $path = $file->storeAs(
            'bmn-dokumen/' . $bmn->id,
            $namaFile,
            'public'
        );

        return BmnDokumen::create([
            'bmn_id' => $bmn->id,
            'jenis' => $jenis,
            'nama_file' => $namaFileAsli,
            'path' => $path,
            'ukuran' => $file->getSize(),
            'tipe_file' => $file->getClientMimeType(),
        ]);
    }
}
