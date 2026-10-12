<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lokasi extends Model
{
    protected $fillable = [
        'nama',
    ];

    public function bmns(): HasMany
    {
        return $this->hasMany(Bmn::class, 'lokasi_id');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $query->where('nama', 'like', '%' . $filters['search'] . '%');
        }

        return $query;
    }
}