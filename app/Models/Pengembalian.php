<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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
        'kondisi_pengembalian',
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
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $concatExpr = DB::getDriverName() === 'sqlite'
                ? "COALESCE(kode_barang, '') || '-NUP.' || COALESCE(nup, '')"
                : "CONCAT(COALESCE(kode_barang, ''), '-NUP.', COALESCE(nup, ''))";

            $query->where(function (Builder $q) use ($search, $concatExpr) {
                $q->where('nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhere('nama_pengaju', 'like', "%{$search}%")
                    ->orWhere('bidang', 'like', "%{$search}%")
                    ->orWhereHas('items', function (Builder $iq) use ($search, $concatExpr) {
                        $iq->where('nama_barang_snapshot', 'like', "%{$search}%")
                            ->orWhere('keterangan', 'like', "%{$search}%")
                            ->orWhereHas('bmn', function (Builder $bq) use ($search, $concatExpr) {
                                $bq->where('kode_barang', 'like', "%{$search}%")
                                    ->orWhere('nup', 'like', "%{$search}%")
                                    ->orWhere('nama_barang', 'like', "%{$search}%")
                                    ->orWhereRaw("{$concatExpr} LIKE ?", ["%{$search}%"]);
                            });
                    });
            });
        }

        if (! empty($filters['bidang'])) {
            $query->where('bidang', $filters['bidang']);
        }

        return $query;
    }
}
