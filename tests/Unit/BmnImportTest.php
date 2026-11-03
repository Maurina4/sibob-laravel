<?php

namespace Tests\Unit;

use App\Models\Bmn;
use App\Models\Lokasi;
use App\Services\Import\BmnExcelParser;
use App\Services\Import\BmnImportValidator;
use App\Services\Import\BmnTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class BmnImportTest extends TestCase
{
    use RefreshDatabase;

    protected function createSampleExcel(array $headers, array $dataRows, ?string $sheetName = 'Data'): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetName ?? 'Data');

        // Headers
        $colIndex = 1;
        foreach ($headers as $h) {
            $sheet->setCellValue([$colIndex, 1], $h);
            $colIndex++;
        }

        // Rows
        $rowIndex = 2;
        foreach ($dataRows as $row) {
            $colIndex = 1;
            foreach ($row as $val) {
                if (is_string($val) && str_starts_with($val, '0')) {
                    $sheet->setCellValueExplicit([$colIndex, $rowIndex], $val, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue([$colIndex, $rowIndex], $val);
                }
                $colIndex++;
            }
            $rowIndex++;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'test_bmn_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    public function test_template_service_generates_valid_master_data_aset_spreadsheet(): void
    {
        $service = new BmnTemplateService();
        $response = $service->generateTemplate();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Template_Master_Data_Aset_BMN.xlsx', $response->headers->get('Content-Disposition'));
    }

    public function test_parser_handles_master_data_aset_11_columns_and_smart_dates(): void
    {
        $headers = [
            'No', 'Jenis BMN', 'Kode Barang', 'NUP', 'Nama Barang',
            'Merk', 'Kondisi', 'Tanggal Buku Pertama', 'Tanggal Perolehan',
            'Nilai Perolehan', 'Lokasi Ruang',
        ];

        $rows = [
            // Row 1: TANAH (tanpa merk, tanggal format Jan-78)
            [1, 'TANAH', '2010101001', 2, 'Tanah Bangunan Rumah Negara Golongan I', '', 'Baik', '31/12/2021', 'Jan-78', 10550088000, 'Belum berlokasi'],
            // Row 2: ALAT BESAR (ada merk, tanggal DD/MM/YYYY)
            [2, 'ALAT BESAR', '3010305010', 1, 'Pompa Air', 'Hitachi WT P-255EX', 'Baik', '31/12/2021', '18/06/2013', 7500000, 'Belum berlokasi'],
        ];

        $file = $this->createSampleExcel($headers, $rows);
        $parser = new BmnExcelParser();
        $result = $parser->parse($file);

        @unlink($file);

        $this->assertTrue($result['header_valid']);
        $this->assertSame('master_data_aset', $result['schema']);
        $this->assertCount(2, $result['rows']);

        $row1 = $result['rows'][0];
        $this->assertSame('2010101001', $row1['kode_barang']);
        $this->assertSame('TANAH', $row1['jenis']);
        $this->assertNull($row1['merek']);
        $this->assertSame('2021-12-31', $row1['tanggal_buku_pertama']);
        $this->assertSame('1978-01-01', $row1['tanggal_terima']);
        $this->assertSame(1978, $row1['tahun_perolehan']);
        $this->assertSame(10550088000.0, $row1['nilai_perolehan']);
        $this->assertSame('Belum berlokasi', $row1['lokasi_ruang']);

        $row2 = $result['rows'][1];
        $this->assertSame('3010305010', $row2['kode_barang']);
        $this->assertSame('ALAT BESAR', $row2['jenis']);
        $this->assertSame('Hitachi WT P-255EX', $row2['merek']);
        $this->assertSame('2013-06-18', $row2['tanggal_terima']);
        $this->assertSame(2013, $row2['tahun_perolehan']);
        $this->assertSame(7500000.0, $row2['nilai_perolehan']);
    }

    public function test_parser_flexible_date_string_conversions(): void
    {
        $parser = new BmnExcelParser();

        $d1 = $parser->parseFlexibleDateString('Jan-78');
        $this->assertSame('1978-01-01', $d1['date']);
        $this->assertSame(1978, $d1['year']);

        $d2 = $parser->parseFlexibleDateString('Jan-81');
        $this->assertSame('1981-01-01', $d2['date']);
        $this->assertSame(1981, $d2['year']);

        $d3 = $parser->parseFlexibleDateString('30/03/2016');
        $this->assertSame('2016-03-30', $d3['date']);
        $this->assertSame(2016, $d3['year']);

        $d4 = $parser->parseFlexibleDateString('31/12/2021');
        $this->assertSame('2021-12-31', $d4['date']);
        $this->assertSame(2021, $d4['year']);

        $d5 = $parser->parseFlexibleDateString('Agu-05');
        $this->assertSame('2005-08-01', $d5['date']);
        $this->assertSame(2005, $d5['year']);

        $d6 = $parser->parseFlexibleDateString('1995');
        $this->assertSame('1995-01-01', $d6['date']);
        $this->assertSame(1995, $d6['year']);
    }

    public function test_parser_supports_legacy_template_headers(): void
    {
        $headers = [
            'Kode Barang', 'NUP', 'Nama Barang', 'Merk/Type', 'Tgl Terima',
            'Tahun Perolehan', 'Nilai Perolehan', 'Jenis Barang', 'Satuan', 'Spesifikasi',
        ];

        $rows = [
            ['3080305002', 1, 'Laptop Asus', 'Zenbook', '12/05/2025', 2025, '15.500.000', 'BMN-TIK', 'unit', 'Core i7'],
        ];

        $file = $this->createSampleExcel($headers, $rows);
        $parser = new BmnExcelParser();
        $result = $parser->parse($file);

        @unlink($file);

        $this->assertTrue($result['header_valid']);
        $this->assertSame('legacy', $result['schema']);
        $this->assertCount(1, $result['rows']);

        $row = $result['rows'][0];
        $this->assertSame('3080305002', $row['kode_barang']);
        $this->assertSame('2025-05-12', $row['tanggal_terima']);
        $this->assertSame(15500000.0, $row['nilai_perolehan']);
        $this->assertSame('BMN-TIK', $row['jenis']);
    }

    public function test_parser_rejects_missing_required_headers(): void
    {
        $headers = ['Kode Barang', 'NUP', 'Nama Barang']; // Header tidak lengkap
        $rows = [['3080305002', 1, 'Laptop Asus']];

        $file = $this->createSampleExcel($headers, $rows);
        $parser = new BmnExcelParser();
        $result = $parser->parse($file);

        @unlink($file);

        $this->assertFalse($result['header_valid']);
        $this->assertNotEmpty($result['header_errors']);
    }

    public function test_validator_accepts_master_data_aset_with_empty_merek_and_flexible_jenis(): void
    {
        $validator = new BmnImportValidator();

        $rows = [
            [
                'excel_row' => 2,
                'kode_barang' => '2010101001',
                'nup_raw' => '2',
                'nama_barang' => 'Tanah Bangunan Rumah Negara Golongan I',
                'merek' => null, // Tanah tidak ada merek
                'kondisi' => 'Baik',
                'tanggal_buku_pertama_raw' => '31/12/2021',
                'tanggal_buku_pertama' => '2021-12-31',
                'tanggal_terima_raw' => 'Jan-78',
                'tanggal_terima' => '1978-01-01',
                'tahun_perolehan_raw' => '1978',
                'tahun_perolehan' => 1978,
                'nilai_perolehan_raw' => '10550088000',
                'nilai_perolehan' => 10550088000.0,
                'jenis' => 'TANAH',
                'satuan' => 'unit',
                'lokasi_ruang' => 'Belum berlokasi',
                'spesifikasi' => null,
            ],
        ];

        $result = $validator->validate($rows);

        $this->assertTrue($result['is_valid']);
        $this->assertEquals(1, $result['valid_count']);
        $this->assertEquals(0, $result['error_count']);
    }

    public function test_validator_detects_in_file_duplicate_and_db_duplicate(): void
    {
        Bmn::create([
            'kode_barang' => '3080305999',
            'nup' => '10',
            'nama_barang' => 'Komputer Lama',
            'lokasi' => 'Gudang BMN',
            'penanggungjawab' => 'Pengelola BMN',
            'kondisi' => 'Baik',
            'jenis' => 'BMN-TIK',
            'dihapus' => false,
        ]);

        $validator = new BmnImportValidator();

        $rows = [
            // Baris 1: duplikat dengan DB
            [
                'excel_row' => 2,
                'kode_barang' => '3080305999',
                'nup_raw' => '10',
                'nama_barang' => 'Komputer Duplikat DB',
                'merek' => 'Dell',
                'tanggal_terima_raw' => '10/01/2026',
                'tanggal_terima' => '2026-01-10',
                'tahun_perolehan_raw' => '2026',
                'tahun_perolehan' => 2026,
                'nilai_perolehan_raw' => '10000000',
                'nilai_perolehan' => 10000000.0,
                'jenis' => 'BMN-TIK',
                'satuan' => 'unit',
                'spesifikasi' => null,
            ],
            // Baris 2: Data baru
            [
                'excel_row' => 3,
                'kode_barang' => '3080305888',
                'nup_raw' => '1',
                'nama_barang' => 'Barang Valid',
                'merek' => 'HP',
                'tanggal_terima_raw' => '12/01/2026',
                'tanggal_terima' => '2026-01-12',
                'tahun_perolehan_raw' => '2026',
                'tahun_perolehan' => 2026,
                'nilai_perolehan_raw' => '8000000',
                'nilai_perolehan' => 8000000.0,
                'jenis' => 'BMN-TIK',
                'satuan' => 'unit',
                'spesifikasi' => null,
            ],
            // Baris 3: Duplikat internal dengan Baris 2
            [
                'excel_row' => 4,
                'kode_barang' => '3080305888',
                'nup_raw' => '1',
                'nama_barang' => 'Barang Duplikat Internal',
                'merek' => 'HP',
                'tanggal_terima_raw' => '12/01/2026',
                'tanggal_terima' => '2026-01-12',
                'tahun_perolehan_raw' => '2026',
                'tahun_perolehan' => 2026,
                'nilai_perolehan_raw' => '8000000',
                'nilai_perolehan' => 8000000.0,
                'jenis' => 'BMN-TIK',
                'satuan' => 'unit',
                'spesifikasi' => null,
            ],
        ];

        $result = $validator->validate($rows);

        $this->assertFalse($result['is_valid']);
        $this->assertEquals(3, $result['total_rows']);
        $this->assertEquals(1, $result['valid_count']); // Hanya baris 2 yang lolos
        $this->assertEquals(2, $result['error_count']); // Baris 1 & Baris 3 gagal
    }

    public function test_validator_validates_business_rules(): void
    {
        $validator = new BmnImportValidator();

        $rows = [
            // Tanggal masa depan & Tahun perolehan > tahun terima & Kode barang bukan 10 digit
            [
                'excel_row' => 2,
                'kode_barang' => '123', // Bukan 10 digit
                'nup_raw' => '0', // NUP <= 0
                'nama_barang' => '', // Nama barang kosong
                'merek' => 'Acer',
                'tanggal_buku_pertama_raw' => '10/01/2099',
                'tanggal_buku_pertama' => '2099-01-10', // Masa depan
                'tanggal_terima_raw' => '10/01/2099',
                'tanggal_terima' => '2099-01-10', // Masa depan
                'tahun_perolehan_raw' => '2030', // > tahun berjalan
                'tahun_perolehan' => 2030,
                'nilai_perolehan_raw' => '-500',
                'nilai_perolehan' => -500.0,
                'jenis' => 'BMN-TIK',
                'satuan' => 'unit',
                'spesifikasi' => null,
            ],
        ];

        $result = $validator->validate($rows);

        $this->assertFalse($result['is_valid']);
        $errors = $result['validated_rows'][0]['errors'];

        $this->assertArrayHasKey('kode_barang', $errors);
        $this->assertArrayHasKey('nama_barang', $errors);
        $this->assertArrayHasKey('nup', $errors);
        $this->assertArrayHasKey('tanggal_buku_pertama', $errors);
        $this->assertArrayHasKey('tanggal_terima', $errors);
        $this->assertArrayHasKey('tahun_perolehan', $errors);
        $this->assertArrayHasKey('nilai_perolehan', $errors);
    }

    public function test_parser_handles_raw_excel_serial_dates(): void
    {
        $parser = new BmnExcelParser();

        // 28491 -> 1978-01-01
        $d1 = $parser->parseFlexibleDateString('28491');
        $this->assertNotNull($d1);
        $this->assertSame('1978-01-01', $d1['date']);
        $this->assertSame(1978, $d1['year']);
        $this->assertSame('01/01/1978', $d1['raw']);

        // 44561 -> 2021-12-31
        $d2 = $parser->parseFlexibleDateString('44561');
        $this->assertNotNull($d2);
        $this->assertSame('2021-12-31', $d2['date']);
        $this->assertSame(2021, $d2['year']);
        $this->assertSame('31/12/2021', $d2['raw']);

        // 45468 -> 2024-06-25
        $d3 = $parser->parseFlexibleDateString('45468');
        $this->assertNotNull($d3);
        $this->assertSame('2024-06-25', $d3['date']);
        $this->assertSame(2024, $d3['year']);
        $this->assertSame('25/06/2024', $d3['raw']);
    }

    public function test_parser_handles_indonesian_full_month_names(): void
    {
        $parser = new BmnExcelParser();

        $d1 = $parser->parseFlexibleDateString('18 Juni 2013');
        $this->assertNotNull($d1);
        $this->assertSame('2013-06-18', $d1['date']);
        $this->assertSame(2013, $d1['year']);

        $d2 = $parser->parseFlexibleDateString('31 Desember 2021');
        $this->assertNotNull($d2);
        $this->assertSame('2021-12-31', $d2['date']);
        $this->assertSame(2021, $d2['year']);
    }

    public function test_master_data_aset_with_raw_serial_dates_validates_successfully(): void
    {
        $headers = [
            'No', 'Jenis BMN', 'Kode Barang', 'NUP', 'Nama Barang',
            'Merk', 'Kondisi', 'Tanggal Buku Pertama', 'Tanggal Perolehan',
            'Nilai Perolehan', 'Lokasi Ruang',
        ];

        // Simulasi berkas Excel dari SIMAN/SAKTI dengan serial number Excel
        $rows = [
            [1, 'BMN-NON TIK', '2010101001', 2, 'Tanah Bangunan Rumah Negara Golongan I', '', 'Baik', 44561, 28491, 10550088000, 'Belum berlokasi'],
            [2, 'BMN-NON TIK', '3010305010', 1, 'Pompa Air', 'Hitachi WT P-255EX', 'Baik', 44561, 41443, 7500000, 'Belum berlokasi'],
            [3, 'BMN-NON TIK', '3020102002', 2, 'Micro Bus', 'TOYOTA/ HIACE', 'Baik', 44774, 44774, 528000000, 'Belum berlokasi'],
        ];

        $file = $this->createSampleExcel($headers, $rows);
        $parser = new BmnExcelParser();
        $parseResult = $parser->parse($file);
        @unlink($file);

        $validator = new BmnImportValidator();
        $validationResult = $validator->validate($parseResult['rows']);

        $this->assertTrue($validationResult['is_valid']);
        $this->assertSame(3, $validationResult['valid_count']);
        $this->assertSame(0, $validationResult['error_count']);

        // Pastikan tanggal dan tahun perolehan terisi dengan tepat
        $first = $validationResult['validated_rows'][0];
        $this->assertSame('1978-01-01', $first['tanggal_terima']);
        $this->assertSame(1978, $first['tahun_perolehan']);
        $this->assertSame('2021-12-31', $first['tanggal_buku_pertama']);
    }
}
