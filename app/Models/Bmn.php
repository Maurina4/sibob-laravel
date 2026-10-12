<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bmn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'kategori_bmn_id',
        'kode_barang',
        'nup',
        'nama_barang',
        'merek',
        'tanggal_terima',
        'jenis',
        'satuan',
        'lokasi',
        'lokasi_id',
        'penanggungjawab',
        'penanggung_jawab_id',
        'penanggungjawab_nip',
        'kondisi',
        'jadwal_pemeliharaan',
        'spesifikasi',
        'dihapus',
        'dihapus_pada',
        'dihapus_oleh',
        'deleted_by',
    ];

    protected $casts = [
        'tanggal_terima' => 'date',
        'jadwal_pemeliharaan' => 'date',
        'dihapus' => 'boolean',
        'dihapus_pada' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==========================================
    // RELASI RELASIONAL (ELOQUENT)
    // ==========================================

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriBmn::class, 'kategori_bmn_id');
    }

    public function lokasiRef(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }

    public function penanggungJawabPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'penanggung_jawab_id');
    }

    public function labDetail(): HasOne
    {
        return $this->hasOne(BmnLabDetail::class, 'bmn_id');
    }

    public function dokumens(): HasMany
    {
        return $this->hasMany(BmnDokumen::class, 'bmn_id');
    }

    public function distribusiItems(): HasMany
    {
        return $this->hasMany(DistribusiItem::class, 'bmn_id');
    }

    public function baPerpindahanItems(): HasMany
    {
        return $this->hasMany(BaPerpindahanItem::class, 'bmn_id');
    }

    public function pengembalianItems(): HasMany
    {
        return $this->hasMany(PengembalianItem::class, 'bmn_id');
    }

    public function laporanKerusakans(): HasMany
    {
        return $this->hasMany(LaporanKerusakan::class, 'bmn_id');
    }

    // ==========================================
    // QUERY SCOPES
    // ==========================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deleted_at')->where('dihapus', false);
    }

    public function scopeUmum(Builder $query): Builder
    {
        return $query->where('kategori_bmn_id', 1);
    }

    public function scopeLab(Builder $query): Builder
    {
        return $query->where('kategori_bmn_id', 2);
    }

    public function scopeTik(Builder $query): Builder
    {
        return $query->where('kategori_bmn_id', 3);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('kode_barang', 'like', "%{$search}%")
                    ->orWhere('nup', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%")
                    ->orWhere('merek', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%")
                    ->orWhere('penanggungjawab', 'like', "%{$search}%")
                    ->orWhere('jenis', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['kategori_bmn_id'])) {
            $query->where('kategori_bmn_id', $filters['kategori_bmn_id']);
        }

        if (!empty($filters['jenis'])) {
            $query->where('jenis', $filters['jenis']);
        }

        if (!empty($filters['kondisi'])) {
            $query->where('kondisi', $filters['kondisi']);
        }

        if (!empty($filters['lokasi_id'])) {
            $query->where('lokasi_id', $filters['lokasi_id']);
        } elseif (!empty($filters['lokasi'])) {
            $query->where('lokasi', $filters['lokasi']);
        }

        if (!empty($filters['penanggung_jawab_id'])) {
            $query->where('penanggung_jawab_id', $filters['penanggung_jawab_id']);
        } elseif (!empty($filters['penanggungjawab'])) {
            $query->where('penanggungjawab', $filters['penanggungjawab']);
        }

        return $query;
    }
}