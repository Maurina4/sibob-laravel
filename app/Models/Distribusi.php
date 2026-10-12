<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Distribusi extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nomor',
        'tanggal',
        'lokasi_asal_id',
        'lokasi_tujuan_id',
        'penerima_id',
        'nama_barang',
        'jumlah',
        'satuan',
        'lokasi_asal',
        'lokasi_tujuan',
        'penerima',
        'keterangan',
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
        return $this->hasMany(DistribusiItem::class, 'distribusi_id');
    }

    public function lokasiAsalRef(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_asal_id');
    }

    public function lokasiTujuanRef(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_tujuan_id');
    }

    public function penerimaPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'penerima_id');
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