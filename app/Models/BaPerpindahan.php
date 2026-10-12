<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaPerpindahan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nomor_ba',
        'tanggal',
        'lokasi_asal_id',
        'lokasi_tujuan_id',
        'pengaju_id',
        'nama_barang',
        'dari',
        'ke',
        'alasan',
        'pihak_kedua',
        'pengaju_email',
        'pengaju_nama',
        'status',
        'catatan_operator',
        'tanggal_keputusan',
        'diputuskan_oleh',
        'dihapus',
        'dihapus_pada',
        'dihapus_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_keputusan' => 'date',
        'dihapus' => 'boolean',
        'dihapus_pada' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(BaPerpindahanItem::class, 'ba_perpindahan_id');
    }

    public function lokasiAsalRef(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_asal_id');
    }

    public function lokasiTujuanRef(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_tujuan_id');
    }

    public function pengajuUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengaju_id');
    }

    public function pihakKeduaUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pihak_kedua');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deleted_at')->where('dihapus', false);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}