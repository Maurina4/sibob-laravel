<?php

namespace App\Services;

use App\Models\BmnLab;
use App\Models\BmnTik;
use App\Models\DataTerhapus;
use App\Models\Distribusi;
use App\Models\Lokasi;
use App\Models\Pegawai;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DistribusiService
{
    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Distribusi::query()
            ->with(['bmn', 'lokasiAsalRef', 'lokasiTujuanRef', 'penerimaPegawai'])
            ->active()
            ->filter($filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function store(array $validated): Distribusi
    {
        return DB::transaction(function () use ($validated) {
            if (! empty($validated['lokasi_asal'])) {
                $lokasiAsal = Lokasi::findByNameOrCode($validated['lokasi_asal']);
                if ($lokasiAsal) {
                    $validated['lokasi_asal_id'] = $lokasiAsal->id;
                    $validated['lokasi_asal'] = $lokasiAsal->nama;
                }
            }

            if (! empty($validated['lokasi_tujuan'])) {
                $lokasiTujuan = Lokasi::findByNameOrCode($validated['lokasi_tujuan']);
                if ($lokasiTujuan) {
                    $validated['lokasi_tujuan_id'] = $lokasiTujuan->id;
                    $validated['lokasi_tujuan'] = $lokasiTujuan->nama;
                }
            }

            if (! empty($validated['penerima'])) {
                $penerimaStr = $validated['penerima'];
                $pegawaiModel = null;
                if (preg_match('/\((\d+)\)/', $penerimaStr, $matches)) {
                    $pegawaiModel = Pegawai::where('nip', $matches[1])->first();
                }
                if (! $pegawaiModel) {
                    $cleanName = trim(preg_replace('/\s*\(.*?\)\s*/', '', $penerimaStr));
                    $pegawaiModel = Pegawai::where('nama', $cleanName)->orWhere('nama', $penerimaStr)->first();
                }
                if ($pegawaiModel) {
                    $validated['penerima_id'] = $pegawaiModel->id;
                }
            }

            $distribusi = Distribusi::create($validated);

            if (! empty($validated['bmn_id'])) {
                $this->syncLinkedBmn($distribusi, $validated);
            }

            return $distribusi;
        });
    }

    public function update(Distribusi $distribusi, array $validated): bool
    {
        return DB::transaction(function () use ($distribusi, $validated) {
            if (! empty($validated['lokasi_asal'])) {
                $lokasiAsal = Lokasi::findByNameOrCode($validated['lokasi_asal']);
                if ($lokasiAsal) {
                    $validated['lokasi_asal_id'] = $lokasiAsal->id;
                    $validated['lokasi_asal'] = $lokasiAsal->nama;
                }
            }

            if (! empty($validated['lokasi_tujuan'])) {
                $lokasiTujuan = Lokasi::findByNameOrCode($validated['lokasi_tujuan']);
                if ($lokasiTujuan) {
                    $validated['lokasi_tujuan_id'] = $lokasiTujuan->id;
                    $validated['lokasi_tujuan'] = $lokasiTujuan->nama;
                }
            }

            if (! empty($validated['penerima'])) {
                $penerimaStr = $validated['penerima'];
                $pegawaiModel = null;
                if (preg_match('/\((\d+)\)/', $penerimaStr, $matches)) {
                    $pegawaiModel = Pegawai::where('nip', $matches[1])->first();
                }
                if (! $pegawaiModel) {
                    $cleanName = trim(preg_replace('/\s*\(.*?\)\s*/', '', $penerimaStr));
                    $pegawaiModel = Pegawai::where('nama', $cleanName)->orWhere('nama', $penerimaStr)->first();
                }
                if ($pegawaiModel) {
                    $validated['penerima_id'] = $pegawaiModel->id;
                }
            }

            $result = $distribusi->update($validated);

            $this->syncLinkedBmn($distribusi, $validated);

            return $result;
        });
    }

    public function delete(Distribusi $distribusi, ?string $userEmail): bool
    {
        $waktuHapus = now();
        $penghapus = $userEmail ?? 'system';

        return DB::transaction(function () use ($distribusi, $waktuHapus, $penghapus) {
            $distribusi->update([
                'dihapus' => true,
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);
            $distribusi->delete();

            DataTerhapus::create([
                'jenis' => 'Distribusi',
                'data_id' => $distribusi->id,
                'bmn_id' => $distribusi->bmn_id,
                'nama' => $distribusi->nomor ?: ('DIST/'.$distribusi->id),
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);

            return true;
        });
    }

    protected function syncLinkedBmn(Distribusi $distribusi, array $data): void
    {
        $bmn = $distribusi->bmn;
        if (! $bmn && $distribusi->items()->exists()) {
            $bmn = $distribusi->items()->first()?->bmn;
        }

        if (! $bmn) {
            return;
        }

        $bmnUpdate = [];
        if (! empty($data['lokasi_tujuan'])) {
            $bmnUpdate['lokasi'] = $data['lokasi_tujuan'];
            if (isset($data['lokasi_tujuan_id'])) {
                $bmnUpdate['lokasi_id'] = $data['lokasi_tujuan_id'];
            }
        }

        if (! empty($data['penerima'])) {
            $bmnUpdate['penanggungjawab'] = $data['penerima'];
            if (isset($data['penerima_id'])) {
                $bmnUpdate['penanggung_jawab_id'] = $data['penerima_id'];
                $pegawai = Pegawai::find($data['penerima_id']);
                if ($pegawai?->nip) {
                    $bmnUpdate['penanggungjawab_nip'] = $pegawai->nip;
                }
            } elseif (preg_match('/\((\d+)\)/', $data['penerima'], $m)) {
                $bmnUpdate['penanggungjawab_nip'] = $m[1];
            }
        }

        if (! empty($bmnUpdate)) {
            $bmn->update($bmnUpdate);

            $subUpdate = [];
            if (isset($bmnUpdate['lokasi'])) {
                $subUpdate['lokasi'] = $bmnUpdate['lokasi'];
            }
            if (isset($bmnUpdate['penanggungjawab'])) {
                $subUpdate['penanggungjawab'] = $bmnUpdate['penanggungjawab'];
            }

            if (! empty($subUpdate)) {
                BmnTik::where('bmn_id', $bmn->id)
                    ->orWhere(fn ($q) => $q->where('kode_barang', $bmn->kode_barang)->where('nup', (int) $bmn->nup))
                    ->update($subUpdate);

                BmnLab::where('bmn_id', $bmn->id)
                    ->orWhere(fn ($q) => $q->where('kode_barang', $bmn->kode_barang)->where('nup', (int) $bmn->nup))
                    ->update($subUpdate);
            }
        }
    }
}
