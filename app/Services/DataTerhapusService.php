<?php

namespace App\Services;

use App\Models\BaPerpindahan;
use App\Models\Bmn;
use App\Models\BmnLab;
use App\Models\BmnTik;
use App\Models\DataTerhapus;
use App\Models\Distribusi;
use App\Models\LaporanKerusakan;
use App\Models\Pengembalian;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
            $model = match ($dataTerhapus->jenis) {
                'BMN' => Bmn::find($dataTerhapus->data_id),
                'BMN TIK' => BmnTik::find($dataTerhapus->data_id),
                'BMN Lab' => BmnLab::find($dataTerhapus->data_id),
                'Pengembalian', 'Pengembalian BMN' => Pengembalian::find($dataTerhapus->data_id),
                'BA Perpindahan' => BaPerpindahan::find($dataTerhapus->data_id),
                'Distribusi' => Distribusi::find($dataTerhapus->data_id),
                'Laporan Kerusakan' => LaporanKerusakan::find($dataTerhapus->data_id),
                default => null,
            };

            if ($model === null && !in_array($dataTerhapus->jenis, ['BMN', 'BMN TIK', 'BMN Lab', 'Pengembalian', 'Pengembalian BMN', 'BA Perpindahan', 'Distribusi', 'Laporan Kerusakan'])) {
                return [
                    'success' => false,
                    'message' => 'Jenis data tidak dikenali: ' . $dataTerhapus->jenis,
                ];
            }

            if ($model) {
                $model->update([
                    'dihapus' => false,
                    'dihapus_pada' => null,
                    'dihapus_oleh' => null,
                ]);
            }

            $dataTerhapus->delete();

            return [
                'success' => true,
                'message' => 'Data berhasil dipulihkan dan dikembalikan ke menu asal.',
            ];
        });
    }
}
