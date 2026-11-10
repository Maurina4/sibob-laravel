<?php

namespace App\Services;

use App\Models\Bmn;
use App\Models\BmnAngkutan;
use App\Models\BmnLab;
use App\Models\BmnTik;
use App\Models\DataTerhapus;
use App\Models\LaporanKerusakan;
use App\Models\Lokasi;
use App\Models\Pegawai;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LaporanKerusakanService
{
    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return LaporanKerusakan::query()
            ->with(['bmn', 'pelaporPegawai'])
            ->active()
            ->filter($filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function store(array $validated, $user): LaporanKerusakan
    {
        $validated['pelapor_id'] = $user?->id;
        $validated['status'] = $validated['status'] ?? 'Dilaporkan';

        if ($user && $user->role !== 'Operator') {
            $userPegawai = $user->pegawai ?? Pegawai::where('nama', $user->name)->first();
            if ($userPegawai) {
                $validated['pelapor_pegawai_id'] = $userPegawai->id;
                $validated['pelapor_nama'] = $userPegawai->nama;
            } else {
                $validated['pelapor_nama'] = $user->name;
            }
        } else {
            if (! empty($validated['pelapor_pegawai_id'])) {
                $pegawai = Pegawai::find($validated['pelapor_pegawai_id']);
                if ($pegawai) {
                    $validated['pelapor_nama'] = $pegawai->nama;
                }
            }

            if (empty($validated['pelapor_nama'])) {
                $validated['pelapor_nama'] = $user?->name;
            }
            if (empty($validated['pelapor_email'])) {
                $validated['pelapor_email'] = $user?->email;
            }

            if (empty($validated['pelapor_pegawai_id']) && $user) {
                $pegawai = $user->pegawai ?? Pegawai::where('nama', $user->name)->first();
                if ($pegawai) {
                    $validated['pelapor_pegawai_id'] = $pegawai->id;
                    if (empty($validated['pelapor_nama'])) {
                        $validated['pelapor_nama'] = $pegawai->nama;
                    }
                }
            }
        }

        if (empty($validated['pelapor_email']) && $user) {
            $validated['pelapor_email'] = $user->email;
        }

        if (! empty($validated['lokasi'])) {
            $lokasiModel = Lokasi::findByNameOrCode($validated['lokasi']);
            if ($lokasiModel) {
                $validated['lokasi_id'] = $lokasiModel->id;
                $validated['lokasi'] = $lokasiModel->nama;
            }
        }

        $laporan = LaporanKerusakan::create($validated);

        $this->syncBmnKondisi($laporan);

        return $laporan;
    }

    public function update(LaporanKerusakan $laporanKerusakan, array $validated, $user): bool
    {
        $validated['tanggal_update'] = now();
        if ($user?->role === 'Operator') {
            $validated['ditanggapi_oleh'] = $user?->name ?? $user?->email;
        }

        if (! empty($validated['pelapor_pegawai_id'])) {
            $pegawai = Pegawai::find($validated['pelapor_pegawai_id']);
            if ($pegawai) {
                $validated['pelapor_nama'] = $pegawai->nama;
            }
        }

        if (! empty($validated['lokasi'])) {
            $lokasiModel = Lokasi::findByNameOrCode($validated['lokasi']);
            if ($lokasiModel) {
                $validated['lokasi_id'] = $lokasiModel->id;
                $validated['lokasi'] = $lokasiModel->nama;
            }
        }

        return DB::transaction(function () use ($laporanKerusakan, $validated) {
            $oldBmnId = $laporanKerusakan->bmn_id;

            $laporanKerusakan->update($validated);
            $laporanKerusakan->refresh();

            if ($oldBmnId && $oldBmnId != $laporanKerusakan->bmn_id) {
                $oldBmn = Bmn::find($oldBmnId);
                if ($oldBmn) {
                    $remainingForOld = LaporanKerusakan::query()
                        ->where('bmn_id', $oldBmn->id)
                        ->where('id', '!=', $laporanKerusakan->id)
                        ->active()
                        ->whereIn('status', ['Diterima', 'Diproses'])
                        ->latest()
                        ->first();

                    $oldTarget = $remainingForOld
                        ? match ($remainingForOld->tingkat) {
                            'Rusak Berat', 'Berat' => 'Rusak Berat',
                            'Sedang', 'Rusak Sedang' => 'Rusak Sedang',
                            default => 'Rusak Berat',
                        }
                    : 'Baik';

                    $this->applyKondisiToBmn($oldBmn, $oldTarget);
                }
            }

            $this->syncBmnKondisi($laporanKerusakan);

            return true;
        });
    }

    public function resolveBmn(LaporanKerusakan $laporanKerusakan): ?Bmn
    {
        if ($laporanKerusakan->bmn_id) {
            $bmn = $laporanKerusakan->bmn ?? Bmn::find($laporanKerusakan->bmn_id);
            if ($bmn) {
                return $bmn;
            }
        }

        if (! empty($laporanKerusakan->nama_barang)) {
            $bmn = Bmn::where('nama_barang', $laporanKerusakan->nama_barang)->first();
            if ($bmn) {
                $laporanKerusakan->updateQuietly(['bmn_id' => $bmn->id]);

                return $bmn;
            }
        }

        return null;
    }

    public function syncBmnKondisi(LaporanKerusakan $laporanKerusakan): void
    {
        $bmn = $this->resolveBmn($laporanKerusakan);
        if (! $bmn) {
            return;
        }

        $targetKondisi = null;

        if (in_array($laporanKerusakan->status, ['Diterima', 'Diproses'])) {
            $targetKondisi = match ($laporanKerusakan->tingkat) {
                'Rusak Berat', 'Berat' => 'Rusak Berat',
                'Sedang', 'Rusak Sedang' => 'Rusak Sedang',
                default => 'Rusak Berat',
            };
        } elseif (in_array($laporanKerusakan->status, ['Selesai', 'Ditolak', 'Dilaporkan'])) {
            $otherReport = LaporanKerusakan::query()
                ->where('bmn_id', $bmn->id)
                ->where('id', '!=', $laporanKerusakan->id)
                ->active()
                ->whereIn('status', ['Diterima', 'Diproses'])
                ->latest()
                ->first();

            if ($otherReport) {
                $targetKondisi = match ($otherReport->tingkat) {
                    'Rusak Berat', 'Berat' => 'Rusak Berat',
                    'Sedang', 'Rusak Sedang' => 'Rusak Sedang',
                    default => 'Rusak Berat',
                };
            } else {
                $targetKondisi = 'Baik';
            }
        }

        if ($targetKondisi) {
            $this->applyKondisiToBmn($bmn, $targetKondisi);
        }
    }

    public function applyKondisiToBmn(Bmn $bmn, string $kondisi): void
    {
        $bmn->update(['kondisi' => $kondisi]);

        BmnLab::where('bmn_id', $bmn->id)
            ->update(['kondisi' => $kondisi]);

        if (! empty($bmn->kode_barang) && ! empty($bmn->nup)) {
            BmnLab::where('kode_barang', $bmn->kode_barang)
                ->where('nup', $bmn->nup)
                ->update(['kondisi' => $kondisi]);
        }

        BmnTik::where('bmn_id', $bmn->id)
            ->update(['kondisi' => $kondisi]);

        if (! empty($bmn->kode_barang) && ! empty($bmn->nup)) {
            BmnTik::where('kode_barang', $bmn->kode_barang)
                ->where('nup', $bmn->nup)
                ->update(['kondisi' => $kondisi]);
        }

        BmnAngkutan::where('bmn_id', $bmn->id)
            ->update(['kondisi' => $kondisi]);

        if (! empty($bmn->kode_barang) && ! empty($bmn->nup)) {
            BmnAngkutan::where('kode_barang', $bmn->kode_barang)
                ->where('nup', $bmn->nup)
                ->update(['kondisi' => $kondisi]);
        }
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
                'nama' => $laporanKerusakan->nomor ?? ('Laporan #'.$laporanKerusakan->id),
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);

            $bmn = $this->resolveBmn($laporanKerusakan);
            if ($bmn) {
                $remainingReport = LaporanKerusakan::query()
                    ->where('bmn_id', $bmn->id)
                    ->where('id', '!=', $laporanKerusakan->id)
                    ->active()
                    ->whereIn('status', ['Diterima', 'Diproses'])
                    ->latest()
                    ->first();

                $targetKondisi = $remainingReport
                    ? match ($remainingReport->tingkat) {
                        'Rusak Berat', 'Berat' => 'Rusak Berat',
                        'Sedang', 'Rusak Sedang' => 'Rusak Sedang',
                        default => 'Rusak Berat',
                    }
                : 'Baik';

                $this->applyKondisiToBmn($bmn, $targetKondisi);
            }

            return true;
        });
    }
}
