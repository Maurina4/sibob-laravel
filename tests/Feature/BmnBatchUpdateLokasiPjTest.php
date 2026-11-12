<?php

namespace Tests\Feature;

use App\Models\Bmn;
use App\Models\BmnLab;
use App\Models\BmnTik;
use App\Models\Lokasi;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BmnBatchUpdateLokasiPjTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_penanggung_jawab_update_works_and_syncs_tik_lab(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $pegawaiA = Pegawai::create([
            'nama' => 'Budi Santoso',
            'nip' => '198501012010011001',
            'jabatan' => 'Staff IT',
            'unit_kerja' => 'Subbag Tata Usaha',
        ]);

        $bmn = Bmn::create([
            'kode_barang' => 'TEST-001',
            'nup' => 1,
            'nama_barang' => 'Komputer Kerja',
            'jenis' => 'BMN-TIK',
            'penanggungjawab' => 'Pengelola BMN',
            'penanggungjawab_nip' => '-',
            'lokasi' => 'Gudang BMN',
            'kondisi' => 'Baik',
        ]);

        $bmnTik = BmnTik::create([
            'bmn_id' => $bmn->id,
            'kode_barang' => $bmn->kode_barang,
            'nup' => $bmn->nup,
            'nama_barang' => $bmn->nama_barang,
            'jenis' => 'BMN-TIK',
            'penanggungjawab' => 'Pengelola BMN',
            'lokasi' => 'Gudang BMN',
        ]);

        $response = $this->actingAs($user)->patch(route('bmns.update-penanggungjawab', $bmn), [
            'penanggungjawab' => $pegawaiA->nama,
            'penanggung_jawab_id' => $pegawaiA->id,
            'penanggungjawab_nip' => $pegawaiA->nip,
            'page' => 2,
            'search' => 'Komputer',
        ]);

        $response->assertRedirect(route('bmns.index', [
            'page' => 2,
            'search' => 'Komputer',
        ]));
        $response->assertSessionHas('success', 'Penanggung jawab BMN berhasil diperbarui.');

        $bmn->refresh();
        $this->assertEquals('Budi Santoso', $bmn->penanggungjawab);
        $this->assertEquals($pegawaiA->id, $bmn->penanggung_jawab_id);
        $this->assertEquals('198501012010011001', $bmn->penanggungjawab_nip);

        $bmnTik->refresh();
        $this->assertEquals('Budi Santoso', $bmnTik->penanggungjawab);
    }

    public function test_batch_update_lokasi_and_pj_works_for_multiple_bmns(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $pegawaiA = Pegawai::create([
            'nama' => 'Ahmad Dahlan',
            'nip' => '199001012015011002',
            'jabatan' => 'Pranata Komputer',
        ]);

        $pegawaiB = Pegawai::create([
            'nama' => 'Siti Aminah',
            'nip' => '199202022016022001',
            'jabatan' => 'Arsiparis',
        ]);

        $lokasiA = Lokasi::create([
            'kode_ruangan' => 'A1.01',
            'nama' => 'Ruang Server',
        ]);

        $lokasiB = Lokasi::create([
            'kode_ruangan' => 'B2.02',
            'nama' => 'Ruang TU',
        ]);

        $bmn1 = Bmn::create([
            'kode_barang' => 'BMN-01',
            'nup' => 1,
            'nama_barang' => 'Laptop Dell',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Gudang Lama',
            'penanggungjawab' => 'Pengelola BMN',
        ]);

        $bmn2 = Bmn::create([
            'kode_barang' => 'BMN-02',
            'nup' => 2,
            'nama_barang' => 'Meja Kerja',
            'jenis' => 'BMN-Umum',
            'lokasi' => 'Ruang TU',
            'penanggungjawab' => 'Pengelola BMN',
        ]);

        $bmn3 = Bmn::create([
            'kode_barang' => 'BMN-03',
            'nup' => 3,
            'nama_barang' => 'Mikroskop Uji',
            'jenis' => 'BMN-LAB',
            'lokasi' => 'Gudang Lama',
            'penanggungjawab' => 'Pengelola BMN',
        ]);
        $bmnLab = BmnLab::create([
            'bmn_id' => $bmn3->id,
            'kode_barang' => $bmn3->kode_barang,
            'nup' => $bmn3->nup,
            'nama_barang' => $bmn3->nama_barang,
            'lokasi' => 'Gudang Lama',
            'penanggungjawab' => 'Pengelola BMN',
        ]);

        // Kirim batch update:
        // bmn1: hanya ubah lokasi
        // bmn2: hanya ubah PJ
        // bmn3: ubah lokasi dan PJ
        $response = $this->actingAs($user)->post(route('bmns.batch-update'), [
            'items' => [
                [
                    'id' => $bmn1->id,
                    'lokasi' => 'A1.01 - Ruang Server',
                ],
                [
                    'id' => $bmn2->id,
                    'penanggungjawab' => $pegawaiA->nama,
                    'penanggung_jawab_id' => $pegawaiA->id,
                    'penanggungjawab_nip' => $pegawaiA->nip,
                ],
                [
                    'id' => $bmn3->id,
                    'lokasi' => 'B2.02 - Ruang TU',
                    'penanggungjawab' => $pegawaiB->nama,
                    'penanggung_jawab_id' => $pegawaiB->id,
                    'penanggungjawab_nip' => $pegawaiB->nip,
                ],
            ],
            'page' => 1,
            'search' => '',
        ]);

        $response->assertRedirect(route('bmns.index', ['page' => 1]));
        $response->assertSessionHas('success', 'Berhasil memperbarui 3 data BMN.');

        $bmn1->refresh();
        $this->assertEquals('Ruang Server', $bmn1->lokasi);
        $this->assertEquals($lokasiA->id, $bmn1->lokasi_id);

        $bmn2->refresh();
        $this->assertEquals('Ahmad Dahlan', $bmn2->penanggungjawab);
        $this->assertEquals($pegawaiA->id, $bmn2->penanggung_jawab_id);
        $this->assertEquals($pegawaiA->nip, $bmn2->penanggungjawab_nip);

        $bmn3->refresh();
        $this->assertEquals('Ruang TU', $bmn3->lokasi);
        $this->assertEquals($lokasiB->id, $bmn3->lokasi_id);
        $this->assertEquals('Siti Aminah', $bmn3->penanggungjawab);
        $this->assertEquals($pegawaiB->id, $bmn3->penanggung_jawab_id);
        $this->assertEquals($pegawaiB->nip, $bmn3->penanggungjawab_nip);

        $bmnLab->refresh();
        $this->assertEquals('Ruang TU', $bmnLab->lokasi);
        $this->assertEquals('Siti Aminah', $bmnLab->penanggungjawab);
    }

    public function test_batch_update_supports_json_string_input(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $bmn = Bmn::create([
            'kode_barang' => 'BMN-JSON',
            'nup' => 99,
            'nama_barang' => 'Proyektor Ruang Rapat',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang 1',
            'penanggungjawab' => 'Pengelola BMN',
        ]);

        $response = $this->actingAs($user)->post(route('bmns.batch-update'), [
            'items' => json_encode([
                [
                    'id' => $bmn->id,
                    'lokasi' => 'Ruang Rapat Utama',
                ],
            ]),
        ]);

        $response->assertRedirect(route('bmns.index'));
        $response->assertSessionHas('success', 'Berhasil memperbarui 1 data BMN.');

        $bmn->refresh();
        $this->assertEquals('Ruang Rapat Utama', $bmn->lokasi);
    }

    public function test_batch_update_fails_validation_when_items_empty(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        $response = $this->actingAs($user)->post(route('bmns.batch-update'), [
            'items' => [],
        ]);

        $response->assertSessionHasErrors(['items']);
    }

    public function test_bmn_index_renders_batch_controls_and_pegawai_dropdown(): void
    {
        $user = User::factory()->create(['role' => 'Operator']);

        Pegawai::create([
            'nama' => 'Dra. Hj. Nurul Hidayati',
            'nip' => '197005121995032001',
            'jabatan' => 'Kepala Lab',
        ]);

        Bmn::create([
            'kode_barang' => 'RENDER-01',
            'nup' => 1,
            'nama_barang' => 'Mesin Spektrofotometer',
            'jenis' => 'BMN-LAB',
            'lokasi' => 'Lab Instrumen',
            'penanggungjawab' => 'Dra. Hj. Nurul Hidayati',
        ]);

        $response = $this->actingAs($user)->get(route('bmns.index'));

        $response->assertOk();
        $response->assertSee('window.__MASTER_PEGAWAIS__', false);
        $response->assertSee('bmnTableBatchManager()', false);
        $response->assertSee('bmnPjCell(', false);
        $response->assertSee('Simpan Semua Perubahan');
        $response->assertSee(route('bmns.batch-update'));
        $response->assertSee(route('bmns.update-penanggungjawab', 1));
    }
}
