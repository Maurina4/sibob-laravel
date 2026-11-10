<?php

namespace App\Services;

use App\Models\Bmn;
use App\Models\BmnAngkutan;
use App\Models\BmnLab;
use App\Models\BmnTik;
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
            ->with('items.bmn')
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
        return DB::transaction(function () use ($validated) {
            $itemsData = $validated['items'] ?? [];
            unset($validated['items']);

            $validated['status'] = 'Diajukan';
            $validated['dihapus'] = false;
            $validated['dihapus_pada'] = null;
            $validated['dihapus_oleh'] = null;

            if (empty($validated['pengaju_id']) && ! empty($validated['nama_pengaju'])) {
                $pegawai = Pegawai::where('nama', $validated['nama_pengaju'])->first()
                    ?? Pegawai::all()->first(fn ($p) => str_contains($validated['nama_pengaju'], $p->nama));
                if ($pegawai) {
                    $validated['pengaju_id'] = $pegawai->id;
                }
            }

            if (empty($validated['kondisi_pengembalian']) && ! empty($itemsData)) {
                $validated['kondisi_pengembalian'] = $itemsData[0]['kondisi'] ?? 'Baik';
            }

            $pengembalian = Pengembalian::create($validated);

            if (! empty($itemsData)) {
                foreach ($itemsData as $item) {
                    if (empty($item['bmn_id'])) {
                        continue;
                    }
                    $bmn = Bmn::find($item['bmn_id']);
                    $rawKondisi = ! empty($item['kondisi']) ? $item['kondisi'] : ($validated['kondisi_pengembalian'] ?? 'Baik');
                    $kondisiFormatted = ucwords(trim($rawKondisi));
                    $kondisiAwal = ! empty($item['kondisi_awal']) ? $item['kondisi_awal'] : ($bmn?->kondisi ?? 'Baik');

                    $pengembalian->items()->create([
                        'bmn_id' => $item['bmn_id'],
                        'nama_barang_snapshot' => $bmn?->nama_barang ?? 'BMN',
                        'kondisi_awal' => $kondisiAwal,
                        'kondisi' => $kondisiFormatted,
                        'keterangan' => $item['keterangan'] ?? null,
                    ]);

                    if ($bmn) {
                        $this->syncMasterBmn($bmn, $kondisiFormatted);
                    }
                }
            }

            return $pengembalian;
        });
    }

    public function update(Pengembalian $pengembalian, array $validated): bool
    {
        return DB::transaction(function () use ($pengembalian, $validated) {
            $itemsData = $validated['items'] ?? null;
            unset($validated['items']);

            if (empty($validated['pengaju_id']) && ! empty($validated['nama_pengaju'])) {
                $pegawai = Pegawai::where('nama', $validated['nama_pengaju'])->first()
                    ?? Pegawai::all()->first(fn ($p) => str_contains($validated['nama_pengaju'], $p->nama));
                if ($pegawai) {
                    $validated['pengaju_id'] = $pegawai->id;
                }
            }

            if (empty($validated['kondisi_pengembalian']) && ! empty($itemsData)) {
                $validated['kondisi_pengembalian'] = $itemsData[0]['kondisi'] ?? ($pengembalian->kondisi_pengembalian ?? 'Baik');
            }

            $pengembalian->update($validated);

            if ($itemsData !== null) {
                $pengembalian->items()->delete();
                foreach ($itemsData as $item) {
                    if (empty($item['bmn_id'])) {
                        continue;
                    }
                    $bmn = Bmn::find($item['bmn_id']);
                    $rawKondisi = ! empty($item['kondisi']) ? $item['kondisi'] : ($pengembalian->kondisi_pengembalian ?? 'Baik');
                    $kondisiFormatted = ucwords(trim($rawKondisi));
                    $kondisiAwal = ! empty($item['kondisi_awal']) ? $item['kondisi_awal'] : ($bmn?->kondisi ?? 'Baik');

                    $pengembalian->items()->create([
                        'bmn_id' => $item['bmn_id'],
                        'nama_barang_snapshot' => $bmn?->nama_barang ?? 'BMN',
                        'kondisi_awal' => $kondisiAwal,
                        'kondisi' => $kondisiFormatted,
                        'keterangan' => $item['keterangan'] ?? null,
                    ]);

                    if ($bmn) {
                        $this->syncMasterBmn($bmn, $kondisiFormatted);
                    }
                }
            }

            return true;
        });
    }

    protected function syncMasterBmn(Bmn $bmn, string $kondisi): void
    {
        $bmn->update([
            'kondisi' => $kondisi,
            'penanggungjawab' => 'Pengelola BMN',
            'penanggung_jawab_id' => null,
            'penanggungjawab_nip' => null,
            'is_dikembalikan' => true,
        ]);

        if (! empty($bmn->kode_barang) && ! empty($bmn->nup)) {
            BmnTik::where('kode_barang', $bmn->kode_barang)
                ->where('nup', $bmn->nup)
                ->update([
                    'kondisi' => $kondisi,
                    'penanggungjawab' => 'Pengelola BMN',
                    'is_dikembalikan' => true,
                ]);

            BmnLab::where('kode_barang', $bmn->kode_barang)
                ->where('nup', $bmn->nup)
                ->update([
                    'kondisi' => $kondisi,
                    'penanggungjawab' => 'Pengelola BMN',
                    'is_dikembalikan' => true,
                ]);

            BmnAngkutan::where('kode_barang', $bmn->kode_barang)
                ->where('nup', $bmn->nup)
                ->update([
                    'kondisi' => $kondisi,
                    'penanggungjawab' => 'Pengelola BMN',
                    'is_dikembalikan' => true,
                ]);
        }
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
