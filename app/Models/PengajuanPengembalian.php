<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengajuanPengembalian extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nomor_pengajuan',
        'nomor_sipanda',
        'bidang',
        'tanggal',
        'pegawai_id',
        'nama_pegawai',
        'nip_pegawai',
        'diajukan_oleh',
        'alasan',
        'status',
        'catatan_pemohon',
        'catatan_penolakan',
        'nomor_ba',
        'disetujui_oleh',
        'disetujui_pada',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'disetujui_pada' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PengajuanPengembalianItem::class, 'pengajuan_id');
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function pemohonUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function penyetujuUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function scopeMilikhak(Builder $query, int $userId): Builder
    {
        return $query->where('diajukan_oleh', $userId);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('nomor_sipanda', 'like', "%{$search}%")
                    ->orWhere('nomor_ba', 'like', "%{$search}%")
                    ->orWhere('nama_pegawai', 'like', "%{$search}%")
                    ->orWhere('nip_pegawai', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['alasan'])) {
            $query->where('alasan', $filters['alasan']);
        }

        if (! empty($filters['bidang'])) {
            $query->where('bidang', $filters['bidang']);
        }

        return $query;
    }
}
