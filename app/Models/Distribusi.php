<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Distribusi extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nomor',
        'tanggal',
        'bmn_id',
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

    public function bmn(): BelongsTo
    {
        return $this->belongsTo(Bmn::class, 'bmn_id');
    }

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

    public function getIsPendingAttribute(): bool
    {
        return empty($this->lokasi_tujuan) || empty($this->penerima) || $this->lokasi_tujuan === 'Belum Ditentukan';
    }

    public function getKodeBarangAttribute(): ?string
    {
        return $this->bmn?->kode_barang;
    }

    public function getNupAttribute(): ?string
    {
        return $this->bmn?->nup !== null ? (string) $this->bmn->nup : null;
    }

    public function getKodeNupAttribute(): ?string
    {
        if (! $this->kode_barang) {
            return null;
        }

        return $this->nup ? "{$this->kode_barang}-NUP.{$this->nup}" : (string) $this->kode_barang;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deleted_at')->where('dihapus', false);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $concatExpr = DB::getDriverName() === 'sqlite'
                ? "COALESCE(kode_barang, '') || '-NUP.' || COALESCE(nup, '')"
                : "CONCAT(COALESCE(kode_barang, ''), '-NUP.', COALESCE(nup, ''))";

            $query->where(function (Builder $q) use ($search, $concatExpr) {
                $q->where('nomor', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%")
                    ->orWhere('penerima', 'like', "%{$search}%")
                    ->orWhere('lokasi_asal', 'like', "%{$search}%")
                    ->orWhere('lokasi_tujuan', 'like', "%{$search}%")
                    ->orWhereHas('bmn', function (Builder $bmnQ) use ($search, $concatExpr) {
                        $bmnQ->where('kode_barang', 'like', "%{$search}%")
                            ->orWhere('nup', 'like', "%{$search}%")
                            ->orWhere('nama_barang', 'like', "%{$search}%")
                            ->orWhereRaw("{$concatExpr} LIKE ?", ["%{$search}%"]);
                    });
            });
        }

        return $query;
    }
}
