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
                'message' => 'Format kolom header tidak sesuai dengan template resmi.',
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
                'message' => 'Berkas Excel tidak memiliki baris data (kosong). Harap isi data pada sheet "Data".',
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
            'status' => $validationResult['is_valid'] ? 'preview' : 'gagal',
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
     * Mengeksekusi simpan permanen All-or-Nothing ke database dalam DB::transaction.
     *
     * @return array{success: bool, message: string, count?: int}
     */
    public function confirmImport(string $token, int $userId): array
    {
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

        // Kebijakan All-or-Nothing
        if (! $validation['is_valid']) {
            if ($importLog) {
                $importLog->update([
                    'status' => 'gagal',
                    'jumlah_gagal' => $validation['error_count'],
                    'jumlah_sukses' => $validation['valid_count'],
                    'error_summary' => [
                        'has_errors' => true,
                        'errors' => $validation['error_messages'],
                        'rows' => $validation['validated_rows'],
                    ],
                ]);
            }

            return [
                'success' => false,
                'message' => "Import dibatalkan (All-or-Nothing): Ditemukan {$validation['error_count']} baris bermasalah. Tidak ada data yang disimpan.",
            ];
        }

        // Eksekusi Simpan Seluruh Baris dalam 1 Transaksi Database
        $validatedRows = $validation['validated_rows'];

        try {
            DB::transaction(function () use ($validatedRows) {
                foreach ($validatedRows as $row) {
                    $this->bmnService->storeRecord([
                        'kode_barang' => $row['kode_barang'],
                        'nup' => $row['nup'],
                        'nama_barang' => $row['nama_barang'],
                        'merek' => $row['merek'],
                        'tanggal_terima' => $row['tanggal_terima'],
                        'tahun_perolehan' => $row['tahun_perolehan'],
                        'nilai_perolehan' => $row['nilai_perolehan'],
                        'jenis' => $row['jenis'],
                        'satuan' => $row['satuan'],
                        'spesifikasi' => $row['spesifikasi'],
                        'lokasi' => 'Gudang BMN',
                        'penanggungjawab' => 'Pengelola BMN',
                        'penanggungjawab_nip' => '-',
                        'kondisi' => 'Baik',
                    ]);
                }
            });

            // Update Log Berhasil
            if ($importLog) {
                $importLog->update([
                    'status' => 'berhasil',
                    'jumlah_sukses' => $validation['total_rows'],
                    'jumlah_gagal' => 0,
                ]);
            }

            // Hapus file temporary
            Storage::delete($storedPath);

            return [
                'success' => true,
                'message' => "Sebanyak {$validation['total_rows']} barang berhasil diimpor dan didaftarkan ke Gudang BMN.",
                'count' => $validation['total_rows'],
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

        // Header
        $headers = [
            'A1' => 'Baris Excel',
            'B1' => 'Kode Barang',
            'C1' => 'NUP',
            'D1' => 'Nama Barang',
            'E1' => 'Merk/Type',
            'F1' => 'Tgl Terima',
            'G1' => 'Tahun Perolehan',
            'H1' => 'Nilai Perolehan',
            'I1' => 'Jenis Barang',
            'J1' => 'Satuan',
            'K1' => 'Status Baris',
            'L1' => 'Keterangan / Alasan Error',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        $sheet->getStyle('A1:L1')->applyFromArray([
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
            $errList = $r['errors'] ?? [];
            $errStr = implode('; ', array_values($errList));

            $sheet->setCellValue("A{$curRow}", $r['excel_row'] ?? $curRow);
            $sheet->setCellValueExplicit("B{$curRow}", (string) ($r['kode_barang'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("C{$curRow}", $r['nup_raw'] ?? '');
            $sheet->setCellValue("D{$curRow}", $r['nama_barang'] ?? '');
            $sheet->setCellValue("E{$curRow}", $r['merek'] ?? '');
            $sheet->setCellValue("F{$curRow}", $r['tanggal_terima_raw'] ?? '');
            $sheet->setCellValue("G{$curRow}", $r['tahun_perolehan_raw'] ?? '');
            $sheet->setCellValue("H{$curRow}", $r['nilai_perolehan_raw'] ?? '');
            $sheet->setCellValue("I{$curRow}", $r['jenis'] ?? '');
            $sheet->setCellValue("J{$curRow}", $r['satuan'] ?? '');
            $sheet->setCellValue("K{$curRow}", $isRowValid ? 'Valid' : 'Error');
            $sheet->setCellValue("L{$curRow}", $errStr ?: '-');

            // Warna baris
            if (! $isRowValid) {
                $sheet->getStyle("A{$curRow}:L{$curRow}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FDF0EE'],
                    ],
                ]);
                $sheet->getStyle("K{$curRow}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('9C322C'));
            } else {
                $sheet->getStyle("K{$curRow}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('137333'));
            }

            $curRow++;
        }

        $sheet->getStyle('A1:L'.($curRow - 1))->applyFromArray([
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

        foreach (range('A', 'L') as $col) {
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
