<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pengembalian extends Model
{
    use SoftDeletes;

    protected $table = 'pengembalians';

    protected $fillable = [
        'nomor_pengajuan',
        'tanggal',
        'pengaju_id',
        'nama_pengaju',
        'bidang',
        'status',
        'dihapus',
        'dihapus_pada',
        'dihapus_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'dihapus' => 'boolean',
        'dihapus_pada' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PengembalianItem::class, 'pengembalian_id');
    }

    public function pengajuPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pengaju_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deleted_at')->where('dihapus', false);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhere('nama_pengaju', 'like', "%{$search}%")
                    ->orWhere('bidang', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['bidang'])) {
            $query->where('bidang', $filters['bidang']);
        }

        return $query;
    }
}