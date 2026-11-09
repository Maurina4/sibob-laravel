<?php

namespace Tests\Feature;

use App\Models\Bmn;
use App\Models\Distribusi;
use App\Models\ImportLog;
use App\Models\Lokasi;
use App\Models\Pegawai;
use App\Models\User;
use App\Services\BmnService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class BmnImportLocationAndNipTest extends TestCase
{
    use RefreshDatabase;

    protected User $operatorUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operatorUser = User::factory()->create([
            'email' => 'operator@pom.go.id',
            'role' => 'Operator',
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
                if (is_string($val) && (str_starts_with($val, '0') || strlen($val) > 10)) {
                    $sheet->setCellValueExplicit([$c++, $r], $val, DataType::TYPE_STRING);
                } else {
                    $sheet->setCellValue([$c++, $r], $val);
                }
            }
            $r++;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'test_loc_nip_').'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return new UploadedFile($tempPath, 'Test_Import_Loc_NIP.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    protected function getHeaders(): array
    {
        return [
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
            'NIP Penanggung Jawab',
        ];
    }

    public function test_import_with_specific_room_location_does_not_create_distribusi(): void
    {
        $headers = $this->getHeaders();
        $rows = [
            [
                1,
                'BMN-NON TIK',
                '3050101001',
                '1',
                'Meja Rapat',
                'Informa',
                'Baik',
                '31/12/2021',
                '18/06/2020',
                '5000000',
                'B1.06 RUANG STAFF INFOKOM',
                '',
            ],
            [
                2,
                'BMN-NON TIK',
                '3050101001',
                '2',
                'Tanaman Hias',
                'Tanaman hias',
                'Rusak Berat',
                '27/06/2024',
                '27/06/2024',
                '700000',
                'C.Bs.05 - Gudang BMN Rusak Berat',
                '',
            ],
            [
                3,
                'BMN-NON TIK',
                '3050101001',
                '3',
                'Lemari Arsip ATK',
                'Brother',
                'Baik',
                '31/12/2021',
                '18/06/2020',
                '2500000',
                'Gudang ATK',
                '',
            ],
            [
                4,
                'BMN-NON TIK',
                '3050101001',
                '4',
                'Kursi Aula',
                'Futura',
                'Baik',
                '31/12/2021',
                '18/06/2020',
                '350000',
                'RUANG AULA UTAMA',
                '',
            ],
            [
                5,
                'BMN-NON TIK',
                '3050101001',
                '5',
                'Kulkas Reagen',
                'Toshiba',
                'Baik',
                '31/12/2021',
                '18/06/2020',
                '4500000',
                '18 - Gudang Reagen',
                '',
            ],
        ];

        $file = $this->createUploadableExcel($headers, $rows);

        $previewResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.preview'), ['file' => $file]);
        $previewResponse->assertStatus(200);
        $token = $previewResponse->viewData('token');

        $confirmResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.confirm'), ['token' => $token]);
        $confirmResponse->assertRedirect(route('bmns.index'));

        // Verifikasi BMN dan Lokasi tersimpan (Lokasi otomatis memisahkan kode ruangan dan nama)
        $bmn1 = Bmn::where('kode_barang', '3050101001')->where('nup', 1)->first();
        $this->assertNotNull($bmn1);
        $this->assertSame('RUANG STAFF INFOKOM', $bmn1->lokasi);
        $this->assertTrue(Lokasi::where('kode_ruangan', 'B1.06')->where('nama', 'RUANG STAFF INFOKOM')->exists());

        $bmn2 = Bmn::where('kode_barang', '3050101001')->where('nup', 2)->first();
        $this->assertNotNull($bmn2);
        $this->assertSame('GUDANG RUSAK BERAT', $bmn2->lokasi);
        $this->assertTrue(Lokasi::where('kode_ruangan', 'C.Bs.05')->where('nama', 'GUDANG RUSAK BERAT')->exists());

        $bmn3 = Bmn::where('kode_barang', '3050101001')->where('nup', 3)->first();
        $this->assertNotNull($bmn3);
        $this->assertSame('GUDANG ATK', $bmn3->lokasi);

        $bmn4 = Bmn::where('kode_barang', '3050101001')->where('nup', 4)->first();
        $this->assertNotNull($bmn4);
        $this->assertSame('RUANG AULA UTAMA', $bmn4->lokasi);

        $bmn5 = Bmn::where('kode_barang', '3050101001')->where('nup', 5)->first();
        $this->assertNotNull($bmn5);
        $this->assertSame('Gudang Reagen', $bmn5->lokasi);
        $this->assertTrue(Lokasi::where('kode_ruangan', '18')->where('nama', 'Gudang Reagen')->exists());

        // Verifikasi 5 BMN tersimpan
        $this->assertSame(5, Bmn::where('kode_barang', '3050101001')->count());

        // Verifikasi TIDAK ADA BA Distribusi yang dibuat untuk seluruh lokasi spesifik tersebut
        $this->assertSame(0, Distribusi::count());
    }

    public function test_import_with_gudang_or_empty_or_belum_berlokasi_creates_distribusi(): void
    {
        $headers = $this->getHeaders();
        $rows = [
            [
                1,
                'BMN-NON TIK',
                '3050101001',
                '10',
                'Kursi Kantor A',
                'Chitose',
                'Baik',
                '31/12/2021',
                '18/06/2020',
                '750000',
                'Gudang BMN',
                '',
            ],
            [
                2,
                'BMN-NON TIK',
                '3050101001',
                '11',
                'Kursi Kantor B',
                'Chitose',
                'Baik',
                '31/12/2021',
                '18/06/2020',
                '750000',
                'Belum berlokasi',
                '',
            ],
            [
                3,
                'BMN-NON TIK',
                '3050101001',
                '12',
                'Kursi Kantor C',
                'Chitose',
                'Baik',
                '31/12/2021',
                '18/06/2020',
                '750000',
                '',
                '',
            ],
        ];

        $file = $this->createUploadableExcel($headers, $rows);

        $previewResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.preview'), ['file' => $file]);
        $token = $previewResponse->viewData('token');

        $confirmResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.confirm'), ['token' => $token]);
        $confirmResponse->assertRedirect(route('bmns.index'));

        // 3 BMN tersimpan
        $this->assertSame(3, Bmn::where('kode_barang', '3050101001')->count());

        // Karena berlokasi di Gudang / Belum berlokasi / kosong, ketiganya MASUK BA Distribusi
        $this->assertSame(3, Distribusi::count());
    }

    public function test_import_resolves_penanggungjawab_by_nip_when_matched(): void
    {
        $pegawai = Pegawai::create([
            'nama' => 'Yuli Supriyatno',
            'nip' => '198001012005011001',
            'jabatan' => 'Staff IT',
            'unit_kerja' => 'Infokom',
        ]);

        $headers = $this->getHeaders();
        $rows = [
            [
                1,
                'BMN-TIK',
                '3080305001',
                '101',
                'PC All in One 1',
                'Lenovo',
                'Baik',
                '31/12/2021',
                '18/06/2020',
                '12000000',
                'B1.06 RUANG STAFF INFOKOM',
                '198001012005011001', // NIP murni angka
            ],
            [
                2,
                'BMN-TIK',
                '3080305001',
                '102',
                'PC All in One 2',
                'Lenovo',
                'Baik',
                '31/12/2021',
                '18/06/2020',
                '12000000',
                'B1.06 RUANG STAFF INFOKOM',
                '19800101 200501 1 001', // NIP dengan spasi
            ],
        ];

        $file = $this->createUploadableExcel($headers, $rows);

        $previewResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.preview'), ['file' => $file]);
        $previewResponse->assertStatus(200);

        // Di preview, nama pegawai resmi tampil
        $previewRows = $previewResponse->viewData('rows');
        $this->assertSame('matched', $previewRows[0]['pj_status']);
        $this->assertSame('Yuli Supriyatno', $previewRows[0]['penanggungjawab']);
        $this->assertSame('matched', $previewRows[1]['pj_status']);
        $this->assertSame('Yuli Supriyatno', $previewRows[1]['penanggungjawab']);

        $token = $previewResponse->viewData('token');

        $confirmResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.confirm'), ['token' => $token]);
        $confirmResponse->assertRedirect(route('bmns.index'));

        // Cek data di database bmns
        $bmn1 = Bmn::where('kode_barang', '3080305001')->where('nup', 101)->first();
        $this->assertNotNull($bmn1);
        $this->assertSame('Yuli Supriyatno', $bmn1->penanggungjawab);
        $this->assertSame('198001012005011001', $bmn1->penanggungjawab_nip);
        $this->assertSame($pegawai->id, $bmn1->penanggung_jawab_id);

        $bmn2 = Bmn::where('kode_barang', '3080305001')->where('nup', 102)->first();
        $this->assertNotNull($bmn2);
        $this->assertSame('Yuli Supriyatno', $bmn2->penanggungjawab);
        $this->assertSame('198001012005011001', $bmn2->penanggungjawab_nip);
        $this->assertSame($pegawai->id, $bmn2->penanggung_jawab_id);
    }

    public function test_import_with_unmatched_nip_fallbacks_to_pengelola_bmn_and_notifies(): void
    {
        $headers = $this->getHeaders();
        $rows = [
            [
                1,
                'BMN-NON TIK',
                '3050101001',
                '201',
                'Lemari Arsip',
                'Lion',
                'Baik',
                '31/12/2021',
                '18/06/2020',
                '3500000',
                'B1.06 RUANG STAFF INFOKOM',
                '199999999999999999', // NIP tidak terdaftar di database
            ],
        ];

        $file = $this->createUploadableExcel($headers, $rows);

        $previewResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.preview'), ['file' => $file]);
        $previewResponse->assertStatus(200);

        // Di preview, tampil peringatan bahwa NIP dialihkan ke Pengelola BMN
        $previewResponse->assertSee('tidak ditemukan di Master Pegawai');
        $previewResponse->assertSee('Dialihkan ke Pengelola BMN');
        $previewRows = $previewResponse->viewData('rows');
        $this->assertSame('unmatched', $previewRows[0]['pj_status']);
        $this->assertSame('Pengelola BMN', $previewRows[0]['penanggungjawab']);

        $token = $previewResponse->viewData('token');

        $confirmResponse = $this->actingAs($this->operatorUser)
            ->post(route('bmns.import.confirm'), ['token' => $token]);
        $confirmResponse->assertRedirect(route('bmns.index'));

        // Session flash memuat notifikasi pengalihan
        $confirmResponse->assertSessionHas('success', function ($msg) {
            return str_contains($msg, 'dialihkan ke Pengelola BMN karena NIP belum terdaftar');
        });

        // Verifikasi data tersimpan dengan penanggung jawab fallback
        $bmn = Bmn::where('kode_barang', '3050101001')->where('nup', 201)->first();
        $this->assertNotNull($bmn);
        $this->assertSame('Pengelola BMN', $bmn->penanggungjawab);
        $this->assertNull($bmn->penanggung_jawab_id);

        // Verifikasi import log mencatat fallback
        $log = ImportLog::latest('id')->first();
        $this->assertNotNull($log);
        $this->assertSame(1, $log->error_summary['fallback_count'] ?? 0);
    }

    public function test_manual_bmn_store_still_follows_old_rules(): void
    {
        $bmnService = app(BmnService::class);

        // Case 1: Manual create dengan lokasi ruang spesifik & penanggung jawab Pengelola BMN
        // Aturan lama manual: str_contains(penanggungjawab, 'pengelola bmn') -> Tetap buat BA Distribusi
        $bmn1 = $bmnService->storeRecord([
            'kode_barang' => '3050101001',
            'nup' => '301',
            'nama_barang' => 'Manual Barang 1',
            'jenis' => 'BMN-NON TIK',
            'lokasi' => 'B1.06 RUANG STAFF INFOKOM',
            'penanggungjawab' => 'Pengelola BMN',
            'kondisi' => 'Baik',
        ], $cache, false);

        $this->assertTrue(Distribusi::where('bmn_id', $bmn1->id)->exists(), 'Manual store with Pengelola BMN must create BA Distribusi under old rules.');

        // Case 2: Manual create dengan lokasi ruang spesifik & penanggung jawab BUKAN Pengelola BMN
        // Aturan lama manual: non-gudang dan non-pengelola -> Tidak buat BA Distribusi
        $bmn2 = $bmnService->storeRecord([
            'kode_barang' => '3050101001',
            'nup' => '302',
            'nama_barang' => 'Manual Barang 2',
            'jenis' => 'BMN-NON TIK',
            'lokasi' => 'B1.06 RUANG STAFF INFOKOM',
            'penanggungjawab' => 'Ahmad Kasubag',
            'kondisi' => 'Baik',
        ], $cache, false);

        $this->assertFalse(Distribusi::where('bmn_id', $bmn2->id)->exists(), 'Manual store with specific person and non-gudang location does not create BA Distribusi.');
    }
}
