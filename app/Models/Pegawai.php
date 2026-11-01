<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pegawai extends Model
{
    protected $fillable = [
        'nama',
        'nip',
        'jabatan',
        'unit_kerja',
    ];

    public function bmns(): HasMany
    {
        return $this->hasMany(Bmn::class, 'penanggung_jawab_id');
    }

    public function getNamaNipAttribute(): string
    {
        return $this->nip ? "{$this->nama} ({$this->nip})" : $this->nama;
    }
}
