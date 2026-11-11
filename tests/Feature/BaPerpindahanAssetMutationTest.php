<?php

namespace Tests\Feature;

use App\Models\BaPerpindahan;
use App\Models\Bmn;
use App\Models\BmnAngkutan;
use App\Models\BmnLab;
use App\Models\BmnTik;
use App\Models\Lokasi;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaPerpindahanAssetMutationTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;
    protected Pegawai $pegawaiAsal;
    protected Pegawai $pegawaiTujuan;
    protected Lokasi $lokasiAsal;
    protected Lokasi $lokasiTujuan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::factory()->create([
            'name' => 'Operator SIBOB',
            'role' => 'Operator',
        ]);

        $this->pegawaiAsal = Pegawai::create([
            'nama' => 'Budi Santoso',
            'nip' => '198001012005011001',
            'jabatan' => 'Staff TU',
            'unit_kerja' => 'Tata Usaha',
        ]);

        $this->pegawaiTujuan = Pegawai::create([
            'nama' => 'Siti Nurhaliza, S.Si.',
            'nip' => '199008202015032003',
            'jabatan' => 'Pranata Laboratorium',
            'unit_kerja' => 'Laboratorium Kimia',
        ]);

        $this->lokasiAsal = Lokasi::create([
            'kode_ruangan' => 'TU.01',
            'nama' => 'Ruang Tata Usaha',
        ]);

        $this->lokasiTujuan = Lokasi::create([
            'kode_ruangan' => 'LAB.01',
            'nama' => 'Ruang Laboratorium Kimia',
        ]);
    }

    public function test_approving_ba_perpindahan_updates_bmn_and_extension_models_location_and_penanggungjawab(): void
    {
        $bmn = Bmn::create([
            'kode_barang' => '3050201001',
            'nup' => 1,
            'nama_barang' => 'Server & Alat Ukur Terpadu',
            'jenis' => 'BMN-TIK & LAB',
            'kondisi' => 'Baik',
            'lokasi' => $this->lokasiAsal->nama,
            'lokasi_id' => $this->lokasiAsal->id,
            'penanggungjawab' => $this->pegawaiAsal->nama,
            'penanggung_jawab_id' => $this->pegawaiAsal->id,
            'penanggungjawab_nip' => $this->pegawaiAsal->nip,
        ]);

        $bmnTik = BmnTik::create([
            'bmn_id' => $bmn->id,
            'kode_barang' => $bmn->kode_barang,
            'nup' => $bmn->nup,
            'nama_barang' => $bmn->nama_barang,
            'lokasi' => $this->lokasiAsal->nama,
            'penanggungjawab' => $this->pegawaiAsal->nama,
            'kondisi' => 'Baik',
        ]);

        $bmnLab = BmnLab::create([
            'bmn_id' => $bmn->id,
            'kode_barang' => $bmn->kode_barang,
            'nup' => $bmn->nup,
            'nama_barang' => $bmn->nama_barang,
            'lokasi' => $this->lokasiAsal->nama,
            'penanggungjawab' => $this->pegawaiAsal->nama,
            'kondisi' => 'Baik',
        ]);

        $ba = BaPerpindahan::create([
            'nomor_ba' => 'BA-MUT/TEST/001',
            'tanggal' => now()->toDateString(),
            'bmn_id' => $bmn->id,
            'nama_barang' => $bmn->nama_barang,
            'dari' => $this->lokasiAsal->nama,
            'ke' => $this->lokasiTujuan->nama,
            'lokasi_asal_id' => $this->lokasiAsal->id,
            'lokasi_tujuan_id' => $this->lokasiTujuan->id,
            'pihak_pertama' => $this->pegawaiAsal->nama,
            'pihak_pertama_id' => $this->pegawaiAsal->id,
            'pihak_kedua' => "{$this->pegawaiTujuan->nama} ({$this->pegawaiTujuan->nip})",
            'alasan' => 'Dibutuhkan untuk penelitian kimia',
            'pengaju_email' => $this->operator->email,
            'pengaju_nama' => $this->operator->name,
            'status' => 'Diajukan',
        ]);

        // Operator approve BA Perpindahan
        $response = $this->actingAs($this->operator)->patch(route('ba-perpindahan.process-decision', $ba), [
            'pihak_kedua' => "{$this->pegawaiTujuan->nama} ({$this->pegawaiTujuan->nip})",
            'keputusan' => 'Setuju',
            'catatan' => 'Disetujui untuk mutasi aset',
        ]);

        $response->assertRedirect(route('ba-perpindahan.show', $ba));
        $this->assertEquals('Disetujui', $ba->fresh()->status);

        // Verifikasi Master BMN otomatis berpindah lokasi dan penanggung jawab
        $bmn->refresh();
        $this->assertEquals($this->lokasiTujuan->nama, $bmn->lokasi);
        $this->assertEquals($this->lokasiTujuan->id, $bmn->lokasi_id);
        $this->assertEquals("{$this->pegawaiTujuan->nama} ({$this->pegawaiTujuan->nip})", $bmn->penanggungjawab);
        $this->assertEquals($this->pegawaiTujuan->id, $bmn->penanggung_jawab_id);
        $this->assertEquals($this->pegawaiTujuan->nip, $bmn->penanggungjawab_nip);

        // Verifikasi Tabel Ekstensi BMN-TIK & BMN-LAB ikut tersinkronisasi
        $bmnTik->refresh();
        $this->assertEquals($this->lokasiTujuan->nama, $bmnTik->lokasi);
        $this->assertEquals("{$this->pegawaiTujuan->nama} ({$this->pegawaiTujuan->nip})", $bmnTik->penanggungjawab);

        $bmnLab->refresh();
        $this->assertEquals($this->lokasiTujuan->nama, $bmnLab->lokasi);
        $this->assertEquals("{$this->pegawaiTujuan->nama} ({$this->pegawaiTujuan->nip})", $bmnLab->penanggungjawab);
    }

    public function test_cancelling_approved_ba_perpindahan_reverts_bmn_and_extension_models_to_original(): void
    {
        $bmn = Bmn::create([
            'kode_barang' => '3050201002',
            'nup' => 1,
            'nama_barang' => 'Workstation Analitik',
            'jenis' => 'BMN-TIK',
            'kondisi' => 'Baik',
            'lokasi' => $this->lokasiAsal->nama,
            'lokasi_id' => $this->lokasiAsal->id,
            'penanggungjawab' => $this->pegawaiAsal->nama,
            'penanggung_jawab_id' => $this->pegawaiAsal->id,
            'penanggungjawab_nip' => $this->pegawaiAsal->nip,
        ]);

        $bmnTik = BmnTik::create([
            'bmn_id' => $bmn->id,
            'kode_barang' => $bmn->kode_barang,
            'nup' => $bmn->nup,
            'nama_barang' => $bmn->nama_barang,
            'lokasi' => $this->lokasiAsal->nama,
            'penanggungjawab' => $this->pegawaiAsal->nama,
            'kondisi' => 'Baik',
        ]);

        $ba = BaPerpindahan::create([
            'nomor_ba' => 'BA-MUT/TEST/002',
            'tanggal' => now()->toDateString(),
            'bmn_id' => $bmn->id,
            'nama_barang' => $bmn->nama_barang,
            'dari' => $this->lokasiAsal->nama,
            'ke' => $this->lokasiTujuan->nama,
            'lokasi_asal_id' => $this->lokasiAsal->id,
            'lokasi_tujuan_id' => $this->lokasiTujuan->id,
            'pihak_pertama' => $this->pegawaiAsal->nama,
            'pihak_pertama_id' => $this->pegawaiAsal->id,
            'pihak_kedua' => "{$this->pegawaiTujuan->nama} ({$this->pegawaiTujuan->nip})",
            'alasan' => 'Mutasi sementara',
            'pengaju_email' => $this->operator->email,
            'pengaju_nama' => $this->operator->name,
            'status' => 'Diajukan',
        ]);

        // 1. Setujui perpindahan
        $this->actingAs($this->operator)->patch(route('ba-perpindahan.process-decision', $ba), [
            'pihak_kedua' => "{$this->pegawaiTujuan->nama} ({$this->pegawaiTujuan->nip})",
            'keputusan' => 'Setuju',
            'catatan' => 'Disetujui',
        ]);

        $this->assertEquals('Disetujui', $ba->fresh()->status);
        $this->assertEquals($this->lokasiTujuan->nama, $bmn->fresh()->lokasi);
        $this->assertEquals("{$this->pegawaiTujuan->nama} ({$this->pegawaiTujuan->nip})", $bmn->fresh()->penanggungjawab);

        // 2. Batalkan keputusan persetujuan
        $cancelResponse = $this->actingAs($this->operator)->patch(route('ba-perpindahan.cancel-decision', $ba), [
            'catatan' => 'Pembatalan serah terima barang',
        ]);

        $cancelResponse->assertSessionHas('success');
        $this->assertEquals('Dibatalkan', $ba->fresh()->status);

        // 3. Verifikasi Master BMN kembali ke lokasi dan penanggung jawab SEMULA
        $bmn->refresh();
        $this->assertEquals($this->lokasiAsal->nama, $bmn->lokasi);
        $this->assertEquals($this->lokasiAsal->id, $bmn->lokasi_id);
        $this->assertEquals($this->pegawaiAsal->nama, $bmn->penanggungjawab);
        $this->assertEquals($this->pegawaiAsal->id, $bmn->penanggung_jawab_id);
        $this->assertEquals($this->pegawaiAsal->nip, $bmn->penanggungjawab_nip);

        // 4. Verifikasi BMN TIK juga kembali ke lokasi dan penanggung jawab SEMULA
        $bmnTik->refresh();
        $this->assertEquals($this->lokasiAsal->nama, $bmnTik->lokasi);
        $this->assertEquals($this->pegawaiAsal->nama, $bmnTik->penanggungjawab);
    }
}
