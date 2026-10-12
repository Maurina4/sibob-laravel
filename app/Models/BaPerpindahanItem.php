<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BaPerpindahanItem extends Model
{
    protected $table = 'ba_perpindahan_items';

    protected $fillable = [
        'ba_perpindahan_id',
        'bmn_id',
        'nama_barang_snapshot',
        'kondisi_saat_pindah',
    ];

    public function baPerpindahan(): BelongsTo
    {
        return $this->belongsTo(BaPerpindahan::class, 'ba_perpindahan_id');
    }

    public function bmn(): BelongsTo
    {
        return $this->belongsTo(Bmn::class, 'bmn_id');
    }
}
