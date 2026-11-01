<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lokasi extends Model
{
    protected $fillable = [
        'nama',
        'kode_ruangan',
    ];

    public function bmns(): HasMany
    {
        return $this->hasMany(Bmn::class, 'lokasi_id');
    }

    public function getFormatKombinasiAttribute(): string
    {
        if (! empty($this->kode_ruangan)) {
            return "{$this->kode_ruangan} - {$this->nama}";
        }

        return $this->nama;
    }

    public static function findByNameOrCode(?string $value): ?self
    {
        if (empty($value)) {
            return null;
        }

        $trimmed = trim($value);

        // 1. Direct name match (case-insensitive)
        $lokasi = static::whereRaw('LOWER(nama) = ?', [strtolower($trimmed)])->first();
        if ($lokasi) {
            return $lokasi;
        }

        // 2. Direct code match (case-insensitive)
        $lokasi = static::whereRaw('LOWER(kode_ruangan) = ?', [strtolower($trimmed)])->first();
        if ($lokasi) {
            return $lokasi;
        }

        // 3. Match format 'B1.01 - RUANG TAMU UTAMA'
        if (str_contains($trimmed, ' - ')) {
            [$code, $name] = explode(' - ', $trimmed, 2);
            $lokasi = static::where(function ($q) use ($code, $name) {
                $q->where('kode_ruangan', trim($code))
                    ->where('nama', trim($name));
            })->first() ?? static::where('kode_ruangan', trim($code))->first() ?? static::where('nama', trim($name))->first();

            if ($lokasi) {
                return $lokasi;
            }
        }

        // 4. Match format 'B1.01 RUANG TAMU UTAMA' (must match room code pattern with dot and digits)
        if (preg_match('/^([A-Za-z]{1,2}(?:\.[A-Za-z]+)?\d*\.\d+)\s+(.+)$/i', $trimmed, $matches)) {
            $lokasi = static::where('kode_ruangan', $matches[1])
                ->where('nama', $matches[2])
                ->first() ?? static::where('kode_ruangan', $matches[1])->first();
            if ($lokasi) {
                return $lokasi;
            }
        }

        return null;
    }

    public static function findOrCreateByName(?string $value): ?self
    {
        if (empty($value)) {
            return null;
        }

        $existing = static::findByNameOrCode($value);
        if ($existing) {
            return $existing;
        }

        $trimmed = trim($value);

        // If it starts with room code like 'B1.01 RUANG ...'
        if (preg_match('/^([A-Za-z]{1,2}(?:\.[A-Za-z]+)?\d*\.\d+)\s+(.+)$/i', $trimmed, $matches)) {
            return static::create([
                'kode_ruangan' => $matches[1],
                'nama' => trim($matches[2]),
            ]);
        }

        return static::create(['nama' => $trimmed]);
    }

    public function scopeOrderedByGedung(Builder $query): Builder
    {
        $driver = $query->getConnection()->getDriverName();

        $orderBuilding = "
            CASE
                WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE 'A%' OR UPPER(nama) LIKE '%GEDUNG A%' THEN 1
                WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE 'B%' OR UPPER(nama) LIKE '%GEDUNG B%' THEN 2
                WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE 'C%' OR UPPER(nama) LIKE '%GEDUNG C%' THEN 3
                ELSE 4
            END ASC
        ";

        $orderFloor = "
            CASE
                WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE '%BS%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '%.BS%' OR UPPER(nama) LIKE '%BASEMENT%' THEN 10
                WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE '_1.%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_1-%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_1 %' OR UPPER(nama) LIKE '%LANTAI 1%' OR UPPER(nama) LIKE '%LT 1%' THEN 1
                WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE '_2.%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_2-%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_2 %' OR UPPER(nama) LIKE '%LANTAI 2%' OR UPPER(nama) LIKE '%LT 2%' THEN 2
                WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE '_3.%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_3-%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_3 %' OR UPPER(nama) LIKE '%LANTAI 3%' OR UPPER(nama) LIKE '%LT 3%' THEN 3
                WHEN UPPER(COALESCE(kode_ruangan, '')) LIKE '_4.%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_4-%' OR UPPER(COALESCE(kode_ruangan, '')) LIKE '_4 %' OR UPPER(nama) LIKE '%LANTAI 4%' OR UPPER(nama) LIKE '%LT 4%' THEN 4
                ELSE 5
            END ASC
        ";

        $orderRoomNum = $driver === 'sqlite'
            ? "CAST(SUBSTR(kode_ruangan, INSTR(kode_ruangan, '.') + 1) AS INTEGER) ASC"
            : "CAST(SUBSTRING_INDEX(COALESCE(kode_ruangan, '0'), '.', -1) AS UNSIGNED) ASC";

        return $query
            ->orderByRaw($orderBuilding)
            ->orderByRaw($orderFloor)
            ->orderByRaw($orderRoomNum)
            ->orderBy('kode_ruangan')
            ->orderBy('id');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $driver = $query->getConnection()->getDriverName();

            $concatWithId = $driver === 'sqlite'
                ? "lokasis.id || ' - ' || COALESCE(lokasis.kode_ruangan, '') || ' - ' || lokasis.nama"
                : "CONCAT(lokasis.id, ' - ', COALESCE(lokasis.kode_ruangan, ''), ' - ', lokasis.nama)";

            $concatWithoutId = $driver === 'sqlite'
                ? "COALESCE(lokasis.kode_ruangan, '') || ' - ' || lokasis.nama"
                : "CONCAT(COALESCE(lokasis.kode_ruangan, ''), ' - ', lokasis.nama)";

            $query->where(function (Builder $q) use ($search, $concatWithId, $concatWithoutId) {
                $q->where('lokasis.id', 'like', "%{$search}%")
                    ->orWhere('lokasis.kode_ruangan', 'like', "%{$search}%")
                    ->orWhere('lokasis.nama', 'like', "%{$search}%")
                    ->orWhereRaw("{$concatWithId} LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("{$concatWithoutId} LIKE ?", ["%{$search}%"]);
            });
        }

        return $query;
    }
}
