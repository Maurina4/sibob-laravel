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
}
