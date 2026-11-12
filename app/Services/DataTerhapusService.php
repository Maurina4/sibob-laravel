<?php

namespace App\Services;

use App\Models\BaPerpindahan;
use App\Models\Bmn;
use App\Models\BmnAngkutan;
use App\Models\BmnLab;
use App\Models\BmnTik;
use App\Models\DataTerhapus;
use App\Models\Distribusi;
use App\Models\LaporanKerusakan;
use App\Models\PengajuanPengembalian;
use App\Models\Pengembalian;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DataTerhapusService
{
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return DataTerhapus::query()
            ->filter($filters)
            ->latest('dihapus_pada')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getJenisList(): Collection
    {
        return DataTerhapus::query()
            ->whereNotNull('jenis')
            ->where('jenis', '!=', '')
            ->distinct()
            ->orderBy('jenis')
            ->pluck('jenis');
    }

    public function restore(DataTerhapus $dataTerhapus): array
    {
        return DB::transaction(function () use ($dataTerhapus) {
            $jenis = $dataTerhapus->jenis;
            $bmnId = $dataTerhapus->bmn_id;
            $dataId = $dataTerhapus->data_id;

            $bmn = null;
            $tik = null;
            $lab = null;
            $angkutan = null;
            $otherModel = null;

            if (in_array($jenis, ['BMN', 'BMN Umum', 'BMN-NON TIK'])) {
                $bmn = Bmn::withTrashed()->find($dataId) ?? ($bmnId ? Bmn::withTrashed()->find($bmnId) : null);
                if ($bmn) {
                    $bmnId = $bmn->id;
                    $tik = $bmn->tik;
                    $lab = $bmn->lab;
                    $angkutan = $bmn->angkutan;
                }
            } elseif (in_array($jenis, ['BMN TIK', 'BMN-TIK'])) {
                $tik = BmnTik::find($dataId);
                if ($tik) {
                    $bmnId = $tik->bmn_id ?? $bmnId;
                    $bmn = $tik->bmn ?? ($bmnId ? Bmn::withTrashed()->find($bmnId) : null);
                } elseif ($bmnId) {
                    $bmn = Bmn::withTrashed()->find($bmnId);
                    $tik = $bmn?->tik;
                } elseif ($fallbackBmn = Bmn::withTrashed()->find($dataId)) {
                    $bmn = $fallbackBmn;
                    $bmnId = $bmn->id;
                    $tik = $bmn->tik;
                }
            } elseif (in_array($jenis, ['BMN Lab', 'BMN-LAB'])) {
                $lab = BmnLab::find($dataId);
                if ($lab) {
                    $bmnId = $lab->bmn_id ?? $bmnId;
                    $bmn = $lab->bmn ?? ($bmnId ? Bmn::withTrashed()->find($bmnId) : null);
                } elseif ($bmnId) {
                    $bmn = Bmn::withTrashed()->find($bmnId);
                    $lab = $bmn?->lab;
                } elseif ($fallbackBmn = Bmn::withTrashed()->find($dataId)) {
                    $bmn = $fallbackBmn;
                    $bmnId = $bmn->id;
                    $lab = $bmn->lab;
                }
            } elseif (in_array($jenis, ['BMN Angkutan', 'BMN-ANGKUTAN', 'Alat Angkutan Bermotor'])) {
                $angkutan = BmnAngkutan::find($dataId);
                if ($angkutan) {
                    $bmnId = $angkutan->bmn_id ?? $bmnId;
                    $bmn = $angkutan->bmn ?? ($bmnId ? Bmn::withTrashed()->find($bmnId) : null);
                } elseif ($bmnId) {
                    $bmn = Bmn::withTrashed()->find($bmnId);
                    $angkutan = $bmn?->angkutan;
                } elseif ($fallbackBmn = Bmn::withTrashed()->find($dataId)) {
                    $bmn = $fallbackBmn;
                    $bmnId = $bmn->id;
                    $angkutan = $bmn->angkutan;
                }
            } else {
                $otherModel = match ($jenis) {
                    'Pengembalian', 'Pengembalian BMN' => Pengembalian::withTrashed()->find($dataId),
                    'Pengajuan Pengembalian' => PengajuanPengembalian::withTrashed()->find($dataId),
                    'BA Perpindahan' => BaPerpindahan::withTrashed()->find($dataId),
                    'Distribusi' => Distribusi::withTrashed()->find($dataId),
                    'Laporan Kerusakan' => LaporanKerusakan::withTrashed()->find($dataId),
                    default => null,
                };

                if ($otherModel === null && ! in_array($jenis, ['Pengembalian', 'Pengembalian BMN', 'Pengajuan Pengembalian', 'BA Perpindahan', 'Distribusi', 'Laporan Kerusakan'])) {
                    return [
                        'success' => false,
                        'message' => 'Jenis data tidak dikenali: '.$jenis,
                    ];
                }
            }

            $restorePayload = [
                'dihapus' => false,
                'dihapus_pada' => null,
                'dihapus_oleh' => null,
                'deleted_at' => null,
                'deleted_by' => null,
            ];

            if ($bmn) {
                $bmn->update($restorePayload);
            }
            if ($tik) {
                $tik->update([
                    'dihapus' => false,
                    'dihapus_pada' => null,
                    'dihapus_oleh' => null,
                ]);
            }
            if ($lab) {
                $lab->update([
                    'dihapus' => false,
                    'dihapus_pada' => null,
                    'dihapus_oleh' => null,
                ]);
            }
            if ($angkutan) {
                $angkutan->update([
                    'dihapus' => false,
                    'dihapus_pada' => null,
                    'dihapus_oleh' => null,
                ]);
            }
            if ($otherModel) {
                if (method_exists($otherModel, 'restore')) {
                    $otherModel->restore();
                }
                $otherModel->update($restorePayload);
                if ($otherModel instanceof LaporanKerusakan) {
                    app(LaporanKerusakanService::class)->syncBmnKondisi($otherModel);
                }
            }

            if ($bmnId) {
                DataTerhapus::where('bmn_id', $bmnId)->delete();
            }
            $dataTerhapus->delete();

            return [
                'success' => true,
                'message' => 'Data berhasil dipulihkan dan dikembalikan ke menu asal.',
            ];
        });
    }

    public function forceDelete(DataTerhapus $dataTerhapus): array
    {
        return DB::transaction(function () use ($dataTerhapus) {
            $nama = $dataTerhapus->nama;
            $jenis = $dataTerhapus->jenis;

            $this->performPermanentDelete($dataTerhapus);

            return [
                'success' => true,
                'message' => "Data '{$nama}' ({$jenis}) berhasil dihapus secara permanen.",
            ];
        });
    }

    public function forceDeleteAll(?array $ids = null): array
    {
        return DB::transaction(function () use ($ids) {
            $query = DataTerhapus::query();
            if (! empty($ids)) {
                $query->whereIn('id', $ids);
            }
            $items = $query->get();
            $count = $items->count();

            if ($count === 0) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada data terhapus yang dipilih untuk dihapus.',
                ];
            }

            foreach ($items as $item) {
                if (! DataTerhapus::where('id', $item->id)->exists()) {
                    continue;
                }
                $this->performPermanentDelete($item);
            }

            if (empty($ids)) {
                DataTerhapus::truncate();
            }

            return [
                'success' => true,
                'message' => empty($ids)
                    ? "Semua data terhapus ({$count} data) berhasil dibersihkan secara permanen."
                    : "Sebanyak {$count} data terhapus berhasil dihapus secara permanen.",
            ];
        });
    }

    protected function performPermanentDelete(DataTerhapus $dataTerhapus): void
    {
        $jenis = $dataTerhapus->jenis;
        $bmnId = $dataTerhapus->bmn_id;
        $dataId = $dataTerhapus->data_id;

        if (in_array($jenis, ['BMN', 'BMN Umum', 'BMN-NON TIK'])) {
            $bmn = Bmn::withTrashed()->find($dataId) ?? ($bmnId ? Bmn::withTrashed()->find($bmnId) : null);
            if ($bmn) {
                $bmnId = $bmn->id;
                $this->deleteBmnComplete($bmn);
            }
        } elseif (in_array($jenis, ['BMN TIK', 'BMN-TIK'])) {
            $tik = BmnTik::find($dataId);
            $bmnId = $tik?->bmn_id ?? $bmnId;
            $bmn = $tik?->bmn ?? ($bmnId ? Bmn::withTrashed()->find($bmnId) : null);

            if ($bmn) {
                $this->deleteBmnComplete($bmn);
            } else {
                $tik?->delete();
            }
        } elseif (in_array($jenis, ['BMN Lab', 'BMN-LAB'])) {
            $lab = BmnLab::find($dataId);
            $bmnId = $lab?->bmn_id ?? $bmnId;
            $bmn = $lab?->bmn ?? ($bmnId ? Bmn::withTrashed()->find($bmnId) : null);

            if ($bmn) {
                $this->deleteBmnComplete($bmn);
            } else {
                $lab?->delete();
            }
        } elseif (in_array($jenis, ['BMN Angkutan', 'BMN-ANGKUTAN', 'Alat Angkutan Bermotor'])) {
            $angkutan = BmnAngkutan::find($dataId);
            $bmnId = $angkutan?->bmn_id ?? $bmnId;
            $bmn = $angkutan?->bmn ?? ($bmnId ? Bmn::withTrashed()->find($bmnId) : null);

            if ($bmn) {
                $this->deleteBmnComplete($bmn);
            } else {
                $angkutan?->delete();
            }
        } else {
            match ($jenis) {
                'Pengembalian', 'Pengembalian BMN' => tap(Pengembalian::withTrashed()->find($dataId), function ($m) {
                    $m?->items()->delete();
                    $m?->forceDelete();
                }),
                'Pengajuan Pengembalian' => tap(PengajuanPengembalian::withTrashed()->find($dataId), function ($m) {
                    $m?->items()->delete();
                    $m?->forceDelete();
                }),
                'BA Perpindahan' => tap(BaPerpindahan::withTrashed()->find($dataId), function ($m) {
                    $m?->items()->delete();
                    $m?->forceDelete();
                }),
                'Distribusi' => tap(Distribusi::withTrashed()->find($dataId), function ($m) {
                    $m?->items()->delete();
                    $m?->forceDelete();
                }),
                'Laporan Kerusakan' => LaporanKerusakan::withTrashed()->find($dataId)?->forceDelete(),
                default => null,
            };
        }

        if ($bmnId) {
            DataTerhapus::where('bmn_id', $bmnId)->delete();
        }
        $dataTerhapus->delete();
    }

    protected function deleteBmnComplete(Bmn $bmn): void
    {
        $bmn->tik?->delete();
        $bmn->lab?->delete();
        $bmn->angkutan?->delete();

        foreach ($bmn->dokumens as $dokumen) {
            if ($dokumen->path && Storage::disk('public')->exists($dokumen->path)) {
                Storage::disk('public')->delete($dokumen->path);
            }
            $dokumen->delete();
        }
        Storage::disk('public')->deleteDirectory('bmn-dokumen/'.$bmn->id);

        $bmn->forceDelete();
    }
}
