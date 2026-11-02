<?php

namespace Tests\Unit;

use App\Models\Bmn;
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

    public function test_template_service_generates_valid_spreadsheet_with_data_and_petunjuk_sheets(): void
    {
        $service = new BmnTemplateService();
        $response = $service->generateTemplate();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Template_Import_Barang_Masuk_BMN.xlsx', $response->headers->get('Content-Disposition'));
    }

    public function test_parser_preserves_leading_zeros_and_parses_dates(): void
    {
        $headers = [
            'Kode Barang', 'NUP', 'Nama Barang', 'Merk/Type', 'Tgl Terima',
            'Tahun Perolehan', 'Nilai Perolehan', 'Jenis Barang', 'Satuan', 'Spesifikasi',
        ];

        $rows = [
            ['03080305002', 1, 'Laptop Asus', 'Zenbook', '12/05/2025', 2025, '15.500.000', 'BMN-TIK', 'unit', 'Core i7'],
            ['03080101005', 2, 'Mikroskop', 'Olympus', '2025-06-15', 2025, 25000000, 'BMN-LAB', 'unit', ''],
            ['', '', '', '', '', '', '', '', '', ''], // Baris kosong di akhir
        ];

        $file = $this->createSampleExcel($headers, $rows);
        $parser = new BmnExcelParser();
        $result = $parser->parse($file);

        @unlink($file);

        $this->assertTrue($result['header_valid']);
        $this->assertCount(2, $result['rows']); // Baris kosong diabaikan

        $row1 = $result['rows'][0];
        $this->assertSame('03080305002', $row1['kode_barang']);
        $this->assertSame('2025-05-12', $row1['tanggal_terima']);
        $this->assertSame(15500000.0, $row1['nilai_perolehan']);
        $this->assertSame('BMN-TIK', $row1['jenis']);
    }

    public function test_parser_rejects_missing_required_headers(): void
    {
        $headers = ['Kode Barang', 'NUP', 'Nama Barang']; // Header tidak lengkap
        $rows = [['03080305002', 1, 'Laptop Asus']];

        $file = $this->createSampleExcel($headers, $rows);
        $parser = new BmnExcelParser();
        $result = $parser->parse($file);

        @unlink($file);

        $this->assertFalse($result['header_valid']);
        $this->assertNotEmpty($result['header_errors']);
    }

    public function test_validator_detects_in_file_duplicate_and_db_duplicate(): void
    {
        // Masukkan data awal ke DB
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
            // Tanggal masa depan & Tahun perolehan > tahun terima
            [
                'excel_row' => 2,
                'kode_barang' => '3080305101',
                'nup_raw' => '0', // NUP <= 0
                'nama_barang' => 'Barang Error',
                'merek' => 'Acer',
                'tanggal_terima_raw' => '10/01/2099',
                'tanggal_terima' => '2099-01-10', // Masa depan
                'tahun_perolehan_raw' => '2030', // > tahun berjalan
                'tahun_perolehan' => 2030,
                'nilai_perolehan_raw' => '-500',
                'nilai_perolehan' => -500.0,
                'jenis' => 'INVALID-TYPE',
                'satuan' => '',
                'spesifikasi' => null,
            ],
        ];

        $result = $validator->validate($rows);

        $this->assertFalse($result['is_valid']);
        $errors = $result['validated_rows'][0]['errors'];

        $this->assertArrayHasKey('nup', $errors);
        $this->assertArrayHasKey('tanggal_terima', $errors);
        $this->assertArrayHasKey('tahun_perolehan', $errors);
        $this->assertArrayHasKey('nilai_perolehan', $errors);
        $this->assertArrayHasKey('jenis', $errors);
        $this->assertArrayHasKey('satuan', $errors);
    }
}
