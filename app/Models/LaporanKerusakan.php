<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaporanKerusakan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nomor',
        'tanggal',
        'bmn_id',
        'lokasi_id',
        'pelapor_id',
        'pelapor_pegawai_id',
        'nama_barang',
        'lokasi',
        'tingkat',
        'deskripsi',
        'pelapor_email',
        'pelapor_nama',
        'status',
        'sumber',
        'pengajuan_id',
        'tanggapan',
        'tanggal_update',
        'ditanggapi_oleh',
        'dihapus',
        'dihapus_pada',
        'dihapus_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_update' => 'date',
        'dihapus' => 'boolean',
        'dihapus_pada' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function bmn(): BelongsTo
    {
        return $this->belongsTo(Bmn::class, 'bmn_id');
    }

    public function lokasiRef(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }

    public function pelaporUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelapor_id');
    }

    public function pelaporPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pelapor_pegawai_id');
    }

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanPengembalian::class, 'pengajuan_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deleted_at')->where('dihapus', false);
    }

    public function scopeBelumSelesai(Builder $query): Builder
    {
        return $query->where('status', '!=', 'Selesai');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('nomor', 'like', "%{$search}%");
                if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                } elseif (preg_match('/^LK-#?(\d+)$/i', $search, $matches)) {
                    $q->orWhere('id', $matches[1]);
                }
            });
        }

        return $query;
    }
}
