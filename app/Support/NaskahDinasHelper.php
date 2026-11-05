<?php

namespace App\Support;

use Carbon\Carbon;

class NaskahDinasHelper
{
    public const KODE_FORM_BAST = 'POM-14.01/CFM.01/SOP.01/IK.17A.01/F.01';

    /**
     * Memformat NIP 18 digit ke format spasi standar naskah dinas pemerintah (8-6-1-3 digit).
     * Contoh: 198407222023211010 -> 19840722 202321 1 010
     */
    public static function formatNip(?string $nip): string
    {
        $trimmed = trim((string) $nip);
        if ($trimmed === '' || $trimmed === '-') {
            return '-';
        }

        $digits = preg_replace('/[^0-9]/', '', $trimmed);
        if (strlen($digits) === 18) {
            return substr($digits, 0, 8).' '
                .substr($digits, 8, 6).' '
                .substr($digits, 14, 1).' '
                .substr($digits, 15, 3);
        }

        return $trimmed;
    }

    /**
     * Konversi angka ke ucapan terbilang bahasa Indonesia.
     */
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

    /**
     * Menghasilkan teks paragraf pembuka tanggal terbilang resmi naskah dinas.
     * Contoh: "Pada hari ini Rabu tanggal Dua puluh lima bulan Maret tahun Dua ribu dua puluh enam yang bertanda tangan di bawah ini:"
     */
    public static function tanggalTerbilang($date = null): string
    {
        if (! $date instanceof Carbon) {
            $date = ! empty($date) ? Carbon::parse($date) : now();
        }

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
}
