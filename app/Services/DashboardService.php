<?php

namespace App\Services;

use App\Models\BaPerpindahan;
use App\Models\Bmn;
use App\Models\BmnLab;
use App\Models\BmnTik;
use App\Models\Distribusi;
use App\Models\LaporanKerusakan;

class DashboardService
{
    public function getStats(): array
    {
        return [
            'jumlahBmn' => Bmn::active()->count(),
            'jumlahBmnTik' => BmnTik::active()->count(),
            'jumlahBmnLab' => BmnLab::active()->count(),
            'jumlahKondisiBaik' => Bmn::active()->where('kondisi', 'Baik')->count(),
            'jumlahPerluKalibrasi' => BmnLab::active()->perluKalibrasi()->count(),
            'jumlahKerusakan' => LaporanKerusakan::active()->belumSelesai()->count(),
            'jumlahDistribusi' => Distribusi::active()->status('Diajukan')->count(),
            'jumlahBaPerpindahan' => BaPerpindahan::active()->status('Diajukan')->count(),
        ];
    }
}
