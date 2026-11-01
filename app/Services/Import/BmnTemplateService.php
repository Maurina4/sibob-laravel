<?php

namespace App\Services\Import;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BmnTemplateService
{
    public const HEADERS = [
        'A' => 'Kode Barang',
        'B' => 'NUP',
        'C' => 'Nama Barang',
        'D' => 'Merk/Type',
        'E' => 'Tgl Terima',
        'F' => 'Tahun Perolehan',
        'G' => 'Nilai Perolehan',
        'H' => 'Jenis Barang',
        'I' => 'Satuan',
        'J' => 'Spesifikasi',
    ];

    /**
     * Menghasilkan berkas template Excel (.xlsx) dengan 2 sheet dan validasi data.
     */
    public function generateTemplate(): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();

        // ----------------------------------------------------
        // SHEET 1: Data
        // ----------------------------------------------------
        $sheetData = $spreadsheet->getActiveSheet();
        $sheetData->setTitle('Data');

        // Header Styling
        $headerRange = 'A1:J1';
        $sheetData->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
                'name' => 'Calibri',
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '163828'], // Dark Green tema SIBOB
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '0E281C'],
                ],
            ],
        ]);
        $sheetData->getRowDimension(1)->setRowHeight(28);

        // Isi Teks Header
        foreach (self::HEADERS as $col => $title) {
            $sheetData->setCellValue("{$col}1", $title);
        }

        // Freeze Pane agar header tetap terlihat saat scroll
        $sheetData->freezePane('A2');

        // Format kolom Kode Barang sebagai Teks Murni (@)
        $sheetData->getStyle('A2:A1000')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        // Format kolom Tgl Terima
        $sheetData->getStyle('E2:E1000')->getNumberFormat()->setFormatCode('dd/mm/yyyy');

        // Format kolom Nilai Perolehan
        $sheetData->getStyle('G2:G1000')->getNumberFormat()->setFormatCode('#,##0');

        // Alignment kolom
        $sheetData->getStyle('A2:A1000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheetData->getStyle('B2:B1000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheetData->getStyle('E2:E1000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheetData->getStyle('F2:F1000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheetData->getStyle('G2:G1000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheetData->getStyle('H2:I1000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Baris Contoh 1: BMN-TIK
        $sheetData->setCellValueExplicit('A2', '3080305002', DataType::TYPE_STRING);
        $sheetData->setCellValue('B2', 1);
        $sheetData->setCellValue('C2', 'Komputer Unit PC');
        $sheetData->setCellValue('D2', 'Lenovo ThinkCentre M70q');
        $sheetData->setCellValue('E2', '15/01/'.date('Y'));
        $sheetData->setCellValue('F2', (int) date('Y'));
        $sheetData->setCellValue('G2', 18500000);
        $sheetData->setCellValue('H2', 'BMN-TIK');
        $sheetData->setCellValue('I2', 'unit');
        $sheetData->setCellValue('J2', 'Core i7, RAM 16GB, SSD 512GB, OS Win 11 Pro');

        // Baris Contoh 2: BMN-LAB
        $sheetData->setCellValueExplicit('A3', '3080101005', DataType::TYPE_STRING);
        $sheetData->setCellValue('B3', 2);
        $sheetData->setCellValue('C3', 'Spektrofotometer UV-Vis');
        $sheetData->setCellValue('D3', 'Shimadzu UV-1900i');
        $sheetData->setCellValue('E3', '20/02/'.date('Y'));
        $sheetData->setCellValue('F3', (int) date('Y'));
        $sheetData->setCellValue('G3', 125000000);
        $sheetData->setCellValue('H3', 'BMN-LAB');
        $sheetData->setCellValue('I3', 'unit');
        $sheetData->setCellValue('J3', 'Rentang panjang gelombang 190-1100 nm, akurasi tinggi');

        // Style baris contoh
        $sheetData->getStyle('A2:J3')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D0D7D3'],
                ],
            ],
            'font' => [
                'name' => 'Calibri',
                'size' => 10,
            ],
        ]);

        // Dropdown Data Validation: Jenis Barang (Kolom H) & Satuan (Kolom I)
        for ($row = 2; $row <= 500; $row++) {
            // Jenis Barang
            $valJenis = $sheetData->getCell("H{$row}")->getDataValidation();
            $valJenis->setType(DataValidation::TYPE_LIST);
            $valJenis->setErrorStyle(DataValidation::STYLE_STOP);
            $valJenis->setAllowBlank(false);
            $valJenis->setShowInputMessage(true);
            $valJenis->setShowErrorMessage(true);
            $valJenis->setShowDropDown(true);
            $valJenis->setErrorTitle('Pilihan Jenis Tidak Valid');
            $valJenis->setError('Pilih Jenis Barang dari opsi: BMN-TIK, BMN-LAB, atau BMN-NON TIK.');
            $valJenis->setPromptTitle('Jenis Barang');
            $valJenis->setPrompt('Pilih jenis barang dari daftar pilihan.');
            $valJenis->setFormula1('"BMN-TIK,BMN-LAB,BMN-NON TIK"');

            // Satuan
            $valSatuan = $sheetData->getCell("I{$row}")->getDataValidation();
            $valSatuan->setType(DataValidation::TYPE_LIST);
            $valSatuan->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $valSatuan->setAllowBlank(false);
            $valSatuan->setShowInputMessage(true);
            $valSatuan->setShowErrorMessage(true);
            $valSatuan->setShowDropDown(true);
            $valSatuan->setErrorTitle('Pilihan Satuan');
            $valSatuan->setError('Gunakan salah satu satuan umum: unit, buah, set, atau paket.');
            $valSatuan->setPromptTitle('Satuan');
            $valSatuan->setPrompt('Pilih satuan yang sesuai.');
            $valSatuan->setFormula1('"unit,buah,set,paket"');
        }

        // Auto width untuk tiap kolom di Sheet Data
        foreach (range('A', 'J') as $col) {
            $sheetData->getColumnDimension($col)->setAutoSize(true);
        }

        // ----------------------------------------------------
        // SHEET 2: Petunjuk Pengisian
        // ----------------------------------------------------
        $sheetPetunjuk = $spreadsheet->createSheet();
        $sheetPetunjuk->setTitle('Petunjuk');

        // Judul Petunjuk
        $sheetPetunjuk->setCellValue('A1', 'PETUNJUK PENGISIAN TEMPLATE IMPORT BMN (SIBOB)');
        $sheetPetunjuk->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('163828'));
        $sheetPetunjuk->mergeCells('A1:D1');

        $sheetPetunjuk->setCellValue('A2', 'Pastikan Anda mengisi data pada sheet "Data" sesuai dengan ketentuan kolom di bawah ini:');
        $sheetPetunjuk->getStyle('A2')->getFont()->setItalic(true)->setSize(10);
        $sheetPetunjuk->mergeCells('A2:D2');

        // Header Tabel Petunjuk
        $petunjukHeaders = [
            'A4' => 'Nama Kolom',
            'B4' => 'Status',
            'C4' => 'Tipe Data / Format',
            'D4' => 'Keterangan & Contoh',
        ];
        foreach ($petunjukHeaders as $cell => $val) {
            $sheetPetunjuk->setCellValue($cell, $val);
        }
        $sheetPetunjuk->getStyle('A4:D4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2C4A38'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheetPetunjuk->getRowDimension(4)->setRowHeight(24);

        $petunjukData = [
            ['Kode Barang', 'Wajib', 'Teks (Angka)', 'Kode barang aset inventaris (contoh: 3080305002). Format teks menjaga angka nol di depan tidak hilang.'],
            ['NUP', 'Wajib', 'Angka Bulat Positif (> 0)', 'Nomor Urut Pendaftaran. Kombinasi Kode Barang + NUP harus unik (tidak boleh duplikat di sistem/file).'],
            ['Nama Barang', 'Wajib', 'Teks', 'Nama lengkap barang milik negara (contoh: Komputer Unit PC, Laptop Lenovo).'],
            ['Merk/Type', 'Wajib', 'Teks', 'Merek, model, atau pabrikan pembuat (contoh: Lenovo ThinkCentre M70q, Dell Inspiron).'],
            ['Tgl Terima', 'Wajib', 'Tanggal (DD/MM/YYYY)', 'Tanggal barang diterima resmi di kantor. Tidak boleh tanggal di masa depan.'],
            ['Tahun Perolehan', 'Wajib', 'Angka 4 Digit (1945 - tahun ini)', 'Tahun saat barang diadakan. Nilai tahun tidak boleh lebih besar dari tahun Tanggal Terima.'],
            ['Nilai Perolehan', 'Wajib', 'Angka (>= 0)', 'Harga perolehan barang dalam rupiah tanpa tulisan "Rp" atau titik (contoh: 15000000 atau 1.500.000).'],
            ['Jenis Barang', 'Wajib', 'Pilihan (Dropdown)', 'Pilih tepat salah satu: BMN-TIK, BMN-LAB, atau BMN-NON TIK.'],
            ['Satuan', 'Wajib', 'Pilihan (Dropdown)', 'Pilih satuan barang: unit, buah, set, atau paket.'],
            ['Spesifikasi', 'Opsional', 'Teks', 'Rincian spesifikasi teknis, kelengkapan, nomor seri internal, atau keterangan pendukung.'],
        ];

        $pRow = 5;
        foreach ($petunjukData as $row) {
            $sheetPetunjuk->setCellValue("A{$pRow}", $row[0]);
            $sheetPetunjuk->setCellValue("B{$pRow}", $row[1]);
            $sheetPetunjuk->setCellValue("C{$pRow}", $row[2]);
            $sheetPetunjuk->setCellValue("D{$pRow}", $row[3]);

            if ($row[1] === 'Wajib') {
                $sheetPetunjuk->getStyle("B{$pRow}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('B22222'));
            }

            $pRow++;
        }

        $sheetPetunjuk->getStyle('A5:D'.($pRow - 1))->applyFromArray([
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
        $sheetPetunjuk->getStyle('B5:B'.($pRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Catatan Otomatisasi Sistem
        $pRow += 2;
        $sheetPetunjuk->setCellValue("A{$pRow}", 'CATATAN NILAI DEFAULT OTOMATIS SISTEM:');
        $sheetPetunjuk->getStyle("A{$pRow}")->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('163828'));

        $catatan = [
            '1. Lokasi Ruangan otomatis diisi ke "Gudang BMN" (dapat dipindahkan nanti melalui modul Berita Acara Perpindahan / Distribusi).',
            '2. Penanggung Jawab otomatis dialokasikan ke "Pengelola BMN" (penampung aset baru di gudang).',
            '3. Kondisi Fisik barang baru otomatis diset "Baik".',
            '4. Berita Acara Distribusi Gudang otomatis diterbitkan sebagai draf untuk penempatan ke ruangan dan penerima.',
            '5. Import menerapkan kebijakan "All-or-Nothing": jika ditemukan 1 baris salah/duplikat, tidak ada data yang disimpan dan sistem menyediakan laporan error Excel.',
        ];

        foreach ($catatan as $c) {
            $pRow++;
            $sheetPetunjuk->setCellValue("A{$pRow}", $c);
            $sheetPetunjuk->mergeCells("A{$pRow}:D{$pRow}");
            $sheetPetunjuk->getStyle("A{$pRow}")->getFont()->setSize(10);
        }

        $sheetPetunjuk->getColumnDimension('A')->setWidth(20);
        $sheetPetunjuk->getColumnDimension('B')->setWidth(14);
        $sheetPetunjuk->getColumnDimension('C')->setWidth(28);
        $sheetPetunjuk->getColumnDimension('D')->setWidth(80);

        // Aktifkan kembali Sheet Data saat pertama kali dibuka
        $spreadsheet->setActiveSheetIndex(0);

        $tempDir = storage_path('framework/cache');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempFile = $tempDir.'/Template_Import_BMN_'.bin2hex(random_bytes(6)).'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        $filename = 'Template_Import_Barang_Masuk_BMN.xlsx';

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-cache, private',
        ])->deleteFileAfterSend(true);
    }
}
