<?php

namespace Tests\Feature;

use App\Models\BaPerpindahan;
use App\Models\Bmn;
use App\Models\BmnLab;
use App\Models\BmnTik;
use App\Models\DataTerhapus;
use App\Models\Distribusi;
use App\Models\LaporanKerusakan;
use App\Models\Lokasi;
use App\Models\Pegawai;
use App\Models\Pengembalian;
use App\Models\PengembalianItem;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BmnModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_service_returns_correct_stats_structure(): void
    {
        $service = app(DashboardService::class);
        $stats = $service->getStats();

        $this->assertArrayHasKey('jumlahBmn', $stats);
        $this->assertArrayHasKey('jumlahBmnUmum', $stats);
        $this->assertArrayHasKey('jumlahBmnTik', $stats);
        $this->assertArrayHasKey('jumlahBmnLab', $stats);
        $this->assertArrayHasKey('jumlahKondisiBaik', $stats);
        $this->assertArrayHasKey('jumlahPerluKalibrasi', $stats);
        $this->assertArrayHasKey('jumlahKerusakan', $stats);
        $this->assertArrayHasKey('jumlahDistribusi', $stats);
        $this->assertArrayHasKey('jumlahBaPerpindahan', $stats);
        $this->assertArrayHasKey('aktivitasTerkini', $stats);
        $this->assertIsArray($stats['aktivitasTerkini']);
    }

    public function test_dashboard_laporan_kerusakan_card_links_to_laporan_kerusakan_index(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee(route('laporan-kerusakan.index'));
        $response->assertSee('Daftar Rusak Berat');
    }

    public function test_bmn_index_page_is_accessible_by_operator(): void
    {
        $user = User::factory()->create([
            'role' => 'Operator',
        ]);

        $response = $this->actingAs($user)->get(route('bmns.index'));

        $response->assertStatus(200);
    }

    public function test_bmn_lab_index_page_is_accessible_by_operator(): void
    {
        $user = User::factory()->create([
            'role' => 'Operator',
        ]);

        $response = $this->actingAs($user)->get(route('bmns-lab.index'));

        $response->assertStatus(200);
        $response->assertViewHas('bmnsLab');
    }

    public function test_bmn_active_scope_excludes_deleted_records(): void
    {
        Bmn::create([
            'kode_barang' => 'TEST-001',
            'nup' => '1',
            'nama_barang' => 'Laptop Uji',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Gudang',
            'penanggungjawab' => 'Admin',
            'kondisi' => 'Baik',
            'dihapus' => false,
        ]);

        Bmn::create([
            'kode_barang' => 'TEST-002',
            'nup' => '2',
            'nama_barang' => 'Laptop Dihapus',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Gudang',
            'penanggungjawab' => 'Admin',
            'kondisi' => 'Baik',
            'dihapus' => true,
        ]);

        $this->assertCount(1, Bmn::active()->get());
    }

    public function test_lokasi_index_accessible_by_operator(): void
    {
        $user = User::factory()->create([
            'role' => 'Operator',
        ]);

        Lokasi::create(['nama' => 'Ruang Server']);

        $response = $this->actingAs($user)->get(route('lokasis.index', ['search' => 'Ruang Server']));

        $response->assertStatus(200);
        $response->assertSee('Ruang Server');
    }

    public function test_lokasi_edit_page_accessible_and_updates_data(): void
    {
        $user = User::factory()->create([
            'role' => 'Operator',
        ]);

        $lokasi = Lokasi::create([
            'kode_ruangan' => 'A1.90',
            'nama' => 'Ruang Coba Edit',
        ]);

        // Check edit view returns 200 and displays form
        $response = $this->actingAs($user)->get(route('lokasis.edit', $lokasi));
        $response->assertStatus(200);
        $response->assertSee('Ubah Data Lokasi');
        $response->assertSee('A1.90');

        // Check update saves and redirects to index
        $updateResponse = $this->actingAs($user)->put(route('lokasis.update', $lokasi), [
            'kode_ruangan' => 'A1.91',
            'nama' => 'Ruang Coba Diedit',
        ]);
        $updateResponse->assertRedirect(route('lokasis.index'));

        $this->assertDatabaseHas('lokasis', [
            'id' => $lokasi->id,
            'kode_ruangan' => 'A1.91',
            'nama' => 'Ruang Coba Diedit',
        ]);
    }

    public function test_bmn_inline_location_update(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $bmn = Bmn::create([
            'kode_barang' => 'BMN-LOC-1',
            'nup' => '1',
            'nama_barang' => 'Meja Kerja',
            'lokasi' => 'Ruang Lama',
            'penanggungjawab' => 'Staff',
            'kondisi' => 'Baik',
            'dihapus' => false,
        ]);

        $response = $this->actingAs($user)->patch(route('bmns.update-lokasi', $bmn), [
            'lokasi' => 'Ruang Baru',
        ]);

        $response->assertRedirect();
        $this->assertEquals('Ruang Baru', $bmn->fresh()->lokasi);
    }

    public function test_bmn_tik_inline_location_update(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $bmnTik = BmnTik::create([
            'kode_barang' => 'TIK-LOC-1',
            'nup' => 1,
            'nama_barang' => 'PC Kantor',
            'lokasi' => 'Lab A',
            'penanggungjawab' => 'Staff',
            'dihapus' => false,
        ]);

        $response = $this->actingAs($user)->patch(route('bmns-tik.update-lokasi', $bmnTik), [
            'lokasi' => 'Lab B',
        ]);

        $response->assertRedirect();
        $this->assertEquals('Lab B', $bmnTik->fresh()->lokasi);
    }

    public function test_bmn_lab_inline_location_update(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $bmnLab = BmnLab::create([
            'kode_barang' => 'LAB-LOC-1',
            'nup' => 1,
            'nama_barang' => 'Mikroskop Uji',
            'lokasi' => 'Lab Kimia',
            'penanggungjawab' => 'Staff',
            'dihapus' => false,
        ]);

        $response = $this->actingAs($user)->patch(route('bmns-lab.update-lokasi', $bmnLab), [
            'lokasi' => 'Lab Mikrobiologi',
        ]);

        $response->assertRedirect();
        $this->assertEquals('Lab Mikrobiologi', $bmnLab->fresh()->lokasi);
    }

    public function test_qr_column_is_present_on_bmn_indexes(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $responseBmn = $this->actingAs($user)->get(route('bmns.index'));
        $responseBmn->assertOk();
        $responseBmn->assertSee('QR');
        $responseBmn->assertSee('qr-asset-modal');

        $responseTik = $this->actingAs($user)->get(route('bmns-tik.index'));
        $responseTik->assertOk();
        $responseTik->assertSee('QR');
        $responseTik->assertSee('qr-asset-modal');

        $responseLab = $this->actingAs($user)->get(route('bmns-lab.index'));
        $responseLab->assertOk();
        $responseLab->assertSee('QR');
        $responseLab->assertSee('qr-asset-modal');
    }

    public function test_bmn_create_displays_dokumen_pendukung_and_validates_max_500kb(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'Operator']);

        $response = $this->actingAs($user)->get(route('bmns.create'));
        $response->assertOk();
        $response->assertSee('Dokumen Pendukung');
        $response->assertSee('Manual Pengguna');
        $response->assertSee('Panduan Perbaikan');
        $response->assertSee('Instruksi Kerja');
        $response->assertSee('Foto Barang');
        $response->assertSee('500 KB');

        // Test file lebih dari 500 KB gagal validasi
        $tooLargeFile = UploadedFile::fake()->create('manual.pdf', 600, 'application/pdf');
        $failResponse = $this->actingAs($user)->post(route('bmns.store'), [
            'kode_barang' => '3050104001',
            'nup' => '1',
            'nama_barang' => 'Alat Uji Coba',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
            'manual_pengguna' => $tooLargeFile,
        ]);
        $failResponse->assertSessionHasErrors(['manual_pengguna']);

        // Test file valid (<= 500 KB) berhasil disimpan
        $validFile = UploadedFile::fake()->create('manual.pdf', 200, 'application/pdf');
        $validPhoto = UploadedFile::fake()->create('foto.jpg', 150, 'image/jpeg');
        $successResponse = $this->actingAs($user)->post(route('bmns.store'), [
            'kode_barang' => '3050104002',
            'nup' => '2',
            'nama_barang' => 'Alat Uji Valid',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
            'manual_pengguna' => $validFile,
            'foto_barang' => $validPhoto,
        ]);

        $successResponse->assertRedirect(route('bmns.index'));
        $bmn = Bmn::where('kode_barang', '3050104002')->first();
        $this->assertNotNull($bmn);
        $this->assertCount(2, $bmn->dokumens);
    }

    public function test_pengembalian_create_page_renders_with_bmn_items(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $bmn = Bmn::create([
            'kode_barang' => 'BMN-RET-01',
            'nup' => '10',
            'nama_barang' => 'Laptop ThinkPad',
            'merek' => 'Lenovo',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang IT',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($user)->get(route('pengembalians.create'));

        $response->assertStatus(200);
        $response->assertViewHas('bmns');
        $response->assertSee('Daftar barang');
        $response->assertSee('TAMBAH BARIS BARU');
        $response->assertSee('Laptop ThinkPad (Kode: BMN-RET-01 | NUP: 10)');
        $response->assertSee('Simpan');
        $response->assertSee('btn-submit-pengembalian');
    }

    public function test_pengembalian_store_with_items_saves_to_database(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $bmn = Bmn::create([
            'kode_barang' => 'BMN-RET-02',
            'nup' => '11',
            'nama_barang' => 'Monitor Dell 24 Inch',
            'merek' => 'Dell',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang TU',
            'penanggungjawab' => 'Budi Santoso',
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($user)->post(route('pengembalians.store'), [
            'nomor_pengajuan' => 'SIPANDA/2026/001',
            'tanggal' => '2026-10-13',
            'nama_pengaju' => 'Budi Santoso',
            'bidang' => 'Tata Usaha',
            'kondisi_pengembalian' => 'Baik',
            'items' => [
                [
                    'bmn_id' => $bmn->id,
                    'kondisi' => 'rusak ringan',
                    'keterangan' => 'Layar bergaris',
                ],
            ],
        ]);

        $response->assertRedirect(route('pengembalians.index'));

        $this->assertDatabaseHas('pengembalians', [
            'nomor_pengajuan' => 'SIPANDA/2026/001',
            'nama_pengaju' => 'Budi Santoso',
            'bidang' => 'Tata Usaha',
            'kondisi_pengembalian' => 'Baik',
        ]);

        $this->assertDatabaseHas('pengembalian_items', [
            'bmn_id' => $bmn->id,
            'nama_barang_snapshot' => 'Monitor Dell 24 Inch',
            'kondisi' => 'Rusak Ringan',
        ]);

        $this->assertDatabaseHas('bmns', [
            'id' => $bmn->id,
            'kondisi' => 'Rusak Ringan',
            'penanggungjawab' => 'Pengelola BMN',
        ]);

        $indexResponse = $this->actingAs($user)->get(route('pengembalians.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Barang yang Dikembalikan');
        $indexResponse->assertSee('Monitor Dell 24 Inch');
        $indexResponse->assertSee('BMN-RET-02');
        $indexResponse->assertSee('11');
        $indexResponse->assertSee('Kode Barang', false);
        $indexResponse->assertSee('NUP', false);
    }

    public function test_pengembalian_store_validation_requires_item_kondisi(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $bmn = Bmn::create([
            'kode_barang' => 'BMN-RET-03',
            'nup' => '12',
            'nama_barang' => 'Printer HP LaserJet',
            'merek' => 'HP',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang TU',
            'penanggungjawab' => 'Budi Santoso',
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($user)->post(route('pengembalians.store'), [
            'nomor_pengajuan' => 'SIPANDA/2026/002',
            'tanggal' => '2026-10-14',
            'nama_pengaju' => 'Budi Santoso',
            'bidang' => 'Tata Usaha',
            'kondisi_pengembalian' => 'Baik',
            'items' => [
                [
                    'bmn_id' => $bmn->id,
                    'kondisi' => '',
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['items.0.kondisi']);
    }

    public function test_adding_bmn_tik_automatically_creates_bmn_master_record(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $response = $this->actingAs($user)->post(route('bmns-tik.store'), [
            'kode_barang' => '3090405099',
            'nup' => '99',
            'nama_barang' => 'Server Dell PowerEdge',
            'jenis' => 'Server',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Admin Jaringan',
        ]);

        $response->assertRedirect(route('bmns-tik.index'));

        // Cek tersimpan di bmns_tik dan memiliki bmn_id
        $tik = BmnTik::where('kode_barang', '3090405099')->where('nup', 99)->first();
        $this->assertNotNull($tik);
        $this->assertNotNull($tik->bmn_id);

        // Cek otomatis tersimpan di tabel bmns (Daftar BMN)
        $this->assertDatabaseHas('bmns', [
            'id' => $tik->bmn_id,
            'kode_barang' => '3090405099',
            'nup' => '99',
            'nama_barang' => 'Server Dell PowerEdge',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Admin Jaringan',
        ]);
    }

    public function test_adding_bmn_lab_automatically_creates_bmn_master_record(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $response = $this->actingAs($user)->post(route('bmns-lab.store'), [
            'kode_barang' => '3080101088',
            'nup' => '88',
            'nama_barang' => 'Spektrofotometer UV-Vis',
            'merek' => 'Shimadzu',
            'lokasi' => 'Lab Kimia',
            'penanggungjawab' => 'Analis Kimia',
            'kondisi' => 'Baik',
            'status_kalibrasi' => 'Berlaku',
            'tanggal_kalibrasi' => '2026-05-10',
        ]);

        $response->assertRedirect(route('bmns-lab.index'));

        // Cek tersimpan di bmns_lab dan memiliki bmn_id
        $lab = BmnLab::where('kode_barang', '3080101088')->where('nup', 88)->first();
        $this->assertNotNull($lab);
        $this->assertNotNull($lab->bmn_id);

        // Cek otomatis tersimpan di tabel bmns (Daftar BMN)
        $this->assertDatabaseHas('bmns', [
            'id' => $lab->bmn_id,
            'kode_barang' => '3080101088',
            'nup' => '88',
            'nama_barang' => 'Spektrofotometer UV-Vis',
            'merek' => 'Shimadzu',
            'jenis' => 'BMN-LAB',
            'lokasi' => 'Lab Kimia',
            'penanggungjawab' => 'Analis Kimia',
            'kondisi' => 'Baik',
        ]);
    }

    public function test_deleting_bmn_tik_records_category_and_bmn_id_in_trash_and_restoring_restores_both(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        // Create via BMN TIK store
        $this->actingAs($user)->post(route('bmns-tik.store'), [
            'kode_barang' => '3090405011',
            'nup' => '11',
            'nama_barang' => 'Laptop ThinkPad X1',
            'jenis' => 'Laptop',
            'lokasi' => 'Ruang IT',
            'penanggungjawab' => 'Staf IT',
        ]);

        $tik = BmnTik::where('kode_barang', '3090405011')->firstOrFail();
        $bmn = $tik->bmn;
        $this->assertNotNull($bmn);

        // Delete via bmns-tik.destroy
        $response = $this->actingAs($user)->delete(route('bmns-tik.destroy', $tik));
        $response->assertRedirect(route('bmns-tik.index'));

        // Check both are soft-deleted
        $this->assertTrue($tik->fresh()->dihapus);
        $this->assertTrue($bmn->fresh()->dihapus);

        // Check DataTerhapus record
        $trash = DataTerhapus::where('bmn_id', $bmn->id)->first();
        $this->assertNotNull($trash);
        $this->assertEquals('BMN TIK', $trash->jenis);
        $this->assertEquals('Laptop ThinkPad X1', $trash->nama);

        // Restore via data-terhapus.restore
        $restoreResponse = $this->actingAs($user)->post(route('data-terhapus.restore', $trash));
        $restoreResponse->assertRedirect(route('data-terhapus.index'));

        // Verify both models are restored
        $this->assertFalse($tik->fresh()->dihapus);
        $this->assertFalse($bmn->fresh()->dihapus);
        $this->assertDatabaseMissing('data_terhapus', ['id' => $trash->id]);
    }

    public function test_deleting_bmn_lab_records_category_and_bmn_id_in_trash_and_restoring_restores_both(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        // Create via BMN LAB store
        $this->actingAs($user)->post(route('bmns-lab.store'), [
            'kode_barang' => '3080101022',
            'nup' => '22',
            'nama_barang' => 'Mikroskop Binokuler',
            'merek' => 'Olympus',
            'lokasi' => 'Lab Mikrobiologi',
            'penanggungjawab' => 'Staf Lab',
            'kondisi' => 'Baik',
            'status_kalibrasi' => 'Belum Dikalibrasi',
        ]);

        $lab = BmnLab::where('kode_barang', '3080101022')->firstOrFail();
        $bmn = $lab->bmn;
        $this->assertNotNull($bmn);

        // Delete via bmns-lab.destroy
        $response = $this->actingAs($user)->delete(route('bmns-lab.destroy', $lab));
        $response->assertRedirect(route('bmns-lab.index'));

        // Check both are soft-deleted
        $this->assertTrue($lab->fresh()->dihapus);
        $this->assertTrue($bmn->fresh()->dihapus);

        // Check DataTerhapus record
        $trash = DataTerhapus::where('bmn_id', $bmn->id)->first();
        $this->assertNotNull($trash);
        $this->assertEquals('BMN Lab', $trash->jenis);

        // Restore via data-terhapus.restore
        $restoreResponse = $this->actingAs($user)->post(route('data-terhapus.restore', $trash));
        $restoreResponse->assertRedirect(route('data-terhapus.index'));

        // Verify both models are restored
        $this->assertFalse($lab->fresh()->dihapus);
        $this->assertFalse($bmn->fresh()->dihapus);
        $this->assertDatabaseMissing('data_terhapus', ['id' => $trash->id]);
    }

    public function test_force_deleting_bmn_tik_from_trash_permanently_removes_all_records(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $this->actingAs($user)->post(route('bmns-tik.store'), [
            'kode_barang' => '3090405033',
            'nup' => '33',
            'nama_barang' => 'Switch Cisco Catalyst',
            'jenis' => 'Switch',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staf Jaringan',
        ]);

        $tik = BmnTik::where('kode_barang', '3090405033')->firstOrFail();
        $bmn = $tik->bmn;
        $this->assertNotNull($bmn);

        // Soft delete first
        $this->actingAs($user)->delete(route('bmns-tik.destroy', $tik));
        $trash = DataTerhapus::where('bmn_id', $bmn->id)->firstOrFail();

        // Force delete
        $response = $this->actingAs($user)->delete(route('data-terhapus.force-delete', $trash));
        $response->assertRedirect(route('data-terhapus.index'));

        // Verify permanently deleted from all tables
        $this->assertDatabaseMissing('data_terhapus', ['id' => $trash->id]);
        $this->assertDatabaseMissing('bmns_tik', ['id' => $tik->id]);
        $this->assertDatabaseMissing('bmns', ['id' => $bmn->id]);
    }

    public function test_force_deleting_bmn_lab_from_trash_permanently_removes_all_records(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $this->actingAs($user)->post(route('bmns-lab.store'), [
            'kode_barang' => '3080101044',
            'nup' => '44',
            'nama_barang' => 'Sentrifugasi Klinis',
            'merek' => 'Eppendorf',
            'lokasi' => 'Lab Sentral',
            'penanggungjawab' => 'Staf Lab',
            'kondisi' => 'Baik',
            'status_kalibrasi' => 'Belum Dikalibrasi',
        ]);

        $lab = BmnLab::where('kode_barang', '3080101044')->firstOrFail();
        $bmn = $lab->bmn;
        $this->assertNotNull($bmn);

        // Soft delete first
        $this->actingAs($user)->delete(route('bmns-lab.destroy', $lab));
        $trash = DataTerhapus::where('bmn_id', $bmn->id)->firstOrFail();

        // Force delete
        $response = $this->actingAs($user)->delete(route('data-terhapus.force-delete', $trash));
        $response->assertRedirect(route('data-terhapus.index'));

        // Verify permanently deleted from all tables
        $this->assertDatabaseMissing('data_terhapus', ['id' => $trash->id]);
        $this->assertDatabaseMissing('bmns_lab', ['id' => $lab->id]);
        $this->assertDatabaseMissing('bmns', ['id' => $bmn->id]);
    }

    public function test_operator_can_force_delete_all_records_at_once_from_trash(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        // Create 2 items to soft delete
        $bmn1 = Bmn::create([
            'kode_barang' => 'TRASH-ALL-01',
            'nup' => '1',
            'nama_barang' => 'Barang Sampah 1',
            'lokasi' => 'Gudang',
            'penanggungjawab' => 'Staf',
            'jenis' => 'BMN Umum',
            'dihapus' => true,
            'dihapus_pada' => now(),
            'dihapus_oleh' => 'Operator',
        ]);
        $bmn1->delete();
        $trash1 = DataTerhapus::create([
            'bmn_id' => $bmn1->id,
            'data_id' => $bmn1->id,
            'nama' => $bmn1->nama_barang,
            'jenis' => 'BMN',
            'dihapus_pada' => now(),
            'dihapus_oleh' => 'Operator',
        ]);

        $bmn2 = Bmn::create([
            'kode_barang' => 'TRASH-ALL-02',
            'nup' => '2',
            'nama_barang' => 'Barang Sampah 2',
            'lokasi' => 'Gudang',
            'penanggungjawab' => 'Staf',
            'jenis' => 'BMN Umum',
            'dihapus' => true,
            'dihapus_pada' => now(),
            'dihapus_oleh' => 'Operator',
        ]);
        $bmn2->delete();
        $trash2 = DataTerhapus::create([
            'bmn_id' => $bmn2->id,
            'data_id' => $bmn2->id,
            'nama' => $bmn2->nama_barang,
            'jenis' => 'BMN',
            'dihapus_pada' => now(),
            'dihapus_oleh' => 'Operator',
        ]);

        $this->assertDatabaseHas('data_terhapus', ['id' => $trash1->id]);
        $this->assertDatabaseHas('data_terhapus', ['id' => $trash2->id]);

        // Force delete all
        $response = $this->actingAs($operator)->delete(route('data-terhapus.force-delete-all'));
        $response->assertRedirect(route('data-terhapus.index'));
        $response->assertSessionHas('success');

        // Verify all permanently removed
        $this->assertEquals(0, DataTerhapus::count());
        $this->assertDatabaseMissing('bmns', ['id' => $bmn1->id]);
        $this->assertDatabaseMissing('bmns', ['id' => $bmn2->id]);
    }

    public function test_operator_can_force_delete_selected_records_from_trash(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $bmn1 = Bmn::create([
            'kode_barang' => 'TRASH-SEL-01',
            'nup' => '1',
            'nama_barang' => 'Barang Sel 1',
            'lokasi' => 'Gudang',
            'penanggungjawab' => 'Staf',
            'jenis' => 'BMN Umum',
            'dihapus' => true,
            'dihapus_pada' => now(),
            'dihapus_oleh' => 'Operator',
        ]);
        $bmn1->delete();
        $trash1 = DataTerhapus::create([
            'bmn_id' => $bmn1->id,
            'data_id' => $bmn1->id,
            'nama' => $bmn1->nama_barang,
            'jenis' => 'BMN',
            'dihapus_pada' => now(),
            'dihapus_oleh' => 'Operator',
        ]);

        $bmn2 = Bmn::create([
            'kode_barang' => 'TRASH-SEL-02',
            'nup' => '2',
            'nama_barang' => 'Barang Sel 2',
            'lokasi' => 'Gudang',
            'penanggungjawab' => 'Staf',
            'jenis' => 'BMN Umum',
            'dihapus' => true,
            'dihapus_pada' => now(),
            'dihapus_oleh' => 'Operator',
        ]);
        $bmn2->delete();
        $trash2 = DataTerhapus::create([
            'bmn_id' => $bmn2->id,
            'data_id' => $bmn2->id,
            'nama' => $bmn2->nama_barang,
            'jenis' => 'BMN',
            'dihapus_pada' => now(),
            'dihapus_oleh' => 'Operator',
        ]);

        // Force delete only trash1
        $response = $this->actingAs($operator)->delete(route('data-terhapus.force-delete-all'), [
            'ids' => (string) $trash1->id,
        ]);
        $response->assertRedirect(route('data-terhapus.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('data_terhapus', ['id' => $trash1->id]);
        $this->assertDatabaseMissing('bmns', ['id' => $bmn1->id]);
        $this->assertDatabaseHas('data_terhapus', ['id' => $trash2->id]);
        $this->assertDatabaseHas('bmns', ['id' => $bmn2->id]);
    }

    public function test_non_operator_cannot_force_delete_all_from_trash(): void
    {
        $pegawai = User::factory()->create(['role' => 'Pegawai']);

        $response = $this->actingAs($pegawai)->delete(route('data-terhapus.force-delete-all'));
        $response->assertForbidden();
    }

    public function test_bmn_tik_store_and_update_with_no_seri_syncs_with_bmn_master(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        // Create BMN TIK with no_seri
        $this->actingAs($user)->post(route('bmns-tik.store'), [
            'kode_barang' => '3090405055',
            'nup' => '55',
            'no_seri' => 'SN-LENOVO-999',
            'nama_barang' => 'ThinkCentre M70q',
            'jenis' => 'Komputer',
            'lokasi' => 'Ruang TIK',
            'penanggungjawab' => 'Staf IT',
        ]);

        $tik = BmnTik::where('kode_barang', '3090405055')->firstOrFail();
        $this->assertEquals('SN-LENOVO-999', $tik->no_seri);

        $bmn = $tik->bmn;
        $this->assertNotNull($bmn);
        $this->assertEquals('SN-LENOVO-999', $bmn->no_seri);

        // Update no_seri via BMN TIK
        $this->actingAs($user)->put(route('bmns-tik.update', $tik), [
            'kode_barang' => '3090405055',
            'nup' => 55,
            'no_seri' => 'SN-LENOVO-888-REV',
            'nama_barang' => 'ThinkCentre M70q Updated',
            'jenis' => 'Komputer',
            'lokasi' => 'Ruang TIK',
            'penanggungjawab' => 'Staf IT',
        ]);

        $this->assertEquals('SN-LENOVO-888-REV', $tik->fresh()->no_seri);
        $this->assertEquals('SN-LENOVO-888-REV', $bmn->fresh()->no_seri);
    }

    public function test_bmn_lab_store_and_update_with_no_seri_syncs_with_bmn_master(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        // Create BMN Lab with no_seri
        $this->actingAs($user)->post(route('bmns-lab.store'), [
            'kode_barang' => '3080101066',
            'nup' => '66',
            'no_seri' => 'SN-SHIMADZU-777',
            'nama_barang' => 'GC-MS Shimadzu',
            'merek' => 'Shimadzu',
            'lokasi' => 'Lab Instrumentasi',
            'penanggungjawab' => 'Analis Lab',
            'kondisi' => 'Baik',
        ]);

        $lab = BmnLab::where('kode_barang', '3080101066')->firstOrFail();
        $this->assertEquals('SN-SHIMADZU-777', $lab->no_seri);

        $bmn = $lab->bmn;
        $this->assertNotNull($bmn);
        $this->assertEquals('SN-SHIMADZU-777', $bmn->no_seri);

        // Update no_seri via BMN Lab
        $this->actingAs($user)->put(route('bmns-lab.update', $lab), [
            'kode_barang' => '3080101066',
            'nup' => 66,
            'no_seri' => 'SN-SHIMADZU-666-REV',
            'nama_barang' => 'GC-MS Shimadzu Updated',
            'merek' => 'Shimadzu',
            'lokasi' => 'Lab Instrumentasi',
            'penanggungjawab' => 'Analis Lab',
            'kondisi' => 'Baik',
        ]);

        $this->assertEquals('SN-SHIMADZU-666-REV', $lab->fresh()->no_seri);
        $this->assertEquals('SN-SHIMADZU-666-REV', $bmn->fresh()->no_seri);
    }

    public function test_distribusi_can_be_created_and_updated_without_status(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        // Create
        $response = $this->actingAs($user)->post(route('distribusi.store'), [
            'nomor' => 'DST-TEST-001',
            'tanggal' => '2026-10-13',
            'nama_barang' => 'Meja Rapat Kayu Jati',
            'jumlah' => 2,
            'satuan' => 'unit',
            'lokasi_asal' => 'Gudang Utama',
            'lokasi_tujuan' => 'Ruang Rapat Pimpinan',
            'penerima' => 'Budi Santoso',
            'keterangan' => 'Untuk kebutuhan rapat koordinasi',
        ]);

        $response->assertRedirect(route('distribusi.index'));

        $this->assertDatabaseHas('distribusis', [
            'nomor' => 'DST-TEST-001',
            'nama_barang' => 'Meja Rapat Kayu Jati',
            'penerima' => 'Budi Santoso',
        ]);

        $distribusi = Distribusi::where('nomor', 'DST-TEST-001')->firstOrFail();

        // Update
        $updateResponse = $this->actingAs($user)->put(route('distribusi.update', $distribusi), [
            'nomor' => 'DST-TEST-001-REV',
            'tanggal' => '2026-10-14',
            'nama_barang' => 'Meja Rapat Kayu Jati Baru',
            'jumlah' => 3,
            'satuan' => 'unit',
            'lokasi_asal' => 'Gudang Utama',
            'lokasi_tujuan' => 'Ruang Rapat Besar',
            'penerima' => 'Budi Santoso',
            'keterangan' => 'Revisi jumlah meja',
        ]);

        $updateResponse->assertRedirect(route('distribusi.index'));

        $this->assertDatabaseHas('distribusis', [
            'id' => $distribusi->id,
            'nomor' => 'DST-TEST-001-REV',
            'nama_barang' => 'Meja Rapat Kayu Jati Baru',
        ]);
    }

    public function test_distribusi_index_displays_kode_barang_and_nup_columns(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $bmn = Bmn::create([
            'kode_barang' => 'KD-BMN-777',
            'nup' => '88',
            'nama_barang' => 'Printer Canon Pixma',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Gudang BMN',
            'penanggungjawab' => 'Pengelola BMN',
            'kondisi' => 'Baik',
            'dihapus' => false,
        ]);

        Distribusi::create([
            'nomor' => 'BA-DIST/20261020/001',
            'tanggal' => '2026-10-20',
            'bmn_id' => $bmn->id,
            'nama_barang' => $bmn->nama_barang,
            'jumlah' => 1,
            'satuan' => 'unit',
            'lokasi_asal' => 'Gudang BMN',
            'lokasi_tujuan' => 'Ruang Tata Usaha',
            'penerima' => 'Ahmad Fauzi',
            'dihapus' => false,
        ]);

        $response = $this->actingAs($user)->get(route('distribusi.index'));

        $response->assertStatus(200);
        $response->assertSee('Kode Barang', false);
        $response->assertSee('NUP', false);
        $response->assertSee('KD-BMN-777');
        $response->assertSee('88');
    }

    public function test_laporan_kerusakan_can_be_created_with_bmn_relation(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $lokasi = Lokasi::create([
            'kode' => 'LOK-TEST-'.rand(100, 999),
            'nama' => 'Ruang Laboratorium Uji',
        ]);

        $bmn = Bmn::create([
            'kode_barang' => 'BMN-LK-001',
            'nup' => '10',
            'nama_barang' => 'Spektrofotometer UV-Vis',
            'lokasi' => 'Ruang Laboratorium Uji',
            'lokasi_id' => $lokasi->id,
            'penanggungjawab' => 'Petugas Lab',
            'jenis' => 'BMN Lab',
            'kondisi' => 'Baik',
        ]);

        $createResponse = $this->actingAs($user)->get(route('laporan-kerusakan.create'));
        $createResponse->assertOk();
        $createResponse->assertViewHas('bmns');
        $createResponse->assertSee('Nama barang terisi otomatis berdasarkan Kode');
        $createResponse->assertSee('Lokasi ruangan terisi otomatis sesuai data BMN.');

        $storeResponse = $this->actingAs($user)->post(route('laporan-kerusakan.store'), [
            'nomor' => 'LK/TEST/999',
            'tanggal' => '2026-10-13',
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Spektrofotometer UV-Vis',
            'lokasi' => 'Ruang Laboratorium Uji',
            'tingkat' => 'Sedang',
            'deskripsi' => 'Lampu deuterium tidak menyala saat inisialisasi alat.',
        ]);

        $storeResponse->assertRedirect(route('laporan-kerusakan.index'));

        $this->assertDatabaseHas('laporan_kerusakans', [
            'nomor' => 'LK/TEST/999',
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Spektrofotometer UV-Vis',
            'lokasi' => 'Ruang Laboratorium Uji',
            'lokasi_id' => $lokasi->id,
        ]);

        $laporan = LaporanKerusakan::where('nomor', 'LK/TEST/999')->firstOrFail();
        $this->assertEquals($bmn->id, $laporan->bmn?->id);

        $editResponse = $this->actingAs($user)->get(route('laporan-kerusakan.edit', $laporan));
        $editResponse->assertOk();
        $editResponse->assertSee('Terkunci');
        $editResponse->assertSee('Barang kerusakan telah ditetapkan oleh pelapor dan tidak dapat diubah oleh operator.');

        $indexResponse = $this->actingAs($user)->get(route('laporan-kerusakan.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('BMN-LK-001');
        $indexResponse->assertSee('10');
    }

    public function test_operator_can_create_ba_perpindahan_with_bmn_and_both_parties(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);
        $lokasiAsal = Lokasi::create(['kode' => 'LOK-01-'.rand(10, 99), 'nama' => 'Ruang Tata Usaha']);
        $lokasiTujuan = Lokasi::create(['kode' => 'LOK-02-'.rand(10, 99), 'nama' => 'Ruang Laboratorium Mikrobiologi']);

        $bmn = Bmn::create([
            'kode_barang' => 'BMN-MUT-01',
            'nup' => '5',
            'nama_barang' => 'Laptop Dell Latitude',
            'lokasi' => 'Ruang Tata Usaha',
            'lokasi_id' => $lokasiAsal->id,
            'penanggungjawab' => 'Ahmad Pegawai TU',
            'jenis' => 'BMN TIK',
            'kondisi' => 'Baik',
        ]);

        $createResponse = $this->actingAs($operator)->get(route('ba-perpindahan.create'));
        $createResponse->assertOk();
        $createResponse->assertViewHas('bmns');
        $createResponse->assertViewHas('pegawais');
        $createResponse->assertViewHas('lokasis');
        $createResponse->assertSee('baBmnSearchableSelect');

        $storeResponse = $this->actingAs($operator)->post(route('ba-perpindahan.store'), [
            'nomor_ba' => 'BA-MUT/TEST/001',
            'tanggal' => '2026-10-13',
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Laptop Dell Latitude',
            'dari' => 'Ruang Tata Usaha',
            'ke' => 'Ruang Laboratorium Mikrobiologi',
            'pengaju_nama' => 'Ahmad Pegawai TU',
            'pihak_kedua' => 'Siti Pegawai Lab',
            'alasan' => 'Dibutuhkan untuk analisis data mikrobiologi.',
        ]);

        $storeResponse->assertRedirect(route('ba-perpindahan.index'));

        $this->assertDatabaseHas('ba_perpindahans', [
            'nomor_ba' => 'BA-MUT/TEST/001',
            'bmn_id' => $bmn->id,
            'pengaju_nama' => 'Ahmad Pegawai TU',
            'pihak_kedua' => 'Siti Pegawai Lab',
            'lokasi_asal_id' => $lokasiAsal->id,
            'lokasi_tujuan_id' => $lokasiTujuan->id,
            'status' => 'Diajukan',
        ]);
    }

    public function test_regular_user_cannot_determine_pihak_kedua_in_ba_perpindahan(): void
    {
        $regularUser = User::factory()->create([
            'role' => 'User',
            'name' => 'Budi Staf Biasa',
        ]);

        $bmn = Bmn::create([
            'kode_barang' => 'BMN-MUT-02',
            'nup' => '8',
            'nama_barang' => 'Printer HP LaserJet',
            'lokasi' => 'Ruang Keuangan',
            'penanggungjawab' => 'Budi Staf Biasa',
            'jenis' => 'BMN Umum',
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($regularUser)->post(route('ba-perpindahan.store'), [
            'nomor_ba' => 'BA-MUT/TEST/002',
            'tanggal' => '2026-10-13',
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Printer HP LaserJet',
            'dari' => 'Ruang Keuangan',
            'ke' => 'Ruang Arsip',
            'pengaju_nama' => 'Mencoba Ganti Nama',
            'pihak_kedua' => 'Mencoba Tentukan Sendiri Pihak Kedua',
            'alasan' => 'Pindah ke ruang arsip.',
        ]);

        $response->assertRedirect(route('ba-perpindahan.index'));

        $this->assertDatabaseHas('ba_perpindahans', [
            'nomor_ba' => 'BA-MUT/TEST/002',
            'bmn_id' => $bmn->id,
            'pengaju_nama' => 'Mencoba Ganti Nama', // Pihak pertama dapat diisi/diubah
            'pihak_kedua' => null, // Pihak kedua tetap null karena hanya operator yang bisa menentukan
        ]);
    }

    public function test_operator_cannot_set_pihak_kedua_same_as_pihak_pertama_on_create(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);
        $bmn = Bmn::create([
            'kode_barang' => 'BMN-MUT-SAME-01',
            'nup' => '1',
            'nama_barang' => 'Laptop Lenovo ThinkPad',
            'lokasi' => 'Ruang Administrasi',
            'penanggungjawab' => 'Ahmad Fauzi (198804122014021001)',
            'jenis' => 'BMN TIK',
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($operator)->post(route('ba-perpindahan.store'), [
            'nomor_ba' => 'BA-MUT/TEST/SAME-01',
            'tanggal' => date('Y-m-d'),
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Laptop Lenovo ThinkPad',
            'dari' => 'Ruang Administrasi',
            'ke' => 'Ruang Rapat',
            'pihak_pertama' => 'Ahmad Fauzi (198804122014021001)',
            'pihak_kedua' => 'Ahmad Fauzi (198804122014021001)',
            'alasan' => 'Pindah ruangan',
        ]);

        $response->assertSessionHasErrors('pihak_kedua');
        $this->assertDatabaseMissing('ba_perpindahans', [
            'nomor_ba' => 'BA-MUT/TEST/SAME-01',
        ]);
    }

    public function test_operator_cannot_set_pihak_kedua_same_as_pihak_pertama_on_process_decision(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);
        $pegawai = Pegawai::create([
            'nama' => 'Hendra Setiawan',
            'nip' => '198501012010011005',
        ]);

        $ba = BaPerpindahan::create([
            'nomor_ba' => 'BA-MUT/SAME/DEC-01',
            'tanggal' => date('Y-m-d'),
            'nama_barang' => 'Kursi Kerja Ergonomis',
            'dari' => 'Ruang Staf',
            'ke' => 'Ruang Arsip',
            'pengaju_nama' => $operator->name,
            'pengaju_email' => $operator->email,
            'pihak_pertama' => 'Hendra Setiawan (198501012010011005)',
            'pihak_pertama_id' => $pegawai->id,
            'status' => 'Diajukan',
            'alasan' => 'Kebutuhan kerja',
        ]);

        $response = $this->actingAs($operator)->patch(route('ba-perpindahan.process-decision', $ba), [
            'pihak_kedua' => 'Hendra Setiawan (198501012010011005)',
            'keputusan' => 'Setuju',
        ]);

        $response->assertSessionHasErrors('pihak_kedua');
        $this->assertEquals('Diajukan', $ba->fresh()->status);
    }

    public function test_penyerah_is_excluded_from_penerima_selection_in_show_decision_view(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);
        $penyerah = Pegawai::create([
            'nama' => 'Rudi Hartono Penyerah',
            'nip' => '198701012011011001',
        ]);
        $penerima = Pegawai::create([
            'nama' => 'Dewi Lestari Penerima',
            'nip' => '199202022016022002',
        ]);

        $ba = BaPerpindahan::create([
            'nomor_ba' => 'BA-MUT/TEST/EXCLUDE-01',
            'tanggal' => date('Y-m-d'),
            'nama_barang' => 'Scanner Canon Lide',
            'dari' => 'Ruang Pelayanan',
            'ke' => 'Ruang Administrasi',
            'pengaju_nama' => $operator->name,
            'pengaju_email' => $operator->email,
            'pihak_pertama' => 'Rudi Hartono Penyerah (198701012011011001)',
            'pihak_pertama_id' => $penyerah->id,
            'status' => 'Diajukan',
            'alasan' => 'Perpindahan scanner',
        ]);

        $response = $this->actingAs($operator)->get(route('ba-perpindahan.show', $ba));
        $response->assertOk();
        $content = $response->getContent();
        $pos = strpos($content, 'process-decision');
        $this->assertNotFalse($pos);
        $formSnippet = substr($content, $pos, 3000);
        $this->assertStringContainsString('Dewi Lestari Penerima', $formSnippet);
        $this->assertStringNotContainsString('Rudi Hartono Penyerah', $formSnippet);
    }

    public function test_operator_can_approve_ba_perpindahan_and_mutate_bmn_location_and_recipient(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);
        $lokasiTujuan = Lokasi::create(['kode' => 'LOK-BARU-'.rand(10, 99), 'nama' => 'Ruang Server TIK']);

        $bmn = Bmn::create([
            'kode_barang' => 'BMN-MUT-03',
            'nup' => '12',
            'nama_barang' => 'Server Rack 24U',
            'lokasi' => 'Gudang Lama',
            'penanggungjawab' => 'Petugas Gudang',
            'jenis' => 'BMN TIK',
            'kondisi' => 'Baik',
        ]);

        $ba = BaPerpindahan::create([
            'nomor_ba' => 'BA-MUT/TEST/003',
            'tanggal' => '2026-10-13',
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Server Rack 24U',
            'dari' => 'Gudang Lama',
            'ke' => 'Ruang Server TIK',
            'pengaju_email' => 'gudang@example.com',
            'pengaju_nama' => 'Petugas Gudang',
            'pihak_kedua' => null, // Belum ditentukan oleh user biasa
            'alasan' => 'Relokasi server ke ruang server utama',
            'status' => 'Diajukan',
        ]);

        // Operator approve dan menentukan pihak_kedua
        $approveResponse = $this->actingAs($operator)->patch(route('ba-perpindahan.approve', $ba), [
            'pihak_kedua' => 'Rahmat Administrator Jaringan',
            'catatan' => 'Disetujui untuk meningkatkan keamanan server.',
        ]);

        $approveResponse->assertRedirect(route('ba-perpindahan.index'));

        $ba->refresh();
        $this->assertEquals('Disetujui', $ba->status);
        $this->assertEquals('Rahmat Administrator Jaringan', $ba->pihak_kedua);

        // Pastikan Master BMN juga ikut termutasi lokasi dan penanggung jawabnya!
        $bmn->refresh();
        $this->assertEquals('Ruang Server TIK', $bmn->lokasi);
        $this->assertEquals($lokasiTujuan->id, $bmn->lokasi_id);
        $this->assertEquals('Rahmat Administrator Jaringan', $bmn->penanggungjawab);
    }

    public function test_ba_perpindahan_relates_to_pegawai_as_pihak_pertama(): void
    {
        $user = User::factory()->create(['role' => 'User']);
        $pegawai = Pegawai::create([
            'nama' => 'Dr. Hendra Saputra',
            'nip' => '198501012010011005',
            'jabatan' => 'Analis Kimia',
            'unit_kerja' => 'Laboratorium Pengujian',
        ]);

        $bmn = Bmn::create([
            'kode_barang' => 'BMN-PGW-01',
            'nup' => '1',
            'nama_barang' => 'Spektrofotometer UV-Vis',
            'lokasi' => 'Lab Kimia',
            'penanggungjawab' => 'Dr. Hendra Saputra',
            'penanggung_jawab_id' => $pegawai->id,
            'jenis' => 'BMN Lab',
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($user)->post(route('ba-perpindahan.store'), [
            'nomor_ba' => 'BA-MUT/PGW/001',
            'tanggal' => '2026-10-13',
            'bmn_id' => $bmn->id,
            'pegawai_id' => $pegawai->id,
            'nama_barang' => 'Spektrofotometer UV-Vis',
            'dari' => 'Lab Kimia',
            'ke' => 'Lab Mikrobiologi',
            'alasan' => 'Dibutuhkan untuk uji spesimen khusus',
        ]);

        $response->assertRedirect(route('ba-perpindahan.index'));

        $this->assertDatabaseHas('ba_perpindahans', [
            'nomor_ba' => 'BA-MUT/PGW/001',
            'bmn_id' => $bmn->id,
            'pegawai_id' => $pegawai->id,
            'pengaju_nama' => 'Dr. Hendra Saputra',
        ]);

        $ba = BaPerpindahan::where('nomor_ba', 'BA-MUT/PGW/001')->first();
        $this->assertNotNull($ba->pegawai);
        $this->assertEquals($pegawai->id, $ba->pegawai->id);
        $this->assertEquals('198501012010011005', $ba->pegawai->nip);
    }

    public function test_ba_perpindahan_auto_fills_pihak_pertama_from_selected_bmn(): void
    {
        $user = User::factory()->create(['role' => 'User']);
        $pegawai = Pegawai::create([
            'nama' => 'Sri Wahyuni, S.Farm',
            'nip' => '199002022015022001',
            'jabatan' => 'Pengelola BMN',
            'unit_kerja' => 'Subbag Tata Usaha',
        ]);

        $bmn = Bmn::create([
            'kode_barang' => 'BMN-AUTO-01',
            'nup' => '5',
            'nama_barang' => 'Mikroskop Binokuler Olympus',
            'lokasi' => 'Lab Biologi',
            'penanggungjawab' => 'Sri Wahyuni, S.Farm',
            'penanggung_jawab_id' => $pegawai->id,
            'jenis' => 'BMN Lab',
            'kondisi' => 'Baik',
        ]);

        // Submit form hanya dengan bmn_id tanpa menyertakan pengaju_nama
        $response = $this->actingAs($user)->post(route('ba-perpindahan.store'), [
            'nomor_ba' => 'BA-MUT/AUTO/001',
            'tanggal' => '2026-10-13',
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Mikroskop Binokuler Olympus',
            'dari' => 'Lab Biologi',
            'ke' => 'Lab Farmasi',
            'alasan' => 'Dibutuhkan untuk pengujian obat',
        ]);

        $response->assertRedirect(route('ba-perpindahan.index'));

        $this->assertDatabaseHas('ba_perpindahans', [
            'nomor_ba' => 'BA-MUT/AUTO/001',
            'bmn_id' => $bmn->id,
            'pegawai_id' => $pegawai->id,
            'pengaju_nama' => 'Sri Wahyuni, S.Farm',
        ]);
    }

    public function test_operator_must_determine_pihak_kedua_before_approving_ba_perpindahan(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);
        $user = User::factory()->create(['role' => 'User']);

        $ba = BaPerpindahan::create([
            'nomor_ba' => 'BA-MUT/TEST/099',
            'tanggal' => '2026-10-14',
            'nama_barang' => 'Kursi Ergonomis',
            'dari' => 'Ruang TU',
            'ke' => 'Ruang Rapat',
            'pengaju_email' => 'user@example.com',
            'pengaju_nama' => 'User Pengaju',
            'pihak_kedua' => null,
            'alasan' => 'Pindah ruangan',
            'status' => 'Diajukan',
        ]);

        // Regular user cannot assign recipient
        $this->actingAs($user)->patch(route('ba-perpindahan.assign-recipient', $ba), [
            'pihak_kedua' => 'Budi Penerima',
        ])->assertForbidden();

        // Operator cannot approve without recipient
        $failApprove = $this->actingAs($operator)->patch(route('ba-perpindahan.approve', $ba), [
            'pihak_kedua' => '',
        ]);
        $failApprove->assertSessionHas('error');
        $this->assertEquals('Diajukan', $ba->fresh()->status);

        // Operator assigns recipient first
        $assignResponse = $this->actingAs($operator)->patch(route('ba-perpindahan.assign-recipient', $ba), [
            'pihak_kedua' => 'Budi Penerima',
        ]);
        $assignResponse->assertRedirect(route('ba-perpindahan.show', $ba));
        $this->assertEquals('Budi Penerima', $ba->fresh()->pihak_kedua);

        // Now Operator approves successfully
        $approveResponse = $this->actingAs($operator)->patch(route('ba-perpindahan.approve', $ba));
        $approveResponse->assertRedirect(route('ba-perpindahan.index'));
        $this->assertEquals('Disetujui', $ba->fresh()->status);
    }

    public function test_operator_can_cancel_approved_ba_perpindahan_and_revert_bmn_state(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);
        $user = User::factory()->create(['role' => 'User']);

        $bmn = Bmn::create([
            'kode_barang' => 'BMN-REV-01',
            'nup' => '1',
            'nama_barang' => 'Genset Honda 5kVA',
            'lokasi' => 'Gudang Genset',
            'penanggungjawab' => 'Pak Joko Genset',
            'jenis' => 'BMN Umum',
            'kondisi' => 'Baik',
        ]);

        $ba = BaPerpindahan::create([
            'nomor_ba' => 'BA-MUT/REV/001',
            'tanggal' => '2026-10-14',
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Genset Honda 5kVA',
            'dari' => 'Gudang Genset',
            'ke' => 'Pos Satpam',
            'pengaju_email' => 'joko@example.com',
            'pengaju_nama' => 'Pak Joko Genset',
            'pihak_kedua' => 'Danang Satpam',
            'alasan' => 'Dibutuhkan untuk backup listrik pos jaga',
            'status' => 'Diajukan',
        ]);

        // 1. Operator approves
        $this->actingAs($operator)->patch(route('ba-perpindahan.approve', $ba), [
            'catatan' => 'Disetujui untuk pos jaga',
        ]);

        $ba->refresh();
        $bmn->refresh();
        $this->assertEquals('Disetujui', $ba->status);
        $this->assertEquals('Pos Satpam', $bmn->lokasi);
        $this->assertEquals('Danang Satpam', $bmn->penanggungjawab);

        // 2. Regular user cannot cancel approval
        $this->actingAs($user)->patch(route('ba-perpindahan.cancel-approval', $ba))->assertForbidden();

        // 3. Operator cancels approval
        $cancelResponse = $this->actingAs($operator)->patch(route('ba-perpindahan.cancel-approval', $ba), [
            'catatan' => 'Batal karena pos jaga sudah pasang genset permanen',
        ]);

        $cancelResponse->assertRedirect(route('ba-perpindahan.show', $ba));

        $ba->refresh();
        $bmn->refresh();

        // Status BA menjadi Dibatalkan
        $this->assertEquals('Dibatalkan', $ba->status);
        $this->assertNotNull($ba->tanggal_keputusan);
        $this->assertEquals($operator->name, $ba->diputuskan_oleh);
        $this->assertStringContainsString('Persetujuan dibatalkan', $ba->catatan_operator);

        // Master BMN pulih ke kondisi asal
        $this->assertEquals('Gudang Genset', $bmn->lokasi);
        $this->assertEquals('Pak Joko Genset', $bmn->penanggungjawab);
    }

    public function test_ba_perpindahan_index_displays_distinct_columns_for_pihak_pertama_and_kedua(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $ba = BaPerpindahan::create([
            'nomor_ba' => 'BA-MUT/TEST/099',
            'tanggal' => '2026-10-14',
            'nama_barang' => 'Printer Epson L3110',
            'dari' => 'Ruang TU',
            'ke' => 'Ruang Guru',
            'pengaju_email' => 'tu@example.com',
            'pengaju_nama' => 'Kepala Tata Usaha',
            'pihak_kedua' => 'Guru Piket',
            'alasan' => 'Pindah tugas',
            'status' => 'Diajukan',
        ]);

        $response = $this->actingAs($operator)->get(route('ba-perpindahan.index'));
        $response->assertOk();
        $response->assertSee('Pengaju');
        $response->assertSee('Pihak I (Penyerah)');
        $response->assertSee('Pihak II (Penerima)');
        $response->assertSee('Kepala Tata Usaha');
        $response->assertSee('Guru Piket');
        $response->assertSee('Kode Barang', false);
        $response->assertSee('NUP', false);
    }

    public function test_operator_can_process_decision_approval_with_searchable_recipient_and_redecide(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $bmn = Bmn::create([
            'kode_barang' => 'BMN-DEC-01',
            'nup' => '1',
            'nama_barang' => 'AC Split Daikin 2PK',
            'lokasi' => 'Ruang Laboratorium 1',
            'penanggungjawab' => 'Laboran A',
            'jenis' => 'BMN Laboratorium',
            'kondisi' => 'Baik',
        ]);

        $ba = BaPerpindahan::create([
            'nomor_ba' => 'BA-MUT/DEC/001',
            'tanggal' => '2026-10-14',
            'bmn_id' => $bmn->id,
            'nama_barang' => 'AC Split Daikin 2PK',
            'dari' => 'Ruang Laboratorium 1',
            'ke' => 'Ruang Server TIK',
            'pengaju_email' => 'laboran@example.com',
            'pengaju_nama' => 'Laboran A',
            'alasan' => 'Server butuh pendingin tambahan',
            'status' => 'Diajukan',
        ]);

        // 1. Operator rejects without reason -> validation error
        $failReject = $this->actingAs($operator)->patch(route('ba-perpindahan.process-decision', $ba), [
            'pihak_kedua' => 'Pranata Komputer TIK',
            'keputusan' => 'Ditolak',
            'alasan_penolakan' => '',
        ]);
        $failReject->assertSessionHasErrors(['alasan_penolakan']);
        $this->assertEquals('Diajukan', $ba->fresh()->status);

        // 2. Operator rejects with reason -> successful rejection
        $rejectResponse = $this->actingAs($operator)->patch(route('ba-perpindahan.process-decision', $ba), [
            'pihak_kedua' => 'Pranata Komputer TIK',
            'keputusan' => 'Ditolak',
            'alasan_penolakan' => 'Ruang Lab 1 masih membutuhkan AC ini untuk kontrol suhu sampel',
        ]);
        $rejectResponse->assertRedirect(route('ba-perpindahan.show', $ba));
        $this->assertEquals('Ditolak', $ba->fresh()->status);
        $this->assertEquals('Ruang Laboratorium 1', $bmn->fresh()->lokasi);

        // 3. Operator cancels rejection -> status becomes Dibatalkan
        $cancelRejection = $this->actingAs($operator)->patch(route('ba-perpindahan.cancel-decision', $ba), [
            'catatan' => 'Ingin mempertimbangkan kembali perpindahan aset ini',
        ]);
        $cancelRejection->assertRedirect(route('ba-perpindahan.show', $ba));
        $this->assertEquals('Dibatalkan', $ba->fresh()->status);

        // 4. Operator decides again -> approves this time
        $approveResponse = $this->actingAs($operator)->patch(route('ba-perpindahan.process-decision', $ba), [
            'pihak_kedua' => 'Pranata Komputer TIK',
            'keputusan' => 'Setuju',
            'catatan' => 'Disetujui setelah konfirmasi lab',
        ]);
        $approveResponse->assertRedirect(route('ba-perpindahan.show', $ba));
        $this->assertEquals('Disetujui', $ba->fresh()->status);
        $this->assertEquals('Ruang Server TIK', $bmn->fresh()->lokasi);
        $this->assertEquals('Pranata Komputer TIK', $bmn->fresh()->penanggungjawab);

        // 5. Operator cancels approval -> status becomes Dibatalkan and BMN reverts
        $cancelApproval = $this->actingAs($operator)->patch(route('ba-perpindahan.cancel-decision', $ba));
        $cancelApproval->assertRedirect(route('ba-perpindahan.show', $ba));
        $this->assertEquals('Dibatalkan', $ba->fresh()->status);
        $this->assertEquals('Ruang Laboratorium 1', $bmn->fresh()->lokasi);
        $this->assertEquals('Laboran A', $bmn->fresh()->penanggungjawab);
    }

    public function test_user_can_view_rejection_reason_on_detail_and_index(): void
    {
        $user = User::factory()->create(['role' => 'User']);

        $ba = BaPerpindahan::create([
            'nomor_ba' => 'BA-MUT/REJ/002',
            'tanggal' => '2026-10-14',
            'nama_barang' => 'Meja Rapat',
            'dari' => 'Ruang Rapat',
            'ke' => 'Kantin',
            'pengaju_email' => $user->email,
            'pengaju_nama' => $user->name,
            'pihak_kedua' => 'Pengelola Kantin',
            'alasan' => 'Dipakai di kantin',
            'status' => 'Ditolak',
            'catatan_operator' => 'Aset ruang rapat tidak boleh dialihkan ke kantin',
            'tanggal_keputusan' => now(),
            'diputuskan_oleh' => 'Operator SIBOB',
        ]);

        $detailResponse = $this->actingAs($user)->get(route('ba-perpindahan.show', $ba));
        $detailResponse->assertOk();
        $detailResponse->assertSee('Alasan Penolakan oleh Operator');
        $detailResponse->assertSee('Aset ruang rapat tidak boleh dialihkan ke kantin');

        $indexResponse = $this->actingAs($user)->get(route('ba-perpindahan.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Alasan: Aset ruang rapat tidak boleh dialihkan ke kantin');
    }

    public function test_accepting_laporan_kerusakan_updates_bmn_and_lab_condition_to_rusak(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $bmn = Bmn::create([
            'kode_barang' => 'BMN-LK-TEST-01',
            'nup' => '1',
            'nama_barang' => 'Spektrofotometer UV-Vis',
            'lokasi' => 'Lab Kimia',
            'penanggungjawab' => 'Petugas Lab',
            'jenis' => 'BMN Lab',
            'kondisi' => 'Baik',
        ]);

        $bmnLab = BmnLab::create([
            'bmn_id' => $bmn->id,
            'kode_barang' => 'BMN-LK-TEST-01',
            'nup' => '1',
            'nama_barang' => 'Spektrofotometer UV-Vis',
            'lokasi' => 'Lab Kimia',
            'penanggungjawab' => 'Petugas Lab',
            'kondisi' => 'Baik',
        ]);

        $bmnTik = BmnTik::create([
            'bmn_id' => $bmn->id,
            'kode_barang' => 'BMN-LK-TEST-01',
            'nup' => '1',
            'nama_barang' => 'Spektrofotometer UV-Vis',
            'jenis' => 'Alat Lab',
            'lokasi' => 'Lab Kimia',
            'penanggungjawab' => 'Petugas Lab',
            'kondisi' => 'Baik',
        ]);

        $laporan = LaporanKerusakan::create([
            'nomor' => 'LK/TEST/001',
            'tanggal' => '2026-10-14',
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Spektrofotometer UV-Vis',
            'lokasi' => 'Lab Kimia',
            'tingkat' => 'Sedang',
            'deskripsi' => 'Lampu tidak menyala',
            'status' => 'Dilaporkan',
            'pelapor_email' => 'pelapor@example.com',
            'pelapor_nama' => 'User Pelapor',
        ]);

        $this->assertEquals('Baik', $bmn->fresh()->kondisi);
        $this->assertEquals('Baik', $bmnLab->fresh()->kondisi);
        $this->assertEquals('Baik', $bmnTik->fresh()->kondisi);

        // Operator accepts the report
        $response = $this->actingAs($operator)->put(route('laporan-kerusakan.update', $laporan), [
            'status' => 'Diterima',
            'tanggapan' => 'Laporan diterima, teknisi dijadwalkan besok.',
        ]);

        $response->assertRedirect(route('laporan-kerusakan.index'));

        $this->assertEquals('Diterima', $laporan->fresh()->status);
        $this->assertEquals('Rusak Sedang', $bmn->fresh()->kondisi);
        $this->assertEquals('Rusak Sedang', $bmnLab->fresh()->kondisi);
        $this->assertEquals('Rusak Sedang', $bmnTik->fresh()->kondisi);

        // Index BMN, BMN TIK, and BMN Lab display Rusak Sedang badge
        $this->actingAs($operator)->get(route('bmns.index'))->assertOk()->assertSee('Rusak Sedang');
        $this->actingAs($operator)->get(route('bmns-tik.index'))->assertOk()->assertSee('Rusak Sedang');
        $this->actingAs($operator)->get(route('bmns-lab.index'))->assertOk()->assertSee('Rusak Sedang');
    }

    public function test_accepting_laporan_kerusakan_ringan_updates_condition_to_rusak_ringan(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $bmn = Bmn::create([
            'kode_barang' => 'BMN-LK-RINGAN-01',
            'nup' => '1',
            'nama_barang' => 'Printer HP LaserJet',
            'lokasi' => 'Ruang TU',
            'penanggungjawab' => 'Petugas TU',
            'jenis' => 'BMN TIK',
            'kondisi' => 'Baik',
        ]);

        $laporan = LaporanKerusakan::create([
            'nomor' => 'LK/TEST/RINGAN',
            'tanggal' => '2026-10-14',
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Printer HP LaserJet',
            'lokasi' => 'Ruang TU',
            'tingkat' => 'Ringan',
            'deskripsi' => 'Roller kertas sedikit kotor',
            'status' => 'Dilaporkan',
            'pelapor_email' => 'pelapor@example.com',
            'pelapor_nama' => 'User Pelapor',
        ]);

        $response = $this->actingAs($operator)->put(route('laporan-kerusakan.update', $laporan), [
            'status' => 'Diterima',
            'tanggapan' => 'Pembersihan roller dijadwalkan.',
        ]);

        $response->assertRedirect(route('laporan-kerusakan.index'));

        $this->assertEquals('Diterima', $laporan->fresh()->status);
        $this->assertEquals('Rusak Berat', $bmn->fresh()->kondisi);
    }

    public function test_accepting_laporan_kerusakan_berat_updates_condition_to_rusak_berat(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $bmn = Bmn::create([
            'kode_barang' => 'BMN-LK-TEST-02',
            'nup' => '2',
            'nama_barang' => 'Server Rack Mount',
            'lokasi' => 'Ruang Server',
            'penanggungjawab' => 'Petugas IT',
            'jenis' => 'BMN TIK',
            'kondisi' => 'Baik',
        ]);

        $laporan = LaporanKerusakan::create([
            'nomor' => 'LK/TEST/002',
            'tanggal' => '2026-10-14',
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Server Rack Mount',
            'lokasi' => 'Ruang Server',
            'tingkat' => 'Berat',
            'deskripsi' => 'Konsleting motherboard, mati total',
            'status' => 'Dilaporkan',
            'pelapor_email' => 'pelapor@example.com',
            'pelapor_nama' => 'User Pelapor',
        ]);

        $response = $this->actingAs($operator)->put(route('laporan-kerusakan.update', $laporan), [
            'status' => 'Diproses',
            'tanggapan' => 'Sedang dilakukan penggantian motherboard.',
        ]);

        $response->assertRedirect(route('laporan-kerusakan.index'));

        $this->assertEquals('Diproses', $laporan->fresh()->status);
        $this->assertEquals('Rusak Berat', $bmn->fresh()->kondisi);
    }

    public function test_completing_laporan_kerusakan_restores_condition_to_baik(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $bmn = Bmn::create([
            'kode_barang' => 'BMN-LK-TEST-03',
            'nup' => '3',
            'nama_barang' => 'Mikroskop Binokuler',
            'lokasi' => 'Lab Biologi',
            'penanggungjawab' => 'Petugas Lab',
            'jenis' => 'BMN Lab',
            'kondisi' => 'Rusak Ringan',
        ]);

        $bmnLab = BmnLab::create([
            'bmn_id' => $bmn->id,
            'kode_barang' => 'BMN-LK-TEST-03',
            'nup' => '3',
            'nama_barang' => 'Mikroskop Binokuler',
            'lokasi' => 'Lab Biologi',
            'penanggungjawab' => 'Petugas Lab',
            'kondisi' => 'Rusak Ringan',
        ]);

        $laporan = LaporanKerusakan::create([
            'nomor' => 'LK/TEST/003',
            'tanggal' => '2026-10-14',
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Mikroskop Binokuler',
            'lokasi' => 'Lab Biologi',
            'tingkat' => 'Ringan',
            'deskripsi' => 'Fokus lensa macet',
            'status' => 'Diproses',
            'pelapor_email' => 'pelapor@example.com',
            'pelapor_nama' => 'User Pelapor',
        ]);

        $response = $this->actingAs($operator)->put(route('laporan-kerusakan.update', $laporan), [
            'status' => 'Selesai',
            'tanggapan' => 'Lensa telah dibersihkan dan dilumasi, alat berfungsi normal.',
        ]);

        $response->assertRedirect(route('laporan-kerusakan.index'));

        $this->assertEquals('Selesai', $laporan->fresh()->status);
        $this->assertEquals('Baik', $bmn->fresh()->kondisi);
        $this->assertEquals('Baik', $bmnLab->fresh()->kondisi);
    }

    public function test_operator_can_link_bmn_on_edit_and_status_diterima_updates_condition(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $bmn = Bmn::create([
            'kode_barang' => 'BMN-LK-TEST-04',
            'nup' => '4',
            'nama_barang' => 'AC Split 2 PK',
            'lokasi' => 'Ruang TU',
            'penanggungjawab' => 'Petugas TU',
            'jenis' => 'BMN Non TIK',
            'kondisi' => 'Baik',
        ]);

        $laporan = LaporanKerusakan::create([
            'nomor' => 'LK/TEST/004',
            'tanggal' => '2026-10-14',
            'bmn_id' => null,
            'nama_barang' => 'AC Split 2 PK',
            'lokasi' => 'Ruang TU',
            'tingkat' => 'Sedang',
            'deskripsi' => 'AC tidak dingin / bocor freon',
            'status' => 'Dilaporkan',
            'pelapor_email' => 'pelapor@example.com',
            'pelapor_nama' => 'User Pelapor',
        ]);

        $editResponse = $this->actingAs($operator)->get(route('laporan-kerusakan.edit', $laporan));
        $editResponse->assertOk();
        $editResponse->assertViewHas('bmns');

        $updateResponse = $this->actingAs($operator)->put(route('laporan-kerusakan.update', $laporan), [
            'bmn_id' => $bmn->id,
            'status' => 'Diterima',
            'tanggapan' => 'Dihubungkan ke master BMN dan dijadwalkan teknisi AC.',
        ]);

        $updateResponse->assertRedirect(route('laporan-kerusakan.index'));

        $this->assertEquals($bmn->id, $laporan->fresh()->bmn_id);
        $this->assertEquals('Diterima', $laporan->fresh()->status);
        $this->assertEquals('Rusak Sedang', $bmn->fresh()->kondisi);
    }

    public function test_bmn_edit_and_update_preserves_page_and_filter_query(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $bmn = Bmn::create([
            'kode_barang' => 'TEST-BMN-PAG-01',
            'nup' => '10',
            'nama_barang' => 'Laptop Lenovo Paginate',
            'lokasi' => 'Ruang TIK',
            'penanggungjawab' => 'Budi',
            'jenis' => 'BMN-TIK',
            'kondisi' => 'Baik',
        ]);

        // Simulasikan operator membuka edit dari halaman 2 dengan filter search
        $editResponse = $this->actingAs($operator)->get(route('bmns.edit', [
            'bmn' => $bmn->id,
            'page' => 2,
            'search' => 'Lenovo',
            'jenis' => 'BMN-TIK',
        ]));
        $editResponse->assertOk();

        // Update data
        $updateResponse = $this->actingAs($operator)->put(route('bmns.update', $bmn), [
            'kode_barang' => '3050104071',
            'nup' => '10',
            'nama_barang' => 'Laptop Lenovo Paginate Updated',
            'lokasi' => 'Ruang TIK',
            'penanggungjawab' => 'Budi',
            'jenis' => 'BMN-TIK',
            'kondisi' => 'Baik',
        ]);

        // Harap dialihkan kembali ke bmns.index dengan page=2 dan search=Lenovo dan jenis=BMN-TIK
        $updateResponse->assertRedirect(route('bmns.index', [
            'page' => 2,
            'search' => 'Lenovo',
            'jenis' => 'BMN-TIK',
        ]));
    }

    public function test_bmn_update_lokasi_preserves_page_and_filter_query(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $bmn = Bmn::create([
            'kode_barang' => 'TEST-BMN-LOK-01',
            'nup' => '11',
            'nama_barang' => 'PC All In One',
            'lokasi' => 'Ruang Lama',
            'penanggungjawab' => 'Siti',
            'jenis' => 'BMN-TIK',
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($operator)->patch(route('bmns.update-lokasi', $bmn), [
            'lokasi' => 'Ruang Baru',
            'page' => 3,
            'search' => 'PC',
            'old_filter_jenis' => 'BMN-TIK',
        ]);

        $response->assertRedirect(route('bmns.index', [
            'page' => 3,
            'search' => 'PC',
            'jenis' => 'BMN-TIK',
        ]));
        $this->assertEquals('Ruang Baru', $bmn->fresh()->lokasi);
    }

    public function test_bmn_tik_edit_and_update_preserves_page_and_filter_query(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $bmnTik = BmnTik::create([
            'kode_barang' => '3090405072',
            'nup' => '1',
            'nama_barang' => 'Switch Hub Paginate',
            'jenis' => 'Perangkat Jaringan',
            'merek' => 'Cisco',
            'lokasi' => 'Server Room',
            'penanggungjawab' => 'Admin TIK',
        ]);

        $this->actingAs($operator)->get(route('bmns-tik.edit', [
            'bmnTik' => $bmnTik->id,
            'page' => 2,
            'q' => 'Switch',
        ]))->assertOk();

        $updateResponse = $this->actingAs($operator)->put(route('bmns-tik.update', $bmnTik), [
            'kode_barang' => '3090405072',
            'nup' => '1',
            'nama_barang' => 'Switch Hub Paginate Edited',
            'jenis' => 'Perangkat Jaringan',
            'merek' => 'Cisco',
            'lokasi' => 'Server Room',
            'penanggungjawab' => 'Admin TIK',
        ]);

        $updateResponse->assertRedirect(route('bmns-tik.index', [
            'page' => 2,
            'q' => 'Switch',
        ]));
    }

    public function test_bmn_lab_edit_and_update_preserves_page_and_filter_query(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $bmnLab = BmnLab::create([
            'kode_barang' => '3080101073',
            'nup' => '1',
            'nama_barang' => 'Spektrofotometer UV',
            'tipe' => 'UV-Vis',
            'nomor_seri' => 'SN-LAB-123',
            'kondisi' => 'Baik',
            'lokasi' => 'Lab Kimia',
            'penanggungjawab' => 'Analis',
        ]);

        $this->actingAs($operator)->get(route('bmns-lab.edit', [
            'bmnLab' => $bmnLab->id,
            'page' => 4,
            'search' => 'Spektro',
        ]))->assertOk();

        $updateResponse = $this->actingAs($operator)->put(route('bmns-lab.update', $bmnLab), [
            'kode_barang' => '3080101073',
            'nup' => '1',
            'nama_barang' => 'Spektrofotometer UV Edited',
            'tipe' => 'UV-Vis',
            'nomor_seri' => 'SN-LAB-123',
            'kondisi' => 'Baik',
            'lokasi' => 'Lab Kimia',
            'penanggungjawab' => 'Analis',
        ]);

        $updateResponse->assertRedirect(route('bmns-lab.index', [
            'page' => 4,
            'search' => 'Spektro',
        ]));
    }

    public function test_pengembalian_sets_is_dikembalikan_true_and_displays_badge_on_index(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $bmn = Bmn::create([
            'kode_barang' => 'TEST-RET-01',
            'nup' => '99',
            'nama_barang' => 'Kamera DSLR Dokumentasi',
            'lokasi' => 'Ruang Humas',
            'penanggungjawab' => 'Staf Humas',
            'jenis' => 'BMN-TIK',
            'kondisi' => 'Baik',
            'is_dikembalikan' => false,
        ]);

        $bmnTik = BmnTik::create([
            'bmn_id' => $bmn->id,
            'kode_barang' => 'TEST-RET-01',
            'nup' => '99',
            'nama_barang' => 'Kamera DSLR Dokumentasi',
            'jenis' => 'Perangkat Multimedia',
            'lokasi' => 'Ruang Humas',
            'penanggungjawab' => 'Staf Humas',
            'is_dikembalikan' => false,
        ]);

        // Simulasikan submit pengembalian
        $response = $this->actingAs($operator)->post(route('pengembalians.store'), [
            'nomor_pengajuan' => 'RET/2026/099',
            'tanggal' => '2026-10-14',
            'nama_pengaju' => 'Staf Humas',
            'bidang' => 'Tata Usaha',
            'kondisi_pengembalian' => 'Rusak Ringan',
            'alasan' => 'Sudah tidak digunakan di unit',
            'items' => [
                [
                    'bmn_id' => $bmn->id,
                    'kondisi' => 'Rusak Ringan',
                    'keterangan' => 'Lensa berjamur',
                ],
            ],
        ]);

        $response->assertRedirect(route('pengembalians.index'));

        // Cek flag is_dikembalikan menjadi true dan penanggungjawab jadi Pengelola BMN
        $this->assertTrue((bool) $bmn->fresh()->is_dikembalikan);
        $this->assertEquals('Pengelola BMN', $bmn->fresh()->penanggungjawab);
        $this->assertTrue((bool) $bmnTik->fresh()->is_dikembalikan);
        $this->assertEquals('Pengelola BMN', $bmnTik->fresh()->penanggungjawab);

        // Cek tampilan indeks menampilkan badge "Barang Pengembalian"
        $indexBmn = $this->actingAs($operator)->get(route('bmns.index'));
        $indexBmn->assertOk();
        $indexBmn->assertSee('Barang Pengembalian');

        $indexTik = $this->actingAs($operator)->get(route('bmns-tik.index'));
        $indexTik->assertOk();
        $indexTik->assertSee('Barang Pengembalian');
    }

    public function test_approving_ba_perpindahan_clears_is_dikembalikan_flag(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);
        $penerima = Pegawai::create([
            'nama' => 'Penerima Baru',
            'nip' => '199001012020011001',
            'jabatan' => 'Pranata Komputer',
            'unit_kerja' => 'TIK',
        ]);

        $bmn = Bmn::create([
            'kode_barang' => 'TEST-MUT-01',
            'nup' => '88',
            'nama_barang' => 'Server Rack Retur',
            'lokasi' => 'Gudang BMN',
            'penanggungjawab' => 'Pengelola BMN',
            'jenis' => 'BMN-TIK',
            'kondisi' => 'Baik',
            'is_dikembalikan' => true,
        ]);

        $bmnTik = BmnTik::create([
            'bmn_id' => $bmn->id,
            'kode_barang' => 'TEST-MUT-01',
            'nup' => '88',
            'nama_barang' => 'Server Rack Retur',
            'jenis' => 'Server',
            'lokasi' => 'Gudang BMN',
            'penanggungjawab' => 'Pengelola BMN',
            'is_dikembalikan' => true,
        ]);

        $ba = BaPerpindahan::create([
            'nomor_ba' => 'BA/TEST/088',
            'tanggal' => '2026-10-14',
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Server Rack Retur',
            'dari' => 'Gudang BMN',
            'ke' => 'Server Room Utama',
            'pengaju_nama' => 'Pengelola BMN',
            'pengaju_email' => 'pengelola@example.com',
            'pihak_kedua' => "{$penerima->nama} ({$penerima->nip})",
            'alasan' => 'Dikeluarkan dari gudang untuk ruang server',
            'status' => 'Diajukan',
        ]);

        // Operator approve mutasi perpindahan
        $response = $this->actingAs($operator)->patch(route('ba-perpindahan.process-decision', $ba), [
            'keputusan' => 'Setuju',
            'pihak_kedua' => "{$penerima->nama} ({$penerima->nip})",
        ]);

        $response->assertRedirect(route('ba-perpindahan.show', $ba));

        // Cek flag is_dikembalikan di-reset ke false
        $this->assertFalse((bool) $bmn->fresh()->is_dikembalikan);
        $this->assertEquals("{$penerima->nama} ({$penerima->nip})", $bmn->fresh()->penanggungjawab);
        $this->assertFalse((bool) $bmnTik->fresh()->is_dikembalikan);
        $this->assertEquals("{$penerima->nama} ({$penerima->nip})", $bmnTik->fresh()->penanggungjawab);
    }

    public function test_bmn_tik_stores_and_displays_kondisi_column(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $response = $this->actingAs($operator)->post(route('bmns-tik.store'), [
            'kode_barang' => '3090405050',
            'nup' => 50,
            'nama_barang' => 'Laptop Lenovo ThinkPad T14',
            'jenis' => 'Laptop',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        $response->assertRedirect(route('bmns-tik.index'));

        $tik = BmnTik::where('kode_barang', '3090405050')->firstOrFail();
        $this->assertEquals('Baik', $tik->kondisi);
        $this->assertEquals('Baik', $tik->bmn?->kondisi);

        // Update to Rusak
        $updateResp = $this->actingAs($operator)->put(route('bmns-tik.update', $tik), [
            'kode_barang' => '3090405050',
            'nup' => 50,
            'nama_barang' => 'Laptop Lenovo ThinkPad T14',
            'jenis' => 'Laptop',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Rusak',
        ]);
        $updateResp->assertRedirect(route('bmns-tik.index'));

        $this->assertEquals('Rusak', $tik->fresh()->kondisi);
        $this->assertEquals('Rusak', $tik->fresh()->bmn?->kondisi);

        // Cek halaman index memuat kolom dan badge Kondisi
        $indexResp = $this->actingAs($operator)->get(route('bmns-tik.index'));
        $indexResp->assertOk();
        $indexResp->assertSee('Kondisi');
        $indexResp->assertSee('Rusak');
    }

    public function test_regular_user_can_view_edit_and_cancel_buttons_when_laporan_is_dilaporkan(): void
    {
        $user = User::factory()->create(['role' => 'User']);
        $bmn = Bmn::create([
            'kode_barang' => 'BMN-LK-USR-01',
            'nup' => '1',
            'nama_barang' => 'Monitor Dell 24 Inch',
            'lokasi' => 'Ruang Administrasi',
            'penanggungjawab' => 'Staff Admin',
            'jenis' => 'BMN-TIK',
            'kondisi' => 'Baik',
        ]);

        $laporan = LaporanKerusakan::create([
            'nomor' => 'LK/USR/001',
            'tanggal' => '2026-10-14',
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Monitor Dell 24 Inch',
            'lokasi' => 'Ruang Administrasi',
            'tingkat' => 'Ringan',
            'deskripsi' => 'Layar bergaris tipis di bagian bawah',
            'status' => 'Dilaporkan',
            'pelapor_email' => $user->email,
            'pelapor_nama' => $user->name,
        ]);

        // Cek halaman index menampilkan aksi edit dan batalkan
        $indexResponse = $this->actingAs($user)->get(route('laporan-kerusakan.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee(route('laporan-kerusakan.edit', $laporan->id));
        $indexResponse->assertSee('Batalkan Laporan');

        // Cek halaman show menampilkan tombol Edit Aduan dan Batalkan Laporan
        $showResponse = $this->actingAs($user)->get(route('laporan-kerusakan.show', $laporan));
        $showResponse->assertOk();
        $showResponse->assertSee('Edit Aduan');
        $showResponse->assertSee('Batalkan Laporan');

        // Cek halaman edit untuk pengguna menampilkan form edit rincian aduan
        $editResponse = $this->actingAs($user)->get(route('laporan-kerusakan.edit', $laporan));
        $editResponse->assertOk();
        $editResponse->assertSee('Edit Laporan Kerusakan');
        $editResponse->assertSee('Simpan Perubahan');
        $editResponse->assertSee('Batalkan Laporan');
        $editResponse->assertDontSee('Status Penanganan *');
    }

    public function test_regular_user_can_update_and_cancel_laporan_kerusakan_before_accepted(): void
    {
        $user = User::factory()->create(['role' => 'User']);
        $bmn = Bmn::create([
            'kode_barang' => 'BMN-LK-USR-02',
            'nup' => '2',
            'nama_barang' => 'Printer Epson L3110',
            'lokasi' => 'Ruang Keuangan',
            'penanggungjawab' => 'Staff Keuangan',
            'jenis' => 'BMN-TIK',
            'kondisi' => 'Baik',
        ]);

        $laporan = LaporanKerusakan::create([
            'nomor' => 'LK/USR/002',
            'tanggal' => '2026-10-14',
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Printer Epson L3110',
            'lokasi' => 'Ruang Keuangan',
            'tingkat' => 'Ringan',
            'deskripsi' => 'Hasil cetak garis putus-putus',
            'status' => 'Dilaporkan',
            'pelapor_email' => $user->email,
            'pelapor_nama' => $user->name,
        ]);

        // User update data laporan (tingkat & deskripsi)
        $updateResponse = $this->actingAs($user)->put(route('laporan-kerusakan.update', $laporan), [
            'tanggal' => '2026-10-14',
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Printer Epson L3110',
            'lokasi' => 'Ruang Keuangan',
            'tingkat' => 'Sedang',
            'deskripsi' => 'Roller macet dan paper jam berulang kali',
            'status' => 'Selesai',
            'tanggapan' => 'Diselesaikan sendiri',
        ]);

        $updateResponse->assertRedirect(route('laporan-kerusakan.index'));

        $fresh = $laporan->fresh();
        $this->assertEquals('Sedang', $fresh->tingkat);
        $this->assertEquals('Roller macet dan paper jam berulang kali', $fresh->deskripsi);
        $this->assertEquals('Dilaporkan', $fresh->status);
        $this->assertNull($fresh->ditanggapi_oleh);

        // User membatalkan proses laporan kerusakan
        $deleteResponse = $this->actingAs($user)->delete(route('laporan-kerusakan.destroy', $laporan));
        $deleteResponse->assertSessionHas('success', 'Laporan kerusakan berhasil dibatalkan.');
        $this->assertEquals(true, (bool) $laporan->fresh()->dihapus);
        $this->assertDatabaseHas('data_terhapus', [
            'jenis' => 'Laporan Kerusakan',
            'data_id' => $laporan->id,
        ]);
    }

    public function test_regular_user_cannot_edit_or_cancel_laporan_kerusakan_after_accepted(): void
    {
        $user = User::factory()->create(['role' => 'User']);
        $bmn = Bmn::create([
            'kode_barang' => 'BMN-LK-USR-03',
            'nup' => '3',
            'nama_barang' => 'UPS ICA 1200VA',
            'lokasi' => 'Ruang Server',
            'penanggungjawab' => 'Staff IT',
            'jenis' => 'BMN-TIK',
            'kondisi' => 'Rusak Ringan',
        ]);

        $laporan = LaporanKerusakan::create([
            'nomor' => 'LK/USR/003',
            'tanggal' => '2026-10-14',
            'bmn_id' => $bmn->id,
            'nama_barang' => 'UPS ICA 1200VA',
            'lokasi' => 'Ruang Server',
            'tingkat' => 'Ringan',
            'deskripsi' => 'Baterai drop tidak tahan pemadaman',
            'status' => 'Diterima',
            'pelapor_email' => $user->email,
            'pelapor_nama' => $user->name,
        ]);

        // Cek halaman index tidak menampilkan tombol edit untuk laporan Diterima
        $indexResponse = $this->actingAs($user)->get(route('laporan-kerusakan.index'));
        $indexResponse->assertOk();
        $indexResponse->assertDontSee(route('laporan-kerusakan.edit', $laporan->id));

        // Cek halaman show tidak menampilkan tombol Edit Aduan atau Batalkan Laporan
        $showResponse = $this->actingAs($user)->get(route('laporan-kerusakan.show', $laporan));
        $showResponse->assertOk();
        $showResponse->assertDontSee('Edit Aduan');
        $showResponse->assertDontSee('Batalkan Laporan');

        // Mengakses edit page langsung diarahkan kembali dengan pesan error
        $editResponse = $this->actingAs($user)->get(route('laporan-kerusakan.edit', $laporan));
        $editResponse->assertRedirect(route('laporan-kerusakan.show', $laporan));
        $editResponse->assertSessionHas('error');

        // Mengirimkan update langsung via PUT di-abort 403
        $updateResponse = $this->actingAs($user)->put(route('laporan-kerusakan.update', $laporan), [
            'deskripsi' => 'Mencoba merubah setelah diterima',
        ]);
        $updateResponse->assertStatus(403);

        // Mengirimkan destroy langsung via DELETE di-abort 403
        $deleteResponse = $this->actingAs($user)->delete(route('laporan-kerusakan.destroy', $laporan));
        $deleteResponse->assertStatus(403);
    }

    public function test_ba_perpindahan_form_renders_nama_barang_dari_and_pengaju_nama_as_readonly(): void
    {
        $user = User::factory()->create(['role' => 'User']);

        $response = $this->actingAs($user)->get(route('ba-perpindahan.create'));
        $response->assertOk();
        $response->assertDontSee('Ubah manual');
        $response->assertSee('Otomatis terisi saat memilih Kode & NUP BMN di atas', false);
        $response->assertSee('cursor-not-allowed', false);
    }

    public function test_ba_perpindahan_can_be_deleted_before_approval(): void
    {
        $user = User::factory()->create(['role' => 'User']);
        $bmn = Bmn::create([
            'kode_barang' => 'BMN-MUT-DEL-01',
            'nup' => '1',
            'nama_barang' => 'Scanner Barcode Honeywell',
            'lokasi' => 'Ruang TU',
            'penanggungjawab' => 'Staff TU',
            'jenis' => 'BMN-TIK',
            'kondisi' => 'Baik',
        ]);

        $ba = BaPerpindahan::create([
            'nomor_ba' => 'BA-MUT/DEL/001',
            'tanggal' => '2026-10-14',
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Scanner Barcode Honeywell',
            'dari' => 'Ruang TU',
            'ke' => 'Ruang Gudang',
            'alasan' => 'Pindah tugas pemindaian',
            'pengaju_nama' => 'Staff TU',
            'pengaju_email' => $user->email,
            'pengaju_id' => $user->id,
            'status' => 'Diajukan',
        ]);

        // Cek halaman index menampilkan tombol hapus untuk BA yang belum disetujui
        $indexResponse = $this->actingAs($user)->get(route('ba-perpindahan.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('title="Hapus BA Perpindahan"', false);

        // Cek halaman show menampilkan tombol Hapus BA
        $showResponse = $this->actingAs($user)->get(route('ba-perpindahan.show', $ba));
        $showResponse->assertOk();
        $showResponse->assertSee('Hapus BA');

        // Cek halaman edit menampilkan tombol Hapus BA
        $editResponse = $this->actingAs($user)->get(route('ba-perpindahan.edit', $ba));
        $editResponse->assertOk();
        $editResponse->assertSee('Hapus BA');

        // Hapus BA perpindahan
        $deleteResponse = $this->actingAs($user)->delete(route('ba-perpindahan.destroy', $ba));
        $deleteResponse->assertRedirect(route('ba-perpindahan.index'));
        $deleteResponse->assertSessionHas('success', 'BA perpindahan berhasil dihapus.');

        $this->assertEquals(true, (bool) $ba->fresh()->dihapus);
        $this->assertDatabaseHas('data_terhapus', [
            'jenis' => 'BA Perpindahan',
            'data_id' => $ba->id,
        ]);
    }

    public function test_ba_perpindahan_cannot_be_deleted_once_approved(): void
    {
        $user = User::factory()->create(['role' => 'User']);
        $bmn = Bmn::create([
            'kode_barang' => 'BMN-MUT-DEL-02',
            'nup' => '2',
            'nama_barang' => 'Switch Cisco 24 Port',
            'lokasi' => 'Ruang Server Baru',
            'penanggungjawab' => 'Staff IT Baru',
            'jenis' => 'BMN-TIK',
            'kondisi' => 'Baik',
        ]);

        $ba = BaPerpindahan::create([
            'nomor_ba' => 'BA-MUT/DEL/002',
            'tanggal' => '2026-10-14',
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Switch Cisco 24 Port',
            'dari' => 'Ruang Server Lama',
            'ke' => 'Ruang Server Baru',
            'alasan' => 'Migrasi server room',
            'pengaju_nama' => 'Staff IT Lama',
            'pengaju_email' => $user->email,
            'pengaju_id' => $user->id,
            'pihak_kedua' => 'Staff IT Baru',
            'status' => 'Disetujui',
        ]);

        // Cek halaman index TIDAK menampilkan aksi hapus untuk BA yang sudah Disetujui
        $indexResponse = $this->actingAs($user)->get(route('ba-perpindahan.index'));
        $indexResponse->assertOk();
        $indexResponse->assertDontSee('title="Hapus BA Perpindahan"', false);

        // Cek halaman show TIDAK menampilkan tombol Hapus BA
        $showResponse = $this->actingAs($user)->get(route('ba-perpindahan.show', $ba));
        $showResponse->assertOk();
        $showResponse->assertDontSee('Hapus BA');

        // Mengirimkan DELETE langsung di-abort 403
        $deleteResponse = $this->actingAs($user)->delete(route('ba-perpindahan.destroy', $ba));
        $deleteResponse->assertStatus(403);
        $this->assertEquals(false, (bool) $ba->fresh()->dihapus);
    }

    public function test_cannot_create_bmn_with_duplicate_kode_barang_and_displays_notification(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        Bmn::create([
            'kode_barang' => '3050104031',
            'nup' => '1',
            'nama_barang' => 'Barang Eksis 1',
            'jenis' => 'BMN-NON TIK',
            'lokasi' => 'Gudang Utama',
            'penanggungjawab' => 'Petugas BMN',
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($user)->post(route('bmns.store'), [
            'kode_barang' => '3050104031',
            'nup' => '1',
            'nama_barang' => 'Barang Baru Kode Sama',
            'jenis' => 'BMN-NON TIK',
            'lokasi' => 'Gudang Utama',
            'penanggungjawab' => 'Petugas BMN',
            'kondisi' => 'Baik',
        ]);

        $response->assertSessionHasErrors(['kode_barang']);
        $errors = session('errors')->get('kode_barang');
        $this->assertStringContainsString('sudah terdaftar dalam sistem', $errors[0]);
    }

    public function test_updating_bmn_with_own_kode_barang_succeeds_but_duplicate_code_fails(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $bmn1 = Bmn::create([
            'kode_barang' => '3050104032',
            'nup' => '1',
            'nama_barang' => 'Barang 1',
            'jenis' => 'BMN-NON TIK',
            'lokasi' => 'Gudang Utama',
            'penanggungjawab' => 'Petugas BMN',
            'kondisi' => 'Baik',
        ]);

        $bmn2 = Bmn::create([
            'kode_barang' => '3050104033',
            'nup' => '2',
            'nama_barang' => 'Barang 2',
            'jenis' => 'BMN-NON TIK',
            'lokasi' => 'Gudang Utama',
            'penanggungjawab' => 'Petugas BMN',
            'kondisi' => 'Baik',
        ]);

        // Update bmn1 with same kode_barang passes
        $passResponse = $this->actingAs($user)->put(route('bmns.update', $bmn1), [
            'kode_barang' => '3050104032',
            'nup' => '1',
            'nama_barang' => 'Barang 1 Diperbarui',
            'jenis' => 'BMN-NON TIK',
            'lokasi' => 'Gudang Utama',
            'penanggungjawab' => 'Petugas BMN',
            'kondisi' => 'Baik',
        ]);
        $passResponse->assertSessionHasNoErrors();
        $this->assertEquals('Barang 1 Diperbarui', $bmn1->fresh()->nama_barang);

        // Update bmn1 with bmn2's kode_barang and NUP fails
        $failResponse = $this->actingAs($user)->put(route('bmns.update', $bmn1), [
            'kode_barang' => '3050104033',
            'nup' => '2',
            'nama_barang' => 'Barang 1 Coba Ganti Kode Sama',
            'jenis' => 'BMN-NON TIK',
            'lokasi' => 'Gudang Utama',
            'penanggungjawab' => 'Petugas BMN',
            'kondisi' => 'Baik',
        ]);
        $failResponse->assertSessionHasErrors(['kode_barang']);
    }

    public function test_cannot_create_bmn_tik_with_duplicate_kode_barang_and_displays_notification(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $this->actingAs($user)->post(route('bmns-tik.store'), [
            'kode_barang' => '3090405041',
            'nup' => 1,
            'nama_barang' => 'Laptop Awal',
            'jenis' => 'Laptop',
            'lokasi' => 'Ruang TIK',
            'penanggungjawab' => 'Staf IT',
        ]);

        $failResponse = $this->actingAs($user)->post(route('bmns-tik.store'), [
            'kode_barang' => '3090405041',
            'nup' => 1,
            'nama_barang' => 'Laptop Kedua Kode Sama',
            'jenis' => 'Laptop',
            'lokasi' => 'Ruang TIK',
            'penanggungjawab' => 'Staf IT',
        ]);

        $failResponse->assertSessionHasErrors(['kode_barang']);
        $errors = session('errors')->get('kode_barang');
        $this->assertStringContainsString('sudah terdaftar dalam sistem', $errors[0]);
    }

    public function test_updating_bmn_tik_with_own_kode_succeeds_but_duplicate_fails(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $this->actingAs($user)->post(route('bmns-tik.store'), [
            'kode_barang' => '3090405042',
            'nup' => 1,
            'nama_barang' => 'PC 1',
            'jenis' => 'PC',
            'lokasi' => 'Ruang TIK',
            'penanggungjawab' => 'Staf IT',
        ]);
        $tik1 = BmnTik::where('kode_barang', '3090405042')->firstOrFail();

        $this->actingAs($user)->post(route('bmns-tik.store'), [
            'kode_barang' => '3090405043',
            'nup' => 2,
            'nama_barang' => 'PC 2',
            'jenis' => 'PC',
            'lokasi' => 'Ruang TIK',
            'penanggungjawab' => 'Staf IT',
        ]);

        // Update tik1 keeping its own kode passes
        $passResponse = $this->actingAs($user)->put(route('bmns-tik.update', $tik1), [
            'kode_barang' => '3090405042',
            'nup' => 1,
            'nama_barang' => 'PC 1 Updated',
            'jenis' => 'PC',
            'lokasi' => 'Ruang TIK',
            'penanggungjawab' => 'Staf IT',
        ]);
        $passResponse->assertSessionHasNoErrors();
        $this->assertEquals('PC 1 Updated', $tik1->fresh()->nama_barang);

        // Update tik1 using tik2's kode and NUP fails
        $failResponse = $this->actingAs($user)->put(route('bmns-tik.update', $tik1), [
            'kode_barang' => '3090405043',
            'nup' => 2,
            'nama_barang' => 'PC 1 Duplicate Code',
            'jenis' => 'PC',
            'lokasi' => 'Ruang TIK',
            'penanggungjawab' => 'Staf IT',
        ]);
        $failResponse->assertSessionHasErrors(['kode_barang']);
    }

    public function test_cannot_create_bmn_lab_with_duplicate_kode_barang_and_displays_notification(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $this->actingAs($user)->post(route('bmns-lab.store'), [
            'kode_barang' => '3080101045',
            'nup' => 1,
            'nama_barang' => 'Mikroskop 1',
            'merek' => 'Olympus',
            'lokasi' => 'Lab Mikrobiologi',
            'penanggungjawab' => 'Analis Lab',
        ]);

        $failResponse = $this->actingAs($user)->post(route('bmns-lab.store'), [
            'kode_barang' => '3080101045',
            'nup' => 1,
            'nama_barang' => 'Mikroskop 2 Kode Sama',
            'merek' => 'Olympus',
            'lokasi' => 'Lab Mikrobiologi',
            'penanggungjawab' => 'Analis Lab',
        ]);

        $failResponse->assertSessionHasErrors(['kode_barang']);
        $errors = session('errors')->get('kode_barang');
        $this->assertStringContainsString('sudah terdaftar dalam sistem', $errors[0]);
    }

    public function test_cannot_create_bmn_tik_or_lab_with_code_already_in_master_bmn(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        Bmn::create([
            'kode_barang' => '3050104046',
            'nup' => '1',
            'nama_barang' => 'Barang Umum Master',
            'jenis' => 'BMN-NON TIK',
            'lokasi' => 'Gudang',
            'penanggungjawab' => 'Admin',
            'kondisi' => 'Baik',
        ]);

        // Attempting to create BMN TIK with existing master code fails
        $failTik = $this->actingAs($user)->post(route('bmns-tik.store'), [
            'kode_barang' => '3050104046',
            'nup' => 1,
            'nama_barang' => 'TIK Cross Duplicate',
            'jenis' => 'PC',
            'lokasi' => 'Ruang Server',
            'penanggungjawab' => 'Admin',
        ]);
        $failTik->assertSessionHasErrors(['kode_barang']);

        // Attempting to create BMN Lab with existing master code fails
        $failLab = $this->actingAs($user)->post(route('bmns-lab.store'), [
            'kode_barang' => '3050104046',
            'nup' => 1,
            'nama_barang' => 'Lab Cross Duplicate',
            'merek' => 'Olympus',
            'lokasi' => 'Lab Kimia',
            'penanggungjawab' => 'Admin',
        ]);
        $failLab->assertSessionHasErrors(['kode_barang']);
    }

    public function test_bmn_created_in_gudang_with_pengelola_bmn_automatically_creates_pending_distribusi(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $response = $this->actingAs($user)->post(route('bmns.store'), [
            'kode_barang' => '3080305999',
            'nup' => '1',
            'nama_barang' => 'Laptop ASUS Core i7 Baru',
            'jenis' => 'BMN-TIK',
            'satuan' => 'unit',
            'lokasi' => 'Gudang BMN',
            'penanggungjawab' => 'Pengelola BMN',
            'penanggungjawab_nip' => '-',
            'kondisi' => 'Baik',
        ]);

        $response->assertRedirect(route('bmns.index'));

        $bmn = Bmn::where('kode_barang', '3080305999')->firstOrFail();
        $this->assertEquals('Gudang BMN', $bmn->lokasi);
        $this->assertEquals('Pengelola BMN', $bmn->penanggungjawab);

        $distribusi = Distribusi::where('bmn_id', $bmn->id)->first();
        $this->assertNotNull($distribusi, 'Berita Acara Distribusi harus otomatis terbuat untuk barang baru di gudang');
        $this->assertTrue($distribusi->is_pending, 'Status Berita Acara Distribusi harus pending (menunggu penempatan)');
        $this->assertNull($distribusi->lokasi_tujuan);
        $this->assertNull($distribusi->penerima);
        $this->assertEquals('Gudang BMN', $distribusi->lokasi_asal);

        $this->assertDatabaseHas('distribusi_items', [
            'distribusi_id' => $distribusi->id,
            'bmn_id' => $bmn->id,
            'nama_barang_snapshot' => 'Laptop ASUS Core i7 Baru',
        ]);

        // Verifikasi di tampilan index distribusi
        $indexResponse = $this->actingAs($user)->get(route('distribusi.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Menunggu Penempatan');
        $indexResponse->assertSee('Distribusikan');
        $indexResponse->assertDontSee('Tambah Distribusi');
        $indexResponse->assertDontSee('+ Buat Distribusi Baru');
        $indexResponse->assertDontSee('title="Hapus Data"', false);
    }

    public function test_bmn_created_in_specific_room_does_not_create_distribusi(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $response = $this->actingAs($user)->post(route('bmns.store'), [
            'kode_barang' => '3080305888',
            'nup' => '1',
            'nama_barang' => 'Mikroskop Binokuler Lab',
            'jenis' => 'BMN-LAB',
            'satuan' => 'unit',
            'lokasi' => 'Laboratorium Pengujian Kimia',
            'penanggungjawab' => 'Siti Rahmah, S.Si (198505052010122002)',
            'penanggungjawab_nip' => '198505052010122002',
            'kondisi' => 'Baik',
        ]);

        $response->assertRedirect(route('bmns.index'));

        $bmn = Bmn::where('kode_barang', '3080305888')->firstOrFail();
        $distribusi = Distribusi::where('bmn_id', $bmn->id)->first();
        $this->assertNull($distribusi, 'BMN yang langsung ditempatkan di ruangan tidak boleh otomatis membuat BA Distribusi');
    }

    public function test_distribusi_update_synchronizes_destination_and_recipient_to_linked_bmn(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $lokasiTujuan = Lokasi::create(['kode' => 'LOK-TU-01', 'nama' => 'Ruang Tata Usaha']);
        $pegawaiPenerima = Pegawai::create(['nip' => '199102022019011003', 'nama' => 'Ahmad Fadillah', 'jabatan' => 'Staff']);

        $this->actingAs($user)->post(route('bmns.store'), [
            'kode_barang' => '3080305777',
            'nup' => '1',
            'nama_barang' => 'Scanner Canon Lide 300',
            'jenis' => 'BMN-TIK',
            'satuan' => 'unit',
            'lokasi' => 'Gudang BMN',
            'penanggungjawab' => 'Pengelola BMN',
            'penanggungjawab_nip' => '-',
            'kondisi' => 'Baik',
        ]);

        $bmn = Bmn::where('kode_barang', '3080305777')->firstOrFail();
        $distribusi = Distribusi::where('bmn_id', $bmn->id)->firstOrFail();

        // Update distribusi untuk mendistribusikan barang ke Ruang Tata Usaha & Ahmad Fadillah
        $penerimaString = 'Ahmad Fadillah (199102022019011003)';
        $updateResponse = $this->actingAs($user)->put(route('distribusi.update', $distribusi), [
            'nomor' => $distribusi->nomor,
            'tanggal' => now()->toDateString(),
            'nama_barang' => $distribusi->nama_barang,
            'jumlah' => 1,
            'satuan' => 'unit',
            'lokasi_asal' => 'Gudang BMN',
            'lokasi_tujuan' => 'Ruang Tata Usaha',
            'penerima' => $penerimaString,
            'keterangan' => 'Didistribusikan untuk operasional TU',
        ]);

        $updateResponse->assertRedirect(route('distribusi.index'));

        // Cek data Distribusi terupdate
        $distribusi->refresh();
        $this->assertFalse($distribusi->is_pending);
        $this->assertEquals('Ruang Tata Usaha', $distribusi->lokasi_tujuan);
        $this->assertEquals($penerimaString, $distribusi->penerima);
        $this->assertEquals($lokasiTujuan->id, $distribusi->lokasi_tujuan_id);
        $this->assertEquals($pegawaiPenerima->id, $distribusi->penerima_id);

        // Verifikasi lokasi dan penanggungjawab di master BMN tersinkronisasi otomatis
        $bmn->refresh();
        $this->assertEquals('Ruang Tata Usaha', $bmn->lokasi);
        $this->assertEquals($lokasiTujuan->id, $bmn->lokasi_id);
        $this->assertEquals($penerimaString, $bmn->penanggungjawab);
        $this->assertEquals($pegawaiPenerima->id, $bmn->penanggung_jawab_id);
        $this->assertEquals('199102022019011003', $bmn->penanggungjawab_nip);

        // Verifikasi BMN TIK terkait juga tersinkronisasi
        $tik = BmnTik::where('bmn_id', $bmn->id)->first();
        $this->assertNotNull($tik);
        $this->assertEquals('Ruang Tata Usaha', $tik->lokasi);
        $this->assertEquals($penerimaString, $tik->penanggungjawab);
    }

    public function test_distribusi_can_be_deleted_and_moved_to_data_terhapus(): void
    {
        $user = User::factory()->create(['role' => 'Operator', 'email' => 'operator@bbpom.go.id']);

        $distribusi = Distribusi::create([
            'nomor' => 'BA-DIST/TEST/DELETE',
            'tanggal' => now()->toDateString(),
            'nama_barang' => 'Printer HP LaserJet',
            'jumlah' => 1,
            'satuan' => 'unit',
            'lokasi_asal' => 'Gudang BMN',
            'lokasi_tujuan' => 'Ruang Keuangan',
            'penerima' => 'Staff Keuangan',
            'dihapus' => false,
        ]);

        $response = $this->actingAs($user)->delete(route('distribusi.destroy', $distribusi));
        $response->assertRedirect();

        $this->assertSoftDeleted('distribusis', ['id' => $distribusi->id]);
        $this->assertDatabaseHas('data_terhapus', [
            'jenis' => 'Distribusi',
            'data_id' => $distribusi->id,
            'nama' => 'BA-DIST/TEST/DELETE',
            'dihapus_oleh' => 'operator@bbpom.go.id',
        ]);
    }

    public function test_distribusi_print_page_renders_properly_and_sidebar_shows_ba_distribusi(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        // 1. Distribusi yang BELUM didistribusikan (pending)
        $pendingDistribusi = Distribusi::create([
            'nomor' => 'BA-DIST/TEST/PENDING01',
            'tanggal' => now()->toDateString(),
            'nama_barang' => 'Barang Gudang Belum Ditentukan',
            'jumlah' => 1,
            'satuan' => 'unit',
            'lokasi_asal' => 'Gudang BMN',
            'lokasi_tujuan' => null,
            'penerima' => null,
            'dihapus' => false,
        ]);

        // Cek halaman cetak pending ditolak & redirect dengan error
        $pendingPrintResponse = $this->actingAs($user)->get(route('distribusi.print', $pendingDistribusi));
        $pendingPrintResponse->assertRedirect(route('distribusi.show', $pendingDistribusi));
        $pendingPrintResponse->assertSessionHas('error');

        // Cek index & show TIDAK menampilkan tombol cetak untuk item pending
        $indexResponse1 = $this->actingAs($user)->get(route('distribusi.index'));
        $indexResponse1->assertOk();
        $indexResponse1->assertDontSee(route('distribusi.print', $pendingDistribusi));

        $showResponse1 = $this->actingAs($user)->get(route('distribusi.show', $pendingDistribusi));
        $showResponse1->assertOk();
        $showResponse1->assertDontSee(route('distribusi.print', $pendingDistribusi));

        // 2. Distribusi yang SUDAH didistribusikan (selesai)
        $distribusi = Distribusi::create([
            'nomor' => 'BA-DIST/TEST/PRINT01',
            'tanggal' => now()->toDateString(),
            'nama_barang' => 'Kursi Kerja Ergonomis',
            'jumlah' => 2,
            'satuan' => 'unit',
            'lokasi_asal' => 'Gudang BMN',
            'lokasi_tujuan' => 'Ruang Administrasi',
            'penerima' => 'Nurul Hidayah',
            'dihapus' => false,
        ]);

        // Cek halaman cetak render sukses
        $printResponse = $this->actingAs($user)->get(route('distribusi.print', $distribusi));
        $printResponse->assertOk();
        $printResponse->assertSee('BERITA ACARA DISTRIBUSI BARANG MILIK NEGARA (BMN)');
        $printResponse->assertSee('BA-DIST/TEST/PRINT01');
        $printResponse->assertSee('Kursi Kerja Ergonomis');
        $printResponse->assertSee('Ruang Administrasi');
        $printResponse->assertSee('Nurul Hidayah');
        $printResponse->assertSee('${ttd_pengirim1}', false);
        $printResponse->assertSee('${ttd_pengirim2}', false);
        $printResponse->assertSee('${ttd_pengirim3}', false);
        $printResponse->assertSee('Kepala Balai Besar POM di Banjarbaru');
        $printResponse->assertSee('Selaku Kuasa Pengguna Barang');
        $printResponse->assertSee('Ary Yustantingsih,S.Si., Apt.');
        $printResponse->assertSee('Format TTE:');
        $printResponse->assertSee('Srikandi TTE');
        $printResponse->assertSee('Tanda Tangan Basah');

        // Cek index menampilkan tombol cetak dan sidebar berlabel BA Distribusi
        $indexResponse2 = $this->actingAs($user)->get(route('distribusi.index'));
        $indexResponse2->assertOk();
        $indexResponse2->assertSee('BA Distribusi');
        $indexResponse2->assertSee(route('distribusi.print', $distribusi));

        // Cek show menampilkan tombol cetak
        $showResponse2 = $this->actingAs($user)->get(route('distribusi.show', $distribusi));
        $showResponse2->assertOk();
        $showResponse2->assertSee('Cetak BA');
        $showResponse2->assertSee(route('distribusi.print', $distribusi));
    }

    public function test_ba_perpindahan_index_can_search_by_nomor_ba(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $ba1 = BaPerpindahan::create([
            'nomor_ba' => 'BA-MUT/2026/SEARCH-AAA',
            'tanggal' => now(),
            'nama_barang' => 'Laptop Dell Latitude',
            'dari' => 'Ruang TU',
            'ke' => 'Ruang Lab',
            'alasan' => 'Kebutuhan uji coba',
            'status' => 'Diajukan',
            'pengaju_nama' => 'User AAA',
            'pengaju_email' => 'user-aaa@example.com',
        ]);

        $ba2 = BaPerpindahan::create([
            'nomor_ba' => 'BA-MUT/2026/SEARCH-BBB',
            'tanggal' => now(),
            'nama_barang' => 'PC All-in-One HP',
            'dari' => 'Ruang Server',
            'ke' => 'Ruang Admin',
            'alasan' => 'Penggantian unit',
            'status' => 'Diajukan',
            'pengaju_nama' => 'User BBB',
            'pengaju_email' => 'user-bbb@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('ba-perpindahan.index', ['search' => 'SEARCH-AAA']));
        $response->assertOk();
        $response->assertSee('BA-MUT/2026/SEARCH-AAA');
        $response->assertDontSee('BA-MUT/2026/SEARCH-BBB');
        $response->assertSee('Cari No. Dokumen BA, Kode BMN, NUP, Nama Barang...');
    }

    public function test_distribusi_index_can_search_by_nomor_ba(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $dist1 = Distribusi::create([
            'nomor' => 'BA-DIST/2026/SEARCH-111',
            'tanggal' => now(),
            'nama_barang' => 'Printer Epson L3210',
            'jumlah' => 1,
            'lokasi_asal' => 'Gudang BMN',
            'lokasi_tujuan' => 'Ruang TU',
            'penerima' => 'Ahmad',
        ]);

        $dist2 = Distribusi::create([
            'nomor' => 'BA-DIST/2026/SEARCH-222',
            'tanggal' => now(),
            'nama_barang' => 'Scanner Canon',
            'jumlah' => 1,
            'lokasi_asal' => 'Gudang BMN',
            'lokasi_tujuan' => 'Ruang Lab',
            'penerima' => 'Budi',
        ]);

        $response = $this->actingAs($user)->get(route('distribusi.index', ['search' => 'SEARCH-111']));
        $response->assertOk();
        $response->assertSee('BA-DIST/2026/SEARCH-111');
        $response->assertDontSee('BA-DIST/2026/SEARCH-222');
        $response->assertSee('Cari No. Dokumen BA, Kode BMN, NUP, Nama Barang...');
    }

    public function test_laporan_kerusakan_index_can_search_by_nomor_laporan(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $lap1 = LaporanKerusakan::create([
            'nomor' => 'LK/2026/SEARCH-XXX',
            'tanggal' => now(),
            'nama_barang' => 'AC Split Panasonic',
            'lokasi' => 'Ruang Rapat',
            'tingkat' => 'Sedang',
            'deskripsi' => 'Pendingin tidak dingin dan berbunyi bising',
            'status' => 'Dilaporkan',
            'pelapor_nama' => 'Pegawai X',
            'pelapor_email' => 'pegawai-x@example.com',
        ]);

        $lap2 = LaporanKerusakan::create([
            'nomor' => 'LK/2026/SEARCH-YYY',
            'tanggal' => now(),
            'nama_barang' => 'Proyektor BenQ',
            'lokasi' => 'Aula',
            'tingkat' => 'Berat',
            'deskripsi' => 'Lampu mati total',
            'status' => 'Dilaporkan',
            'pelapor_nama' => 'Pegawai Y',
            'pelapor_email' => 'pegawai-y@example.com',
        ]);

        $response = $this->actingAs($user)->get(route('laporan-kerusakan.index', ['search' => 'SEARCH-XXX']));
        $response->assertOk();
        $response->assertSee('LK/2026/SEARCH-XXX');
        $response->assertDontSee('LK/2026/SEARCH-YYY');
        $response->assertSee('Cari berdasarkan No. Laporan...');
    }

    public function test_sidebar_preserves_navigation_state_across_modules(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        // 1. Kunjungi Daftar BMN dengan filter pencarian dan kondisi
        $bmnUrl = route('bmns.index', ['search' => 'LaptopThinkpad', 'kondisi' => 'Baik']);
        $response1 = $this->actingAs($user)->get($bmnUrl);
        $response1->assertOk();
        $this->assertStringContainsString('search=LaptopThinkpad', session('nav_last_url.bmns'));
        $this->assertStringContainsString('kondisi=Baik', session('nav_last_url.bmns'));

        // 2. Buka menu lain di sidebar (misal: BA Perpindahan)
        $response2 = $this->actingAs($user)->get(route('ba-perpindahan.index'));
        $response2->assertOk();
        // Cek link Daftar BMN di sidebar pada halaman BA Perpindahan tetap memuat filter sebelumnya
        $response2->assertSee('search=LaptopThinkpad');
        $response2->assertSee('kondisi=Baik');

        // 3. Kunjungi BA Distribusi dengan filter nomor dokumen
        $distribusiUrl = route('distribusi.index', ['search' => 'BA-DIST/TEST/999']);
        $response3 = $this->actingAs($user)->get($distribusiUrl);
        $response3->assertOk();
        $this->assertEquals($distribusiUrl, session('nav_last_url.distribusi'));

        // 4. Buka menu Laporan Kerusakan
        $response4 = $this->actingAs($user)->get(route('laporan-kerusakan.index'));
        $response4->assertOk();
        // Cek link BA Distribusi dan Daftar BMN di sidebar tetap memuat state sebelumnya
        $response4->assertSee('BA-DIST%2FTEST%2F999');
        $response4->assertSee('search=LaptopThinkpad');
    }

    public function test_bmn_lokasi_is_linked_to_master_lokasi_and_creates_if_not_exists(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);
        $pegawai = Pegawai::create([
            'nama' => 'Staff Test',
            'nip' => '199001012020011001',
            'jabatan' => 'Staff',
        ]);

        // 1. Simpan BMN dengan lokasi baru
        $response = $this->actingAs($user)->post(route('bmns.store'), [
            'kode_barang' => '3050104091',
            'nup' => '1',
            'nama_barang' => 'Kursi Kerja Baru',
            'jenis' => 'BMN-NON TIK',
            'kondisi' => 'Baik',
            'lokasi' => 'Ruang Rapat Utama',
            'penanggungjawab' => $pegawai->nama,
            'penanggungjawab_nip' => $pegawai->nip,
        ]);

        $response->assertRedirect(route('bmns.index'));
        $this->assertDatabaseHas('lokasis', ['nama' => 'Ruang Rapat Utama']);

        $bmn = Bmn::where('kode_barang', '3050104091')->firstOrFail();
        $lokasi = Lokasi::where('nama', 'Ruang Rapat Utama')->firstOrFail();
        $this->assertEquals($lokasi->id, $bmn->lokasi_id);
        $this->assertEquals('Ruang Rapat Utama', $bmn->lokasi);

        // 2. Simpan BMN kedua dengan lokasi yang sudah ada (tidak boleh duplikat di lokasis)
        $response2 = $this->actingAs($user)->post(route('bmns.store'), [
            'kode_barang' => '3050104092',
            'nup' => '2',
            'nama_barang' => 'Meja Rapat',
            'jenis' => 'BMN-NON TIK',
            'kondisi' => 'Baik',
            'lokasi' => 'Ruang Rapat Utama',
            'penanggungjawab' => $pegawai->nama,
            'penanggungjawab_nip' => $pegawai->nip,
        ]);

        $response2->assertRedirect(route('bmns.index'));
        $this->assertEquals(1, Lokasi::where('nama', 'Ruang Rapat Utama')->count());
        $bmn2 = Bmn::where('kode_barang', '3050104092')->firstOrFail();
        $this->assertEquals($lokasi->id, $bmn2->lokasi_id);

        // 3. Update BMN dengan lokasi baru lainnya
        $response3 = $this->actingAs($user)->put(route('bmns.update', $bmn), [
            'kode_barang' => '3050104091',
            'nup' => '1',
            'nama_barang' => 'Kursi Kerja Baru Edit',
            'jenis' => 'BMN-NON TIK',
            'kondisi' => 'Baik',
            'lokasi' => 'Ruang Arsip Lt 2',
            'penanggungjawab' => $pegawai->nama,
            'penanggungjawab_nip' => $pegawai->nip,
        ]);

        $response3->assertRedirect(route('bmns.index'));
        $this->assertDatabaseHas('lokasis', ['nama' => 'Ruang Arsip Lt 2']);
        $bmn->refresh();
        $lokasiArsip = Lokasi::where('nama', 'Ruang Arsip Lt 2')->firstOrFail();
        $this->assertEquals($lokasiArsip->id, $bmn->lokasi_id);
        $this->assertEquals('Ruang Arsip Lt 2', $bmn->lokasi);
    }

    public function test_ba_perpindahan_relates_pengaju_to_pegawai_and_separates_pihak_pertama(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        // Pegawai Pemegang BMN saat ini (Pihak Pertama)
        $pemegangAwal = Pegawai::create([
            'nama' => 'Sri Wahyuni, S.Farm',
            'nip' => '199002022015022001',
            'jabatan' => 'Pengelola BMN',
            'unit_kerja' => 'Subbag Tata Usaha',
        ]);

        // Pegawai yang mengajukan perpindahan (Pengaju)
        $pengaju = Pegawai::create([
            'nama' => 'Ahmad Fauzi, S.Si',
            'nip' => '199205052018011002',
            'jabatan' => 'Analis Laboratorium',
            'unit_kerja' => 'Laboratorium Mikrobiologi',
        ]);

        $bmn = Bmn::create([
            'kode_barang' => 'BMN-MUT-99',
            'nup' => '10',
            'nama_barang' => 'Incubator Shaker',
            'lokasi' => 'Gudang BMN',
            'penanggungjawab' => $pemegangAwal->nama,
            'penanggung_jawab_id' => $pemegangAwal->id,
            'jenis' => 'BMN-LAB',
            'kondisi' => 'Baik',
        ]);

        // 1. Simpan BA Perpindahan dengan pengaju_pegawai_id
        $response = $this->actingAs($user)->post(route('ba-perpindahan.store'), [
            'nomor_ba' => 'BA-MUT/TEST/PENGAJU-01',
            'tanggal' => '2026-10-15',
            'bmn_id' => $bmn->id,
            'pengaju_pegawai_id' => $pengaju->id,
            'nama_barang' => 'Incubator Shaker',
            'dari' => 'Gudang BMN',
            'ke' => 'Lab Mikrobiologi Lt 2',
            'alasan' => 'Dibutuhkan untuk inkubasi biakan',
        ]);

        $response->assertRedirect(route('ba-perpindahan.index'));

        $this->assertDatabaseHas('ba_perpindahans', [
            'nomor_ba' => 'BA-MUT/TEST/PENGAJU-01',
            'bmn_id' => $bmn->id,
            'pengaju_pegawai_id' => $pengaju->id,
            'pengaju_nama' => 'Ahmad Fauzi, S.Si',
            'pihak_pertama' => 'Sri Wahyuni, S.Farm',
            'pihak_pertama_id' => $pemegangAwal->id,
        ]);

        $ba = BaPerpindahan::where('nomor_ba', 'BA-MUT/TEST/PENGAJU-01')->firstOrFail();
        $this->assertEquals($pengaju->id, $ba->pengajuPegawai->id);
        $this->assertEquals($pemegangAwal->id, $ba->pihakPertamaPegawai->id);

        // 2. Cek index menampilkan Pengaju terpisah dari Pihak I
        $indexResponse = $this->actingAs($user)->get(route('ba-perpindahan.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Ahmad Fauzi, S.Si');
        $indexResponse->assertSee('Sri Wahyuni, S.Farm');

        // 3. Cek show menampilkan Pegawai Pengaju dan Pihak Pertama
        $showResponse = $this->actingAs($user)->get(route('ba-perpindahan.show', $ba));
        $showResponse->assertOk();
        $showResponse->assertSee('Pegawai Pengaju');
        $showResponse->assertSee('Ahmad Fauzi, S.Si');
        $showResponse->assertSee('Sri Wahyuni, S.Farm');
        $showResponse->assertSee('Pihak Pertama (Penyerah / Pemegang Awal)');

        // 4. BA belum dapat dicetak sebelum pihak_kedua (penerima) ditentukan
        $failPrint = $this->actingAs($user)->get(route('ba-perpindahan.print', $ba));
        $failPrint->assertRedirect(route('ba-perpindahan.show', $ba));
        $failPrint->assertSessionHas('error');

        // Setelah penerima ditentukan, baru dapat dicetak
        $ba->update(['pihak_kedua' => 'Yuli Supriyatno, A.Md.']);

        // Cek print view memuat Diajukan Oleh dan Pihak I Pemegang Awal
        $printResponse = $this->actingAs($user)->get(route('ba-perpindahan.print', $ba));
        $printResponse->assertOk();
        $printResponse->assertSee('Diajukan Oleh');
        $printResponse->assertSee('Ahmad Fauzi, S.Si');
        $printResponse->assertSee('Sri Wahyuni, S.Farm');
    }

    public function test_laporan_kerusakan_relates_pelapor_to_pegawai_and_displays_applicant_details(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $pegawai = Pegawai::create([
            'nama' => 'Budi Santoso, S.Kom',
            'nip' => '198801012010121001',
            'jabatan' => 'Pranata Komputer Ahli Muda',
            'unit_kerja' => 'Subbag Tata Usaha',
        ]);

        $bmn = Bmn::create([
            'kode_barang' => 'BMN-LK-01',
            'nup' => '5',
            'nama_barang' => 'Laptop Asus ExpertBook',
            'lokasi' => 'Ruang TIK Lt 1',
            'penanggungjawab' => $pegawai->nama,
            'jenis' => 'BMN-TIK',
            'kondisi' => 'Baik',
        ]);

        // 1. Simpan Laporan Kerusakan dengan pelapor_pegawai_id
        $response = $this->actingAs($operator)->post(route('laporan-kerusakan.store'), [
            'nomor' => 'LK/20261015/001',
            'tanggal' => '2026-10-15',
            'bmn_id' => $bmn->id,
            'pelapor_pegawai_id' => $pegawai->id,
            'nama_barang' => 'Laptop Asus ExpertBook',
            'lokasi' => 'Ruang TIK Lt 1',
            'tingkat' => 'Sedang',
            'deskripsi' => 'Layar berkedip dan adaptor sering panas',
        ]);

        $response->assertRedirect(route('laporan-kerusakan.index'));

        $this->assertDatabaseHas('laporan_kerusakans', [
            'nomor' => 'LK/20261015/001',
            'bmn_id' => $bmn->id,
            'pelapor_pegawai_id' => $pegawai->id,
            'pelapor_nama' => 'Budi Santoso, S.Kom',
            'pelapor_email' => $operator->email,
            'tingkat' => 'Sedang',
        ]);

        $laporan = LaporanKerusakan::where('nomor', 'LK/20261015/001')->firstOrFail();
        $this->assertEquals($pegawai->id, $laporan->pelaporPegawai->id);
        $this->assertEquals('198801012010121001', $laporan->pelaporPegawai->nip);

        // 2. Cek index menampilkan Pelapor beserta NIP / unit kerja
        $indexResponse = $this->actingAs($operator)->get(route('laporan-kerusakan.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Budi Santoso, S.Kom');
        $indexResponse->assertSee('198801012010121001');

        // 3. Cek show menampilkan detail lengkap Pengaju / Pelapor
        $showResponse = $this->actingAs($operator)->get(route('laporan-kerusakan.show', $laporan));
        $showResponse->assertOk();
        $showResponse->assertSee('Budi Santoso, S.Kom');
        $showResponse->assertSee('198801012010121001');
        $showResponse->assertSee('Subbag Tata Usaha');

        // 4. Cek edit view menampilkan ringkasan pengaju untuk Operator
        $editResponse = $this->actingAs($operator)->get(route('laporan-kerusakan.edit', $laporan));
        $editResponse->assertOk();
        $editResponse->assertSee('Pegawai Pengaju / Pelapor');
        $editResponse->assertSee('Budi Santoso, S.Kom');
        $editResponse->assertSee('198801012010121001');
    }

    public function test_bmn_umum_stores_and_displays_tahun_and_nilai_perolehan(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $response = $this->actingAs($operator)->post(route('bmns.store'), [
            'kode_barang' => '3050104081',
            'nup' => '10',
            'no_seri' => 'SN-THN-01',
            'nama_barang' => 'Kursi Kerja Ergonomis',
            'merek' => 'Informa',
            'tahun_perolehan' => 2024,
            'nilai_perolehan' => '3500000',
            'jenis' => 'BMN-NON TIK',
            'satuan' => 'unit',
            'lokasi' => 'Ruang TU',
            'penanggungjawab' => 'Staf TU',
            'kondisi' => 'Baik',
        ]);

        $response->assertRedirect(route('bmns.index'));

        $bmn = Bmn::where('kode_barang', '3050104081')->firstOrFail();
        $this->assertEquals(2024, $bmn->tahun_perolehan);
        $this->assertEquals(3500000.0, (float) $bmn->nilai_perolehan);

        // Check index view displays them
        $indexResponse = $this->actingAs($operator)->get(route('bmns.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('2024');
        $indexResponse->assertSee('Rp 3.500.000');

        // Check show view displays them
        $showResponse = $this->actingAs($operator)->get(route('bmns.show', $bmn));
        $showResponse->assertOk();
        $showResponse->assertSee('Tahun Perolehan');
        $showResponse->assertSee('2024');
        $showResponse->assertSee('Nilai Perolehan');
        $showResponse->assertSee('Rp 3.500.000');
    }

    public function test_bmn_tik_stores_and_syncs_tahun_and_nilai_perolehan(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $response = $this->actingAs($operator)->post(route('bmns-tik.store'), [
            'kode_barang' => '3090405082',
            'nup' => 12,
            'no_seri' => 'SN-TIK-THN-01',
            'nama_barang' => 'PC All-in-One Dell',
            'jenis' => 'Komputer',
            'tahun_perolehan' => 2023,
            'nilai_perolehan' => '17500000',
            'lokasi' => 'Ruang Server',
            'penanggungjawab' => 'Staf IT',
            'kondisi' => 'Baik',
        ]);

        $response->assertRedirect(route('bmns-tik.index'));

        $tik = BmnTik::where('kode_barang', '3090405082')->firstOrFail();
        $this->assertEquals(2023, $tik->tahun_perolehan);
        $this->assertEquals(17500000.0, (float) $tik->nilai_perolehan);

        $bmn = $tik->bmn;
        $this->assertNotNull($bmn);
        $this->assertEquals(2023, $bmn->tahun_perolehan);
        $this->assertEquals(17500000.0, (float) $bmn->nilai_perolehan);

        // Check index view displays them
        $indexResponse = $this->actingAs($operator)->get(route('bmns-tik.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('2023');
        $indexResponse->assertSee('Rp 17.500.000');
    }

    public function test_bmn_lab_stores_and_syncs_tahun_and_nilai_perolehan(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $response = $this->actingAs($operator)->post(route('bmns-lab.store'), [
            'kode_barang' => '3080101083',
            'nup' => 15,
            'no_seri' => 'SN-LAB-THN-01',
            'nama_barang' => 'Mikroskop Olympus CX23',
            'merek' => 'Olympus',
            'tahun_perolehan' => 2022,
            'nilai_perolehan' => '25000000',
            'lokasi' => 'Lab Biologi',
            'penanggungjawab' => 'Analis Lab',
            'kondisi' => 'Baik',
        ]);

        $response->assertRedirect(route('bmns-lab.index'));

        $lab = BmnLab::where('kode_barang', '3080101083')->firstOrFail();
        $this->assertEquals(2022, $lab->tahun_perolehan);
        $this->assertEquals(25000000.0, (float) $lab->nilai_perolehan);

        $bmn = $lab->bmn;
        $this->assertNotNull($bmn);
        $this->assertEquals(2022, $bmn->tahun_perolehan);
        $this->assertEquals(25000000.0, (float) $bmn->nilai_perolehan);

        // Check index view displays them
        $indexResponse = $this->actingAs($operator)->get(route('bmns-lab.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('2022');
        $indexResponse->assertSee('Rp 25.000.000');
    }

    public function test_pengembalian_stores_kondisi_awal_and_kondisi_per_item(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $bmn = Bmn::create([
            'kode_barang' => 'RET-ITEM-01',
            'nup' => '77',
            'nama_barang' => 'Laptop Asus ROG',
            'lokasi' => 'Ruang Multimedia',
            'penanggungjawab' => 'Staf Multimedia',
            'jenis' => 'BMN-TIK',
            'kondisi' => 'Baik',
        ]);

        // 1. Check create view contains headers
        $createResponse = $this->actingAs($operator)->get(route('pengembalians.create'));
        $createResponse->assertOk();
        $createResponse->assertSee('Kondisi Awal');
        $createResponse->assertSee('Kondisi Pengembalian');
        $createResponse->assertSee('Ket');

        // 2. Submit store without top card kondisi_pengembalian
        $storeResponse = $this->actingAs($operator)->post(route('pengembalians.store'), [
            'nomor_pengajuan' => 'RET/2026/ITEM/77',
            'tanggal' => '2026-10-19',
            'nama_pengaju' => 'Staf Multimedia',
            'bidang' => 'Tata Usaha',
            'items' => [
                [
                    'bmn_id' => $bmn->id,
                    'kondisi_awal' => 'Baik',
                    'kondisi' => 'Rusak Berat',
                    'keterangan' => 'Motherboard terbakar',
                ],
            ],
        ]);

        $storeResponse->assertRedirect(route('pengembalians.index'));

        // 3. Assert database stores kondisi_awal, kondisi, and keterangan per item
        $this->assertDatabaseHas('pengembalian_items', [
            'bmn_id' => $bmn->id,
            'kondisi_awal' => 'Baik',
            'kondisi' => 'Rusak Berat',
            'keterangan' => 'Motherboard terbakar',
        ]);

        // 4. Assert BMN master condition synchronized to return condition
        $this->assertEquals('Rusak Berat', $bmn->fresh()->kondisi);

        // 5. Assert index view displays Awal, Kembali, and Ket
        $indexResponse = $this->actingAs($operator)->get(route('pengembalians.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Awal: Baik');
        $indexResponse->assertSee('Kembali: Rusak Berat');
        $indexResponse->assertSee('(Motherboard terbakar)');
    }

    public function test_lokasi_kode_ruangan_and_format_kombinasi_search(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        // 1. Create lokasi with kode_ruangan
        $lokasi = Lokasi::create([
            'nama' => 'Ruang Tata Usaha',
            'kode_ruangan' => 'R.TU',
        ]);

        $this->assertEquals('R.TU - Ruang Tata Usaha', $lokasi->format_kombinasi);

        // 2. Test search by ID
        $byId = Lokasi::filter(['search' => (string) $lokasi->id])->get();
        $this->assertTrue($byId->contains('id', $lokasi->id));

        // 3. Test search by kode_ruangan
        $byKode = Lokasi::filter(['search' => 'R.TU'])->get();
        $this->assertTrue($byKode->contains('id', $lokasi->id));

        // 4. Test search by format kombinasi
        $byFormat = Lokasi::filter(['search' => "{$lokasi->id} - R.TU"])->get();
        $this->assertTrue($byFormat->contains('id', $lokasi->id));

        // 5. Test index page displays format kombinasi
        $response = $this->actingAs($operator)->get(route('lokasis.index', ['search' => 'R.TU']));
        $response->assertOk();
        $response->assertSee($lokasi->format_kombinasi);

        // 6. Test update kode_ruangan
        $updateResponse = $this->actingAs($operator)->put(route('lokasis.update', $lokasi), [
            'nama' => 'Ruang Tata Usaha Terpadu',
            'kode_ruangan' => 'R.TU.01',
        ]);
        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('lokasis', [
            'id' => $lokasi->id,
            'nama' => 'Ruang Tata Usaha Terpadu',
            'kode_ruangan' => 'R.TU.01',
        ]);
    }

    public function test_lokasi_urutan_gedung_a_b_c(): void
    {
        $ruangA = Lokasi::create(['kode_ruangan' => 'A1.99', 'nama' => 'Ruang Baru Gedung A']);
        $ruangB = Lokasi::create(['kode_ruangan' => 'B1.99', 'nama' => 'Ruang Baru Gedung B']);
        $ruangC = Lokasi::create(['kode_ruangan' => 'C1.99', 'nama' => 'Ruang Baru Gedung C']);

        $ordered = Lokasi::orderedByGedung()->get();

        $indexA = $ordered->search(fn ($l) => $l->id === $ruangA->id);
        $indexB = $ordered->search(fn ($l) => $l->id === $ruangB->id);
        $indexC = $ordered->search(fn ($l) => $l->id === $ruangC->id);

        $this->assertNotFalse($indexA);
        $this->assertNotFalse($indexB);
        $this->assertNotFalse($indexC);
        $this->assertLessThan($indexB, $indexA);
        $this->assertLessThan($indexC, $indexB);
    }

    public function test_lokasi_urutan_lantai_a1_a2_b1_b2_c1_c2(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        // Test insertion through controller / service to verify normalization and ordering
        $this->actingAs($operator)->post(route('lokasis.store'), [
            'kode_ruangan' => 'A1.36',
            'nama' => 'Ruang Tambahan A1',
        ])->assertRedirect();

        $this->actingAs($operator)->post(route('lokasis.store'), [
            'kode_ruangan' => 'A2.26',
            'nama' => 'Ruang Tambahan A2',
        ])->assertRedirect();

        $this->actingAs($operator)->post(route('lokasis.store'), [
            'kode_ruangan' => 'B1.14',
            'nama' => 'Ruang Tambahan B1',
        ])->assertRedirect();

        $this->actingAs($operator)->post(route('lokasis.store'), [
            'kode_ruangan' => 'B2.11',
            'nama' => 'Ruang Tambahan B2',
        ])->assertRedirect();

        $this->actingAs($operator)->post(route('lokasis.store'), [
            'kode_ruangan' => 'C1.18',
            'nama' => 'Ruang Tambahan C1',
        ])->assertRedirect();

        $this->actingAs($operator)->post(route('lokasis.store'), [
            'kode_ruangan' => 'C2.10',
            'nama' => 'Ruang Tambahan C2',
        ])->assertRedirect();

        $all = Lokasi::orderedByGedung()->get();

        $posA1 = $all->search(fn ($l) => $l->kode_ruangan === 'A1.36');
        $posA2 = $all->search(fn ($l) => $l->kode_ruangan === 'A2.26');
        $posB1 = $all->search(fn ($l) => $l->kode_ruangan === 'B1.14');
        $posB2 = $all->search(fn ($l) => $l->kode_ruangan === 'B2.11');
        $posC1 = $all->search(fn ($l) => $l->kode_ruangan === 'C1.18');
        $posC2 = $all->search(fn ($l) => $l->kode_ruangan === 'C2.10');

        $this->assertLessThan($posA2, $posA1, 'A1 must be ordered before A2');
        $this->assertLessThan($posB1, $posA2, 'A2 must be ordered before B1');
        $this->assertLessThan($posB2, $posB1, 'B1 must be ordered before B2');
        $this->assertLessThan($posC1, $posB2, 'B2 must be ordered before C1');
        $this->assertLessThan($posC2, $posC1, 'C1 must be ordered before C2');
    }

    public function test_ba_perpindahan_print_template_srikandi_a4_and_f4(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $bmn = Bmn::create([
            'nama_barang' => 'Lap Top Sony Vaio',
            'kode_barang' => '3100102002',
            'nup' => '10',
            'merek' => 'Sony Vaio',
            'tahun_perolehan' => 2012,
            'kondisi' => 'Rusak',
            'lokasi' => 'Ruang Tata Usaha',
            'penanggungjawab' => 'Irma Nur Azizah, A.Md.Kes.',
            'jenis' => 'BMN-TIK',
        ]);

        $ba = BaPerpindahan::create([
            'nomor_ba' => 'PL.03.03.43B.08.26.944',
            'tanggal' => '2026-08-12',
            'bmn_id' => $bmn->id,
            'nama_barang' => $bmn->nama_barang,
            'dari' => 'Ruang Tata Usaha',
            'ke' => 'Gudang BMN',
            'alasan' => 'Barang rusak dan dipindahkan ke gudang',
            'pihak_pertama' => 'Irma Nur Azizah, A.Md.Kes.',
            'pihak_kedua' => 'Yuli Supriyatno,A.Md.',
            'pengaju_nama' => 'Irma Nur Azizah, A.Md.Kes.',
            'pengaju_email' => 'operator@bpom.go.id',
            'status' => 'Disetujui',
        ]);

        // 1. Assert terbilang date accessor
        $this->assertStringContainsString('Rabu tanggal Dua belas bulan Agustus tahun Dua ribu dua puluh enam', $ba->tanggal_terbilang);

        // 2. Test print A4
        $resA4 = $this->actingAs($operator)->get(route('ba-perpindahan.print', [$ba, 'size' => 'a4']));
        $resA4->assertOk();
        $resA4->assertSee('size-a4');
        $resA4->assertSee('BERITA ACARA PERPINDAHAN BARANG MILIK NEGARA (BMN)');
        $resA4->assertSee('PL.03.03.43B.08.26.944');
        $resA4->assertSee('Ruang Asal');
        $resA4->assertSee('Ruang Baru');
        $resA4->assertSee('Gudang BMN');
        $resA4->assertSee('${ttd_pengirim1}', false);
        $resA4->assertSee('${ttd_pengirim2}', false);
        $resA4->assertSee('${ttd_pengirim3}', false);
        $resA4->assertSee('Ary Yustantiningsih,S.Si.,Apt.');

        // 3. Test print F4
        $resF4 = $this->actingAs($operator)->get(route('ba-perpindahan.print', [$ba, 'size' => 'f4']));
        $resF4->assertOk();
        $resF4->assertSee('size-f4');
    }

    public function test_distribusi_search_by_kode_barang_and_nama_barang(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $bmn1 = Bmn::create([
            'kode_barang' => 'KD-DIST-FIND-111',
            'nup' => '10',
            'nama_barang' => 'Kursi Ergonomis Putar',
            'jenis' => 'BMN Non TIK',
            'lokasi' => 'Gudang BMN',
            'penanggungjawab' => 'Pengelola BMN',
            'kondisi' => 'Baik',
            'dihapus' => false,
        ]);

        $bmn2 = Bmn::create([
            'kode_barang' => 'KD-DIST-FIND-222',
            'nup' => '20',
            'nama_barang' => 'Laptop ThinkPad X1',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Gudang BMN',
            'penanggungjawab' => 'Pengelola BMN',
            'kondisi' => 'Baik',
            'dihapus' => false,
        ]);

        Distribusi::create([
            'nomor' => 'BA-DIST/2026/F1',
            'tanggal' => now(),
            'bmn_id' => $bmn1->id,
            'nama_barang' => $bmn1->nama_barang,
            'jumlah' => 1,
            'satuan' => 'unit',
            'lokasi_asal' => 'Gudang BMN',
            'lokasi_tujuan' => 'Ruang TU',
            'penerima' => 'Ahmad',
            'dihapus' => false,
        ]);

        Distribusi::create([
            'nomor' => 'BA-DIST/2026/F2',
            'tanggal' => now(),
            'bmn_id' => $bmn2->id,
            'nama_barang' => $bmn2->nama_barang,
            'jumlah' => 1,
            'satuan' => 'unit',
            'lokasi_asal' => 'Gudang BMN',
            'lokasi_tujuan' => 'Ruang IT',
            'penerima' => 'Budi',
            'dihapus' => false,
        ]);

        // 1. Search by kode_barang
        $resKode = $this->actingAs($user)->get(route('distribusi.index', ['search' => 'KD-DIST-FIND-111']));
        $resKode->assertOk();
        $resKode->assertSee('BA-DIST/2026/F1');
        $resKode->assertDontSee('BA-DIST/2026/F2');

        // 2. Search by nama_barang
        $resNama = $this->actingAs($user)->get(route('distribusi.index', ['search' => 'ThinkPad']));
        $resNama->assertOk();
        $resNama->assertSee('BA-DIST/2026/F2');
        $resNama->assertDontSee('BA-DIST/2026/F1');
    }

    public function test_ba_perpindahan_search_by_kode_barang_and_nama_barang(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $bmn1 = Bmn::create([
            'kode_barang' => 'KD-MUT-FIND-333',
            'nup' => '30',
            'nama_barang' => 'Mikroskop Olympus CX23',
            'jenis' => 'BMN Laboratorium',
            'lokasi' => 'Lab Kimia',
            'penanggungjawab' => 'Laboran',
            'kondisi' => 'Baik',
            'dihapus' => false,
        ]);

        $bmn2 = Bmn::create([
            'kode_barang' => 'KD-MUT-FIND-444',
            'nup' => '40',
            'nama_barang' => 'Server Dell PowerEdge',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server',
            'penanggungjawab' => 'Admin IT',
            'kondisi' => 'Baik',
            'dihapus' => false,
        ]);

        BaPerpindahan::create([
            'nomor_ba' => 'BA-MUT/2026/P1',
            'tanggal' => now(),
            'bmn_id' => $bmn1->id,
            'nama_barang' => $bmn1->nama_barang,
            'dari' => 'Lab Kimia',
            'ke' => 'Lab Biologi',
            'alasan' => 'Penelitian bersama',
            'pihak_pertama' => 'Laboran A',
            'pihak_kedua' => 'Laboran B',
            'pengaju_nama' => 'Laboran A',
            'pengaju_email' => 'laboran-a@example.com',
            'status' => 'Disetujui',
            'dihapus' => false,
        ]);

        BaPerpindahan::create([
            'nomor_ba' => 'BA-MUT/2026/P2',
            'tanggal' => now(),
            'bmn_id' => $bmn2->id,
            'nama_barang' => $bmn2->nama_barang,
            'dari' => 'Ruang Server',
            'ke' => 'Gudang BMN',
            'alasan' => 'Upgrade perangkat',
            'pihak_pertama' => 'Admin IT',
            'pihak_kedua' => 'Pengelola BMN',
            'pengaju_nama' => 'Admin IT',
            'pengaju_email' => 'admin-it@example.com',
            'status' => 'Disetujui',
            'dihapus' => false,
        ]);

        // 1. Search by kode_barang
        $resKode = $this->actingAs($user)->get(route('ba-perpindahan.index', ['search' => 'KD-MUT-FIND-333']));
        $resKode->assertOk();
        $resKode->assertSee('BA-MUT/2026/P1');
        $resKode->assertDontSee('BA-MUT/2026/P2');

        // 2. Search by nama_barang
        $resNama = $this->actingAs($user)->get(route('ba-perpindahan.index', ['search' => 'PowerEdge']));
        $resNama->assertOk();
        $resNama->assertSee('BA-MUT/2026/P2');
        $resNama->assertDontSee('BA-MUT/2026/P1');
    }

    public function test_pengembalian_search_by_kode_barang_and_nama_barang(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $bmn1 = Bmn::create([
            'kode_barang' => 'KD-RET-FIND-555',
            'nup' => '50',
            'nama_barang' => 'Proyektor Epson EB-E500',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Aula Pertemuan',
            'penanggungjawab' => 'Staff TU',
            'kondisi' => 'Baik',
            'dihapus' => false,
        ]);

        $bmn2 = Bmn::create([
            'kode_barang' => 'KD-RET-FIND-666',
            'nup' => '60',
            'nama_barang' => 'Timbangan Analitik Sartorius',
            'jenis' => 'BMN Laboratorium',
            'lokasi' => 'Lab Pengujian',
            'penanggungjawab' => 'Staff Pengujian',
            'kondisi' => 'Baik',
            'dihapus' => false,
        ]);

        $ret1 = Pengembalian::create([
            'nomor_pengajuan' => 'RET/2026/R1',
            'tanggal' => now(),
            'nama_pengaju' => 'Staff TU',
            'bidang' => 'Tata Usaha',
            'status' => 'Selesai',
            'dihapus' => false,
        ]);
        PengembalianItem::create([
            'pengembalian_id' => $ret1->id,
            'bmn_id' => $bmn1->id,
            'nama_barang_snapshot' => $bmn1->nama_barang,
            'kondisi_awal' => 'Baik',
            'kondisi' => 'Baik',
        ]);

        $ret2 = Pengembalian::create([
            'nomor_pengajuan' => 'RET/2026/R2',
            'tanggal' => now(),
            'nama_pengaju' => 'Staff Pengujian',
            'bidang' => 'Pengujian',
            'status' => 'Selesai',
            'dihapus' => false,
        ]);
        PengembalianItem::create([
            'pengembalian_id' => $ret2->id,
            'bmn_id' => $bmn2->id,
            'nama_barang_snapshot' => $bmn2->nama_barang,
            'kondisi_awal' => 'Baik',
            'kondisi' => 'Rusak Ringan',
        ]);

        // 1. Search by kode_barang
        $resKode = $this->actingAs($user)->get(route('pengembalians.index', ['search' => 'KD-RET-FIND-555']));
        $resKode->assertOk();
        $resKode->assertSee('RET/2026/R1');
        $resKode->assertDontSee('RET/2026/R2');

        // 2. Search by nama_barang
        $resNama = $this->actingAs($user)->get(route('pengembalians.index', ['search' => 'Sartorius']));
        $resNama->assertOk();
        $resNama->assertSee('RET/2026/R2');
        $resNama->assertDontSee('RET/2026/R1');
    }

    public function test_master_lokasi_edit_cascades_to_all_forms_and_tables(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        // 1. Create a Master Lokasi
        $lokasi = Lokasi::create([
            'kode_ruangan' => 'B1.01',
            'nama' => 'RUANG TAMU UTAMA',
        ]);

        // 2. Create related entities referencing this location
        $bmn = Bmn::create([
            'kode_barang' => 'BMN-CASCADE-1',
            'nup' => '101',
            'nama_barang' => 'Sofa Tamu Eksekutif',
            'jenis' => 'BMN-NON TIK',
            'lokasi' => 'RUANG TAMU UTAMA',
            'lokasi_id' => $lokasi->id,
            'penanggungjawab' => 'Admin Umum',
            'kondisi' => 'Baik',
            'dihapus' => false,
        ]);

        $bmnTik = BmnTik::create([
            'bmn_id' => $bmn->id,
            'kode_barang' => 'TIK-CASCADE-1',
            'nup' => 101,
            'nama_barang' => 'Display Monitor Lobby',
            'lokasi' => 'RUANG TAMU UTAMA',
            'penanggungjawab' => 'Admin Umum',
            'kondisi' => 'Baik',
            'dihapus' => false,
        ]);

        $bmnLab = BmnLab::create([
            'bmn_id' => $bmn->id,
            'kode_barang' => 'LAB-CASCADE-1',
            'nup' => 101,
            'nama_barang' => 'Termometer Ruangan',
            'lokasi' => 'RUANG TAMU UTAMA',
            'penanggungjawab' => 'Admin Umum',
            'kondisi' => 'Baik',
            'dihapus' => false,
        ]);

        $distribusi = Distribusi::create([
            'nomor' => 'DIS-CASCADE-001',
            'tanggal' => now(),
            'bmn_id' => $bmn->id,
            'nama_barang' => $bmn->nama_barang,
            'lokasi_asal' => 'RUANG TAMU UTAMA',
            'lokasi_asal_id' => $lokasi->id,
            'lokasi_tujuan' => 'RUANG TAMU UTAMA',
            'lokasi_tujuan_id' => $lokasi->id,
            'status' => 'Pending',
        ]);

        $ba = BaPerpindahan::create([
            'nomor_ba' => 'BA-CASCADE-001',
            'tanggal' => now(),
            'bmn_id' => $bmn->id,
            'nama_barang' => $bmn->nama_barang,
            'dari' => 'RUANG TAMU UTAMA',
            'lokasi_asal_id' => $lokasi->id,
            'ke' => 'RUANG TAMU UTAMA',
            'lokasi_tujuan_id' => $lokasi->id,
            'status' => 'Menunggu',
            'pihak_pertama' => 'Admin Umum',
            'alasan' => 'Renovasi tata ruang',
            'pengaju_email' => 'operator@example.com',
            'pengaju_nama' => 'Admin Operator',
        ]);

        $laporan = LaporanKerusakan::create([
            'nomor' => 'LK-CASCADE-001',
            'tanggal' => now(),
            'bmn_id' => $bmn->id,
            'nama_barang' => $bmn->nama_barang,
            'lokasi' => 'RUANG TAMU UTAMA',
            'lokasi_id' => $lokasi->id,
            'tingkat' => 'Ringan',
            'deskripsi' => 'Kulit sofa terkelupas',
            'status' => 'Dilaporkan',
            'pelapor_email' => 'operator@example.com',
            'pelapor_nama' => 'Admin Operator',
        ]);

        // 3. Update the Master Lokasi via PUT endpoint
        $response = $this->actingAs($operator)->put(route('lokasis.update', $lokasi), [
            'kode_ruangan' => 'B1.99',
            'nama' => 'RUANG TAMU VIP UTAMA',
        ]);

        $response->assertRedirect(route('lokasis.index'));

        // 4. Assert database tables are automatically updated
        $this->assertDatabaseHas('lokasis', [
            'id' => $lokasi->id,
            'kode_ruangan' => 'B1.99',
            'nama' => 'RUANG TAMU VIP UTAMA',
        ]);

        $this->assertEquals('RUANG TAMU VIP UTAMA', $bmn->fresh()->lokasi);
        $this->assertEquals($lokasi->id, $bmn->fresh()->lokasi_id);

        $this->assertEquals('RUANG TAMU VIP UTAMA', $bmnTik->fresh()->lokasi);
        $this->assertEquals('RUANG TAMU VIP UTAMA', $bmnLab->fresh()->lokasi);

        $this->assertEquals('RUANG TAMU VIP UTAMA', $distribusi->fresh()->lokasi_asal);
        $this->assertEquals('RUANG TAMU VIP UTAMA', $distribusi->fresh()->lokasi_tujuan);
        $this->assertEquals($lokasi->id, $distribusi->fresh()->lokasi_asal_id);
        $this->assertEquals($lokasi->id, $distribusi->fresh()->lokasi_tujuan_id);

        $this->assertEquals('RUANG TAMU VIP UTAMA', $ba->fresh()->dari);
        $this->assertEquals('RUANG TAMU VIP UTAMA', $ba->fresh()->ke);
        $this->assertEquals($lokasi->id, $ba->fresh()->lokasi_asal_id);
        $this->assertEquals($lokasi->id, $ba->fresh()->lokasi_tujuan_id);

        $this->assertEquals('RUANG TAMU VIP UTAMA', $laporan->fresh()->lokasi);
        $this->assertEquals($lokasi->id, $laporan->fresh()->lokasi_id);

        // 5. Assert the edit forms return 200 and display the cascaded location
        $resBmnEdit = $this->actingAs($operator)->get(route('bmns.edit', $bmn));
        $resBmnEdit->assertOk();
        $resBmnEdit->assertSee('RUANG TAMU VIP UTAMA');

        $resBaEdit = $this->actingAs($operator)->get(route('ba-perpindahan.edit', $ba));
        $resBaEdit->assertOk();
        $resBaEdit->assertSee('RUANG TAMU VIP UTAMA');

        $resDistEdit = $this->actingAs($operator)->get(route('distribusi.edit', $distribusi));
        $resDistEdit->assertOk();
        $resDistEdit->assertSee('RUANG TAMU VIP UTAMA');

        $resLapEdit = $this->actingAs($operator)->get(route('laporan-kerusakan.edit', $laporan));
        $resLapEdit->assertOk();
        $resLapEdit->assertSee('RUANG TAMU VIP UTAMA');
    }

    public function test_future_dates_are_rejected_across_all_modules(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);
        $lokasi = Lokasi::create(['nama' => 'Ruang Server TIK', 'kode_ruangan' => 'B1.01']);
        $pegawai = Pegawai::create([
            'nama' => 'Ahmad Fauzi',
            'nip' => '198804122014021001',
            'jabatan' => 'Staff IT',
            'unit_kerja' => 'Tata Usaha',
        ]);

        $futureDate = now()->addDays(5)->toDateString();

        // 1. BMN Store with future tanggal_terima
        $resBmn = $this->actingAs($operator)->post(route('bmns.store'), [
            'kode_barang' => '3050104084',
            'nup' => '1',
            'nama_barang' => 'Laptop Masa Depan',
            'tanggal_terima' => $futureDate,
            'jenis' => 'BMN-TIK',
            'lokasi' => $lokasi->nama,
            'penanggungjawab' => $pegawai->nama,
            'kondisi' => 'Baik',
        ]);
        $resBmn->assertSessionHasErrors('tanggal_terima');

        // 2. BA Perpindahan with future tanggal
        $resBa = $this->actingAs($operator)->post(route('ba-perpindahan.store'), [
            'tanggal' => $futureDate,
            'nama_barang' => 'Laptop Uji',
            'dari' => 'Gudang',
            'alasan' => 'Uji mutasi',
        ]);
        $resBa->assertSessionHasErrors('tanggal');

        // 3. Distribusi with future tanggal
        $resDist = $this->actingAs($operator)->post(route('distribusi.store'), [
            'nomor' => 'DST-TEST-001',
            'tanggal' => $futureDate,
            'nama_barang' => 'Laptop Uji',
            'jumlah' => 1,
            'satuan' => 'unit',
            'lokasi_asal' => 'Gudang',
            'lokasi_tujuan' => 'Ruang TU',
            'penerima' => 'Ahmad Fauzi',
        ]);
        $resDist->assertSessionHasErrors('tanggal');

        // 4. Laporan Kerusakan with future tanggal
        $resLap = $this->actingAs($operator)->post(route('laporan-kerusakan.store'), [
            'nomor' => 'LK-TEST-001',
            'tanggal' => $futureDate,
            'nama_barang' => 'Laptop Uji',
            'lokasi' => 'Ruang TU',
            'tingkat' => 'Ringan',
            'deskripsi' => 'Kerusakan uji coba',
        ]);
        $resLap->assertSessionHasErrors('tanggal');

        // 5. Pengembalian with future tanggal
        $bmn = Bmn::create([
            'kode_barang' => 'TEST-BMN-KMB',
            'nup' => '1',
            'nama_barang' => 'Laptop Uji KMB',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Gudang',
            'penanggungjawab' => 'Ahmad Fauzi',
            'kondisi' => 'Baik',
            'dihapus' => false,
        ]);

        $resKmb = $this->actingAs($operator)->post(route('pengembalians.store'), [
            'nomor_pengajuan' => 'KMB-TEST-001',
            'tanggal' => $futureDate,
            'nama_pengaju' => 'Ahmad Fauzi',
            'bidang' => 'Tata Usaha',
            'items' => [
                [
                    'bmn_id' => $bmn->id,
                    'kondisi' => 'Baik',
                ],
            ],
        ]);
        $resKmb->assertSessionHasErrors('tanggal');
    }

    public function test_laporan_kerusakan_tingkat_accepts_rusak_ringan_and_rusak_berat(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        // 1. Rusak Ringan is valid
        $resRingan = $this->actingAs($operator)->post(route('laporan-kerusakan.store'), [
            'nomor' => 'LK-TEST-RR-01',
            'tanggal' => now()->toDateString(),
            'nama_barang' => 'Laptop HP',
            'lokasi' => 'Ruang TU',
            'tingkat' => 'Rusak Ringan',
            'deskripsi' => 'Layar bergaris tipis',
        ]);
        $resRingan->assertSessionHasNoErrors();
        $this->assertDatabaseHas('laporan_kerusakans', [
            'nomor' => 'LK-TEST-RR-01',
            'tingkat' => 'Rusak Ringan',
        ]);

        // 2. Rusak Berat is valid
        $resBerat = $this->actingAs($operator)->post(route('laporan-kerusakan.store'), [
            'nomor' => 'LK-TEST-RB-01',
            'tanggal' => now()->toDateString(),
            'nama_barang' => 'Server Dell',
            'lokasi' => 'Ruang Server',
            'tingkat' => 'Rusak Berat',
            'deskripsi' => 'Motherboard mati total terbakar',
        ]);
        $resBerat->assertSessionHasNoErrors();
        $this->assertDatabaseHas('laporan_kerusakans', [
            'nomor' => 'LK-TEST-RB-01',
            'tingkat' => 'Rusak Berat',
        ]);

        // 3. Invalid tingkat fails validation
        $resInvalid = $this->actingAs($operator)->post(route('laporan-kerusakan.store'), [
            'nomor' => 'LK-TEST-INV-01',
            'tanggal' => now()->toDateString(),
            'nama_barang' => 'Kursi',
            'lokasi' => 'Ruang Rapat',
            'tingkat' => 'ParahSekali',
            'deskripsi' => 'Patah kaki',
        ]);
        $resInvalid->assertSessionHasErrors('tingkat');

        // 4. Create form displays only Rusak Berat (no Ringan or Sedang)
        $resCreate = $this->actingAs($operator)->get(route('laporan-kerusakan.create'));
        $resCreate->assertOk();
        $resCreate->assertDontSee('value="Rusak Ringan"', false);
        $resCreate->assertSee('value="Rusak Berat"', false);
        $resCreate->assertDontSee('value="Sedang"', false);
    }

    public function test_bmn_kondisi_automatically_updates_when_laporan_kerusakan_is_accepted_and_restores_on_complete(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);
        $user = User::factory()->create(['role' => 'User']);

        $bmn = Bmn::create([
            'kode_barang' => 'TEST-BMN-SYNC-01',
            'nup' => '1',
            'nama_barang' => 'Laptop Asus ROG Zephyrus',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        $bmnTik = BmnTik::create([
            'bmn_id' => $bmn->id,
            'kode_barang' => 'TEST-BMN-SYNC-01',
            'nup' => '1',
            'nama_barang' => 'Laptop Asus ROG Zephyrus',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        // 1. User membuat laporan kerusakan: kondisi BMN awal tetap 'Baik' sebelum diterima
        $createRes = $this->actingAs($user)->post(route('laporan-kerusakan.store'), [
            'nomor' => 'LK-SYNC-001',
            'tanggal' => now()->toDateString(),
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Laptop Asus ROG Zephyrus',
            'lokasi' => 'Ruang Server TIK',
            'tingkat' => 'Rusak Berat',
            'deskripsi' => 'Motherboard korsleting mati total',
        ]);
        $createRes->assertRedirect(route('laporan-kerusakan.index'));

        $this->assertEquals('Baik', $bmn->fresh()->kondisi);
        $this->assertEquals('Baik', $bmnTik->fresh()->kondisi);

        $laporan = LaporanKerusakan::where('nomor', 'LK-SYNC-001')->firstOrFail();

        // 2. Operator menerima laporan: kondisi BMN otomatis berubah menjadi 'Rusak Berat'
        $acceptRes = $this->actingAs($operator)->put(route('laporan-kerusakan.update', $laporan), [
            'status' => 'Diterima',
            'tanggapan' => 'Laporan diterima dan dijadwalkan penggantian motherboard.',
        ]);
        $acceptRes->assertRedirect(route('laporan-kerusakan.index'));

        $this->assertEquals('Rusak Berat', $bmn->fresh()->kondisi);
        $this->assertEquals('Rusak Berat', $bmnTik->fresh()->kondisi);

        // 3. Operator menyelesaikan laporan: kondisi BMN otomatis pulih menjadi 'Baik'
        $completeRes = $this->actingAs($operator)->put(route('laporan-kerusakan.update', $laporan), [
            'status' => 'Selesai',
            'tanggapan' => 'Motherboard berhasil diganti dan berfungsi normal kembali.',
        ]);
        $completeRes->assertRedirect(route('laporan-kerusakan.index'));

        $this->assertEquals('Baik', $bmn->fresh()->kondisi);
        $this->assertEquals('Baik', $bmnTik->fresh()->kondisi);

        // 4. Fallback lookup nama_barang jika bmn_id null
        $bmn2 = Bmn::create([
            'kode_barang' => 'TEST-BMN-SYNC-02',
            'nup' => '2',
            'nama_barang' => 'Proyektor Epson EB-X500',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Rapat',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        $laporan2 = LaporanKerusakan::create([
            'nomor' => 'LK-SYNC-002',
            'tanggal' => now()->toDateString(),
            'nama_barang' => 'Proyektor Epson EB-X500',
            'lokasi' => 'Ruang Rapat',
            'tingkat' => 'Rusak Ringan',
            'deskripsi' => 'Lampu proyektor berkedip',
            'status' => 'Dilaporkan',
            'pelapor_email' => $user->email,
            'pelapor_nama' => $user->name,
        ]);

        $acceptRes2 = $this->actingAs($operator)->put(route('laporan-kerusakan.update', $laporan2), [
            'status' => 'Diterima',
            'tanggapan' => 'Laporan diterima.',
        ]);
        $acceptRes2->assertRedirect(route('laporan-kerusakan.index'));

        $this->assertEquals('Rusak Berat', $bmn2->fresh()->kondisi);
        $this->assertEquals($bmn2->id, $laporan2->fresh()->bmn_id);

        // 5. Jika laporan ditolak, kondisi kembali menjadi 'Baik'
        $rejectRes2 = $this->actingAs($operator)->put(route('laporan-kerusakan.update', $laporan2), [
            'status' => 'Ditolak',
            'tanggapan' => 'Bukan kerusakan teknis, hanya kabel power kendur.',
        ]);
        $rejectRes2->assertRedirect(route('laporan-kerusakan.index'));

        $this->assertEquals('Baik', $bmn2->fresh()->kondisi);
    }

    public function test_pengembalian_show_page_renders_correctly(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);
        $pegawai = Pegawai::create([
            'nama' => 'Bambang Hendrawan, S.Farm.',
            'nip' => '199201152019021002',
            'jabatan' => 'Analis Pengujian',
            'unit_kerja' => 'Pengujian',
        ]);

        $bmn = Bmn::create([
            'kode_barang' => 'BMN-KMB-001',
            'nup' => '1',
            'nama_barang' => 'Spektrofotometer UV-Vis Shimadzu',
            'jenis' => 'BMN-Laboratorium',
            'lokasi' => 'Lab Kimia',
            'penanggungjawab' => 'Bambang Hendrawan',
            'kondisi' => 'Baik',
        ]);

        $pengembalian = Pengembalian::create([
            'nomor_pengajuan' => '111111YF',
            'tanggal' => '2026-10-20',
            'pengaju_id' => $pegawai->id,
            'nama_pengaju' => 'Bambang Hendrawan, S.Farm. (199201152019021002)',
            'bidang' => 'Pengujian',
            'kondisi_pengembalian' => 'Rusak Berat',
            'status' => 'Diajukan',
        ]);

        $pengembalian->items()->create([
            'bmn_id' => $bmn->id,
            'nama_barang_snapshot' => 'Spektrofotometer UV-Vis Shimadzu',
            'kondisi_awal' => 'Baik',
            'kondisi' => 'Rusak Berat',
            'keterangan' => 'Lampu deuterium mati dan detector error',
        ]);

        // 1. Show page renders successfully
        $response = $this->actingAs($operator)->get(route('pengembalians.show', $pengembalian));
        $response->assertOk();
        $response->assertSee('111111YF');
        $response->assertSee('Bambang Hendrawan, S.Farm.');
        $response->assertSee('Pengujian');
        $response->assertSee('Spektrofotometer UV-Vis Shimadzu');
        $response->assertSee('BMN-KMB-001');
        $response->assertSee('1');
        $response->assertSee('Rusak Berat');
        $response->assertSee('Lampu deuterium mati dan detector error');
        $response->assertSee('Cetak BA');
        $response->assertSee('Edit Data');
        $response->assertDontSee('Status Penyerahan');
        $response->assertDontSee('Kondisi Umum Pengembalian');

        // 2. Index page contains link to detail show page
        $indexResponse = $this->actingAs($operator)->get(route('pengembalians.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee(route('pengembalians.show', $pengembalian));
    }

    public function test_pengembalian_print_template_matches_ba_perpindahan(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $pengembalian = Pengembalian::create([
            'nomor_pengajuan' => 'BA-KEMBALI/TEST/001',
            'tanggal' => now()->toDateString(),
            'nama_pengaju' => 'Bambang Hendrawan, S.Farm.',
            'bidang' => 'Pengujian',
            'kondisi_pengembalian' => 'Rusak Ringan',
            'status' => 'Diajukan',
        ]);

        $response = $this->actingAs($operator)->get(route('pengembalians.print', $pengembalian));
        $response->assertOk();
        $response->assertSee('BERITA ACARA SERAH TERIMA BARANG MILIK NEGARA (BMN)');
        $response->assertSee('BA-KEMBALI/TEST/001');
        $response->assertSee('${ttd_pengirim1}', false);
        $response->assertSee('${ttd_pengirim2}', false);
        $response->assertSee('${ttd_pengirim3}', false);
        $response->assertSee('Kepala Balai Besar POM di Banjarbaru');
        $response->assertSee('Selaku Kuasa Pengguna Barang');
        $response->assertSee('Ary Yustantiningsih,S.Si.,Apt.');
        $response->assertSee('Format TTE:');
        $response->assertSee('Srikandi TTE');
        $response->assertSee('Tanda Tangan Basah');
    }

    public function test_ba_perpindahan_penerima_cannot_be_same_as_pengaju(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $penyerah = Pegawai::create([
            'nama' => 'Hendro Siswanto, S.T.',
            'nip' => '198501012010121001',
            'jabatan' => 'Staff Pengelola BMN',
            'unit_kerja' => 'Subbag Tata Usaha',
        ]);

        $pengaju = Pegawai::create([
            'nama' => 'Dewi Anggraini, S.Si.',
            'nip' => '199304152019032005',
            'jabatan' => 'Analis Kimia',
            'unit_kerja' => 'Laboratorium Kimia',
        ]);

        $calonPenerima = Pegawai::create([
            'nama' => 'Rahmat Hidayat, S.Kom.',
            'nip' => '199506202020121003',
            'jabatan' => 'Pranata Komputer',
            'unit_kerja' => 'TIK',
        ]);

        $bmn = Bmn::create([
            'kode_barang' => 'BMN-TEST-MUTASI-01',
            'nup' => '5',
            'nama_barang' => 'Laptop Dell Latitude',
            'lokasi' => 'Ruang TU',
            'penanggungjawab' => $penyerah->nama,
            'penanggung_jawab_id' => $penyerah->id,
            'jenis' => 'BMN-TIK',
            'kondisi' => 'Baik',
        ]);

        // 1. Create fails if pihak_kedua matches pengaju
        $createResponse = $this->actingAs($operator)->post(route('ba-perpindahan.store'), [
            'nomor_ba' => 'BA-MUT/TEST/SAME-PENGAJU-01',
            'tanggal' => now()->toDateString(),
            'bmn_id' => $bmn->id,
            'pengaju_pegawai_id' => $pengaju->id,
            'nama_barang' => 'Laptop Dell Latitude',
            'dari' => 'Ruang TU',
            'ke' => 'Ruang Lab Kimia',
            'alasan' => 'Analisis data sampel',
            'pihak_kedua' => $pengaju->nama.' ('.$pengaju->nip.')',
        ]);
        $createResponse->assertSessionHasErrors(['pihak_kedua']);

        // 2. Create succeeds when pihak_kedua is another person
        $createValid = $this->actingAs($operator)->post(route('ba-perpindahan.store'), [
            'nomor_ba' => 'BA-MUT/TEST/VALID-01',
            'tanggal' => now()->toDateString(),
            'bmn_id' => $bmn->id,
            'pengaju_pegawai_id' => $pengaju->id,
            'nama_barang' => 'Laptop Dell Latitude',
            'dari' => 'Ruang TU',
            'ke' => 'Ruang Lab Kimia',
            'alasan' => 'Analisis data sampel',
            'pihak_kedua' => $calonPenerima->nama.' ('.$calonPenerima->nip.')',
        ]);
        $createValid->assertRedirect(route('ba-perpindahan.index'));
        $ba = BaPerpindahan::where('nomor_ba', 'BA-MUT/TEST/VALID-01')->firstOrFail();

        // 3. Update fails if pihak_kedua matches pengaju
        $updateResponse = $this->actingAs($operator)->put(route('ba-perpindahan.update', $ba), [
            'nomor_ba' => 'BA-MUT/TEST/VALID-01',
            'tanggal' => now()->toDateString(),
            'bmn_id' => $bmn->id,
            'pengaju_pegawai_id' => $pengaju->id,
            'nama_barang' => 'Laptop Dell Latitude',
            'dari' => 'Ruang TU',
            'ke' => 'Ruang Lab Kimia',
            'alasan' => 'Analisis data sampel',
            'pihak_kedua' => $pengaju->nama,
        ]);
        $updateResponse->assertSessionHasErrors(['pihak_kedua']);

        // 4. Operator decision fails if pihak_kedua matches pengaju
        $decisionFail = $this->actingAs($operator)->patch(route('ba-perpindahan.process-decision', $ba), [
            'pihak_kedua' => $pengaju->nama,
            'keputusan' => 'Setuju',
            'catatan' => 'Disetujui',
        ]);
        $decisionFail->assertSessionHasErrors(['pihak_kedua']);

        // 5. Operator direct approve fails if pihak_kedua matches pengaju
        $approveFail = $this->actingAs($operator)->patch(route('ba-perpindahan.approve', $ba), [
            'pihak_kedua' => $pengaju->nama.' ('.$pengaju->nip.')',
        ]);
        $approveFail->assertSessionHasErrors(['pihak_kedua']);

        // 6. Show page combobox excludes both penyerah and pengaju from JSON pegawais list
        $showResponse = $this->actingAs($operator)->get(route('ba-perpindahan.show', $ba));
        $showResponse->assertOk();
        $content = $showResponse->getContent();

        preg_match('/pegawais:\s*JSON\.parse\(\'([^\']+)\'\)/', $content, $matches);
        $this->assertNotEmpty($matches, 'Could not find pegawais JSON payload in show page');
        $pegawaisPayload = $matches[1];

        $this->assertStringContainsString($calonPenerima->nama, $pegawaisPayload);
        $this->assertStringNotContainsString($pengaju->nama, $pegawaisPayload);
        $this->assertStringNotContainsString($penyerah->nama, $pegawaisPayload);

        // 7. Edit page datalist excludes both pengaju and penyerah
        $editResponse = $this->actingAs($operator)->get(route('ba-perpindahan.edit', $ba));
        $editResponse->assertOk();
        $editContent = $editResponse->getContent();
        preg_match('/<datalist id="pegawai_pihak_kedua_list_edit">(.*?)<\/datalist>/s', $editContent, $editDatalistMatches);
        $this->assertNotEmpty($editDatalistMatches);
        $datalistHtml = $editDatalistMatches[1];
        $this->assertStringContainsString($calonPenerima->nama, $datalistHtml);
        $this->assertStringNotContainsString($pengaju->nama, $datalistHtml);
        $this->assertStringNotContainsString($penyerah->nama, $datalistHtml);
    }

    public function test_ba_perpindahan_cannot_be_printed_if_penerima_is_not_determined(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $penyerah = Pegawai::create([
            'nama' => 'Bambang Irawan, S.T.',
            'nip' => '198801012011011001',
            'jabatan' => 'Staff Pengelola BMN',
            'unit_kerja' => 'Subbag Tata Usaha',
        ]);

        $pengaju = Pegawai::create([
            'nama' => 'Siti Aminah, S.Farm.',
            'nip' => '199203102018022001',
            'jabatan' => 'Apoteker Pertama',
            'unit_kerja' => 'Seksi Farmasi',
        ]);

        $bmn = Bmn::create([
            'kode_barang' => '3100102099',
            'nup' => '1',
            'nama_barang' => 'Scanner Barcode Zebra',
            'lokasi' => 'Gudang BMN',
            'penanggungjawab' => $penyerah->nama,
            'penanggung_jawab_id' => $penyerah->id,
            'jenis' => 'BMN-TIK',
            'kondisi' => 'Baik',
        ]);

        $ba = BaPerpindahan::create([
            'nomor_ba' => 'BA-MUT/TEST/NO-RECIPIENT-01',
            'tanggal' => now()->toDateString(),
            'bmn_id' => $bmn->id,
            'pengaju_pegawai_id' => $pengaju->id,
            'pengaju_nama' => $pengaju->nama,
            'pengaju_email' => 'siti@bpom.go.id',
            'pihak_pertama' => $penyerah->nama,
            'pihak_pertama_id' => $penyerah->id,
            'nama_barang' => $bmn->nama_barang,
            'dari' => 'Gudang BMN',
            'ke' => 'Ruang Farmasi',
            'alasan' => 'Untuk input data obat',
            'pihak_kedua' => null, // Belum ditentukan
            'status' => 'Diajukan',
        ]);

        // 1. Model helper canBePrinted returns false
        $this->assertFalse($ba->canBePrinted());

        // 2. Direct print attempt redirects to show with error
        $printResponse = $this->actingAs($operator)->get(route('ba-perpindahan.print', $ba));
        $printResponse->assertRedirect(route('ba-perpindahan.show', $ba));
        $printResponse->assertSessionHas('error', 'Berita Acara Perpindahan belum dapat dicetak karena Pegawai Penerima (Pihak Kedua) belum ditentukan.');

        // 3. Show page shows disabled indicator
        $showResponse = $this->actingAs($operator)->get(route('ba-perpindahan.show', $ba));
        $showResponse->assertOk();
        $showResponse->assertSee('Penerima Belum Ada');

        // 4. Index page shows disabled print button
        $indexResponse = $this->actingAs($operator)->get(route('ba-perpindahan.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Belum dapat dicetak: Pegawai Penerima (Pihak Kedua) belum ditentukan');

        // 5. Once recipient (pihak_kedua) is determined, canBePrinted is true and print works
        $ba->update(['pihak_kedua' => 'Tri Wulandari, S.Farm. (199405052020122002)']);
        $this->assertTrue($ba->fresh()->canBePrinted());

        $validPrintResponse = $this->actingAs($operator)->get(route('ba-perpindahan.print', $ba));
        $validPrintResponse->assertOk();
        $validPrintResponse->assertSee('BERITA ACARA PERPINDAHAN BARANG MILIK NEGARA (BMN)');
        $validPrintResponse->assertSee('Tri Wulandari, S.Farm.');
    }

    public function test_bmn_index_location_filter_is_searchable_dropdown_based_on_code(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $lokasiA = Lokasi::where('kode_ruangan', 'A1.01')->first() ?? Lokasi::create([
            'kode_ruangan' => 'A1.01',
            'nama' => 'RUANG DISKUSI LAB KIMIA',
        ]);

        $lokasiB = Lokasi::where('kode_ruangan', 'B1.06')->first() ?? Lokasi::create([
            'kode_ruangan' => 'B1.06',
            'nama' => 'RUANG STAFF INFOKOM',
        ]);

        $bmn1 = Bmn::create([
            'kode_barang' => '3100102001',
            'nup' => '1',
            'nama_barang' => 'Komputer PC Dell Optiplex',
            'lokasi' => $lokasiA->nama,
            'lokasi_id' => $lokasiA->id,
            'penanggungjawab' => 'Staff A',
            'jenis' => 'BMN-TIK',
            'kondisi' => 'Baik',
        ]);

        $bmn2 = Bmn::create([
            'kode_barang' => '3100102002',
            'nup' => '2',
            'nama_barang' => 'Laptop Lenovo ThinkPad',
            'lokasi' => $lokasiB->nama,
            'lokasi_id' => $lokasiB->id,
            'penanggungjawab' => 'Staff B',
            'jenis' => 'BMN-TIK',
            'kondisi' => 'Baik',
        ]);

        // 1. Initial index page renders searchable dropdown with room codes and live search
        $response = $this->actingAs($operator)->get(route('bmns.index'));
        $response->assertOk();
        $response->assertSee('name="lokasi"', false);
        $response->assertSee('Semua Lokasi');
        $response->assertSee('A1.01 - RUANG DISKUSI LAB KIMIA');
        $response->assertSee('B1.06 - RUANG STAFF INFOKOM');
        $response->assertSee('placeholder="Cari kode (A1, B1..) atau nama ruangan..."', false);
        $response->assertSee('title="Ubah Lokasi Ruangan (Klik untuk cari & pilih)"', false);
        $response->assertSee('Apakah Anda yakin ingin mengubah lokasi barang', false);

        // 2. Filter by combined format
        $filterResponse = $this->actingAs($operator)->get(route('bmns.index', ['lokasi' => 'A1.01 - RUANG DISKUSI LAB KIMIA']));
        $filterResponse->assertOk();
        $filterResponse->assertSee($bmn1->nama_barang);
        $filterResponse->assertDontSee($bmn2->nama_barang);

        // 3. Filter by just the code 'B1.06'
        $filterByCodeResponse = $this->actingAs($operator)->get(route('bmns.index', ['lokasi' => 'B1.06']));
        $filterByCodeResponse->assertOk();
        $filterByCodeResponse->assertSee($bmn2->nama_barang);
        $filterByCodeResponse->assertDontSee($bmn1->nama_barang);

        // 4. Inline location update via dropdown selection
        $updateLokasiRes = $this->actingAs($operator)->patch(route('bmns.update-lokasi', $bmn1), [
            'lokasi' => 'B1.06 - RUANG STAFF INFOKOM',
        ]);
        $updateLokasiRes->assertRedirect();
        $this->assertEquals($lokasiB->nama, $bmn1->fresh()->lokasi);
        $this->assertEquals($lokasiB->id, $bmn1->fresh()->lokasi_id);
    }

    public function test_bmn_create_and_edit_pages_use_searchable_location_dropdown(): void
    {
        $operator = User::factory()->create(['role' => 'Operator']);

        $lokasiA = Lokasi::where('kode_ruangan', 'A1.01')->first() ?? Lokasi::create([
            'kode_ruangan' => 'A1.01',
            'nama' => 'RUANG DISKUSI LAB KIMIA',
        ]);

        // 1. Create page renders searchable location dropdown defaulting to Gudang BMN
        $createResponse = $this->actingAs($operator)->get(route('bmns.create'));
        $createResponse->assertOk();
        $createResponse->assertSee('placeholder="Ketik kode (A1, B1..) atau nama ruangan..."', false);
        $createResponse->assertSee('Gudang BMN');
        $createResponse->assertSee('A1.01 - RUANG DISKUSI LAB KIMIA');

        // 2. Submit create form with selected location from master
        $storeResponse = $this->actingAs($operator)->post(route('bmns.store'), [
            'kode_barang' => '3100102999',
            'nup' => '99',
            'nama_barang' => 'Switch Cisco Catalyst',
            'jenis' => 'BMN-TIK',
            'kondisi' => 'Baik',
            'lokasi' => 'A1.01 - RUANG DISKUSI LAB KIMIA',
            'penanggungjawab' => 'Pengelola BMN',
        ]);
        $storeResponse->assertRedirect(route('bmns.index'));

        $newBmn = Bmn::where('kode_barang', '3100102999')->where('nup', '99')->first();
        $this->assertNotNull($newBmn);
        $this->assertEquals($lokasiA->nama, $newBmn->lokasi);
        $this->assertEquals($lokasiA->id, $newBmn->lokasi_id);

        // 3. Submit another create form relying on default Gudang BMN
        $storeDefaultResponse = $this->actingAs($operator)->post(route('bmns.store'), [
            'kode_barang' => '3100102998',
            'nup' => '98',
            'nama_barang' => 'Server Rack Cabinet',
            'jenis' => 'BMN-TIK',
            'kondisi' => 'Baik',
            'lokasi' => 'Gudang BMN',
            'penanggungjawab' => 'Pengelola BMN',
        ]);
        $storeDefaultResponse->assertRedirect(route('bmns.index'));

        $defaultBmn = Bmn::where('kode_barang', '3100102998')->first();
        $this->assertNotNull($defaultBmn);
        $this->assertEquals('Gudang BMN', $defaultBmn->lokasi);

        // 3. Edit page pre-populates existing location and renders searchable dropdown
        $editResponse = $this->actingAs($operator)->get(route('bmns.edit', $newBmn));
        $editResponse->assertOk();
        $editResponse->assertSee('placeholder="Ketik kode (A1, B1..) atau nama ruangan..."', false);
        $editResponse->assertSee('A1.01');
        $editResponse->assertSee('RUANG DISKUSI LAB KIMIA');
    }
}
