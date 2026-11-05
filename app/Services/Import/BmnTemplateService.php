<?php

namespace App\Services\Import;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BmnTemplateService
{
    /**
     * Header standar Master Data Aset (SIMAN / SAKTI).
     */
    public const HEADERS = [
        'A' => 'No',
        'B' => 'Jenis BMN',
        'C' => 'Kode Barang',
        'D' => 'NUP',
        'E' => 'Nama Barang',
        'F' => 'Merk',
        'G' => 'Kondisi',
        'H' => 'Tanggal Buku Pertama',
        'I' => 'Tanggal Perolehan',
        'J' => 'Nilai Perolehan',
        'K' => 'Lokasi Ruang',
        'L' => 'NIP Penanggung Jawab',
    ];

    /**
     * Menghasilkan berkas template Excel (.xlsx) dengan 2 sheet dan validasi data.
     */
    public function generateTemplate(): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet;

        // ----------------------------------------------------
        // SHEET 1: Data
        // ----------------------------------------------------
        $sheetData = $spreadsheet->getActiveSheet();
        $sheetData->setTitle('Data');

        // Header Styling
        $headerRange = 'A1:L1';
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

        // Format kolom Kode Barang dan NIP sebagai Teks Murni (@)
        $sheetData->getStyle('C2:C1000')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $sheetData->getStyle('L2:L1000')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        // Format kolom Nilai Perolehan
        $sheetData->getStyle('J2:J1000')->getNumberFormat()->setFormatCode('#,##0');

