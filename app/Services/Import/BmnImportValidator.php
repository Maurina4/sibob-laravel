<?php

namespace App\Services\Import;

use App\Models\Bmn;
use App\Models\Pegawai;
use Illuminate\Support\Carbon;

class BmnImportValidator
{
    public function __construct(
        protected ?BmnExcelParser $parser = null
    ) {
        $this->parser = $parser ?? new BmnExcelParser;
    }

    /**
     * Memvalidasi seluruh baris hasil parsing Excel.
     * Mendukung format Master Data Aset (11 kolom) dan Template Legacy (10 kolom).
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
        @set_time_limit(300);

        $today = Carbon::today()->format('Y-m-d');
        $currentYear = (int) date('Y');

        $seenInFile = [];
        $uniquePairs = [];

        // Kumpulkan pasangan (kode_barang, nup) untuk pengecekan batch ke DB
        foreach ($rows as $r) {
            $kode = str_replace(['.', '-', ' '], '', trim((string) ($r['kode_barang'] ?? '')));
            $nup = trim((string) ($r['nup_raw'] ?? ''));
            if ($kode !== '' && $nup !== '') {
                $uniquePairs[] = ['kode' => $kode, 'nup' => $nup];
            }
        }

        // Ambil data duplikat yang sudah ada di database (bmns aktif / belum dihapus)
        // Dijalankan dalam chunk 500 kode barang untuk keamanan batas parameter SQLite / MySQL
        $existingDbMap = [];
        if (! empty($uniquePairs)) {
            $uniqueKodes = array_values(array_unique(array_column($uniquePairs, 'kode')));
            foreach (array_chunk($uniqueKodes, 500) as $kodeChunk) {
                $existingDbRecords = Bmn::query()
                    ->where('dihapus', false)
                    ->whereIn('kode_barang', $kodeChunk)
                    ->select(['kode_barang', 'nup'])
                    ->get();

                foreach ($existingDbRecords as $record) {
                    $key = strtolower(trim((string) $record->kode_barang)).'#'.trim((string) $record->nup);
                    $existingDbMap[$key] = true;
                }
            }
        }

        // Ambil peta seluruh Pegawai berdasarkan NIP yang sudah dibersihkan
        $pegawaiMap = Pegawai::query()
            ->select(['id', 'nama', 'nip', 'jabatan'])
            ->get()
            ->keyBy(fn ($p) => preg_replace('/[^0-9]/', '', (string) $p->nip))
            ->toArray();

        $validatedRows = [];
        $allErrorMessages = [];
        $validCount = 0;
        $errorCount = 0;

        foreach ($rows as $row) {
            $rowErrors = [];
            $excelRow = $row['excel_row'];

            // 1. Kode Barang (wajib 10 digit angka)
            $rawKode = trim((string) ($row['kode_barang'] ?? ''));
            $cleanKode = str_replace(['.', '-', ' '], '', $rawKode);

            if ($rawKode === '') {
                $rowErrors['kode_barang'] = 'Kode Barang wajib diisi.';
                $kode = '';
            } elseif (! ctype_digit($cleanKode) || strlen($cleanKode) !== 10) {
                $rowErrors['kode_barang'] = 'Kode Barang harus berupa 10 digit angka (contoh: 3080305002 atau 3.08.03.05.002).';
                $kode = $cleanKode;
            } else {
                $kode = $cleanKode;
                $row['kode_barang'] = $cleanKode;
            }

            // 2. NUP (wajib bilangan bulat positif)
            $nupRaw = trim((string) ($row['nup_raw'] ?? ''));
            $nupInt = null;
            if ($nupRaw === '') {
                $rowErrors['nup'] = 'NUP wajib diisi.';
            } elseif (! ctype_digit($nupRaw) || (int) $nupRaw <= 0) {
                $rowErrors['nup'] = 'NUP harus berupa bilangan bulat positif lebih dari 0.';
            } else {
                $nupInt = (int) $nupRaw;
            }

            // 3. Nama Barang (wajib teks)
            $nama = trim((string) ($row['nama_barang'] ?? ''));
            if ($nama === '') {
                $rowErrors['nama_barang'] = 'Nama Barang wajib diisi.';
            }

            // 4. Merek / Brand (opsional pada Master Data Aset seperti tanah/gedung)
            $merek = trim((string) ($row['merek'] ?? ''));
            if (strlen($merek) > 255) {
                $rowErrors['merek'] = 'Merk/Type maksimal 255 karakter.';
            }

            // 5. Tanggal Perolehan / Terima (wajib, fleksibel)
            $tglTerima = $row['tanggal_terima'] ?? null;
            $tglRaw = trim((string) ($row['tanggal_terima_raw'] ?? ''));

            if ($tglTerima === null && $tglRaw !== '') {
                $parsed = $this->parser->parseFlexibleDateString($tglRaw);
                if ($parsed) {
                    $tglTerima = $parsed['date'];
                    $row['tanggal_terima'] = $tglTerima;
                }
            }

            if ($tglRaw === '') {
                $rowErrors['tanggal_terima'] = 'Tanggal Perolehan wajib diisi.';
            } elseif ($tglTerima === null) {
                $rowErrors['tanggal_terima'] = "Format Tanggal Perolehan tidak valid ('{$tglRaw}'). Gunakan format DD/MM/YYYY atau Bulan-Tahun (contoh: Jan-78).";
            } elseif ($tglTerima > $today) {
                $rowErrors['tanggal_terima'] = 'Tanggal Perolehan tidak boleh melebihi hari ini.';
            }

            // 6. Tahun Perolehan (otomatis diekstrak dari Tanggal Perolehan jika tidak ada kolom terpisah)
            $tahunRaw = trim((string) ($row['tahun_perolehan_raw'] ?? ''));
            $tahunInt = $row['tahun_perolehan'] ?? null;

            if ($tahunInt === null && $tglTerima !== null) {
                $tahunInt = (int) substr($tglTerima, 0, 4);
                $row['tahun_perolehan'] = $tahunInt;
            }

            if ($tahunInt === null || strlen((string) $tahunInt) !== 4 || $tahunInt < 1945 || $tahunInt > $currentYear) {
                $rowErrors['tahun_perolehan'] = "Tahun Perolehan harus 4 digit antara 1945 s.d. {$currentYear}.";
            } elseif ($tglTerima !== null) {
                $yearOfTgl = (int) substr($tglTerima, 0, 4);
                if ($tahunInt > $yearOfTgl) {
                    $rowErrors['tahun_perolehan'] = "Tahun Perolehan ({$tahunInt}) tidak boleh lebih besar dari tahun Tanggal Perolehan ({$yearOfTgl}).";
                }
            }

            // 7. Tanggal Buku Pertama (opsional/nullable)
            $tglBukuRaw = trim((string) ($row['tanggal_buku_pertama_raw'] ?? ''));
            $tglBuku = $row['tanggal_buku_pertama'] ?? null;

            if ($tglBuku === null && $tglBukuRaw !== '') {
                $parsedBuku = $this->parser->parseFlexibleDateString($tglBukuRaw);
                if ($parsedBuku) {
                    $tglBuku = $parsedBuku['date'];
                    $row['tanggal_buku_pertama'] = $tglBuku;
                }
            }

            if ($tglBukuRaw !== '' && $tglBuku === null) {
                $rowErrors['tanggal_buku_pertama'] = "Format Tanggal Buku Pertama tidak valid ('{$tglBukuRaw}').";
            } elseif ($tglBuku !== null && $tglBuku > $today) {
                $rowErrors['tanggal_buku_pertama'] = 'Tanggal Buku Pertama tidak boleh melebihi hari ini.';
            }

            // 8. Nilai Perolehan (wajib angka positif)
            $nilaiRaw = trim((string) ($row['nilai_perolehan_raw'] ?? ''));
            $nilaiFloat = $row['nilai_perolehan'] ?? null;
            if ($nilaiRaw === '') {
                $rowErrors['nilai_perolehan'] = 'Nilai Perolehan wajib diisi.';
            } elseif ($nilaiFloat === null || $nilaiFloat < 0) {
                $rowErrors['nilai_perolehan'] = "Nilai Perolehan harus berupa angka positif (diterima: '{$nilaiRaw}').";
            }

            // 9. Jenis BMN (fleksibel: TANAH, ALAT BESAR, BMN-TIK, dsb.)
            $jenis = strtoupper(trim((string) ($row['jenis'] ?? '')));
            if ($jenis === '') {
                $jenis = 'UMUM';
            }

            // 10. Kondisi (opsional, default: Baik; jika barang rusak maka Rusak Berat)
            $kondisi = trim((string) ($row['kondisi'] ?? ''));
            if ($kondisi === '' || strcasecmp($kondisi, 'baik') === 0) {
                $kondisi = 'Baik';
            } elseif (in_array(strtolower($kondisi), ['rusak', 'rusak ringan', 'rusak berat', 'berat', 'ringan', 'sedang', 'rusak sedang'])) {
                $kondisi = 'Rusak Berat';
            }

            // 11. Lokasi Ruang (opsional, default: Belum berlokasi / Gudang BMN)
            $lokasiRuang = trim((string) ($row['lokasi_ruang'] ?? ''));
            if ($lokasiRuang === '') {
                $lokasiRuang = ($row['schema'] ?? '') === 'legacy' ? 'Gudang BMN' : 'Belum berlokasi';
            }

            // Hitung klasifikasi cerdas & Dual Tracking untuk preview
            $lokasiLower = strtolower($lokasiRuang);
            $isLabLocation = $lokasiLower !== 'belum berlokasi' && (
                preg_match('/^A[\d.]/i', $lokasiRuang) ||
                str_contains($lokasiLower, 'lab') ||
                str_contains($lokasiLower, 'laboratorium')
            );

            $jenisUpper = strtoupper($jenis);
            $isNonTik = str_contains($jenisUpper, 'NON TIK') || str_contains($jenisUpper, 'NON-TIK') || str_contains($jenisUpper, 'NONTIK');
            $isPureTik = ! $isNonTik && str_contains($jenisUpper, 'TIK');
            $isLab = $isLabLocation || str_contains($jenisUpper, 'LAB');

            $jenisTerklasifikasi = match (true) {
                $isPureTik && $isLab => 'BMN-TIK & LAB',
                $isPureTik => 'BMN-TIK',
                $isLab => 'BMN-LAB',
                default => 'BMN-NON TIK',
            };

            // 12. Satuan (opsional, default: unit)
            $satuan = trim((string) ($row['satuan'] ?? ''));
            if ($satuan === '') {
                $satuan = 'unit';
            }

            // 13. Resolusi NIP Penanggung Jawab
            $rawNip = trim((string) ($row['nip_raw'] ?? ''));
            $cleanNip = trim((string) ($row['nip'] ?? ''));
            $pjStatus = 'empty';
            $pjNama = 'Pengelola BMN';
            $pjNip = '-';
            $pjId = null;
            $rowWarnings = [];

            if ($cleanNip !== '') {
                if (isset($pegawaiMap[$cleanNip])) {
                    $peg = $pegawaiMap[$cleanNip];
                    $pjStatus = 'matched';
                    $pjNama = $peg['nama'];
                    $pjNip = $peg['nip'];
                    $pjId = $peg['id'];
                } else {
                    $pjStatus = 'unmatched';
                    $pjNama = 'Pengelola BMN';
                    $pjNip = '-';
                    $rowWarnings['nip_unmatched'] = "NIP ({$rawNip}) tidak ditemukan di Master Pegawai. Dialihkan ke Pengelola BMN.";
                }
            }

            // 14. Cek Duplikasi Kombinasi (Kode Barang + NUP)
            if ($kode !== '' && $nupInt !== null) {
                $pairKey = strtolower($kode).'#'.$nupInt;

                // Cek duplikat dalam file yang sama
                if (isset($seenInFile[$pairKey])) {
                    $prevRow = $seenInFile[$pairKey];
                    $rowErrors['duplikat'] = "Kombinasi Kode Barang ({$kode}) dan NUP ({$nupInt}) duplikat dengan Baris {$prevRow} di dalam berkas ini.";
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
                'jenis_asal' => $jenis,
                'jenis' => $jenisTerklasifikasi,
                'jenis_terklasifikasi' => $jenisTerklasifikasi,
                'kondisi' => $kondisi,
                'satuan' => $satuan,
                'lokasi_ruang' => $lokasiRuang,
                'penanggungjawab' => $pjNama,
                'penanggungjawab_nip' => $pjNip,
                'penanggung_jawab_id' => $pjId,
                'pj_status' => $pjStatus,
                'warnings' => $rowWarnings,
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
