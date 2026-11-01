<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanPengembalianItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pengajuan_id',
        'bmn_id',
        'kode_barang',
        'nup',
        'nama_barang',
        'merek',
        'lokasi_awal',
        'kondisi_awal',
        'kondisi',
        'keterangan',
        'tanggal_temuan',
        'deskripsi_kerusakan',
        'laporan_kerusakan_id',
    ];

    protected $casts = [
        'tanggal_temuan' => 'date',
        'nup' => 'integer',
    ];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanPengembalian::class, 'pengajuan_id');
    }

    public function bmn(): BelongsTo
    {
        return $this->belongsTo(Bmn::class, 'bmn_id');
    }

    public function laporanKerusakan(): BelongsTo
    {
        return $this->belongsTo(LaporanKerusakan::class, 'laporan_kerusakan_id');
    }
}
