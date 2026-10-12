<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenanggungJawab extends Model
{
    protected $fillable = [
        'nama',
        'nip',
        'jabatan',
        'unit_kerja',
    ];
}