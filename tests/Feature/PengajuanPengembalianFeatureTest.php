<?php

namespace Tests\Feature;

use App\Models\Bmn;
use App\Models\LaporanKerusakan;
use App\Models\Lokasi;
use App\Models\Pegawai;
use App\Models\PengajuanPengembalian;
use App\Models\PengajuanPengembalianItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengajuanPengembalianFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;

    protected User $regularUser;

    protected User $otherUser;

    protected Pegawai $pegawaiUser;

    protected Pegawai $pegawaiOperator;

    protected function setUp(): void
    {
        parent::setUp();

        // Master Lokasi default
        Lokasi::create(['nama' => 'Gudang BMN', 'keterangan' => 'Gudang Utama']);
        Lokasi::create(['nama' => 'Ruang TU', 'keterangan' => 'Tata Usaha']);

        // Pegawai untuk Operator
        $this->pegawaiOperator = Pegawai::create([
            'nama' => 'Operator Test',
            'nip' => '198001012005011001',
            'jabatan' => 'Pengelola BMN',
            'bidang' => 'Tata Usaha',
        ]);

        $this->operator = User::factory()->create([
            'name' => 'Operator Test',
            'email' => 'operator@bbpom.go.id',
            'role' => 'Operator',
        ]);

        // Pegawai untuk Regular User
        $this->pegawaiUser = Pegawai::create([
            'nama' => 'Budi Santoso',
            'nip' => '199002022015021002',
            'jabatan' => 'Penguji Laboratorium',
            'bidang' => 'Pengujian',
        ]);

        $this->regularUser = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@bbpom.go.id',
            'role' => 'User',
        ]);

        // User lain
        $this->otherUser = User::factory()->create([
            'name' => 'Siti Rahma',
            'email' => 'siti@bbpom.go.id',
            'role' => 'User',
        ]);
    }

    protected function createSampleBmn(array $attributes = []): Bmn
    {
        return Bmn::create(array_merge([
            'kode_barang' => '3050104001',
            'nup' => '1',
            'nama_barang' => 'Laptop Dell Latitude',
            'merek' => 'Dell Latitude 5420',
            'kondisi' => 'Baik',
            'lokasi' => 'Ruang TU',
            'ruangan' => 'Ruang TU',
            'penanggungjawab' => 'Budi Santoso',
            'penanggung_jawab_id' => $this->pegawaiUser->id,
            'is_dikembalikan' => false,
            'status' => 'Aktif',
        ], $attributes));
    }

    protected function createPengajuan(array $attributes = []): PengajuanPengembalian
    {
        static $counter = 1;

        return PengajuanPengembalian::create(array_merge([
            'nomor_pengajuan' => 'BMN-'.now()->format('Y').str_pad((string) $counter++, 4, '0', STR_PAD_LEFT),
            'diajukan_oleh' => $this->regularUser->id,
            'pegawai_id' => $this->pegawaiUser->id,
            'nama_pegawai' => $this->regularUser->name,
            'nip_pegawai' => $this->pegawaiUser->nip,
            'tanggal' => now()->toDateString(),
            'status' => 'diajukan',
            'alasan' => 'pengembalian_biasa',
            'bidang' => 'Pengujian',
        ], $attributes));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('pengajuan-pengembalian.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_view_only_their_own_pengajuan(): void
    {
        $this->createPengajuan([
            'nomor_pengajuan' => 'BMN-20260001',
            'diajukan_oleh' => $this->regularUser->id,
            'nama_pegawai' => $this->regularUser->name,
            'bidang' => 'Pengujian',
        ]);

        $this->createPengajuan([
            'nomor_pengajuan' => 'BMN-20260002',
            'diajukan_oleh' => $this->otherUser->id,
            'nama_pegawai' => $this->otherUser->name,
            'bidang' => 'Tata Usaha',
        ]);

        $response = $this->actingAs($this->regularUser)->get(route('pengajuan-pengembalian.index'));

        $response->assertOk();
        $response->assertSee('BMN-20260001');
        $response->assertDontSee('BMN-20260002');
    }

    public function test_regular_user_cannot_access_admin_verifikasi(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.pengajuan-pengembalian.index'));
        $response->assertForbidden();
    }

    public function test_operator_can_access_admin_verifikasi(): void
    {
        $response = $this->actingAs($this->operator)->get(route('admin.pengajuan-pengembalian.index'));
        $response->assertOk();
        $response->assertSee('Verifikasi Pengembalian BMN');
    }

    public function test_operator_can_see_incoming_pengembalian_and_terima_button_on_main_index(): void
    {
        $pengajuan = $this->createPengajuan([
            'nomor_pengajuan' => 'BMN-20260777',
            'diajukan_oleh' => $this->regularUser->id,
            'nama_pegawai' => $this->regularUser->name,
            'bidang' => 'Tata Usaha',
            'status' => 'diajukan',
        ]);

        $response = $this->actingAs($this->operator)->get(route('pengajuan-pengembalian.index'));
        $response->assertOk();
        $response->assertSee('BMN-20260777');
        $response->assertSee('Terima');
        $response->assertSee(route('admin.pengajuan-pengembalian.show', $pengajuan));
    }

    public function test_operator_visiting_user_show_is_redirected_to_admin_verification(): void
    {
        $pengajuan = $this->createPengajuan([
            'nomor_pengajuan' => 'BMN-20260888',
            'diajukan_oleh' => $this->regularUser->id,
            'nama_pegawai' => $this->regularUser->name,
            'bidang' => 'Tata Usaha',
            'status' => 'diajukan',
        ]);

        $response = $this->actingAs($this->operator)->get(route('pengajuan-pengembalian.show', $pengajuan));
        $response->assertRedirect(route('admin.pengajuan-pengembalian.show', $pengajuan));
    }

    public function test_user_can_submit_pengembalian_biasa(): void
    {
        $bmn = $this->createSampleBmn();

        $payload = [
            'alasan' => 'pengembalian_biasa',
            'bidang' => 'Pengujian',
            'tanggal' => now()->toDateString(),
            'catatan_pemohon' => 'Barang sudah selesai digunakan untuk penelitian.',
            'items' => [
                [
                    'bmn_id' => $bmn->id,
                    'kondisi' => 'Baik',
                    'keterangan' => 'Lengkap dengan adaptor bawaan.',
                ],
            ],
        ];

        $response = $this->actingAs($this->regularUser)->post(route('pengajuan-pengembalian.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('pengajuan_pengembalians', [
            'diajukan_oleh' => $this->regularUser->id,
            'alasan' => 'pengembalian_biasa',
            'status' => 'diajukan',
            'bidang' => 'Pengujian',
        ]);

        $pengajuan = PengajuanPengembalian::latest('id')->first();
        $this->assertStringStartsWith('BMN-', $pengajuan->nomor_pengajuan);
        $this->assertMatchesRegularExpression('/^BMN-\d{4}\d{4}$/', $pengajuan->nomor_pengajuan);

        $this->assertDatabaseHas('pengajuan_pengembalian_items', [
            'pengajuan_id' => $pengajuan->id,
            'bmn_id' => $bmn->id,
            'nama_barang' => 'Laptop Dell Latitude',
            'kondisi' => 'Baik',
        ]);
    }

    public function test_user_can_submit_rusak_berat_with_damage_details(): void
    {
        $bmn = $this->createSampleBmn(['nup' => '2']);

        $payload = [
            'alasan' => 'rusak_berat',
            'bidang' => 'Pengujian',
            'tanggal' => now()->toDateString(),
            'catatan_pemohon' => 'Kerusakan fisik parah akibat lonjakan listrik.',
            'items' => [
                [
                    'bmn_id' => $bmn->id,
                    'kondisi' => 'Rusak Berat',
                    'tanggal_temuan' => now()->toDateString(),
                    'deskripsi_kerusakan' => 'Mati total setelah korsleting listrik, IC power hangus dan tidak ekonomis diperbaiki.',
                    'keterangan' => 'Diperiksa teknisi eksternal dinyatakan rusak permanen.',
                ],
            ],
        ];

        $response = $this->actingAs($this->regularUser)->post(route('pengajuan-pengembalian.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('pengajuan_pengembalians', [
            'diajukan_oleh' => $this->regularUser->id,
            'alasan' => 'rusak_berat',
            'status' => 'diajukan',
        ]);

        $this->assertDatabaseHas('pengajuan_pengembalian_items', [
            'bmn_id' => $bmn->id,
            'kondisi' => 'Rusak Berat',
            'deskripsi_kerusakan' => 'Mati total setelah korsleting listrik, IC power hangus dan tidak ekonomis diperbaiki.',
        ]);
    }

    public function test_validation_rejects_duplicate_bmn_in_same_form(): void
    {
        $bmn = $this->createSampleBmn();

        $payload = [
            'alasan' => 'pengembalian_biasa',
            'bidang' => 'Pengujian',
            'tanggal' => now()->toDateString(),
            'items' => [
                [
                    'bmn_id' => $bmn->id,
                    'kondisi' => 'Baik',
                ],
                [
                    'bmn_id' => $bmn->id,
                    'kondisi' => 'Baik',
                ],
            ],
        ];

        $response = $this->actingAs($this->regularUser)->post(route('pengajuan-pengembalian.store'), $payload);

        $response->assertSessionHasErrors(['items.0.bmn_id']);
    }

    public function test_validation_rejects_bmn_already_in_pending_submission(): void
    {
        $bmn = $this->createSampleBmn();

        // Buat pengajuan yang masih berstatus 'diajukan'
        $existing = $this->createPengajuan([
            'diajukan_oleh' => $this->otherUser->id,
            'nama_pegawai' => $this->otherUser->name,
            'status' => 'diajukan',
        ]);

        PengajuanPengembalianItem::create([
            'pengajuan_id' => $existing->id,
            'bmn_id' => $bmn->id,
            'kode_barang' => $bmn->kode_barang,
            'nup' => (int) $bmn->nup,
            'nama_barang' => $bmn->nama_barang,
            'kondisi' => 'Baik',
        ]);

        // Coba ajukan ulang bmn yang sama
        $payload = [
            'alasan' => 'pengembalian_biasa',
            'bidang' => 'Pengujian',
            'tanggal' => now()->toDateString(),
            'items' => [
                [
                    'bmn_id' => $bmn->id,
                    'kondisi' => 'Baik',
                ],
            ],
        ];

        $response = $this->actingAs($this->regularUser)->post(route('pengajuan-pengembalian.store'), $payload);
        $response->assertSessionHasErrors(['items.0.bmn_id']);
    }

    public function test_validation_rejects_rusak_berat_without_damage_description(): void
    {
        $bmn = $this->createSampleBmn();

        $payload = [
            'alasan' => 'rusak_berat',
            'bidang' => 'Pengujian',
            'tanggal' => now()->toDateString(),
            'items' => [
                [
                    'bmn_id' => $bmn->id,
                    'kondisi' => 'Rusak Berat',
                    'tanggal_temuan' => now()->toDateString(),
                    'deskripsi_kerusakan' => '', // kosong
                ],
            ],
        ];

        $response = $this->actingAs($this->regularUser)->post(route('pengajuan-pengembalian.store'), $payload);
        $response->assertSessionHasErrors(['items.0.deskripsi_kerusakan']);
    }

    public function test_user_can_cancel_their_own_pending_pengajuan(): void
    {
        $pengajuan = $this->createPengajuan([
            'diajukan_oleh' => $this->regularUser->id,
            'status' => 'diajukan',
        ]);

        $response = $this->actingAs($this->regularUser)->post(route('pengajuan-pengembalian.batalkan', $pengajuan));

        $response->assertRedirect();
        $this->assertEquals('dibatalkan', $pengajuan->fresh()->status);
    }

    public function test_user_cannot_cancel_other_users_pengajuan(): void
    {
        $pengajuan = $this->createPengajuan([
            'diajukan_oleh' => $this->otherUser->id,
            'nama_pegawai' => $this->otherUser->name,
            'status' => 'diajukan',
        ]);

        $response = $this->actingAs($this->regularUser)->post(route('pengajuan-pengembalian.batalkan', $pengajuan));

        $response->assertSessionHas('error');
        $this->assertEquals('diajukan', $pengajuan->fresh()->status);
    }

    public function test_operator_can_approve_pengembalian_biasa_and_updates_bmn(): void
    {
        $bmn = $this->createSampleBmn([
            'ruangan' => 'Ruang TU',
            'penanggungjawab' => 'Budi Santoso',
            'is_dikembalikan' => false,
        ]);

        $pengajuan = $this->createPengajuan([
            'diajukan_oleh' => $this->regularUser->id,
            'status' => 'diajukan',
            'alasan' => 'pengembalian_biasa',
        ]);

        PengajuanPengembalianItem::create([
            'pengajuan_id' => $pengajuan->id,
            'bmn_id' => $bmn->id,
            'kode_barang' => $bmn->kode_barang,
            'nup' => (int) $bmn->nup,
            'nama_barang' => $bmn->nama_barang,
            'kondisi_awal' => 'Baik',
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($this->operator)->post(route('admin.pengajuan-pengembalian.setujui', $pengajuan));

        $response->assertRedirect(route('pengembalians.index'));

        $pengajuan->refresh();
        $this->assertEquals('disetujui', $pengajuan->status);
        $this->assertEquals($this->operator->id, $pengajuan->disetujui_oleh);
        $this->assertNotNull($pengajuan->disetujui_pada);
        $this->assertStringStartsWith('BA-PGM/', $pengajuan->nomor_ba);

        // Verifikasi otomatis tercatat di BA Pengembalian
        $this->assertDatabaseHas('pengembalians', [
            'pengajuan_id' => $pengajuan->id,
            'nomor_pengajuan' => $pengajuan->nomor_ba,
            'status' => 'Disetujui',
        ]);
        $this->assertDatabaseHas('pengembalian_items', [
            'bmn_id' => $bmn->id,
            'nama_barang_snapshot' => $bmn->nama_barang,
        ]);

        // Verifikasi mutasi BMN
        $bmn->refresh();
        $this->assertEquals('Gudang BMN', $bmn->lokasi);
        $this->assertEquals('Pengelola BMN', $bmn->penanggungjawab);
        $this->assertTrue((bool) $bmn->is_dikembalikan);
    }

    public function test_operator_can_approve_rusak_berat_and_generates_laporan_kerusakan(): void
    {
        $bmn = $this->createSampleBmn([
            'ruangan' => 'Ruang TU',
            'kondisi' => 'Baik',
            'penanggungjawab' => 'Budi Santoso',
            'is_dikembalikan' => false,
        ]);

        $pengajuan = $this->createPengajuan([
            'diajukan_oleh' => $this->regularUser->id,
            'status' => 'diajukan',
            'alasan' => 'rusak_berat',
        ]);

        $item = PengajuanPengembalianItem::create([
            'pengajuan_id' => $pengajuan->id,
            'bmn_id' => $bmn->id,
            'kode_barang' => $bmn->kode_barang,
            'nup' => (int) $bmn->nup,
            'nama_barang' => $bmn->nama_barang,
            'kondisi_awal' => 'Baik',
            'kondisi' => 'Rusak Berat',
            'tanggal_temuan' => now()->toDateString(),
            'deskripsi_kerusakan' => 'Layar LCD pecah dan komponen board utama hangus terbakar.',
        ]);

        $response = $this->actingAs($this->operator)->post(route('admin.pengajuan-pengembalian.setujui', $pengajuan));

        $response->assertRedirect(route('laporan-kerusakan.index'));

        $pengajuan->refresh();
        $this->assertEquals('disetujui', $pengajuan->status);
        $this->assertStringStartsWith('BA-RB/', $pengajuan->nomor_ba);

        // Verifikasi TIDAK masuk ke BA Pengembalian
        $this->assertDatabaseMissing('pengembalians', [
            'pengajuan_id' => $pengajuan->id,
        ]);

        // Verifikasi mutasi BMN: kondisi berubah jadi Rusak Berat, data ruangan & penanggung jawab tetap utuh
        $bmn->refresh();
        $this->assertEquals('Ruang TU', $bmn->lokasi);
        $this->assertEquals('Budi Santoso', $bmn->penanggungjawab);
        $this->assertEquals('Rusak Berat', $bmn->kondisi);
        $this->assertFalse((bool) $bmn->is_dikembalikan);

        // Verifikasi terbitnya Laporan Kerusakan otomatis
        $this->assertDatabaseHas('laporan_kerusakans', [
            'bmn_id' => $bmn->id,
            'sumber' => 'pengajuan',
            'pengajuan_id' => $pengajuan->id,
            'tingkat' => 'Rusak Berat',
        ]);

        $laporan = LaporanKerusakan::where('bmn_id', $bmn->id)->first();
        $this->assertNotNull($laporan);
        $this->assertStringStartsWith('LK/', $laporan->nomor);

        // Pastikan relasi item ke laporan kerusakan tercatat
        $item->refresh();
        $this->assertEquals($laporan->id, $item->laporan_kerusakan_id);
    }

    public function test_operator_can_reject_submission_with_rejection_notes(): void
    {
        $pengajuan = $this->createPengajuan([
            'diajukan_oleh' => $this->regularUser->id,
            'status' => 'diajukan',
        ]);

        $payload = [
            'catatan_penolakan' => 'Kabel charger dan tas laptop belum disertakan saat penyerahan fisik.',
        ];

        $response = $this->actingAs($this->operator)->post(route('admin.pengajuan-pengembalian.tolak', $pengajuan), $payload);

        $response->assertRedirect(route('admin.pengajuan-pengembalian.show', $pengajuan));

        $pengajuan->refresh();
        $this->assertEquals('ditolak', $pengajuan->status);
        $this->assertEquals('Kabel charger dan tas laptop belum disertakan saat penyerahan fisik.', $pengajuan->catatan_penolakan);
    }

    public function test_operator_cannot_reject_without_notes_or_too_short(): void
    {
        $pengajuan = $this->createPengajuan([
            'diajukan_oleh' => $this->regularUser->id,
            'status' => 'diajukan',
        ]);

        $response = $this->actingAs($this->operator)->post(route('admin.pengajuan-pengembalian.tolak', $pengajuan), [
            'catatan_penolakan' => 'tes', // < 5 chars
        ]);

        $response->assertSessionHasErrors(['catatan_penolakan']);
        $this->assertEquals('diajukan', $pengajuan->fresh()->status);
    }

    public function test_cetak_ba_can_be_accessed_only_when_approved(): void
    {
        $pengajuan = $this->createPengajuan([
            'diajukan_oleh' => $this->regularUser->id,
            'status' => 'diajukan',
        ]);

        // Belum disetujui -> 403 Forbidden
        $response = $this->actingAs($this->operator)->get(route('admin.pengajuan-pengembalian.cetak-ba', $pengajuan));
        $response->assertForbidden();

        // Disetujui
        $pengajuan->update([
            'status' => 'disetujui',
            'nomor_ba' => 'BA-PGM/20261101/001',
            'disetujui_oleh' => $this->operator->id,
            'disetujui_pada' => now(),
        ]);

        // Operator bisa cetak
        $response = $this->actingAs($this->operator)->get(route('admin.pengajuan-pengembalian.cetak-ba', $pengajuan));
        $response->assertOk();
        $response->assertSee('BERITA ACARA PENGEMBALIAN BARANG MILIK NEGARA');
        $response->assertSee('BA-PGM/20261101/001');

        // Pemohon juga bisa cetak via user route
        $responseUser = $this->actingAs($this->regularUser)->get(route('pengajuan-pengembalian.cetak-ba', $pengajuan));
        $responseUser->assertOk();
        $responseUser->assertSee('BERITA ACARA PENGEMBALIAN BARANG MILIK NEGARA');

        // User lain tidak punya hak akses -> 403
        $responseOther = $this->actingAs($this->otherUser)->get(route('pengajuan-pengembalian.cetak-ba', $pengajuan));
        $responseOther->assertForbidden();
    }

    public function test_api_search_bmn_marks_ownership_and_filters_pending_items(): void
    {
        // BMN milik user
        $myBmn = $this->createSampleBmn([
            'nup' => '101',
            'nama_barang' => 'Laptop Asus ROG',
            'penanggung_jawab_id' => $this->pegawaiUser->id,
            'penanggungjawab' => 'Budi Santoso',
        ]);

        // BMN orang lain
        $otherBmn = $this->createSampleBmn([
            'nup' => '102',
            'nama_barang' => 'Printer HP LaserJet',
            'penanggung_jawab_id' => null,
            'penanggungjawab' => 'Orang Lain',
        ]);

        // BMN yang sedang pending dalam pengajuan lain
        $pendingBmn = $this->createSampleBmn([
            'nup' => '103',
            'nama_barang' => 'Monitor Samsung 24 Inch',
        ]);

        $pendingPengajuan = $this->createPengajuan([
            'diajukan_oleh' => $this->otherUser->id,
            'nama_pegawai' => $this->otherUser->name,
            'status' => 'diajukan',
        ]);

        PengajuanPengembalianItem::create([
            'pengajuan_id' => $pendingPengajuan->id,
            'bmn_id' => $pendingBmn->id,
            'kode_barang' => $pendingBmn->kode_barang,
            'nup' => (int) $pendingBmn->nup,
            'nama_barang' => $pendingBmn->nama_barang,
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($this->regularUser)->getJson(route('pengajuan-pengembalian.api-search-bmn'));

        $response->assertOk();
        $data = $response->json();

        // Monitor Samsung yang sedang pending TIDAK boleh muncul
        // BMN milik orang lain TIDAK boleh muncul untuk user biasa
        $bmnIds = array_column($data, 'id');
        $this->assertContains($myBmn->id, $bmnIds);
        $this->assertNotContains($otherBmn->id, $bmnIds);
        $this->assertNotContains($pendingBmn->id, $bmnIds);

        // BMN milik saya memiliki is_mine = true
        $myEntry = collect($data)->firstWhere('id', $myBmn->id);
        $this->assertTrue($myEntry['is_mine']);
    }

    public function test_operator_can_cancel_approved_pengajuan_and_reverts_bmn_assets(): void
    {
        $bmn = $this->createSampleBmn([
            'lokasi' => 'Ruang Laboratorium Kimia',
            'ruangan' => 'Ruang Laboratorium Kimia',
            'penanggungjawab' => 'Siti Aminah',
            'kondisi' => 'Baik',
            'is_dikembalikan' => false,
        ]);

        $pengajuan = $this->createPengajuan([
            'diajukan_oleh' => $this->regularUser->id,
            'nama_pegawai' => 'Siti Aminah',
            'nip_pegawai' => '198501012010012001',
            'status' => 'diajukan',
            'alasan' => 'rusak_berat',
        ]);

        $item = PengajuanPengembalianItem::create([
            'pengajuan_id' => $pengajuan->id,
            'bmn_id' => $bmn->id,
            'kode_barang' => $bmn->kode_barang,
            'nup' => (int) $bmn->nup,
            'nama_barang' => $bmn->nama_barang,
            'lokasi_awal' => 'Ruang Laboratorium Kimia',
            'kondisi_awal' => 'Baik',
            'kondisi' => 'Rusak Berat',
            'tanggal_temuan' => now()->toDateString(),
            'deskripsi_kerusakan' => 'Konsleting komponen utama',
        ]);

        // 1. Setujui oleh operator
        $this->actingAs($this->operator)->post(route('admin.pengajuan-pengembalian.setujui', $pengajuan));

        $pengajuan->refresh();
        $bmn->refresh();
        $this->assertEquals('disetujui', $pengajuan->status);
        $this->assertEquals('Ruang Laboratorium Kimia', $bmn->lokasi);
        $this->assertEquals('Rusak Berat', $bmn->kondisi);
        $this->assertEquals('Siti Aminah', $bmn->penanggungjawab);
        $this->assertFalse((bool) $bmn->is_dikembalikan);
        $this->assertDatabaseMissing('pengembalians', ['pengajuan_id' => $pengajuan->id]);
        $this->assertDatabaseHas('laporan_kerusakans', ['bmn_id' => $bmn->id]);

        // 2. Batalkan persetujuan oleh operator
        $cancelResponse = $this->actingAs($this->operator)->post(route('pengajuan-pengembalian.batalkan', $pengajuan));
        $cancelResponse->assertSessionHas('success');

        $pengajuan->refresh();
        $bmn->refresh();
        $this->assertEquals('dibatalkan', $pengajuan->status);
        $this->assertNull($pengajuan->nomor_ba);
        $this->assertEquals('Ruang Laboratorium Kimia', $bmn->lokasi);
        $this->assertEquals('Baik', $bmn->kondisi);
        $this->assertEquals('Siti Aminah', $bmn->penanggungjawab);
        $this->assertFalse($bmn->is_dikembalikan);
        $this->assertDatabaseMissing('pengembalians', ['pengajuan_id' => $pengajuan->id]);

        // Laporan Kerusakan otomatis harus ditolak / dihapus
        $item->refresh();
        if ($item->laporan_kerusakan_id) {
            $lk = LaporanKerusakan::find($item->laporan_kerusakan_id);
            $this->assertTrue($lk->dihapus || $lk->status === 'Ditolak');
        }
    }

    public function test_user_or_operator_can_uncancel_dibatalkan_pengajuan_restoring_to_diajukan(): void
    {
        $pengajuan = $this->createPengajuan([
            'diajukan_oleh' => $this->regularUser->id,
            'nama_pegawai' => $this->regularUser->name,
            'status' => 'dibatalkan',
        ]);

        // Batalkan pembatalan (kembali ke diajukan)
        $response = $this->actingAs($this->regularUser)->post(route('pengajuan-pengembalian.batalkan', $pengajuan));
        $response->assertSessionHas('success');

        $this->assertEquals('diajukan', $pengajuan->fresh()->status);
    }

    public function test_regular_user_cannot_cancel_approved_pengajuan(): void
    {
        $pengajuan = $this->createPengajuan([
            'diajukan_oleh' => $this->regularUser->id,
            'status' => 'disetujui',
            'nomor_ba' => 'BA-PGM/20261110/001',
        ]);

        $response = $this->actingAs($this->regularUser)->post(route('pengajuan-pengembalian.batalkan', $pengajuan));
        $response->assertSessionHas('error');

        $this->assertEquals('disetujui', $pengajuan->fresh()->status);
    }

    public function test_pengajuan_pengembalian_views_do_not_have_cetak_button(): void
    {
        $pengajuan = $this->createPengajuan([
            'diajukan_oleh' => $this->regularUser->id,
            'status' => 'disetujui',
            'nomor_ba' => 'BA-PGM/20261110/001',
        ]);

        // 1. User view index & show
        $resIndex = $this->actingAs($this->regularUser)->get(route('pengajuan-pengembalian.index'));
        $resIndex->assertOk();
        $resIndex->assertDontSee('Cetak Berita Acara');

        $resShow = $this->actingAs($this->regularUser)->get(route('pengajuan-pengembalian.show', $pengajuan));
        $resShow->assertOk();
        $resShow->assertDontSee('Cetak Berita Acara');

        // 2. Admin view show
        $resAdminShow = $this->actingAs($this->operator)->get(route('admin.pengajuan-pengembalian.show', $pengajuan));
        $resAdminShow->assertOk();
        $resAdminShow->assertDontSee('Cetak Berita Acara');
    }

    public function test_create_view_allows_both_pengembalian_biasa_and_rusak_berat(): void
    {
        $this->createSampleBmn(['kondisi' => 'Baik']);

        $response = $this->actingAs($this->regularUser)->get(route('pengajuan-pengembalian.create'));
        $response->assertOk();
        $response->assertSee('Jenis & Alasan Pengembalian', false);
        $response->assertSee('Pengembalian Barang Layak Pakai');
        $response->assertSee('Pelaporan Rusak Berat');
        $response->assertSee('value="pengembalian_biasa"', false);
        $response->assertSee('value="rusak_berat"', false);
    }

    public function test_available_bmns_for_pengembalian_only_returns_kondisi_baik(): void
    {
        $bmnBaik = $this->createSampleBmn([
            'nama_barang' => 'Laptop Baik',
            'nup' => '101',
            'kondisi' => 'Baik',
        ]);

        $bmnRusak = $this->createSampleBmn([
            'nama_barang' => 'Laptop Rusak Berat',
            'nup' => '102',
            'kondisi' => 'Rusak Berat',
        ]);

        $response = $this->actingAs($this->regularUser)->getJson(route('pengajuan-pengembalian.api-search-bmn'));
        $response->assertOk();

        $ids = collect($response->json())->pluck('id')->toArray();
        $this->assertContains($bmnBaik->id, $ids);
        $this->assertNotContains($bmnRusak->id, $ids);
    }

    public function test_laporan_kerusakan_index_displays_laporkan_rusak_berat_button(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('laporan-kerusakan.index'));
        $response->assertOk();
        $response->assertSee('Laporkan Rusak Berat');
        $response->assertSee(route('laporan-kerusakan.create'));
    }
}
