<?php

namespace App\Services\Import;

use DateTimeInterface;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class BmnExcelParser
{
    /**
     * Header standar yang diharapkan (case-insensitive & trimmed).
     */
    public const EXPECTED_HEADERS = [
        'kode barang',
        'nup',
        'nama barang',
        'merk/type',
        'tgl terima',
        'tahun perolehan',
        'nilai perolehan',
        'jenis barang',
        'satuan',
        'spesifikasi',
    ];

    /**
     * Membaca berkas Excel (.xlsx) dan mengembalikan array baris data ternormalisasi.
     *
     * @param  string  $filePath
     * @return array{
     *     header_valid: bool,
     *     header_errors: array<int, string>,
     *     rows: array<int, array<string, mixed>>,
     *     total_raw_rows: int
     * }
     */
    public function parse(string $filePath): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(false); // Baca format sel & tanggal

        $spreadsheet = $reader->load($filePath);

        // Cari sheet bernama 'Data' atau gunakan sheet aktif pertama
        $sheet = $spreadsheet->getSheetByName('Data') ?? $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestDataRow();
        $highestCol = $sheet->getHighestDataColumn();
        $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

        // 1. Validasi & Mapping Header (Baris 1)
        $rawHeaders = [];
        $headerMap = [];
        for ($col = 1; $col <= $highestColIndex; $col++) {
            $val = trim((string) $sheet->getCell([$col, 1])->getValue());
            if ($val !== '') {
                $rawHeaders[$col] = $val;
                $headerMap[strtolower($val)] = $col;
            }
        }

        $headerErrors = [];
        $headerValid = true;

        foreach (self::EXPECTED_HEADERS as $expected) {
            if (! isset($headerMap[$expected])) {
                // Cek variasi nama kolom yang umum
                $found = false;
                if ($expected === 'merk/type' && (isset($headerMap['merek']) || isset($headerMap['merk']))) {
                    $found = true;
                } elseif ($expected === 'tgl terima' && isset($headerMap['tanggal terima'])) {
                    $found = true;
                } elseif ($expected === 'jenis barang' && isset($headerMap['jenis'])) {
                    $found = true;
                }

                if (! $found) {
                    $headerValid = false;
                    $headerErrors[] = "Kolom wajib '{$expected}' tidak ditemukan pada baris header (Baris 1).";
                }
            }
        }

        if (! $headerValid) {
            return [
                'header_valid' => false,
                'header_errors' => $headerErrors,
                'rows' => [],
                'total_raw_rows' => max(0, $highestRow - 1),
            ];
        }

        // Tentukan index kolom untuk setiap field
        $colKodeBarang = $headerMap['kode barang'] ?? 1;
        $colNup = $headerMap['nup'] ?? 2;
        $colNamaBarang = $headerMap['nama barang'] ?? 3;
        $colMerek = $headerMap['merk/type'] ?? ($headerMap['merek'] ?? ($headerMap['merk'] ?? 4));
        $colTglTerima = $headerMap['tgl terima'] ?? ($headerMap['tanggal terima'] ?? 5);
        $colTahun = $headerMap['tahun perolehan'] ?? 6;
        $colNilai = $headerMap['nilai perolehan'] ?? 7;
        $colJenis = $headerMap['jenis barang'] ?? ($headerMap['jenis'] ?? 8);
        $colSatuan = $headerMap['satuan'] ?? 9;
        $colSpesifikasi = $headerMap['spesifikasi'] ?? 10;

        $rows = [];

        // 2. Baca Baris Data (Mulai Baris 2)
        for ($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++) {
            // Periksa apakah seluruh baris kosong
            $allEmpty = true;
            for ($c = 1; $c <= 10; $c++) {
                $cellVal = $sheet->getCell([$c, $rowIndex])->getValue();
                if ($cellVal !== null && trim((string) $cellVal) !== '') {
                    $allEmpty = false;
                    break;
                }
            }

            if ($allEmpty) {
                // Abaikan baris kosong
                continue;
            }

            $cellKode = $sheet->getCell([$colKodeBarang, $rowIndex]);
            $cellNup = $sheet->getCell([$colNup, $rowIndex]);
            $cellNama = $sheet->getCell([$colNamaBarang, $rowIndex]);
            $cellMerek = $sheet->getCell([$colMerek, $rowIndex]);
            $cellTgl = $sheet->getCell([$colTglTerima, $rowIndex]);
            $cellTahun = $sheet->getCell([$colTahun, $rowIndex]);
            $cellNilai = $sheet->getCell([$colNilai, $rowIndex]);
            $cellJenis = $sheet->getCell([$colJenis, $rowIndex]);
            $cellSatuan = $sheet->getCell([$colSatuan, $rowIndex]);
            $cellSpesifikasi = $sheet->getCell([$colSpesifikasi, $rowIndex]);

            $kodeBarang = $this->extractKodeBarang($cellKode);
            $nupRaw = $this->extractString($cellNup);
            $namaBarang = $this->extractString($cellNama);
            $merek = $this->extractString($cellMerek);
            $tglTerima = $this->extractDate($cellTgl);
            $tahunPerolehan = $this->extractInteger($cellTahun);
            $nilaiPerolehan = $this->extractNumeric($cellNilai);
            $jenis = strtoupper($this->extractString($cellJenis));
            $satuan = strtolower($this->extractString($cellSatuan));
            $spesifikasi = $this->extractString($cellSpesifikasi);

            $rows[] = [
                'excel_row' => $rowIndex,
                'kode_barang' => $kodeBarang,
                'nup_raw' => $nupRaw,
                'nama_barang' => $namaBarang,
                'merek' => $merek !== '' ? $merek : null,
                'tanggal_terima_raw' => $this->extractString($cellTgl),
                'tanggal_terima' => $tglTerima,
                'tahun_perolehan_raw' => $this->extractString($cellTahun),
                'tahun_perolehan' => $tahunPerolehan,
                'nilai_perolehan_raw' => $this->extractString($cellNilai),
                'nilai_perolehan' => $nilaiPerolehan,
                'jenis' => $jenis,
                'satuan' => $satuan !== '' ? $satuan : 'unit',
                'spesifikasi' => $spesifikasi !== '' ? $spesifikasi : null,
            ];
        }

        return [
            'header_valid' => true,
            'header_errors' => [],
            'rows' => $rows,
            'total_raw_rows' => count($rows),
        ];
    }

    /**
     * Memastikan kode barang diekstrak utuh sebagai string murni (tanpa hilang angka 0 di depan).
     */
    protected function extractKodeBarang(Cell $cell): string
    {
        $val = $cell->getValue();
        if ($val === null) {
            return '';
        }

        // Jika terformat sebagai float/notasi ilmiah
        if (is_numeric($val) && ! is_string($val)) {
            // Hindari notasi ilmiah (mis. 3.08031E+9)
            $str = number_format((float) $val, 0, '', '');
        } else {
            $str = (string) $cell->getFormattedValue();
            if ($str === '' || str_contains(strtolower($str), 'e+')) {
                $str = (string) $val;
            }
        }

        return $this->sanitizeText(trim($str));
    }

    protected function extractString(Cell $cell): string
    {
        $val = $cell->getValue();
        if ($val === null) {
            return '';
        }

        return $this->sanitizeText(trim((string) $val));
    }

    /**
     * Mengekstrak tanggal dari sel Excel (mendukung serial date & string).
     */
    protected function extractDate(Cell $cell): ?string
    {
        $val = $cell->getValue();
        if ($val === null || trim((string) $val) === '') {
            return null;
        }

        // Cek jika serial date Excel
        if (is_numeric($val) && ExcelDate::isDateTime($cell)) {
            try {
                $dt = ExcelDate::excelToDateTimeObject((float) $val);

                return $dt->format('Y-m-d');
            } catch (Exception) {
                // Lanjut ke string parsing
            }
        }

        $str = trim((string) $cell->getFormattedValue() ?: (string) $val);

        // Parsing format teks umum di Indonesia (DD/MM/YYYY, DD-MM-YYYY, YYYY-MM-DD)
        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd.m.Y', 'Y/m/d'];
        foreach ($formats as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, $str);
            if ($dt && $dt->format($fmt) === $str) {
                return $dt->format('Y-m-d');
            }
        }

        // Coba strtotime standar
        $ts = strtotime($str);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }

        return null;
    }

    protected function extractInteger(Cell $cell): ?int
    {
        $val = $cell->getValue();
        if ($val === null || trim((string) $val) === '') {
            return null;
        }

        $cleaned = preg_replace('/[^0-9]/', '', (string) $val);

        return $cleaned !== '' ? (int) $cleaned : null;
    }

    /**
     * Normalisasi nilai rupiah (mengatasi format 1.500.000, 1.500.000,00, dsb).
     */
    protected function extractNumeric(Cell $cell): ?float
    {
        $val = $cell->getValue();
        if ($val === null || trim((string) $val) === '') {
            return null;
        }

        if (is_numeric($val)) {
            return (float) $val;
        }

        $str = trim((string) $val);
        $str = str_ireplace(['rp', 'idr', ' '], '', $str);

        // Jika format indonesia (titik ribuan, koma desimal: 1.500.000,00)
        if (str_contains($str, ',') && str_contains($str, '.')) {
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        } elseif (str_contains($str, ',')) {
            // Hanya ada koma (mis. 1500000,50 atau 1,500,000)
            if (preg_match('/,\d{2}$/', $str)) {
                $str = str_replace(',', '.', $str);
            } else {
                $str = str_replace(',', '', $str);
            }
        } elseif (substr_count($str, '.') > 1) {
            // Titik berkali-kali sebagai pemisah ribuan (1.500.000)
            $str = str_replace('.', '', $str);
        }

        $cleaned = preg_replace('/[^0-9.]/', '', $str);

        return $cleaned !== '' && is_numeric($cleaned) ? (float) $cleaned : null;
    }

    /**
     * Sanitasi teks dari karakter Formula Injection (=, +, -, @) dan non-breaking spaces.
     */
    protected function sanitizeText(string $text): string
    {
        // Ubah non-breaking space (0xA0 / \u00a0) menjadi spasi normal
        $text = str_replace(["\xc2\xa0", "\u{00a0}"], ' ', $text);

        $trimmed = trim($text);

        // Perlindungan CSV / Formula Injection
        if (str_starts_with($trimmed, '=') || str_starts_with($trimmed, '+') || str_starts_with($trimmed, '-') || str_starts_with($trimmed, '@')) {
            // Awali dengan tanda petik tunggal untuk mematikan eksekusi formula jika di-export ulang
            $trimmed = "'".$trimmed;
        }

        return $trimmed;
    }
}
