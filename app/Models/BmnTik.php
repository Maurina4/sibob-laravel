<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class BmnTik extends Model
{
    protected $table = 'bmns_tik';

    protected $fillable = [
        'kode_barang',
        'nup',
        'nama_barang',
        'jenis',
        'lokasi',
        'penanggungjawab',
        'dihapus',
        'dihapus_pada',
        'dihapus_oleh',
    ];

    protected $casts = [
        'nup' => 'integer',
        'dihapus' => 'boolean',
        'dihapus_pada' => 'datetime',
    ];

    protected function kodeNup(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                $kodeBarang = trim((string) ($attributes['kode_barang'] ?? ''));
                $nup = $attributes['nup'] ?? null;

                if ($kodeBarang === '') {
                    return '-';
                }

                if ($nup === null || $nup === '') {
                    return $kodeBarang;
                }

                return $kodeBarang . '-NUP.' . str_pad(
                    (string) $nup,
                    2,
                    '0',
                    STR_PAD_LEFT
                );
            }
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('dihapus', false);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (!empty($filters['q'])) {
            $search = trim($filters['q']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('kode_barang', 'like', "%{$search}%")
                    ->orWhere('nup', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%")
                    ->orWhere('jenis', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%")
                    ->orWhere('penanggungjawab', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['jenis'])) {
            $query->where('jenis', $filters['jenis']);
        }

        if (!empty($filters['lokasi'])) {
            $query->where('lokasi', $filters['lokasi']);
        }

        if (!empty($filters['penanggungjawab'])) {
            $query->where('penanggungjawab', $filters['penanggungjawab']);
        }

        return $query;
    }
}