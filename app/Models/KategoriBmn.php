<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriBmn extends Model
{
    protected $table = 'kategori_bmns';

    protected $fillable = [
        'kode',
        'nama',
        'deskripsi',
    ];

    public function bmns(): HasMany
    {
        return $this->hasMany(Bmn::class, 'kategori_bmn_id');
    }
}
