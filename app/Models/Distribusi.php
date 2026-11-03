<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Distribusi extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nomor',
        'tanggal',
        'bmn_id',
        'lokasi_asal_id',
        'lokasi_tujuan_id',
        'penerima_id',
        'nama_barang',
        'jumlah',
        'satuan',
        'lokasi_asal',
        'lokasi_tujuan',
        'penerima',
        'keterangan',
        'dihapus',
        'dihapus_pada',
        'dihapus_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'dihapus' => 'boolean',
        'dihapus_pada' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function bmn(): BelongsTo
    {
        return $this->belongsTo(Bmn::class, 'bmn_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DistribusiItem::class, 'distribusi_id');
    }

    public function lokasiAsalRef(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_asal_id');
    }

    public function lokasiTujuanRef(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_tujuan_id');
    }

    public function penerimaPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'penerima_id');
    }

    public function getIsPendingAttribute(): bool
    {
        return empty($this->lokasi_tujuan) || empty($this->penerima) || $this->lokasi_tujuan === 'Belum Ditentukan';
    }

    public function getKodeBarangAttribute(): ?string
    {
        return $this->bmn?->kode_barang;
    }

    public function getNupAttribute(): ?string
    {
        return $this->bmn?->nup !== null ? (string) $this->bmn->nup : null;
    }

    public function getKodeNupAttribute(): ?string
    {
        if (! $this->kode_barang) {
            return null;
        }

        return $this->nup ? "{$this->kode_barang}-NUP.{$this->nup}" : (string) $this->kode_barang;
    }

    public function getTanggalTerbilangAttribute(): string
    {
        $date = $this->tanggal ?: now();
        $hari = match ($date->dayOfWeek) {
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            default => '',
        };
        $tglTerbilang = ucfirst(self::terbilang($date->day));
        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $bulan = $bulanNames[$date->month] ?? '';
        $tahunTerbilang = ucfirst(self::terbilang($date->year));

        return "Pada hari ini {$hari} tanggal {$tglTerbilang} bulan {$bulan} tahun {$tahunTerbilang} yang bertanda tangan di bawah ini:";
    }

    public static function terbilang(int $angka): string
    {
        $bilangan = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        if ($angka < 12) {
            return $bilangan[$angka];
        } elseif ($angka < 20) {
            return self::terbilang($angka - 10).' belas';
        } elseif ($angka < 100) {
            $puluh = (int) ($angka / 10);
            $sisa = $angka % 10;

            return self::terbilang($puluh).' puluh'.($sisa > 0 ? ' '.self::terbilang($sisa) : '');
        } elseif ($angka < 200) {
            $sisa = $angka - 100;

            return 'seratus'.($sisa > 0 ? ' '.self::terbilang($sisa) : '');
        } elseif ($angka < 1000) {
            $ratus = (int) ($angka / 100);
            $sisa = $angka % 100;

            return self::terbilang($ratus).' ratus'.($sisa > 0 ? ' '.self::terbilang($sisa) : '');
        } elseif ($angka < 2000) {
            $sisa = $angka - 1000;

            return 'seribu'.($sisa > 0 ? ' '.self::terbilang($sisa) : '');
        } elseif ($angka < 1000000) {
            $ribu = (int) ($angka / 1000);
            $sisa = $angka % 1000;

            return self::terbilang($ribu).' ribu'.($sisa > 0 ? ' '.self::terbilang($sisa) : '');
        }

        return (string) $angka;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deleted_at')->where('dihapus', false);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $concatExpr = DB::getDriverName() === 'sqlite'
                ? "COALESCE(kode_barang, '') || '-NUP.' || COALESCE(nup, '')"
                : "CONCAT(COALESCE(kode_barang, ''), '-NUP.', COALESCE(nup, ''))";

            $query->where(function (Builder $q) use ($search, $concatExpr) {
                $q->where('nomor', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%")
                    ->orWhere('penerima', 'like', "%{$search}%")
                    ->orWhere('lokasi_asal', 'like', "%{$search}%")
                    ->orWhere('lokasi_tujuan', 'like', "%{$search}%")
                    ->orWhereHas('bmn', function (Builder $bmnQ) use ($search, $concatExpr) {
                        $bmnQ->where('kode_barang', 'like', "%{$search}%")
                            ->orWhere('nup', 'like', "%{$search}%")
                            ->orWhere('nama_barang', 'like', "%{$search}%")
                            ->orWhereRaw("{$concatExpr} LIKE ?", ["%{$search}%"]);
                    });
            });
        }

        return $query;
    }
}
