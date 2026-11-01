<?php

namespace App\Services\Import;

use App\Models\Bmn;
use Illuminate\Support\Carbon;

class BmnImportValidator
{
    public const VALID_JENIS = [
        'BMN-TIK',
        'BMN-LAB',
        'BMN-NON TIK',
    ];

    /**
     * Memvalidasi seluruh baris hasil parsing Excel.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{
     *     is_valid: bool,
     *     total_rows: int,
     *     valid_count: int,
     *     error_count: int,
     *     validated_rows: array<int, array<string, mixed>>,
     *     error_messages: array<int, array{row: int, column: string, message: string}>
     * }
     */
    public function validate(array $rows): array
    {
        $today = Carbon::today()->format('Y-m-d');
        $currentYear = (int) date('Y');

        $seenInFile = [];
        $uniquePairs = [];

        // Kumpulkan pasangan (kode_barang, nup) untuk pengecekan batch ke DB
        foreach ($rows as $r) {
            $kode = trim((string) ($r['kode_barang'] ?? ''));
            $nup = trim((string) ($r['nup_raw'] ?? ''));
            if ($kode !== '' && $nup !== '') {
                $uniquePairs[] = ['kode' => $kode, 'nup' => $nup];
            }
        }

        // Ambil data duplikat yang sudah ada di database (bmns aktif / belum dihapus)
        $existingDbMap = [];
        if (! empty($uniquePairs)) {
            $uniqueKodes = array_unique(array_column($uniquePairs, 'kode'));
            $existingDbRecords = Bmn::query()
                ->where('dihapus', false)
                ->whereIn('kode_barang', $uniqueKodes)
                ->select(['kode_barang', 'nup'])
                ->get();

            foreach ($existingDbRecords as $record) {
                $key = strtolower(trim((string) $record->kode_barang)).'#'.trim((string) $record->nup);
                $existingDbMap[$key] = true;
            }
        }

        $validatedRows = [];
        $allErrorMessages = [];
        $validCount = 0;
        $errorCount = 0;

        foreach ($rows as $row) {
            $rowErrors = [];
            $excelRow = $row['excel_row'];

            // 1. Kode Barang
            $kode = trim((string) ($row['kode_barang'] ?? ''));
            if ($kode === '') {
                $rowErrors['kode_barang'] = 'Kode Barang wajib diisi.';
            }

            // 2. NUP
            $nupRaw = trim((string) ($row['nup_raw'] ?? ''));
            $nupInt = null;
            if ($nupRaw === '') {
                $rowErrors['nup'] = 'NUP wajib diisi.';
            } elseif (! ctype_digit($nupRaw) || (int) $nupRaw <= 0) {
                $rowErrors['nup'] = 'NUP harus berupa bilangan bulat positif lebih dari 0.';
            } else {
                $nupInt = (int) $nupRaw;
            }

            // 3. Nama Barang
            $nama = trim((string) ($row['nama_barang'] ?? ''));
            if ($nama === '') {
                $rowErrors['nama_barang'] = 'Nama Barang wajib diisi.';
            }

            // 4. Merek / Type
            $merek = trim((string) ($row['merek'] ?? ''));
            if ($merek === '') {
                $rowErrors['merek'] = 'Merk/Type wajib diisi.';
            }

            // 5. Tanggal Terima
            $tglTerima = $row['tanggal_terima'];
            $tglRaw = trim((string) ($row['tanggal_terima_raw'] ?? ''));
            if ($tglRaw === '') {
                $rowErrors['tanggal_terima'] = 'Tgl Terima wajib diisi.';
            } elseif ($tglTerima === null) {
                $rowErrors['tanggal_terima'] = "Format Tgl Terima tidak valid ('{$tglRaw}'). Gunakan format DD/MM/YYYY.";
            } elseif ($tglTerima > $today) {
                $rowErrors['tanggal_terima'] = 'Tgl Terima tidak boleh tanggal di masa depan (melebihi hari ini).';
            }

            // 6. Tahun Perolehan
            $tahunRaw = trim((string) ($row['tahun_perolehan_raw'] ?? ''));
            $tahunInt = $row['tahun_perolehan'];
            if ($tahunRaw === '') {
                $rowErrors['tahun_perolehan'] = 'Tahun Perolehan wajib diisi.';
            } elseif ($tahunInt === null || strlen((string) $tahunInt) !== 4 || $tahunInt < 1945 || $tahunInt > $currentYear) {
                $rowErrors['tahun_perolehan'] = "Tahun Perolehan harus 4 digit antara 1945 s.d. {$currentYear}.";
            } elseif ($tglTerima !== null) {
                $yearOfTgl = (int) substr($tglTerima, 0, 4);
                if ($tahunInt > $yearOfTgl) {
                    $rowErrors['tahun_perolehan'] = "Tahun Perolehan ({$tahunInt}) tidak boleh lebih besar dari tahun Tgl Terima ({$yearOfTgl}).";
                }
            }

            // 7. Nilai Perolehan
            $nilaiRaw = trim((string) ($row['nilai_perolehan_raw'] ?? ''));
            $nilaiFloat = $row['nilai_perolehan'];
            if ($nilaiRaw === '') {
                $rowErrors['nilai_perolehan'] = 'Nilai Perolehan wajib diisi.';
            } elseif ($nilaiFloat === null || $nilaiFloat < 0) {
                $rowErrors['nilai_perolehan'] = "Nilai Perolehan harus berupa angka positif (diterima: '{$nilaiRaw}').";
            }

            // 8. Jenis Barang
            $jenis = strtoupper(trim((string) ($row['jenis'] ?? '')));
            if ($jenis === '') {
                $rowErrors['jenis'] = 'Jenis Barang wajib dipilih.';
            } elseif (! in_array($jenis, self::VALID_JENIS, true)) {
                $rowErrors['jenis'] = "Jenis Barang '{$jenis}' tidak valid. Pilihan: BMN-TIK, BMN-LAB, BMN-NON TIK.";
            }

            // 9. Satuan
            $satuan = trim((string) ($row['satuan'] ?? ''));
            if ($satuan === '') {
                $rowErrors['satuan'] = 'Satuan wajib diisi.';
            }

            // 10. Cek Duplikasi Kombinasi (Kode Barang + NUP)
            if ($kode !== '' && $nupInt !== null) {
                $pairKey = strtolower($kode).'#'.$nupInt;

                // Cek duplikat dalam file yang sama
                if (isset($seenInFile[$pairKey])) {
                    $prevRow = $seenInFile[$pairKey];
                    $rowErrors['duplikat'] = "Kombinasi Kode Barang ({$kode}) dan NUP ({$nupInt}) duplikat dengan Baris {$prevRow} di dalam file ini.";
                } else {
                    $seenInFile[$pairKey] = $excelRow;
                }

                // Cek duplikat terhadap database
                if (isset($existingDbMap[$pairKey])) {
                    $rowErrors['duplikat_db'] = "Kombinasi Kode Barang ({$kode}) dan NUP ({$nupInt}) sudah terdaftar di database.";
                }
            }

            $isValid = empty($rowErrors);
            if ($isValid) {
                $validCount++;
            } else {
                $errorCount++;
                foreach ($rowErrors as $colName => $msg) {
                    $allErrorMessages[] = [
                        'row' => $excelRow,
                        'column' => $colName,
                        'message' => $msg,
                    ];
                }
            }

            $validatedRows[] = array_merge($row, [
                'nup' => $nupInt,
                'jenis' => $jenis,
                'satuan' => $satuan ?: 'unit',
                'is_valid' => $isValid,
                'errors' => $rowErrors,
            ]);
        }

        return [
            'is_valid' => $errorCount === 0,
            'total_rows' => count($rows),
            'valid_count' => $validCount,
            'error_count' => $errorCount,
            'validated_rows' => $validatedRows,
            'error_messages' => $allErrorMessages,
        ];
    }
}
