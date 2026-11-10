<?php

namespace Tests\Feature;

use App\Models\Bmn;
use App\Models\BmnAngkutan;
use App\Models\DataTerhapus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BmnAngkutanFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function createOperator(): User
    {
        return User::factory()->create([
            'role' => 'Operator',
        ]);
    }

    public function test_operator_can_view_bmn_angkutan_index(): void
    {
        $user = $this->createOperator();

        BmnAngkutan::create([
            'kode_barang' => '3020101001',
            'nup' => 1,
            'nama_barang' => 'Toyota Kijang Innova 2.0 V A/T',
            'merek' => 'Toyota',
            'no_polisi' => 'DA 1234 AB',
            'tahun_perolehan' => 2022,
            'nilai_perolehan' => 350000000,
            'lokasi' => 'Garasi / Parkir BMN',
            'penanggungjawab' => 'Pengelola BMN',
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($user)->get(route('bmns-angkutan.index'));

        $response->assertStatus(200);
        $response->assertSee('Daftar Alat Angkutan Bermotor');
        $response->assertSee('Toyota Kijang Innova 2.0 V A/T');
        $response->assertSee('DA 1234 AB');
        $response->assertSee('Tambah Kendaraan');
    }

    public function test_operator_can_view_create_form(): void
    {
        $user = $this->createOperator();

        $response = $this->actingAs($user)->get(route('bmns-angkutan.create'));

        $response->assertStatus(200);
        $response->assertSee('Tambah Data Alat Angkutan Bermotor');
        $response->assertSee('Nomor Polisi (Plat Nomor)');
    }

    public function test_operator_can_create_bmn_angkutan_with_no_polisi_and_syncs_to_bmns(): void
    {
        $user = $this->createOperator();

        $postData = [
            'kode_barang' => '3020101001',
            'nup' => 1,
            'nama_barang' => 'Toyota Hilux Double Cabin 2.4G',
            'merek' => 'Toyota',
            'no_polisi' => 'da 1024 bp', // testing lowercase auto-uppercasing
            'tahun_perolehan' => 2023,
            'nilai_perolehan' => '480000000',
            'lokasi' => 'Garasi BMN',
            'penanggungjawab' => 'Budi Santoso',
            'kondisi' => 'Baik',
        ];

        $response = $this->actingAs($user)->post(route('bmns-angkutan.store'), $postData);

        $response->assertRedirect(route('bmns-angkutan.index'));

        // Pastikan tersimpan di bmns_angkutan dengan plat nomor huruf besar
        $this->assertDatabaseHas('bmns_angkutan', [
            'kode_barang' => '3020101001',
            'nup' => 1,
            'nama_barang' => 'Toyota Hilux Double Cabin 2.4G',
            'no_polisi' => 'DA 1024 BP',
            'dihapus' => false,
        ]);

        // Pastikan tersinkron ke tabel master bmns
        $this->assertDatabaseHas('bmns', [
            'kode_barang' => '3020101001',
            'nup' => '1',
            'nama_barang' => 'Toyota Hilux Double Cabin 2.4G',
            'jenis' => 'BMN-ANGKUTAN',
            'no_polisi' => 'DA 1024 BP',
            'dihapus' => false,
        ]);

        $angkutan = BmnAngkutan::where('kode_barang', '3020101001')->first();
        $this->assertNotNull($angkutan->bmn_id);
    }

    public function test_validation_requires_no_polisi(): void
    {
        $user = $this->createOperator();

        $postData = [
            'kode_barang' => '3020101002',
            'nup' => 1,
            'nama_barang' => 'Mitsubishi Pajero Sport',
            'no_polisi' => '', // kosong
            'lokasi' => 'Garasi BMN',
            'penanggungjawab' => 'Budi Santoso',
        ];

        $response = $this->actingAs($user)->post(route('bmns-angkutan.store'), $postData);

        $response->assertSessionHasErrors(['no_polisi']);
        $this->assertDatabaseMissing('bmns_angkutan', [
            'nama_barang' => 'Mitsubishi Pajero Sport',
        ]);
    }

    public function test_operator_can_update_bmn_angkutan_and_no_polisi(): void
    {
        $user = $this->createOperator();

        $angkutan = BmnAngkutan::create([
            'kode_barang' => '3020101003',
            'nup' => 1,
            'nama_barang' => 'Daihatsu Gran Max Blind Van',
            'merek' => 'Daihatsu',
            'no_polisi' => 'DA 5555 XY',
            'tahun_perolehan' => 2020,
            'nilai_perolehan' => 160000000,
            'lokasi' => 'Garasi BMN',
            'penanggungjawab' => 'Pengelola BMN',
            'kondisi' => 'Baik',
        ]);

        $bmn = Bmn::create([
            'kode_barang' => '3020101003',
            'nup' => '1',
            'nama_barang' => 'Daihatsu Gran Max Blind Van',
            'merek' => 'Daihatsu',
            'no_polisi' => 'DA 5555 XY',
            'jenis' => 'BMN-ANGKUTAN',
            'lokasi' => 'Garasi BMN',
            'penanggungjawab' => 'Pengelola BMN',
            'kondisi' => 'Baik',
        ]);

        $angkutan->update(['bmn_id' => $bmn->id]);

        $updateData = [
            'kode_barang' => '3020101003',
            'nup' => 1,
            'nama_barang' => 'Daihatsu Gran Max Blind Van (Operasional)',
            'merek' => 'Daihatsu',
            'no_polisi' => 'DA 8888 ZZ',
            'tahun_perolehan' => 2020,
            'nilai_perolehan' => 160000000,
            'lokasi' => 'Garasi Utama',
            'penanggungjawab' => 'Pengelola BMN',
            'kondisi' => 'Baik',
        ];

        $response = $this->actingAs($user)->put(route('bmns-angkutan.update', $angkutan), $updateData);

        $response->assertRedirect(route('bmns-angkutan.index'));

        $this->assertDatabaseHas('bmns_angkutan', [
            'id' => $angkutan->id,
            'nama_barang' => 'Daihatsu Gran Max Blind Van (Operasional)',
            'no_polisi' => 'DA 8888 ZZ',
            'lokasi' => 'Garasi Utama',
        ]);

        $this->assertDatabaseHas('bmns', [
            'id' => $bmn->id,
            'nama_barang' => 'Daihatsu Gran Max Blind Van (Operasional)',
            'no_polisi' => 'DA 8888 ZZ',
            'lokasi' => 'Garasi Utama',
        ]);
    }

    public function test_operator_can_soft_delete_bmn_angkutan(): void
    {
        $user = $this->createOperator();

        $bmn = Bmn::create([
            'kode_barang' => '3020101004',
            'nup' => '1',
            'nama_barang' => 'Honda Vario 125',
            'merek' => 'Honda',
            'no_polisi' => 'DA 1111 AB',
            'jenis' => 'BMN-ANGKUTAN',
            'lokasi' => 'Garasi BMN',
            'penanggungjawab' => 'Pengelola BMN',
            'kondisi' => 'Baik',
            'dihapus' => false,
        ]);

        $angkutan = BmnAngkutan::create([
            'bmn_id' => $bmn->id,
            'kode_barang' => '3020101004',
            'nup' => 1,
            'nama_barang' => 'Honda Vario 125',
            'merek' => 'Honda',
            'no_polisi' => 'DA 1111 AB',
            'lokasi' => 'Garasi BMN',
            'penanggungjawab' => 'Pengelola BMN',
            'kondisi' => 'Baik',
            'dihapus' => false,
        ]);

        $response = $this->actingAs($user)->delete(route('bmns-angkutan.destroy', $angkutan));

        $response->assertRedirect(route('bmns-angkutan.index'));

        $this->assertDatabaseHas('bmns_angkutan', [
            'id' => $angkutan->id,
            'dihapus' => true,
        ]);

        $this->assertDatabaseHas('bmns', [
            'id' => $bmn->id,
            'dihapus' => true,
        ]);

        $this->assertDatabaseHas('data_terhapus', [
            'jenis' => 'Alat Angkutan Bermotor',
            'data_id' => $angkutan->id,
            'bmn_id' => $bmn->id,
        ]);
    }

    public function test_dashboard_displays_alat_angkutan_bermotor(): void
    {
        $user = $this->createOperator();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('4 Kategori');
        $response->assertSee('Alat Angkutan Bermotor');
    }

    public function test_general_daftar_bmn_remains_unmodified_without_no_polisi_input(): void
    {
        $user = $this->createOperator();

        $response = $this->actingAs($user)->get(route('bmns.create'));

        $response->assertStatus(200);
        // Form Daftar BMN umum TIDAK boleh memiliki input No. Polisi
        $response->assertDontSee('name="no_polisi"', false);
    }
}