        // Alignment kolom
        $sheetData->getStyle('A2:A1000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheetData->getStyle('B2:B1000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheetData->getStyle('C2:C1000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheetData->getStyle('D2:D1000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheetData->getStyle('E2:F1000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheetData->getStyle('G2:I1000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheetData->getStyle('J2:J1000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheetData->getStyle('K2:K1000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheetData->getStyle('L2:L1000')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Baris Contoh 1: TANAH (tanpa merk, tanggal format Jan-78)
        $sheetData->setCellValue('A2', 1);
        $sheetData->setCellValue('B2', 'TANAH');
        $sheetData->setCellValueExplicit('C2', '2010101001', DataType::TYPE_STRING);
        $sheetData->setCellValue('D2', 2);
        $sheetData->setCellValue('E2', 'Tanah Bangunan Rumah Negara Golongan I');
        $sheetData->setCellValue('F2', '');
        $sheetData->setCellValue('G2', 'Baik');
        $sheetData->setCellValue('H2', '31/12/2021');
        $sheetData->setCellValue('I2', 'Jan-78');
        $sheetData->setCellValue('J2', 10550088000);
        $sheetData->setCellValue('K2', 'Belum berlokasi');
        $sheetData->setCellValueExplicit('L2', '', DataType::TYPE_STRING);

        // Baris Contoh 2: ALAT BESAR (ada merk, tanggal DD/MM/YYYY)
        $sheetData->setCellValue('A3', 2);
        $sheetData->setCellValue('B3', 'ALAT BESAR');
        $sheetData->setCellValueExplicit('C3', '3010305010', DataType::TYPE_STRING);
        $sheetData->setCellValue('D3', 1);
        $sheetData->setCellValue('E3', 'Pompa Air');
        $sheetData->setCellValue('F3', 'Hitachi WT P-255EX');
        $sheetData->setCellValue('G3', 'Baik');
        $sheetData->setCellValue('H3', '31/12/2021');
        $sheetData->setCellValue('I3', '18/06/2013');
        $sheetData->setCellValue('J3', 7500000);
        $sheetData->setCellValue('K3', 'Belum berlokasi');
        $sheetData->setCellValueExplicit('L3', '', DataType::TYPE_STRING);

        // Baris Contoh 3: BMN-TIK
        $sheetData->setCellValue('A4', 3);
        $sheetData->setCellValue('B4', 'BMN-TIK');
        $sheetData->setCellValueExplicit('C4', '3080305002', DataType::TYPE_STRING);
        $sheetData->setCellValue('D4', 1);
        $sheetData->setCellValue('E4', 'Komputer Unit PC');
        $sheetData->setCellValue('F4', 'Lenovo ThinkCentre M70q');
        $sheetData->setCellValue('G4', 'Baik');
        $sheetData->setCellValue('H4', '31/12/2021');
        $sheetData->setCellValue('I4', '15/01/2023');
        $sheetData->setCellValue('J4', 18500000);
        $sheetData->setCellValue('K4', 'Gudang BMN');
        $sheetData->setCellValueExplicit('L4', '198001012005011001', DataType::TYPE_STRING);

        // Style baris contoh
        $sheetData->getStyle('A2:L4')->applyFromArray([
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

        // Dropdown Data Validation: Kondisi (Kolom G)
        for ($row = 2; $row <= 500; $row++) {
            $valKondisi = $sheetData->getCell("G{$row}")->getDataValidation();
            $valKondisi->setType(DataValidation::TYPE_LIST);
            $valKondisi->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $valKondisi->setAllowBlank(true);
            $valKondisi->setShowDropDown(true);
            $valKondisi->setFormula1('"Baik,Rusak Berat"');
        }

        // Auto width untuk tiap kolom di Sheet Data
        foreach (range('A', 'L') as $col) {
            $sheetData->getColumnDimension($col)->setAutoSize(true);
        }

        // ----------------------------------------------------
        // SHEET 2: Petunjuk Pengisian
        // ----------------------------------------------------
        $sheetPetunjuk = $spreadsheet->createSheet();
        $sheetPetunjuk->setTitle('Petunjuk');

        // Judul Petunjuk
        $sheetPetunjuk->setCellValue('A1', 'PETUNJUK PENGISIAN TEMPLATE IMPORT MASTER DATA ASET (SIBOB)');
        $sheetPetunjuk->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new Color('163828'));
        $sheetPetunjuk->mergeCells('A1:D1');

        $sheetPetunjuk->setCellValue('A2', 'Format ini kompatibel langsung dengan ekspor Master Data Aset dari SIMAN/SAKTI maupun input manual:');
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
            ['No', 'Opsional', 'Angka', 'Nomor urut baris dokumen.'],
            ['Jenis BMN', 'Opsional', 'Teks', 'Teks klasifikasi aset (contoh: TANAH, ALAT BESAR, BMN-TIK, BMN-LAB, dsb.). Sistem otomatis mengelompokkan ke kategori yang sesuai.'],
            ['Kode Barang', 'Wajib', 'Teks (10 Digit Angka)', 'Kode barang aset inventaris wajib tepat 10 digit angka (contoh: 2010101001 atau 3080305002).'],
            ['NUP', 'Wajib', 'Angka Bulat Positif (> 0)', 'Nomor Urut Pendaftaran. Kombinasi Kode Barang + NUP harus unik (tidak boleh duplikat di sistem/file).'],
            ['Nama Barang', 'Wajib', 'Teks', 'Nama lengkap barang milik negara (contoh: Tanah Bangunan Rumah Negara Golongan I, Pompa Air, PC).'],
            ['Merk', 'Opsional', 'Teks', 'Merek atau pabrikan aset. Dapat dikosongkan untuk aset seperti tanah atau bangunan.'],
            ['Kondisi', 'Opsional', 'Teks (Default: Baik)', 'Kondisi fisik aset (contoh: Baik, Rusak Berat). Jika dikosongkan otomatis terisi "Baik".'],
            ['Tanggal Buku Pertama', 'Opsional', 'Tanggal (DD/MM/YYYY)', 'Tanggal pembukuan pertama kali aset pada neraca (contoh: 31/12/2021).'],
            ['Tanggal Perolehan', 'Wajib', 'Tanggal / Bulan-Tahun', 'Mendukung format DD/MM/YYYY (30/03/2016), Bulan-Tahun (Jan-78, Jan-81), atau Tahun (1978). Sistem otomatis mengekstrak Tahun Perolehan.'],
            ['Nilai Perolehan', 'Wajib', 'Angka (>= 0)', 'Nilai perolehan barang dalam rupiah tanpa tulisan "Rp" (contoh: 10550088000 atau 7500000).'],
            ['Lokasi Ruang', 'Opsional', 'Teks', 'Nama ruangan penempatan aset. Jika diisi ruangan spesifik, aset langsung ditempatkan di ruangan tersebut dan TIDAK masuk BA Distribusi. Jika terisi "Belum berlokasi" / "Gudang BMN" / kosong, aset otomatis masuk antrean BA Distribusi Gudang.'],
            ['NIP Penanggung Jawab', 'Opsional', 'Teks (Angka NIP)', 'NIP pegawai penanggung jawab aset (18 digit angka). Jika terdaftar di Master Pegawai, nama resmi otomatis disinkronkan. Jika dikosongkan/tidak terdaftar, otomatis dialihkan ke "Pengelola BMN".'],
        ];

        $pRow = 5;
        foreach ($petunjukData as $row) {
            $sheetPetunjuk->setCellValue("A{$pRow}", $row[0]);
            $sheetPetunjuk->setCellValue("B{$pRow}", $row[1]);
            $sheetPetunjuk->setCellValue("C{$pRow}", $row[2]);
            $sheetPetunjuk->setCellValue("D{$pRow}", $row[3]);

            if ($row[1] === 'Wajib') {
                $sheetPetunjuk->getStyle("B{$pRow}")->getFont()->setBold(true)->setColor(new Color('B22222'));
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
        $sheetPetunjuk->setCellValue("A{$pRow}", 'CATATAN OTOMATISASI DAN KEBIJAKAN SISTEM:');
        $sheetPetunjuk->getStyle("A{$pRow}")->getFont()->setBold(true)->setSize(11)->setColor(new Color('163828'));

        $catatan = [
            '1. Auto-Deteksi Format Tanggal: Tanggal seperti "Jan-78" atau "Jan-81" otomatis diubah menjadi "1978-01-01" / "1981-01-01" dan diekstrak tahunnya.',
            '2. Lokasi "Belum berlokasi": Terintegrasi resmi ke master data ruangan sehingga aset dapat didistribusikan / dipindahkan kemudian via Berita Acara.',
            '3. Toleransi Merek Kosong: Khusus aset tanah, gedung, atau infrastruktur yang tidak memiliki merek, kolom Merk dapat dikosongkan tanpa error.',
            '4. Kebijakan Impor Parsial (Toleransi Error): Baris data yang valid akan langsung tersimpan ke sistem. Baris yang bermasalah akan dilewati dan disediakan file Excel Laporan Error untuk diperbaiki.',
            '5. Dual-Support Skema: Parser sistem juga tetap menerima berkas Excel dengan format template lama 10 kolom (Kode Barang, NUP, Merek, Tgl Terima, Tahun, Nilai, Jenis, Satuan, Spesifikasi).',
            '6. Penanganan Lokasi & BA Distribusi: Aset dengan lokasi ruangan spesifik tidak akan dibuatkan tiket Berita Acara Distribusi. Tiket distribusi hanya dibuat untuk aset yang berlokasi di Gudang BMN atau Belum berlokasi.',
        ];

        foreach ($catatan as $c) {
            $pRow++;
            $sheetPetunjuk->setCellValue("A{$pRow}", $c);
            $sheetPetunjuk->mergeCells("A{$pRow}:D{$pRow}");
            $sheetPetunjuk->getStyle("A{$pRow}")->getFont()->setSize(10);
        }

        $sheetPetunjuk->getColumnDimension('A')->setWidth(22);
        $sheetPetunjuk->getColumnDimension('B')->setWidth(14);
        $sheetPetunjuk->getColumnDimension('C')->setWidth(26);
        $sheetPetunjuk->getColumnDimension('D')->setWidth(85);

        // Aktifkan kembali Sheet Data saat pertama kali dibuka
        $spreadsheet->setActiveSheetIndex(0);

        $tempDir = storage_path('framework/cache');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempFile = $tempDir.'/Template_Import_BMN_'.bin2hex(random_bytes(6)).'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        $filename = 'Template_Master_Data_Aset_BMN.xlsx';

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-cache, private',
        ])->deleteFileAfterSend(true);
    }
}
