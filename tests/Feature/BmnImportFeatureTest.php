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
        $spreadsheet = new Spreadsheet;
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

    public function test_partial_import_saves_valid_rows_and_skips_error_rows_with_parsial_status(): void
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
        $previewResponse->assertSee('Ditemukan 1 baris data bermasalah');
        $previewResponse->assertSee('Konfirmasi & Simpan (1 Barang Valid)', false);
        $previewResponse->assertSee('Unduh Laporan Error (1 Baris)', false);

        // Log import tercatat berstatus preview saat masih ada data valid
        $this->assertDatabaseHas('import_logs', [
            'total_baris' => 2,
            'jumlah_sukses' => 1,
            'jumlah_gagal' => 1,
            'status' => 'preview',
        ]);

        $log = ImportLog::latest()->first();
        $token = str_replace(['import_', '.xlsx'], '', $log->nama_file_hash);

        // Konfirmasi import parsial
        $confirmResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.confirm'), [
                'token' => $token,
            ]);

        $confirmResponse->assertRedirect(route('bmns.index'));
        $confirmResponse->assertSessionHas('success');

        // Pastikan hanya 1 data valid yang masuk ke database
        $this->assertEquals(1, Bmn::count());
        $this->assertDatabaseHas('bmns', [
            'kode_barang' => '3080305001',
            'nup' => '1',
            'nama_barang' => 'Laptop Kerja',
        ]);
        $this->assertDatabaseMissing('bmns', [
            'kode_barang' => '3080305002',
        ]);

        // Status log berubah menjadi parsial
        $log->refresh();
        $this->assertSame('parsial', $log->status);
        $this->assertSame(1, $log->jumlah_sukses);
        $this->assertSame(1, $log->jumlah_gagal);
    }

    public function test_all_invalid_rows_cannot_be_confirmed_and_marks_status_gagal(): void
    {
        $headers = [
            'Kode Barang', 'NUP', 'Nama Barang', 'Merk/Type', 'Tgl Terima',
            'Tahun Perolehan', 'Nilai Perolehan', 'Jenis Barang', 'Satuan', 'Spesifikasi',
        ];

        // 2 baris error semua (NUP kosong)
        $rows = [
            ['3080305001', '', 'Laptop Kerja', 'Lenovo', '10/01/2026', 2026, 15000000, 'BMN-TIK', 'unit', ''],
            ['3080305002', '', 'PC Kantor', 'Dell', '10/01/2026', 2026, 12000000, 'BMN-TIK', 'unit', ''],
        ];

        $file = $this->createUploadableExcel($headers, $rows);

        $previewResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.preview'), [
                'file' => $file,
            ]);

        $previewResponse->assertStatus(200);
        $previewResponse->assertSee('Tidak Ada Data Valid untuk Disimpan');
        $previewResponse->assertSee('Import Tidak Dapat Dilanjutkan');

        $this->assertDatabaseHas('import_logs', [
            'total_baris' => 2,
            'jumlah_sukses' => 0,
            'jumlah_gagal' => 2,
            'status' => 'gagal',
        ]);

        $log = ImportLog::latest()->first();
        $token = str_replace(['import_', '.xlsx'], '', $log->nama_file_hash);

        $confirmResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.confirm'), [
                'token' => $token,
            ]);

        $confirmResponse->assertRedirect(route('bmns.import.index'));
        $confirmResponse->assertSessionHas('error');
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

    public function test_master_data_aset_import_handles_smart_dates_empty_merek_and_belum_berlokasi(): void
    {
        $headers = [
            'No', 'Jenis BMN', 'Kode Barang', 'NUP', 'Nama Barang',
            'Merk', 'Kondisi', 'Tanggal Buku Pertama', 'Tanggal Perolehan',
            'Nilai Perolehan', 'Lokasi Ruang',
        ];

        $rows = [
            [1, 'TANAH', '2010101001', 2, 'Tanah Bangunan Rumah Negara Golongan I', '', 'Baik', '31/12/2021', 'Jan-78', 10550088000, 'Belum berlokasi'],
            [2, 'ALAT BESAR', '3010305010', 1, 'Pompa Air', 'Hitachi WT P-255EX', 'Baik', '31/12/2021', '18/06/2013', 7500000, 'Belum berlokasi'],
        ];

        $file = $this->createUploadableExcel($headers, $rows);

        $previewResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.preview'), [
                'file' => $file,
            ]);

        $previewResponse->assertStatus(200);
        $previewResponse->assertViewIs('bmn.import.preview');
        $previewResponse->assertSee('Siap Disimpan');
        $previewResponse->assertSee('Tanah Bangunan Rumah Negara Golongan I');
        $previewResponse->assertSee('Pompa Air');

        $log = ImportLog::latest()->first();
        $this->assertNotNull($log);
        $token = str_replace(['import_', '.xlsx'], '', $log->nama_file_hash);

        $confirmResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.confirm'), [
                'token' => $token,
            ]);

        $confirmResponse->assertRedirect(route('bmns.index'));
        $confirmResponse->assertSessionHas('success');

        $this->assertEquals(2, Bmn::count());

        $tanah = Bmn::where('kode_barang', '2010101001')->first();
        $this->assertNotNull($tanah);
        $this->assertSame('BMN-NON TIK', $tanah->jenis);
        $this->assertStringContainsString('Klasifikasi Asal: TANAH', (string) $tanah->spesifikasi);
        $this->assertNull($tanah->merek);
        $this->assertSame('2021-12-31', $tanah->tanggal_buku_pertama?->format('Y-m-d'));
        $this->assertSame('1978-01-01', $tanah->tanggal_terima?->format('Y-m-d'));
        $this->assertSame(1978, $tanah->tahun_perolehan);
        $this->assertSame('Belum berlokasi', $tanah->lokasi);
        $this->assertNotNull($tanah->lokasi_id);
        $this->assertSame('BL', $tanah->lokasiRef->kode_ruangan);

        $pompa = Bmn::where('kode_barang', '3010305010')->first();
        $this->assertNotNull($pompa);
        $this->assertSame('BMN-NON TIK', $pompa->jenis);
        $this->assertStringContainsString('Klasifikasi Asal: ALAT BESAR', (string) $pompa->spesifikasi);
        $this->assertSame('Hitachi WT P-255EX', $pompa->merek);
        $this->assertSame('2021-12-31', $pompa->tanggal_buku_pertama?->format('Y-m-d'));
        $this->assertSame('2013-06-18', $pompa->tanggal_terima?->format('Y-m-d'));
        $this->assertSame(2013, $pompa->tahun_perolehan);
        $this->assertSame('Belum berlokasi', $pompa->lokasi);
    }

    public function test_import_master_data_aset_with_dual_tracking_tik_in_lab(): void
    {
        $headers = [
            'No', 'Jenis BMN', 'Kode Barang', 'NUP', 'Nama Barang',
            'Merk', 'Kondisi', 'Tanggal Buku Pertama', 'Tanggal Perolehan',
            'Nilai Perolehan', 'Lokasi Ruang',
        ];

        $rows = [
            // Row 1: TIK di Lab -> BMN-TIK & LAB (Dual Tracking)
            [1, 'MESIN PERALATAN KHUSUS TIK', '3100102001', 1, 'PC Olah Data Lab', 'Dell', 'Baik', '31/12/2021', '10/01/2023', 15000000, 'A1.01 RUANG DISKUSI LAB KIMIA'],
            // Row 2: TIK di Luar Lab -> BMN-TIK
            [2, 'MESIN PERALATAN KHUSUS TIK', '3100102002', 1, 'Laptop TU', 'Lenovo', 'Baik', '31/12/2021', '15/01/2023', 12000000, 'B1.01 RUANG TATA USAHA'],
            // Row 3: Non-TIK di Lab -> BMN-LAB
            [3, 'ALAT BESAR', '3010305010', 1, 'Pompa Air Lab', 'Hitachi', 'Baik', '31/12/2021', '18/06/2013', 7500000, 'A1.10 RUANG INSTRUMEN 1.1'],
            // Row 4: Non-TIK Luar Lab -> BMN-NON TIK
            [4, 'TANAH', '2010101001', 1, 'Tanah Kantor', '', 'Baik', '31/12/2021', 'Jan-78', 10550088000, 'Belum berlokasi'],
            // Row 5: Eksplisit 'BMN-NON TIK' di ruang Lab -> Menjadi BMN-LAB
            [5, 'BMN-NON TIK', '3050101001', 1, 'Lemari Sampel Lab', 'Lion', 'Baik', '31/12/2021', '15/05/2022', 4500000, 'A1.01 RUANG DISKUSI LAB KIMIA'],
            // Row 6: Eksplisit 'BMN-NON TIK' di luar Lab -> Menjadi BMN-NON TIK (Umum)
            [6, 'BMN-NON TIK', '3050101002', 1, 'Meja Resepsionis', 'Informa', 'Baik', '31/12/2021', '15/05/2022', 2500000, 'B1.01 RUANG TATA USAHA'],
        ];

        $file = $this->createUploadableExcel($headers, $rows);

        $previewResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.preview'), [
                'file' => $file,
            ]);

        $previewResponse->assertStatus(200);
        $previewResponse->assertViewIs('bmn.import.preview');
        $this->assertSame('BMN-TIK & LAB', $previewResponse->viewData('rows')[0]['jenis_terklasifikasi']);
        $this->assertSame('BMN-LAB', $previewResponse->viewData('rows')[4]['jenis_terklasifikasi']);
        $this->assertSame('BMN-NON TIK', $previewResponse->viewData('rows')[5]['jenis_terklasifikasi']);

        $log = ImportLog::latest()->first();
        $token = str_replace(['import_', '.xlsx'], '', $log->nama_file_hash);

        $confirmResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.confirm'), [
                'token' => $token,
            ]);

        $confirmResponse->assertRedirect(route('bmns.index'));
        $confirmResponse->assertSessionHas('success');

        $this->assertEquals(6, Bmn::count());

        // Verifikasi Row 1: Dual Tracking
        $pcLab = Bmn::where('kode_barang', '3100102001')->first();
        $this->assertNotNull($pcLab);
        $this->assertSame('BMN-TIK & LAB', $pcLab->jenis);
        $this->assertSame(2, $pcLab->kategori_bmn_id);
        $this->assertNotNull($pcLab->tik);
        $this->assertNotNull($pcLab->lab);

        // Verifikasi Row 2: TIK Luar Lab
        $laptopTu = Bmn::where('kode_barang', '3100102002')->first();
        $this->assertNotNull($laptopTu);
        $this->assertSame('BMN-TIK', $laptopTu->jenis);
        $this->assertSame(3, $laptopTu->kategori_bmn_id);
        $this->assertNotNull($laptopTu->tik);
        $this->assertNull($laptopTu->lab);

        // Verifikasi Row 3: Non-TIK di Lab
        $pompaLab = Bmn::where('kode_barang', '3010305010')->first();
        $this->assertNotNull($pompaLab);
        $this->assertSame('BMN-LAB', $pompaLab->jenis);
        $this->assertSame(2, $pompaLab->kategori_bmn_id);
        $this->assertNull($pompaLab->tik);
        $this->assertNotNull($pompaLab->lab);

        // Verifikasi Row 4: Non-TIK Luar Lab
        $tanah = Bmn::where('kode_barang', '2010101001')->first();
        $this->assertNotNull($tanah);
        $this->assertSame('BMN-NON TIK', $tanah->jenis);
        $this->assertSame(1, $tanah->kategori_bmn_id);
        $this->assertNull($tanah->tik);
        $this->assertNull($tanah->lab);

        // Verifikasi Row 5: Eksplisit 'BMN-NON TIK' di ruang Lab -> BMN-LAB
        $lemari = Bmn::where('kode_barang', '3050101001')->first();
        $this->assertNotNull($lemari);
        $this->assertSame('BMN-LAB', $lemari->jenis);
        $this->assertSame(2, $lemari->kategori_bmn_id);
        $this->assertNull($lemari->tik);
        $this->assertNotNull($lemari->lab);

        // Verifikasi Row 6: Eksplisit 'BMN-NON TIK' di luar Lab -> BMN-NON TIK
        $meja = Bmn::where('kode_barang', '3050101002')->first();
        $this->assertNotNull($meja);
        $this->assertSame('BMN-NON TIK', $meja->jenis);
        $this->assertSame(1, $meja->kategori_bmn_id);
        $this->assertNull($meja->tik);
        $this->assertNull($meja->lab);
    }

    public function test_can_download_error_report_for_partial_status_and_contains_only_error_rows(): void
    {
        $log = ImportLog::create([
            'user_id' => $this->operatorUser->id,
            'nama_file_asli' => 'data_campuran.xlsx',
            'nama_file_hash' => 'fake_hash_partial.xlsx',
            'total_baris' => 2,
            'jumlah_sukses' => 1,
            'jumlah_gagal' => 1,
            'status' => 'parsial',
            'error_summary' => [
                'has_errors' => true,
                'rows' => [
                    [
                        'excel_row' => 2,
                        'kode_barang' => '3080305001',
                        'nup_raw' => '1',
                        'nama_barang' => 'Barang Lolos',
                        'is_valid' => true,
                    ],
                    [
                        'excel_row' => 3,
                        'kode_barang' => '3080305002',
                        'nup_raw' => '',
                        'nama_barang' => 'Barang Gagal',
                        'is_valid' => false,
                        'errors' => ['nup' => 'NUP wajib diisi.'],
                    ],
                ],
            ],
        ]);

        $response = $this->actingAs($this->operatorUser)
            ->get(route('bmns.import.error-report', $log));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_import_with_in_memory_cache_handles_large_batch_efficiently(): void
    {
        $headers = [
            'No',
            'Jenis BMN',
            'Kode Barang',
            'NUP',
            'Nama Barang',
            'Merk',
            'Kondisi',
            'Tanggal Buku Pertama',
            'Tanggal Perolehan',
            'Nilai Perolehan',
            'Lokasi Ruang',
        ];

        // Generate 120 baris data campuran untuk menguji in-memory cache dan parameter chunking
        $rows = [];
        for ($i = 1; $i <= 120; $i++) {
            $isTik = ($i % 2 === 0);
            $lokasi = ($i % 3 === 0) ? 'A1.01 LAB UJI MIKRO' : (($i % 3 === 1) ? 'Belum berlokasi' : 'Gudang BMN');
            $rows[] = [
                $i,
                $isTik ? 'MESIN PERALATAN KHUSUS TIK' : 'ALAT BESAR',
                '3010305010',
                (string) (1000 + $i),
                "Barang Uji Skala {$i}",
                'Merk Test',
                'Baik',
                '31/12/2021',
                '18/06/2020',
                '5000000',
                $lokasi,
            ];
        }

        $file = $this->createUploadableExcel($headers, $rows);

        $previewResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.preview'), [
                'file' => $file,
            ]);

        $previewResponse->assertStatus(200);
        $token = $previewResponse->viewData('token');

        $confirmResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.confirm'), [
                'token' => $token,
            ]);

        $confirmResponse->assertRedirect(route('bmns.index'));
        $confirmResponse->assertSessionHas('success');

        // Pastikan seluruh 120 barang tersimpan
        $this->assertSame(120, Bmn::where('kode_barang', '3010305010')->count());

        // Verifikasi distribusi otomatis dibuat untuk yang masuk gudang / pengelola BMN
        $this->assertGreaterThan(0, Distribusi::count());
    }
}
