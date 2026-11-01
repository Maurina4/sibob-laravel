<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BmnTik extends Model
{
    protected $table = 'bmns_tik';

    protected $fillable = [
        'bmn_id',
        'kode_barang',
        'nup',
        'no_seri',
        'nama_barang',
        'tahun_perolehan',
        'nilai_perolehan',
        'jenis',
        'lokasi',
        'penanggungjawab',
        'kondisi',
        'is_dikembalikan',
        'dihapus',
        'dihapus_pada',
        'dihapus_oleh',
    ];

    public function bmn(): BelongsTo
    {
        return $this->belongsTo(Bmn::class, 'bmn_id');
    }

    protected $casts = [
        'nup' => 'integer',
        'tahun_perolehan' => 'integer',
        'nilai_perolehan' => 'float',
        'is_dikembalikan' => 'boolean',
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

                return $kodeBarang.'-NUP.'.str_pad(
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
        if (! empty($filters['q'])) {
            $search = trim($filters['q']);
            $query->where(function (Builder $q) use ($search) {
                $q->where('kode_barang', 'like', "%{$search}%")
                    ->orWhere('nup', 'like', "%{$search}%")
                    ->orWhere('no_seri', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%")
                    ->orWhere('jenis', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%")
                    ->orWhere('penanggungjawab', 'like', "%{$search}%")
                    ->orWhere('kondisi', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['jenis'])) {
            $query->where('jenis', $filters['jenis']);
        }

        if (! empty($filters['lokasi'])) {
            $query->where('lokasi', $filters['lokasi']);
        }

        if (! empty($filters['penanggungjawab'])) {
            $query->where('penanggungjawab', $filters['penanggungjawab']);
        }

        if (! empty($filters['kondisi'])) {
            $query->where('kondisi', $filters['kondisi']);
        }

        return $query;
    }
}
