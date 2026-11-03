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
        'no_seri',
        'nama_barang',
        'merek',
        'tahun_perolehan',
        'nilai_perolehan',
        'tanggal_terima',
        'jenis',
        'satuan',
        'lokasi',
        'lokasi_id',
        'penanggungjawab',
        'penanggung_jawab_id',
        'penanggungjawab_nip',
        'kondisi',
        'is_dikembalikan',
        'jadwal_pemeliharaan',
        'spesifikasi',
        'dihapus',
        'dihapus_pada',
        'dihapus_oleh',
        'deleted_by',
    ];

    protected $casts = [
        'tahun_perolehan' => 'integer',
        'nilai_perolehan' => 'float',
        'tanggal_terima' => 'date',
        'jadwal_pemeliharaan' => 'date',
        'is_dikembalikan' => 'boolean',
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

    public function distribusis(): HasMany
    {
        return $this->hasMany(Distribusi::class, 'bmn_id');
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

    public function tik(): HasOne
    {
        return $this->hasOne(BmnTik::class, 'bmn_id');
    }

    public function lab(): HasOne
    {
        return $this->hasOne(BmnLab::class, 'bmn_id');
    }

    public function dataTerhapus(): HasMany
    {
        return $this->hasMany(DataTerhapus::class, 'bmn_id');
    }

    public function getKodeNupAttribute(): string
    {
        return $this->nup ? "{$this->kode_barang}-NUP.{$this->nup}" : (string) $this->kode_barang;
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
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('kode_barang', 'like', "%{$search}%")
                    ->orWhere('nup', 'like', "%{$search}%")
                    ->orWhere('no_seri', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%")
                    ->orWhere('merek', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%")
                    ->orWhere('penanggungjawab', 'like', "%{$search}%")
                    ->orWhere('jenis', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['kategori_bmn_id'])) {
            $query->where('kategori_bmn_id', $filters['kategori_bmn_id']);
        }

        if (! empty($filters['jenis'])) {
            $query->where('jenis', $filters['jenis']);
        }

        if (! empty($filters['kondisi'])) {
            $query->where('kondisi', $filters['kondisi']);
        }

        if (! empty($filters['tahun'])) {
            if ($filters['tahun'] === 'current') {
                $query->where('tahun_perolehan', (int) date('Y'));
            } elseif ($filters['tahun'] === 'previous') {
                $query->where('tahun_perolehan', (int) date('Y') - 1);
            } elseif ($filters['tahun'] === 'prior_all') {
                $query->where('tahun_perolehan', '<=', (int) date('Y') - 1);
            } else {
                $query->where('tahun_perolehan', (int) $filters['tahun']);
            }
        } elseif (! empty($filters['tahun_perolehan'])) {
            $query->where('tahun_perolehan', (int) $filters['tahun_perolehan']);
        }

        if (! empty($filters['lokasi_id'])) {
            $query->where('lokasi_id', $filters['lokasi_id']);
        } elseif (! empty($filters['lokasi'])) {
            $lokasiInput = trim($filters['lokasi']);
            $lokasiModel = Lokasi::findByNameOrCode($lokasiInput);
            if ($lokasiModel) {
                $query->where(function (Builder $q) use ($lokasiModel, $lokasiInput) {
                    $q->where('lokasi_id', $lokasiModel->id)
                        ->orWhere('lokasi', $lokasiInput)
                        ->orWhere('lokasi', $lokasiModel->nama)
                        ->orWhere('lokasi', $lokasiModel->format_kombinasi)
                        ->orWhere('lokasi', "{$lokasiModel->kode_ruangan} {$lokasiModel->nama}")
                        ->orWhere('lokasi', 'like', "%{$lokasiModel->nama}%");
                });
            } else {
                $query->where(function (Builder $q) use ($lokasiInput) {
                    $q->where('lokasi', $lokasiInput)
                        ->orWhere('lokasi', 'like', "%{$lokasiInput}%");
                });
            }
        }

        if (! empty($filters['penanggung_jawab_id'])) {
            $query->where('penanggung_jawab_id', $filters['penanggung_jawab_id']);
        } elseif (! empty($filters['penanggungjawab'])) {
            $pjInput = trim($filters['penanggungjawab']);
            $concatSql = DB::getDriverName() === 'sqlite'
                ? "nama || ' (' || nip || ')' = ?"
                : "CONCAT(nama, ' (', nip, ')') = ?";

            $pegawai = Pegawai::where('nama', $pjInput)
                ->orWhere('nip', $pjInput)
                ->orWhereRaw($concatSql, [$pjInput])
                ->first();
            if ($pegawai) {
                $query->where(function (Builder $q) use ($pegawai, $pjInput) {
                    $q->where('penanggung_jawab_id', $pegawai->id)
                        ->orWhere('penanggungjawab', $pjInput)
                        ->orWhere('penanggungjawab', 'like', "%{$pegawai->nama}%")
                        ->orWhere('penanggungjawab_nip', $pegawai->nip);
                });
            } else {
                $query->where(function (Builder $q) use ($pjInput) {
                    $q->where('penanggungjawab', $pjInput)
                        ->orWhere('penanggungjawab', 'like', "%{$pjInput}%");
                });
            }
        }

        return $query;
    }
}
