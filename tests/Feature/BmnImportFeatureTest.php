<?php

namespace Tests\Feature;

use App\Models\Bmn;
use App\Models\BmnLab;
use App\Models\BmnTik;
use App\Models\Distribusi;
use App\Models\ImportLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class BmnImportFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $operatorUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operatorUser = User::factory()->create([
            'email' => 'operator@pom.go.id',
            'role' => 'Operator',
        ]);

        $this->regularUser = User::factory()->create([
            'email' => 'staff@pom.go.id',
            'role' => 'User',
        ]);
    }

    protected function createUploadableExcel(array $headers, array $dataRows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data');

        $c = 1;
        foreach ($headers as $h) {
            $sheet->setCellValue([$c++, 1], $h);
        }

        $r = 2;
        foreach ($dataRows as $row) {
            $c = 1;
            foreach ($row as $val) {
                if (is_string($val) && str_starts_with($val, '0')) {
                    $sheet->setCellValueExplicit([$c++, $r], $val, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue([$c++, $r], $val);
                }
            }
            $r++;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'test_upload_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return new UploadedFile($tempPath, 'Test_Import_BMN.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    public function test_user_without_operator_role_is_forbidden_from_import_module(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('bmns.import.index'));

        $response->assertStatus(403);
    }

    public function test_operator_can_access_import_page_and_download_template(): void
    {
        $indexResponse = $this->actingAs($this->operatorUser)
            ->get(route('bmns.import.index'));

        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Import Barang Masuk via Excel');

        $templateResponse = $this->actingAs($this->operatorUser)
            ->get(route('bmns.import.template'));

        $templateResponse->assertStatus(200);
        $templateResponse->assertHeader('Content-Disposition');
    }

    public function test_preview_rejects_non_xlsx_files(): void
    {
        $txtFile = UploadedFile::fake()->create('dokumen.txt', 100, 'text/plain');

        $response = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.preview'), [
                'file' => $txtFile,
            ]);

        $response->assertSessionHas('error');
    }

    public function test_all_or_nothing_policy_locks_confirmation_when_rows_have_errors(): void
    {
        $headers = [
            'Kode Barang', 'NUP', 'Nama Barang', 'Merk/Type', 'Tgl Terima',
            'Tahun Perolehan', 'Nilai Perolehan', 'Jenis Barang', 'Satuan', 'Spesifikasi',
        ];

        // 1 baris valid, 1 baris error (NUP kosong)
        $rows = [
            ['3080305001', 1, 'Laptop Kerja', 'Lenovo', '10/01/2026', 2026, 15000000, 'BMN-TIK', 'unit', ''],
            ['3080305002', '', 'PC Kantor', 'Dell', '10/01/2026', 2026, 12000000, 'BMN-TIK', 'unit', ''], // NUP kosong
        ];

        $file = $this->createUploadableExcel($headers, $rows);

        $previewResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.preview'), [
                'file' => $file,
            ]);

        $previewResponse->assertStatus(200);
        $previewResponse->assertViewIs('bmn.import.preview');
        $previewResponse->assertSee('Ditemukan 1 baris yang bermasalah');
        $previewResponse->assertSee('Konfirmasi Import (Terkunci)');

        // Log import tercatat berstatus gagal
        $this->assertDatabaseHas('import_logs', [
            'total_baris' => 2,
            'jumlah_sukses' => 1,
            'jumlah_gagal' => 1,
            'status' => 'gagal',
        ]);

        // Coba bypass konfirmasi dengan token
        $log = ImportLog::latest()->first();
        $token = str_replace(['import_', '.xlsx'], '', $log->nama_file_hash);

        $confirmResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.confirm'), [
                'token' => $token,
            ]);

        $confirmResponse->assertRedirect(route('bmns.import.index'));
        $confirmResponse->assertSessionHas('error');

        // Pastikan tidak ada data yang masuk ke database sama sekali
        $this->assertEquals(0, Bmn::count());
    }

    public function test_valid_import_persists_data_creates_categories_and_automatic_distribusi(): void
    {
        $headers = [
            'Kode Barang', 'NUP', 'Nama Barang', 'Merk/Type', 'Tgl Terima',
            'Tahun Perolehan', 'Nilai Perolehan', 'Jenis Barang', 'Satuan', 'Spesifikasi',
        ];

        $rows = [
            ['3080305001', 101, 'PC All in One', 'HP Pavilion', '15/01/2026', 2026, 18000000, 'BMN-TIK', 'unit', 'Core i7'],
            ['3080101009', 202, 'Timbangan Analitik', 'Mettler Toledo', '20/01/2026', 2026, 35000000, 'BMN-LAB', 'unit', 'Presisi 0.1mg'],
        ];

        $file = $this->createUploadableExcel($headers, $rows);

        $previewResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.preview'), [
                'file' => $file,
            ]);

        $previewResponse->assertStatus(200);
        $previewResponse->assertViewIs('bmn.import.preview');
        $previewResponse->assertSee('Siap Disimpan');

        $log = ImportLog::latest()->first();
        $this->assertNotNull($log);
        $token = str_replace(['import_', '.xlsx'], '', $log->nama_file_hash);

        $confirmResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.confirm'), [
                'token' => $token,
            ]);

        $confirmResponse->assertRedirect(route('bmns.index'));
        $confirmResponse->assertSessionHas('success');

        // Verifikasi tabel bmns
        $this->assertEquals(2, Bmn::count());

        $tikBmn = Bmn::where('kode_barang', '3080305001')->first();
        $this->assertNotNull($tikBmn);
        $this->assertSame('Gudang BMN', $tikBmn->lokasi);
        $this->assertSame('Pengelola BMN', $tikBmn->penanggungjawab);
        $this->assertSame('Baik', $tikBmn->kondisi);

        // Verifikasi tabel bmns_tik & bmns_lab
        $this->assertEquals(1, BmnTik::count());
        $this->assertEquals(1, BmnLab::count());

        // Verifikasi pembuatan otomatis Berita Acara Distribusi Gudang
        $this->assertEquals(2, Distribusi::count());

        // Verifikasi status import_log
        $log->refresh();
        $this->assertSame('berhasil', $log->status);
        $this->assertSame(2, $log->jumlah_sukses);
        $this->assertSame(0, $log->jumlah_gagal);
    }

    public function test_can_download_error_report_when_import_fails(): void
    {
        $log = ImportLog::create([
            'user_id' => $this->operatorUser->id,
            'nama_file_asli' => 'data_rusak.xlsx',
            'nama_file_hash' => 'fake_hash.xlsx',
            'total_baris' => 1,
            'jumlah_sukses' => 0,
            'jumlah_gagal' => 1,
            'status' => 'gagal',
            'error_summary' => [
                'has_errors' => true,
                'rows' => [
                    [
                        'excel_row' => 2,
                        'kode_barang' => 'KD-ERR',
                        'nup_raw' => '-1',
                        'nama_barang' => 'Barang Gagal',
                        'merek' => 'Acer',
                        'tanggal_terima_raw' => 'invalid-date',
                        'tahun_perolehan_raw' => '2099',
                        'nilai_perolehan_raw' => 'abc',
                        'jenis' => 'BMN-TIK',
                        'satuan' => 'unit',
                        'is_valid' => false,
                        'errors' => [
                            'nup' => 'NUP harus berupa bilangan bulat positif.',
                            'tanggal_terima' => 'Format tanggal tidak valid.',
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->actingAs($this->operatorUser)
            ->get(route('bmns.import.error-report', $log));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
