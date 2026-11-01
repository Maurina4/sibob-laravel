<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengembalianItem extends Model
{
    protected $table = 'pengembalian_items';

    protected $fillable = [
        'pengembalian_id',
        'bmn_id',
        'nama_barang_snapshot',
        'kondisi_awal',
        'kondisi',
        'keterangan',
    ];

    public function pengembalian(): BelongsTo
    {
        return $this->belongsTo(Pengembalian::class, 'pengembalian_id');
    }

    public function bmn(): BelongsTo
    {
        return $this->belongsTo(Bmn::class, 'bmn_id');
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
}
