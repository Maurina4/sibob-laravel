<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class BaPerpindahan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nomor_ba',
        'tanggal',
        'bmn_id',
        'pegawai_id',
        'pengaju_pegawai_id',
        'lokasi_asal_id',
        'lokasi_tujuan_id',
        'pengaju_id',
        'nama_barang',
        'dari',
        'ke',
        'alasan',
        'pihak_pertama',
        'pihak_pertama_id',
        'pihak_kedua',
        'pengaju_email',
        'pengaju_nama',
        'status',
        'catatan_operator',
        'tanggal_keputusan',
        'diputuskan_oleh',
        'dihapus',
        'dihapus_pada',
        'dihapus_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_keputusan' => 'date',
        'dihapus' => 'boolean',
        'dihapus_pada' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function bmn(): BelongsTo
    {
        return $this->belongsTo(Bmn::class, 'bmn_id');
    }

    public function pengajuPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pengaju_pegawai_id');
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function pihakPertamaPegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pihak_pertama_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(BaPerpindahanItem::class, 'ba_perpindahan_id');
    }

    public function lokasiAsalRef(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_asal_id');
    }

    public function lokasiTujuanRef(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_tujuan_id');
    }

    public function pengajuUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengaju_id');
    }

    public function pihakKeduaUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pihak_kedua');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deleted_at')->where('dihapus', false);
    }

    public function canBePrinted(): bool
    {
        $penerima = trim((string) $this->pihak_kedua);

        return $penerima !== '' && $penerima !== '-';
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $concatExpr = DB::getDriverName() === 'sqlite'
                ? "COALESCE(kode_barang, '') || '-NUP.' || COALESCE(nup, '')"
                : "CONCAT(COALESCE(kode_barang, ''), '-NUP.', COALESCE(nup, ''))";

            $query->where(function (Builder $q) use ($search, $concatExpr) {
                $q->where('nomor_ba', 'like', "%{$search}%")
                    ->orWhere('nama_barang', 'like', "%{$search}%")
                    ->orWhere('dari', 'like', "%{$search}%")
                    ->orWhere('ke', 'like', "%{$search}%")
                    ->orWhere('pengaju_nama', 'like', "%{$search}%")
                    ->orWhere('pihak_pertama', 'like', "%{$search}%")
                    ->orWhere('pihak_kedua', 'like', "%{$search}%")
                    ->orWhereHas('bmn', function (Builder $bmnQ) use ($search, $concatExpr) {
                        $bmnQ->where('kode_barang', 'like', "%{$search}%")
                            ->orWhere('nup', 'like', "%{$search}%")
                            ->orWhere('nama_barang', 'like', "%{$search}%")
                            ->orWhereRaw("{$concatExpr} LIKE ?", ["%{$search}%"]);
                    });

                if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                } elseif (preg_match('/^BA-MUT\/(\d+)$/i', $search, $matches)) {
                    $q->orWhere('id', $matches[1]);
                }
            });
        }

        return $query;
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

    public static function isSamePerson(?string $personA, ?string $personB, ?int $personAId = null, ?int $personBId = null): bool
    {
        if ($personAId && $personBId && (int) $personAId === (int) $personBId) {
            return true;
        }

        $cleanA = trim((string) $personA);
        $cleanB = trim((string) $personB);

        if ($cleanA === '' || $cleanB === '') {
            return false;
        }

        if (strcasecmp($cleanA, $cleanB) === 0) {
            return true;
        }

        $nipA = null;
        $nipB = null;
        if (preg_match('/\b(\d{8,18})\b/', $cleanA, $matchesA)) {
            $nipA = $matchesA[1];
        }
        if (preg_match('/\b(\d{8,18})\b/', $cleanB, $matchesB)) {
            $nipB = $matchesB[1];
        }

        if ($nipA && $nipB && $nipA === $nipB) {
            return true;
        }

        $strippedA = trim((string) preg_replace('/\s*\(.*?\)\s*/', '', $cleanA));
        $strippedB = trim((string) preg_replace('/\s*\(.*?\)\s*/', '', $cleanB));

        if ($strippedA !== '' && $strippedB !== '' && strcasecmp($strippedA, $strippedB) === 0) {
            return true;
        }

        if ($personAId) {
            $pegawaiA = Pegawai::find($personAId);
            if ($pegawaiA) {
                if ($nipB && $pegawaiA->nip && $pegawaiA->nip === $nipB) {
                    return true;
                }
                if ($strippedB !== '' && strcasecmp($pegawaiA->nama, $strippedB) === 0) {
                    return true;
                }
            }
        }

        if ($personBId) {
            $pegawaiB = Pegawai::find($personBId);
            if ($pegawaiB) {
                if ($nipA && $pegawaiB->nip && $pegawaiB->nip === $nipA) {
                    return true;
                }
                if ($strippedA !== '' && strcasecmp($pegawaiB->nama, $strippedA) === 0) {
                    return true;
                }
            }
        }

        if ($nipB && ! $nipA) {
            $pegB = Pegawai::where('nip', $nipB)->first();
            if ($pegB && $strippedA !== '' && strcasecmp($pegB->nama, $strippedA) === 0) {
                return true;
            }
        }

        if ($nipA && ! $nipB) {
            $pegA = Pegawai::where('nip', $nipA)->first();
            if ($pegA && $strippedB !== '' && strcasecmp($pegA->nama, $strippedB) === 0) {
                return true;
            }
        }

        return false;
    }
}
