<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DataTerhapus extends Model
{
    protected $table = 'data_terhapus';

    protected $fillable = [
        'jenis',
        'data_id',
        'nama',
        'dihapus_pada',
        'dihapus_oleh',
    ];

    protected $casts = [
        'dihapus_pada' => 'datetime',
    ];

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('jenis', 'like', "%{$search}%")
                    ->orWhere('dihapus_oleh', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['jenis'])) {
            $query->where('jenis', $filters['jenis']);
        }

        return $query;
    }
}