<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BmnLabDetail extends Model
{
    protected $table = 'bmn_lab_details';

    protected $fillable = [
        'bmn_id',
        'status_kalibrasi',
        'tanggal_kalibrasi',
        'lembaga_kalibrasi',
        'nomor_sertifikat',
    ];

    protected $casts = [
        'tanggal_kalibrasi' => 'date',
    ];

    public function bmn(): BelongsTo
    {
        return $this->belongsTo(Bmn::class, 'bmn_id');
    }
}
