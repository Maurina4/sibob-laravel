<?php

namespace App\Services\Import;

use DateTime;
use Exception;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class BmnExcelParser
{
    /**
     * Header standar template legacy.
     */
    public const LEGACY_HEADERS = [
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
     * Header standar Master Data Aset (SIMAN / SAKTI).
     */
    public const MASTER_DATA_HEADERS = [
        'no',
        'jenis bmn',
        'kode barang',
        'nup',
        'nama barang',
        'merk',
        'kondisi',
        'tanggal buku pertama',
        'tanggal perolehan',
        'nilai perolehan',
        'lokasi ruang',
    ];

    /**
     * Membaca berkas Excel (.xlsx) dan mengembalikan array baris data ternormalisasi.
     * Mendukung format Master Data Aset (11 kolom) dan Template Legacy (10 kolom).
     *
     * @return array{
     *     header_valid: bool,
     *     header_errors: array<int, string>,
     *     schema: string,
     *     rows: array<int, array<string, mixed>>,
     *     total_raw_rows: int
     * }
     */
    public function parse(string $filePath): array
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(false); // Baca format sel & tanggal

        $spreadsheet = $reader->load($filePath);

        // Cari sheet bernama 'Data' atau sheet aktif pertama
        $sheet = $spreadsheet->getSheetByName('Data') ?? $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestDataRow();
        $highestCol = $sheet->getHighestDataColumn();
        $highestColIndex = Coordinate::columnIndexFromString($highestCol);

        // 1. Baca Header (Baris 1)
        $rawHeaders = [];
        $headerMap = [];
        for ($col = 1; $col <= $highestColIndex; $col++) {
            $val = trim((string) $sheet->getCell([$col, 1])->getValue());
            if ($val !== '') {
                $rawHeaders[$col] = $val;
                // Bersihkan newline dari header
                $cleanHeader = strtolower(trim(preg_replace('/\s+/', ' ', $val)));
                $headerMap[$cleanHeader] = $col;
            }
        }

        // 2. Deteksi Skema: Master Data Aset vs Legacy
        $isMasterDataSchema = isset($headerMap['tanggal perolehan'])
            || isset($headerMap['tgl perolehan'])
            || isset($headerMap['tanggal buku pertama'])
            || isset($headerMap['tgl buku pertama'])
            || isset($headerMap['lokasi ruang'])
            || isset($headerMap['jenis bmn']);

        $schema = $isMasterDataSchema ? 'master_data_aset' : 'legacy';
        $headerErrors = [];
        $headerValid = true;

        if ($isMasterDataSchema) {
            // Kolom wajib Master Data Aset
            $requiredCols = [
                'kode barang' => ['kode barang'],
                'nup' => ['nup'],
                'nama barang' => ['nama barang'],
                'tanggal perolehan' => ['tanggal perolehan', 'tgl perolehan', 'tgl terima', 'tanggal terima'],
                'nilai perolehan' => ['nilai perolehan'],
            ];

            foreach ($requiredCols as $field => $aliases) {
                $found = false;
                foreach ($aliases as $alias) {
                    if (isset($headerMap[$alias])) {
                        $found = true;
                        break;
                    }
                }
                if (! $found) {
                    $headerValid = false;
                    $headerErrors[] = "Kolom wajib '{$field}' tidak ditemukan pada baris header (Baris 1).";
                }
            }
        } else {
            // Kolom wajib Legacy Template
            $requiredCols = [
                'kode barang' => ['kode barang'],
                'nup' => ['nup'],
                'nama barang' => ['nama barang'],
                'tgl terima' => ['tgl terima', 'tanggal terima'],
                'tahun perolehan' => ['tahun perolehan'],
                'nilai perolehan' => ['nilai perolehan'],
            ];

            foreach ($requiredCols as $field => $aliases) {
                $found = false;
                foreach ($aliases as $alias) {
                    if (isset($headerMap[$alias])) {
                        $found = true;
                        break;
                    }
                }
                if (! $found) {
                    $headerValid = false;
                    $headerErrors[] = "Kolom wajib '{$field}' tidak ditemukan pada baris header (Baris 1).";
                }
            }
        }

        if (! $headerValid) {
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $reader);

            return [
                'header_valid' => false,
                'header_errors' => $headerErrors,
                'schema' => $schema,
                'rows' => [],
                'total_raw_rows' => max(0, $highestRow - 1),
            ];
        }

        // Resolusi index kolom berdasarkan nama header (dengan alias pendukung)
        $colKodeBarang = $this->resolveColIndex($headerMap, ['kode barang']);
        $colNup = $this->resolveColIndex($headerMap, ['nup']);
        $colNamaBarang = $this->resolveColIndex($headerMap, ['nama barang']);
        $colMerek = $this->resolveColIndex($headerMap, ['merk', 'merek', 'merk/type', 'merk / type']);
        $colJenis = $this->resolveColIndex($headerMap, ['jenis bmn', 'jenis barang', 'jenis']);
        $colKondisi = $this->resolveColIndex($headerMap, ['kondisi']);
        $colTglBukuPertama = $this->resolveColIndex($headerMap, ['tanggal buku pertama', 'tgl buku pertama']);
        $colTglPerolehan = $this->resolveColIndex($headerMap, ['tanggal perolehan', 'tgl perolehan', 'tgl terima', 'tanggal terima']);
        $colTahunPerolehan = $this->resolveColIndex($headerMap, ['tahun perolehan', 'tahun']);
        $colNilai = $this->resolveColIndex($headerMap, ['nilai perolehan', 'nilai perolehan (rp)', 'nilai']);
        $colLokasiRuang = $this->resolveColIndex($headerMap, ['lokasi ruang', 'lokasi', 'ruangan']);
        $colNip = $this->resolveColIndex($headerMap, [
            'nip penanggung jawab', 'nip penanggungjawab', 'nip pj',
            'nip pengguna', 'nip pemakai', 'nip',
        ]);
        $colSatuan = $this->resolveColIndex($headerMap, ['satuan']);
        $colSpesifikasi = $this->resolveColIndex($headerMap, ['spesifikasi', 'spesifikasi / keterangan', 'keterangan']);

        $rows = [];

        // 3. Baca Baris Data (Mulai Baris 2)
        for ($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++) {
            // Periksa apakah baris seluruhnya kosong
            $allEmpty = true;
            for ($c = 1; $c <= min(15, $highestColIndex); $c++) {
                $cellVal = $sheet->getCell([$c, $rowIndex])->getValue();
                if ($cellVal !== null && trim((string) $cellVal) !== '') {
                    $allEmpty = false;
                    break;
                }
            }

            if ($allEmpty) {
                continue;
            }

            // Ekstrak nilai per sel
            $cellKode = $colKodeBarang ? $sheet->getCell([$colKodeBarang, $rowIndex]) : null;
            $cellNup = $colNup ? $sheet->getCell([$colNup, $rowIndex]) : null;
            $cellNama = $colNamaBarang ? $sheet->getCell([$colNamaBarang, $rowIndex]) : null;
            $cellMerek = $colMerek ? $sheet->getCell([$colMerek, $rowIndex]) : null;
            $cellJenis = $colJenis ? $sheet->getCell([$colJenis, $rowIndex]) : null;
            $cellKondisi = $colKondisi ? $sheet->getCell([$colKondisi, $rowIndex]) : null;
            $cellTglBuku = $colTglBukuPertama ? $sheet->getCell([$colTglBukuPertama, $rowIndex]) : null;
            $cellTglPerolehan = $colTglPerolehan ? $sheet->getCell([$colTglPerolehan, $rowIndex]) : null;
            $cellTahun = $colTahunPerolehan ? $sheet->getCell([$colTahunPerolehan, $rowIndex]) : null;
            $cellNilai = $colNilai ? $sheet->getCell([$colNilai, $rowIndex]) : null;
            $cellLokasi = $colLokasiRuang ? $sheet->getCell([$colLokasiRuang, $rowIndex]) : null;
            $cellNip = $colNip ? $sheet->getCell([$colNip, $rowIndex]) : null;
            $cellSatuan = $colSatuan ? $sheet->getCell([$colSatuan, $rowIndex]) : null;
            $cellSpesifikasi = $colSpesifikasi ? $sheet->getCell([$colSpesifikasi, $rowIndex]) : null;

            $kodeBarang = $cellKode ? $this->extractKodeBarang($cellKode) : '';
            $nupRaw = $cellNup ? $this->extractString($cellNup) : '';
            $namaBarang = $cellNama ? $this->extractString($cellNama) : '';
            $merek = $cellMerek ? $this->extractString($cellMerek) : '';
            $jenisRaw = $cellJenis ? $this->extractString($cellJenis) : '';
            $kondisiRaw = $cellKondisi ? $this->extractString($cellKondisi) : '';
            $satuanRaw = $cellSatuan ? $this->extractString($cellSatuan) : '';
            $spesifikasi = $cellSpesifikasi ? $this->extractString($cellSpesifikasi) : '';
            $lokasiRaw = $cellLokasi ? $this->extractString($cellLokasi) : '';
            $nipRaw = $cellNip ? $this->extractString($cellNip) : '';
            $cleanNip = preg_replace('/[^0-9]/', '', $nipRaw);

            // Ekstrak Tanggal Perolehan (Fleksibel: DD/MM/YYYY, Jan-78, YYYY, dsb)
            $parsedTglPerolehan = $cellTglPerolehan ? $this->extractFlexibleDate($cellTglPerolehan) : null;
            $tglPerolehan = $parsedTglPerolehan['date'] ?? null;
            $tglPerolehanRaw = $parsedTglPerolehan['raw'] ?? ($cellTglPerolehan ? $this->extractRawValue($cellTglPerolehan) : '');

            // Ekstrak Tanggal Buku Pertama (Opsional / Nullable)
            $parsedTglBuku = $cellTglBuku ? $this->extractFlexibleDate($cellTglBuku) : null;
            $tglBukuPertama = $parsedTglBuku['date'] ?? null;
            $tglBukuPertamaRaw = $parsedTglBuku['raw'] ?? ($cellTglBuku ? $this->extractRawValue($cellTglBuku) : '');

            // Ekstrak Tahun Perolehan (Gunakan kolom Tahun Perolehan jika ada, atau ambil tahun dari Tanggal Perolehan)
            $tahunPerolehan = null;
            $tahunPerolehanRaw = '';
            if ($cellTahun && trim((string) $cellTahun->getValue()) !== '') {
                $tahunPerolehan = $this->extractInteger($cellTahun);
                $tahunPerolehanRaw = $this->extractString($cellTahun);
            } elseif ($parsedTglPerolehan && ! empty($parsedTglPerolehan['year'])) {
                $tahunPerolehan = $parsedTglPerolehan['year'];
                $tahunPerolehanRaw = (string) $parsedTglPerolehan['year'];
            }

            // Ekstrak Nilai Perolehan
            $nilaiPerolehan = $cellNilai ? $this->extractNumeric($cellNilai) : null;
            $nilaiPerolehanRaw = $cellNilai ? $this->extractRawValue($cellNilai) : '';

            // Nilai Default Cerdas
            $jenis = $jenisRaw !== '' ? strtoupper($jenisRaw) : 'UMUM';
            $kondisi = $kondisiRaw !== '' ? $kondisiRaw : 'Baik';
            $satuan = $satuanRaw !== '' ? strtolower($satuanRaw) : 'unit';
            $lokasiRuang = $lokasiRaw !== '' ? $lokasiRaw : ($isMasterDataSchema ? 'Belum berlokasi' : 'Gudang BMN');

            $rows[] = [
                'excel_row' => $rowIndex,
                'schema' => $schema,
                'kode_barang' => $kodeBarang,
                'nup_raw' => $nupRaw,
                'nama_barang' => $namaBarang,
                'merek' => $merek !== '' ? $merek : null,
                'kondisi' => $kondisi,
                'tanggal_buku_pertama_raw' => $tglBukuPertamaRaw,
                'tanggal_buku_pertama' => $tglBukuPertama,
                'tanggal_terima_raw' => $tglPerolehanRaw,
                'tanggal_terima' => $tglPerolehan,
                'tahun_perolehan_raw' => $tahunPerolehanRaw,
                'tahun_perolehan' => $tahunPerolehan,
                'nilai_perolehan_raw' => $nilaiPerolehanRaw,
                'nilai_perolehan' => $nilaiPerolehan,
                'lokasi_ruang' => $lokasiRuang,
                'nip_raw' => $nipRaw,
                'nip' => $cleanNip,
                'jenis' => $jenis,
                'satuan' => $satuan,
                'spesifikasi' => $spesifikasi !== '' ? $spesifikasi : null,
            ];
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet, $reader);

        return [
            'header_valid' => true,
            'header_errors' => [],
            'schema' => $schema,
            'rows' => $rows,
            'total_raw_rows' => count($rows),
        ];
    }

    /**
     * Resolusi nomor kolom dari daftar alias yang didukung.
     *
     * @param  array<string, int>  $headerMap
     * @param  array<int, string>  $aliases
     */
    protected function resolveColIndex(array $headerMap, array $aliases): ?int
    {
        foreach ($aliases as $alias) {
            if (isset($headerMap[$alias])) {
                return $headerMap[$alias];
            }
        }

        return null;
    }

    /**
     * Mengekstrak tanggal secara cerdas dan fleksibel.
     * Mendukung:
     * 1. Excel Serial Date (isDateTime)
     * 2. Format tanggal umum Indonesia / ISO (DD/MM/YYYY, DD-MM-YYYY, YYYY-MM-DD, dsb.)
     * 3. Format Bulan-Tahun (contoh: Jan-78 -> 1978-01-01, Jan-81 -> 1981-01-01, Feb-24 -> 2024-02-01)
     * 4. Format 4 Digit Tahun (contoh: 1978 -> 1978-01-01)
     *
     * @return array{date: string, year: int, raw: string}|null
     */
    public function extractFlexibleDate(Cell $cell): ?array
    {
        $val = $cell->getValue();
        if ($val === null || trim((string) $val) === '') {
            return null;
        }

        $formatted = trim((string) $cell->getFormattedValue());
        $rawStr = $formatted !== '' ? $formatted : trim((string) $val);

        // 1. Cek jika Excel Date Serial (baik metadata cell isDateTime maupun numeric serial date)
        if (is_numeric($val)) {
            $numVal = (float) $val;

            // Jika cell secara eksplisit bertipe date di Excel
            if (ExcelDate::isDateTime($cell)) {
                try {
                    $dt = ExcelDate::excelToDateTimeObject($numVal);
                    $yearVal = (int) $dt->format('Y');

                    return [
                        'date' => $dt->format('Y-m-d'),
                        'year' => $yearVal,
                        'raw' => ($formatted !== '' && ! is_numeric($formatted)) ? $formatted : $dt->format('d/m/Y'),
                    ];
                } catch (Exception) {
                    // Lanjut ke string parsing
                }
            }

            // Jika numeric tanpa format date: cek apakah 4 digit tahun (1900-2099)
            if (preg_match('/^(19\d{2}|20\d{2})$/', trim((string) $val), $m)) {
                $yearVal = (int) $m[1];

                return [
                    'date' => "{$yearVal}-01-01",
                    'year' => $yearVal,
                    'raw' => (string) $val,
                ];
            }

            // Jika angka serial Excel murni (misal 28491, 44561, dsb rentang 1 s.d. 100000)
            if ($numVal >= 1 && $numVal <= 100000) {
                try {
                    $dt = ExcelDate::excelToDateTimeObject($numVal);
                    $yearVal = (int) $dt->format('Y');
                    if ($yearVal >= 1900 && $yearVal <= 2100) {
                        return [
                            'date' => $dt->format('Y-m-d'),
                            'year' => $yearVal,
                            'raw' => ($formatted !== '' && ! is_numeric($formatted)) ? $formatted : $dt->format('d/m/Y'),
                        ];
                    }
                } catch (Exception) {
                    // Lanjut ke string parsing
                }
            }
        }

        return $this->parseFlexibleDateString($rawStr);
    }

    /**
     * Parsing string tanggal dengan aturan konversi cerdas Indonesia & format BMN.
     *
     * @return array{date: string, year: int, raw: string}|null
     */
    public function parseFlexibleDateString(string $str): ?array
    {
        $str = trim($str);
        if ($str === '') {
            return null;
        }

        // 1. Cek jika numeric string (4-digit tahun atau Excel Date Serial)
        if (is_numeric($str)) {
            // Pola Tahun 4 Digit saja (contoh: 1978 atau 2016)
            if (preg_match('/^(19\d{2}|20\d{2})$/', $str, $matches)) {
                $yearVal = (int) $matches[1];

                return [
                    'date' => "{$yearVal}-01-01",
                    'year' => $yearVal,
                    'raw' => $str,
                ];
            }

            // Excel serial date (1 s.d. 100000, contoh: 28491, 44561)
            $numVal = (float) $str;
            if ($numVal >= 1 && $numVal <= 100000) {
                try {
                    $dt = ExcelDate::excelToDateTimeObject($numVal);
                    $yearVal = (int) $dt->format('Y');
                    if ($yearVal >= 1900 && $yearVal <= 2100) {
                        return [
                            'date' => $dt->format('Y-m-d'),
                            'year' => $yearVal,
                            'raw' => $dt->format('d/m/Y'),
                        ];
                    }
                } catch (Exception) {
                    // Lanjut
                }
            }
        }

        // Kamus bulan Indonesia & Inggris
        $monthMap = [
            'januari' => 'Jan', 'jan' => 'Jan',
            'februari' => 'Feb', 'feb' => 'Feb',
            'maret' => 'Mar', 'mar' => 'Mar',
            'april' => 'Apr', 'apr' => 'Apr',
            'mei' => 'May', 'may' => 'May',
            'juni' => 'Jun', 'jun' => 'Jun',
            'juli' => 'Jul', 'jul' => 'Jul',
            'agustus' => 'Aug', 'agu' => 'Aug', 'ags' => 'Aug', 'aug' => 'Aug',
            'september' => 'Sep', 'sep' => 'Sep',
            'oktober' => 'Oct', 'okt' => 'Oct', 'oct' => 'Oct',
            'november' => 'Nov', 'nop' => 'Nov', 'nov' => 'Nov',
            'desember' => 'Dec', 'des' => 'Dec', 'dec' => 'Dec',
        ];

        // Format DD/MM/YYYY, DD-MM-YYYY, YYYY-MM-DD
        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd.m.Y', 'Y/m/d'];
        foreach ($formats as $fmt) {
            $dt = DateTime::createFromFormat($fmt, $str);
            if ($dt && $dt->format($fmt) === $str) {
                return [
                    'date' => $dt->format('Y-m-d'),
                    'year' => (int) $dt->format('Y'),
                    'raw' => $str,
                ];
            }
        }

        // Pola Bulan-Tahun: Jan-78, Jan-81, Mei-2016, Agu-05, dsb.
        if (preg_match('/^([a-zA-Z]+)[-\s\/](\d{2,4})$/', $str, $matches)) {
            $monthKey = strtolower($matches[1]);
            $yearVal = (int) $matches[2];

            if (isset($monthMap[$monthKey])) {
                // Auto-konversi tahun 2 digit (> 50 -> 19xx, <= 50 -> 20xx)
                if (strlen($matches[2]) === 2) {
                    $yearVal = $yearVal > 50 ? (1900 + $yearVal) : (2000 + $yearVal);
                }

                $englishMonth = $monthMap[$monthKey];
                $dt = DateTime::createFromFormat('M Y d', "{$englishMonth} {$yearVal} 01");
                if ($dt) {
                    return [
                        'date' => $dt->format('Y-m-d'),
                        'year' => $yearVal,
                        'raw' => $str,
                    ];
                }
            }
        }

        // Normalisasi nama bulan Indonesia untuk format teks lengkap (contoh: 18 Juni 2013, 31 Desember 2021)
        $normStr = $str;
        foreach ($monthMap as $idMonth => $enMonth) {
            $normStr = preg_replace('/\b'.preg_quote($idMonth, '/').'\b/i', $enMonth, $normStr);
        }

        $textFormats = ['j M Y', 'j-M-Y', 'j M y', 'j-M-y', 'j F Y', 'j-F-Y', 'd M Y', 'd-M-Y', 'd M y', 'd-M-y'];
        foreach ($textFormats as $fmt) {
            $dt = DateTime::createFromFormat($fmt, $normStr);
            if ($dt && $dt->format($fmt) === $normStr) {
                return [
                    'date' => $dt->format('Y-m-d'),
                    'year' => (int) $dt->format('Y'),
                    'raw' => $str,
                ];
            }
        }

        // Fallback strtotime standar
        $ts = strtotime($normStr);
        if ($ts !== false) {
            return [
                'date' => date('Y-m-d', $ts),
                'year' => (int) date('Y', $ts),
                'raw' => $str,
            ];
        }

        return null;
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

    protected function extractRawValue(Cell $cell): string
    {
        $formatted = trim((string) $cell->getFormattedValue());
        if ($formatted !== '') {
            return $this->sanitizeText($formatted);
        }

        $val = $cell->getValue();

        return $val !== null ? $this->sanitizeText(trim((string) $val)) : '';
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
            if (preg_match('/,\d{2}$/', $str)) {
                $str = str_replace(',', '.', $str);
            } else {
                $str = str_replace(',', '', $str);
            }
        } elseif (substr_count($str, '.') > 1) {
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
        $text = str_replace(["\xc2\xa0", "\u{00a0}"], ' ', $text);
        $trimmed = trim($text);

        if (str_starts_with($trimmed, '=') || str_starts_with($trimmed, '+') || str_starts_with($trimmed, '-') || str_starts_with($trimmed, '@')) {
            $trimmed = "'".$trimmed;
        }

        return $trimmed;
    }
}
