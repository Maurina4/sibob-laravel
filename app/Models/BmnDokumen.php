<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BmnDokumen extends Model
{
    protected $table = 'bmn_dokumens';

    protected $fillable = [
        'bmn_id',
        'jenis',
        'nama_file',
        'path',
        'ukuran',
        'tipe_file',
    ];

    protected $casts = [
        'ukuran' => 'integer',
    ];

    public function bmn()
    {
        return $this->belongsTo(Bmn::class, 'bmn_id');
    }
}
