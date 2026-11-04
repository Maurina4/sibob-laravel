<?php

namespace App\Services\Import;

use App\Models\ImportLog;
use App\Services\BmnService;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BmnImportService
{
    public function __construct(
        protected BmnExcelParser $parser,
        protected BmnImportValidator $validator,
        protected BmnService $bmnService
    ) {}

    /**
     * Memproses file upload untuk menghasilkan preview validasi.
     *
     * @return array{
     *     success: bool,
     *     message?: string,
     *     token?: string,
     *     log_id?: int,
     *     summary?: array<string, mixed>,
     *     rows?: array<int, array<string, mixed>>,
     *     header_errors?: array<int, string>
     * }
     */
    public function processPreview(UploadedFile $file, int $userId): array
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        // 1. Validasi ekstensi & mime
        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext !== 'xlsx') {
            return [
                'success' => false,
                'message' => 'Format file tidak didukung. Harap unggah file spreadsheet Microsoft Excel berformat .xlsx.',
            ];
        }

        // Ukuran maks 5 MB
        if ($file->getSize() > 5 * 1024 * 1024) {
            return [
                'success' => false,
                'message' => 'Ukuran file melebihi batas maksimum 5 MB.',
            ];
        }

        // Buat direktori temporary di storage
        $token = bin2hex(random_bytes(16));
        $tempFilename = 'import_'.$token.'.xlsx';
        $storedPath = $file->storeAs('imports', $tempFilename);
        $fullPath = Storage::path($storedPath);

        // 2. Parse file
        try {
            $parseResult = $this->parser->parse($fullPath);
        } catch (Exception $e) {
            Storage::delete($storedPath);

            return [
                'success' => false,
                'message' => 'Gagal membaca isi berkas Excel: '.$e->getMessage(),
            ];
        }

        // Cek jika header tidak sesuai
        if (! $parseResult['header_valid']) {
            Storage::delete($storedPath);

            return [
                'success' => false,
                'message' => 'Format kolom header tidak sesuai dengan skema Master Data Aset maupun template BMN.',
                'header_errors' => $parseResult['header_errors'],
            ];
        }

        // Cek jika baris melebihi 5.000
        if ($parseResult['total_raw_rows'] > 5000) {
            Storage::delete($storedPath);

            return [
                'success' => false,
                'message' => 'Jumlah baris data dalam berkas ('.$parseResult['total_raw_rows'].') melebihi batas maksimum 5.000 baris.',
            ];
        }

        // Cek jika file tidak memiliki baris data
        if (empty($parseResult['rows'])) {
            Storage::delete($storedPath);

            return [
                'success' => false,
                'message' => 'Berkas Excel tidak memiliki baris data (kosong). Harap isi data pada sheet "Data" atau sheet utama.',
            ];
        }

        // 3. Validasi Baris Data
        $validationResult = $this->validator->validate($parseResult['rows']);

        // 4. Catat Log Status Preview
        $importLog = ImportLog::create([
            'user_id' => $userId,
            'nama_file_asli' => $file->getClientOriginalName(),
            'nama_file_hash' => $tempFilename,
            'total_baris' => $validationResult['total_rows'],
            'jumlah_sukses' => $validationResult['valid_count'],
            'jumlah_gagal' => $validationResult['error_count'],
            'status' => $validationResult['valid_count'] > 0 ? 'preview' : 'gagal',
            'error_summary' => [
                'has_errors' => ! $validationResult['is_valid'],
                'errors' => $validationResult['error_messages'],
                'rows' => $validationResult['validated_rows'],
            ],
        ]);

        return [
            'success' => true,
            'token' => $token,
            'log_id' => $importLog->id,
            'summary' => [
                'nama_file' => $file->getClientOriginalName(),
                'total_rows' => $validationResult['total_rows'],
                'valid_count' => $validationResult['valid_count'],
                'error_count' => $validationResult['error_count'],
                'is_valid' => $validationResult['is_valid'],
            ],
            'rows' => $validationResult['validated_rows'],
        ];
    }

    /**
     * Mengeksekusi simpan permanen ke database dalam DB::transaction.
     * Menerapkan kebijakan parsial: baris valid disimpan, baris error dilewati.
     *
     * @return array{success: bool, message: string, count?: int}
     */
    public function confirmImport(string $token, int $userId): array
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $tempFilename = 'import_'.$token.'.xlsx';
        $storedPath = 'imports/'.$tempFilename;

        if (! Storage::exists($storedPath)) {
            return [
                'success' => false,
                'message' => 'Sesi import telah kedaluwarsa atau berkas tidak ditemukan. Silakan upload ulang berkas Anda.',
            ];
        }

        $fullPath = Storage::path($storedPath);
        $importLog = ImportLog::where('nama_file_hash', $tempFilename)->first();

        // Parse ulang untuk memastikan integritas
        $parseResult = $this->parser->parse($fullPath);
        if (! $parseResult['header_valid'] || empty($parseResult['rows'])) {
            return [
                'success' => false,
                'message' => 'Berkas temporary tidak valid atau kosong saat konfirmasi.',
            ];
        }

        // Re-validasi akhir (menghindari duplikasi bila ada data baru masuk selama operator membuka preview)
        $validation = $this->validator->validate($parseResult['rows']);

        // Filter baris yang valid
        $validRows = array_values(array_filter($validation['validated_rows'], fn ($r) => $r['is_valid']));
        $validCount = count($validRows);
        $errorCount = $validation['error_count'];

        // Jika tidak ada satu pun baris valid (100% error)
        if ($validCount === 0) {
            if ($importLog) {
                $importLog->update([
                    'status' => 'gagal',
                    'jumlah_gagal' => $errorCount,
                    'jumlah_sukses' => 0,
                    'error_summary' => [
                        'has_errors' => true,
                        'errors' => $validation['error_messages'],
                        'rows' => $validation['validated_rows'],
                    ],
                ]);
            }

            return [
                'success' => false,
                'message' => "Import tidak dapat dilanjutkan: Seluruh {$errorCount} baris data mengalami kesalahan. Tidak ada data yang disimpan.",
            ];
        }

        // Siapkan in-memory cache untuk penanganan performa tinggi skala 5.000 data
        $cache = $this->bmnService->createImportCache();

        // Eksekusi Simpan Baris Valid dalam 1 Transaksi Database
        try {
            DB::transaction(function () use ($validRows, &$cache) {
                foreach ($validRows as $row) {
                    $this->bmnService->storeRecord([
                        'kode_barang' => $row['kode_barang'],
                        'nup' => $row['nup'],
                        'nama_barang' => $row['nama_barang'],
                        'merek' => $row['merek'],
                        'tanggal_terima' => $row['tanggal_terima'],
                        'tanggal_buku_pertama' => $row['tanggal_buku_pertama'] ?? null,
                        'tahun_perolehan' => $row['tahun_perolehan'],
                        'nilai_perolehan' => $row['nilai_perolehan'],
                        'jenis' => $row['jenis_asal'] ?? $row['jenis'],
                        'satuan' => $row['satuan'] ?? 'unit',
                        'spesifikasi' => $row['spesifikasi'] ?? null,
                        'lokasi' => ! empty($row['lokasi_ruang']) ? $row['lokasi_ruang'] : 'Gudang BMN',
                        'penanggungjawab' => 'Pengelola BMN',
                        'penanggungjawab_nip' => '-',
                        'kondisi' => ! empty($row['kondisi']) ? $row['kondisi'] : 'Baik',
                    ], $cache);
                }
            });

            // Update Log
            $status = $errorCount > 0 ? 'parsial' : 'berhasil';
            if ($importLog) {
                $importLog->update([
                    'status' => $status,
                    'jumlah_sukses' => $validCount,
                    'jumlah_gagal' => $errorCount,
                    'error_summary' => [
                        'has_errors' => $errorCount > 0,
                        'errors' => $validation['error_messages'],
                        'rows' => $validation['validated_rows'],
                    ],
                ]);
            }

            // Hapus file temporary
            Storage::delete($storedPath);

            $message = $errorCount > 0
                ? "Sebanyak {$validCount} barang berhasil diimpor. {$errorCount} baris bermasalah dilewati."
                : "Sebanyak {$validCount} barang berhasil diimpor dan didaftarkan ke sistem.";

            return [
                'success' => true,
                'message' => $message,
                'count' => $validCount,
            ];
        } catch (Exception $e) {
            if ($importLog) {
                $importLog->update([
                    'status' => 'gagal',
                    'error_summary' => array_merge($importLog->error_summary ?? [], ['exception' => $e->getMessage()]),
                ]);
            }

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data ke database: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Menghasilkan file Excel laporan error untuk diunduh operator.
     */
    public function generateErrorReport(ImportLog $importLog): BinaryFileResponse
    {
        $summary = $importLog->error_summary ?? [];
        $rows = $summary['rows'] ?? [];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Error Import');

        // Header Master Data Aset + Status Error
        $headers = [
            'A1' => 'Baris Excel',
            'B1' => 'Jenis BMN',
            'C1' => 'Kode Barang',
            'D1' => 'NUP',
            'E1' => 'Nama Barang',
            'F1' => 'Merk',
            'G1' => 'Kondisi',
            'H1' => 'Tanggal Buku Pertama',
            'I1' => 'Tanggal Perolehan',
            'J1' => 'Nilai Perolehan',
            'K1' => 'Lokasi Ruang',
            'L1' => 'Status Baris',
            'M1' => 'Keterangan / Alasan Error',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '9C322C'], // Merah error
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        $curRow = 2;
        foreach ($rows as $r) {
            $isRowValid = $r['is_valid'] ?? true;
            if ($isRowValid) {
                continue; // Hanya masukkan baris yang gagal agar user mudah memperbaikinya
            }

            $errList = $r['errors'] ?? [];
            $errStr = implode('; ', array_values($errList));

            $sheet->setCellValue("A{$curRow}", $r['excel_row'] ?? $curRow);
            $sheet->setCellValue("B{$curRow}", $r['jenis'] ?? '');
            $sheet->setCellValueExplicit("C{$curRow}", (string) ($r['kode_barang'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("D{$curRow}", $r['nup_raw'] ?? '');
            $sheet->setCellValue("E{$curRow}", $r['nama_barang'] ?? '');
            $sheet->setCellValue("F{$curRow}", $r['merek'] ?? '');
            $sheet->setCellValue("G{$curRow}", $r['kondisi'] ?? 'Baik');
            $sheet->setCellValue("H{$curRow}", $r['tanggal_buku_pertama_raw'] ?? '');
            $sheet->setCellValue("I{$curRow}", $r['tanggal_terima_raw'] ?? '');
            $sheet->setCellValue("J{$curRow}", $r['nilai_perolehan_raw'] ?? '');
            $sheet->setCellValue("K{$curRow}", $r['lokasi_ruang'] ?? 'Belum berlokasi');
            $sheet->setCellValue("L{$curRow}", 'Error');
            $sheet->setCellValue("M{$curRow}", $errStr ?: '-');

            // Warna baris error
            $sheet->getStyle("A{$curRow}:M{$curRow}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FDF0EE'],
                ],
            ]);
            $sheet->getStyle("L{$curRow}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('9C322C'));

            $curRow++;
        }

        $lastBorderRow = max(1, $curRow - 1);
        $sheet->getStyle("A1:M{$lastBorderRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D0D7D3'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $tempDir = storage_path('framework/cache');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $tempDir.'/Laporan_Error_Import_'.bin2hex(random_bytes(6)).'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        $downloadName = 'Laporan_Error_Import_BMN_'.date('Ymd_His').'.xlsx';

        return response()->download($tempPath, $downloadName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-cache, private',
        ])->deleteFileAfterSend(true);
    }
}
