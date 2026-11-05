<?php

namespace Tests\Unit;

use App\Models\Bmn;
use App\Models\Lokasi;
use App\Services\BmnService;
use App\Services\Import\BmnImportValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BmnClassificationTest extends TestCase
{
    use RefreshDatabase;

    protected BmnService $bmnService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bmnService = app(BmnService::class);

        // Siapkan master lokasi sampel
        Lokasi::firstOrCreate(['nama' => 'Belum berlokasi'], ['kode_ruangan' => 'BL']);
        Lokasi::firstOrCreate(['kode_ruangan' => 'A1.01'], ['nama' => 'RUANG DISKUSI LAB KIMIA']);
        Lokasi::firstOrCreate(['kode_ruangan' => 'B1.01'], ['nama' => 'RUANG TATA USAHA']);
    }

    public function test_tik_equipment_in_lab_room_gets_dual_tracking_tik_and_lab(): void
    {
        $bmn = $this->bmnService->storeRecord([
            'kode_barang' => '3100102001',
            'nup' => 1,
            'nama_barang' => 'PC Olah Data Lab',
            'merek' => 'Lenovo',
            'tahun_perolehan' => 2023,
            'nilai_perolehan' => 15000000,
            'tanggal_terima' => '2023-05-10',
            'jenis' => 'MESIN PERALATAN KHUSUS TIK',
            'lokasi' => 'A1.01 RUANG DISKUSI LAB KIMIA',
            'kondisi' => 'Baik',
        ]);

        $this->assertSame('BMN-TIK & LAB', $bmn->jenis);
        $this->assertSame(2, $bmn->kategori_bmn_id);

        // Verifikasi terdaftar di KEDUA tabel relasi
        $this->assertDatabaseHas('bmns_tik', [
            'bmn_id' => $bmn->id,
            'kode_barang' => '3100102001',
            'nup' => 1,
        ]);

        $this->assertDatabaseHas('bmns_lab', [
            'bmn_id' => $bmn->id,
            'kode_barang' => '3100102001',
            'nup' => 1,
        ]);

        $this->assertNotNull($bmn->tik);
        $this->assertNotNull($bmn->lab);
    }

    public function test_non_tik_asset_in_lab_room_becomes_bmn_lab(): void
    {
        $bmn = $this->bmnService->storeRecord([
            'kode_barang' => '3010305010',
            'nup' => 1,
            'nama_barang' => 'Pompa Sirkulasi Lab',
            'merek' => 'Hitachi',
            'tahun_perolehan' => 2021,
            'nilai_perolehan' => 8500000,
            'tanggal_terima' => '2021-08-15',
            'jenis' => 'ALAT BESAR',
            'lokasi' => 'A1.01 RUANG DISKUSI LAB KIMIA',
            'kondisi' => 'Baik',
        ]);

        $this->assertSame('BMN-LAB', $bmn->jenis);
        $this->assertSame(2, $bmn->kategori_bmn_id);
        $this->assertStringContainsString('Klasifikasi Asal: ALAT BESAR', (string) $bmn->spesifikasi);

        // Terdaftar di bmns_lab
        $this->assertDatabaseHas('bmns_lab', [
            'bmn_id' => $bmn->id,
            'kode_barang' => '3010305010',
        ]);

        // Tidak masuk ke bmns_tik
        $this->assertDatabaseMissing('bmns_tik', [
            'bmn_id' => $bmn->id,
        ]);
    }

    public function test_tik_equipment_outside_lab_becomes_bmn_tik(): void
    {
        $bmn = $this->bmnService->storeRecord([
            'kode_barang' => '3100102002',
            'nup' => 2,
            'nama_barang' => 'Laptop TU',
            'merek' => 'HP',
            'tahun_perolehan' => 2024,
            'nilai_perolehan' => 12000000,
            'tanggal_terima' => '2024-01-20',
            'jenis' => 'MESIN PERALATAN KHUSUS TIK',
            'lokasi' => 'B1.01 RUANG TATA USAHA',
            'kondisi' => 'Baik',
        ]);

        $this->assertSame('BMN-TIK', $bmn->jenis);
        $this->assertSame(3, $bmn->kategori_bmn_id);

        $this->assertDatabaseHas('bmns_tik', [
            'bmn_id' => $bmn->id,
            'kode_barang' => '3100102002',
        ]);

        $this->assertDatabaseMissing('bmns_lab', [
            'bmn_id' => $bmn->id,
        ]);
    }

    public function test_general_asset_outside_lab_becomes_bmn_non_tik(): void
    {
        $bmn = $this->bmnService->storeRecord([
            'kode_barang' => '2010101001',
            'nup' => 1,
            'nama_barang' => 'Tanah Bangunan Negara',
            'merek' => null,
            'tahun_perolehan' => 1978,
            'nilai_perolehan' => 10550088000,
            'tanggal_terima' => '1978-01-01',
            'jenis' => 'TANAH',
            'lokasi' => 'Belum berlokasi',
            'kondisi' => 'Baik',
        ]);

        $this->assertSame('BMN-NON TIK', $bmn->jenis);
        $this->assertSame(1, $bmn->kategori_bmn_id);
        $this->assertStringContainsString('Klasifikasi Asal: TANAH', (string) $bmn->spesifikasi);

        $this->assertDatabaseMissing('bmns_tik', ['bmn_id' => $bmn->id]);
        $this->assertDatabaseMissing('bmns_lab', ['bmn_id' => $bmn->id]);
    }

    public function test_unlisted_location_is_auto_created_in_master_lokasis(): void
    {
        $newLocationStr = 'A2.99 - RUANG BARU PENELITIAN FARMAKOLOGI';

        $this->assertDatabaseMissing('lokasis', ['kode_ruangan' => 'A2.99']);

        $bmn = $this->bmnService->storeRecord([
            'kode_barang' => '3010305099',
            'nup' => 99,
            'nama_barang' => 'Alat Uji Baru',
            'merek' => 'Yamato',
            'tahun_perolehan' => 2025,
            'nilai_perolehan' => 25000000,
            'tanggal_terima' => '2025-02-15',
            'jenis' => 'ALAT BESAR',
            'lokasi' => $newLocationStr,
            'kondisi' => 'Baik',
        ]);

        // Pastikan ruangan otomatis terdaftar di master lokasis
        $this->assertDatabaseHas('lokasis', [
            'kode_ruangan' => 'A2.99',
        ]);

        $this->assertNotNull($bmn->lokasi_id);
        $this->assertSame('A2.99', $bmn->lokasiRef->kode_ruangan);
        // Karena kode A2.99 adalah Gedung A, otomatis terdeteksi sebagai Lab
        $this->assertSame('BMN-LAB', $bmn->jenis);
        $this->assertSame(2, $bmn->kategori_bmn_id);
    }

    public function test_asset_with_non_tik_keyword_in_lab_becomes_bmn_lab_and_outside_lab_becomes_bmn_non_tik(): void
    {
        // Uji dengan jenis bertuliskan 'BMN-NON TIK' di ruang Lab Gedung A -> Harus masuk BMN-LAB
        $bmn1 = $this->bmnService->storeRecord([
            'kode_barang' => '3050101001',
            'nup' => 1,
            'nama_barang' => 'Meja Kerja Lab',
            'merek' => 'Informa',
            'tahun_perolehan' => 2022,
            'nilai_perolehan' => 2500000,
            'tanggal_terima' => '2022-03-10',
            'jenis' => 'BMN-NON TIK',
            'lokasi' => 'A1.01 RUANG DISKUSI LAB KIMIA',
            'kondisi' => 'Baik',
        ]);

        $this->assertSame('BMN-LAB', $bmn1->jenis);
        $this->assertSame(2, $bmn1->kategori_bmn_id);
        $this->assertNull($bmn1->tik);
        $this->assertNotNull($bmn1->lab);
        $this->assertDatabaseMissing('bmns_tik', ['bmn_id' => $bmn1->id]);
        $this->assertDatabaseHas('bmns_lab', ['bmn_id' => $bmn1->id]);

        // Uji dengan variasi teks 'ALAT NON TIK' di ruang non-lab (B1.01) -> Harus masuk BMN-NON TIK (Umum)
        $bmn2 = $this->bmnService->storeRecord([
            'kode_barang' => '3050101002',
            'nup' => 2,
            'nama_barang' => 'Lemari Dokumen',
            'merek' => 'Lion',
            'tahun_perolehan' => 2023,
            'nilai_perolehan' => 3500000,
            'tanggal_terima' => '2023-01-15',
            'jenis' => 'ALAT NON TIK',
            'lokasi' => 'B1.01 RUANG TATA USAHA',
            'kondisi' => 'Baik',
        ]);

        $this->assertSame('BMN-NON TIK', $bmn2->jenis);
        $this->assertSame(1, $bmn2->kategori_bmn_id);
        $this->assertNull($bmn2->tik);
        $this->assertNull($bmn2->lab);
        $this->assertDatabaseMissing('bmns_tik', ['bmn_id' => $bmn2->id]);
        $this->assertDatabaseMissing('bmns_lab', ['bmn_id' => $bmn2->id]);
    }

    public function test_import_validator_correctly_classifies_non_tik_in_preview(): void
    {
        $validator = app(BmnImportValidator::class);

        $rows = [
            [
                'excel_row' => 2,
                'kode_barang' => '3050101001',
                'nup_raw' => '1',
                'nama_barang' => 'Kursi Lab',
                'merek' => 'Chitose',
                'tanggal_terima' => '2022-01-01',
                'tahun_perolehan' => 2022,
                'nilai_perolehan' => 500000,
                'jenis' => 'BMN-NON TIK',
                'lokasi_ruang' => 'A1.01 RUANG DISKUSI LAB KIMIA',
                'kondisi' => 'Baik',
            ],
            [
                'excel_row' => 3,
                'kode_barang' => '3050101002',
                'nup_raw' => '2',
                'nama_barang' => 'Brankas',
                'merek' => 'Chubb',
                'tanggal_terima' => '2022-01-01',
                'tahun_perolehan' => 2022,
                'nilai_perolehan' => 15000000,
                'jenis' => 'PERALATAN NON TIK',
                'lokasi_ruang' => 'B1.01 RUANG TATA USAHA',
                'kondisi' => 'Baik',
            ],
        ];

        $res = $validator->validate($rows);
        $this->assertSame('BMN-LAB', $res['validated_rows'][0]['jenis_terklasifikasi']);
        $this->assertSame('BMN-NON TIK', $res['validated_rows'][1]['jenis_terklasifikasi']);
    }
}
