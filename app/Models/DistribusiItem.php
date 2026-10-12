<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistribusiItem extends Model
{
    protected $table = 'distribusi_items';

    protected $fillable = [
        'distribusi_id',
        'bmn_id',
        'nama_barang_snapshot',
        'jumlah',
        'satuan',
        'catatan',
    ];

    public function distribusi(): BelongsTo
    {
        return $this->belongsTo(Distribusi::class, 'distribusi_id');
    }

    public function bmn(): BelongsTo
    {
        return $this->belongsTo(Bmn::class, 'bmn_id');
    }
}
