<?php

namespace Tests\Feature;

use App\Models\Bmn;
use App\Models\BmnLab;
use App\Models\BmnTik;
use App\Models\Lokasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KodeBarangValidationFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $operatorUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operatorUser = User::factory()->create([
            'role' => 'Operator',
        ]);

        Lokasi::create([
            'kode_ruangan' => 'LOK-01',
            'nama' => 'Ruang Server TIK',
        ]);
    }

    public function test_bmn_store_succeeds_with_pure_10_digits(): void
    {
        $response = $this->actingAs($this->operatorUser)->post(route('bmns.store'), [
            'kode_barang' => '3050104001',
            'nup' => '1',
            'nama_barang' => 'Server Rack',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        $response->assertRedirect(route('bmns.index'));
        $this->assertDatabaseHas('bmns', [
            'kode_barang' => '3050104001',
            'nup' => '1',
        ]);
    }

    public function test_bmn_store_auto_sanitizes_dots_dashes_and_spaces(): void
    {
        $response = $this->actingAs($this->operatorUser)->post(route('bmns.store'), [
            'kode_barang' => '3.05.01.04.002',
            'nup' => '2',
            'nama_barang' => 'Router Core',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        $response->assertRedirect(route('bmns.index'));
        $this->assertDatabaseHas('bmns', [
            'kode_barang' => '3050104002',
            'nup' => '2',
        ]);
    }

    public function test_bmn_store_fails_when_less_than_10_digits(): void
    {
        $response = $this->actingAs($this->operatorUser)->post(route('bmns.store'), [
            'kode_barang' => '305010400', // 9 digit
            'nup' => '3',
            'nama_barang' => 'Switch',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        $response->assertSessionHasErrors(['kode_barang']);
        $this->assertEquals(0, Bmn::count());
    }

    public function test_bmn_store_fails_when_more_than_10_digits(): void
    {
        $response = $this->actingAs($this->operatorUser)->post(route('bmns.store'), [
            'kode_barang' => '305010400011', // 12 digit
            'nup' => '4',
            'nama_barang' => 'Switch Extra',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        $response->assertSessionHasErrors(['kode_barang']);
        $this->assertEquals(0, Bmn::count());
    }

    public function test_bmn_store_fails_when_contains_alphabet(): void
    {
        $response = $this->actingAs($this->operatorUser)->post(route('bmns.store'), [
            'kode_barang' => '305010400A',
            'nup' => '5',
            'nama_barang' => 'Access Point',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        $response->assertSessionHasErrors(['kode_barang']);
        $this->assertEquals(0, Bmn::count());
    }

    public function test_bmn_tik_store_and_update_sanitizes_and_enforces_10_digits(): void
    {
        // Store via bmns-tik.store with dots
        $storeResponse = $this->actingAs($this->operatorUser)->post(route('bmns-tik.store'), [
            'kode_barang' => '3.08.03.05.001',
            'nup' => '10',
            'nama_barang' => 'PC Laboratorium TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
            'jenis' => 'PC',
        ]);

        $storeResponse->assertRedirect(route('bmns-tik.index'));
        $this->assertDatabaseHas('bmns', [
            'kode_barang' => '3080305001',
            'nup' => '10',
        ]);
        $this->assertDatabaseHas('bmns_tik', [
            'kode_barang' => '3080305001',
            'nama_barang' => 'PC Laboratorium TIK',
        ]);

        $bmn = Bmn::where('kode_barang', '3080305001')->first();
        $bmnTik = BmnTik::where('bmn_id', $bmn->id)->first();

        // Update via bmns-tik.update with dashes
        $updateResponse = $this->actingAs($this->operatorUser)->put(route('bmns-tik.update', $bmnTik->id), [
            'kode_barang' => '3-08-03-05-001',
            'nup' => '10',
            'nama_barang' => 'PC Laboratorium TIK Updated',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
            'jenis' => 'PC',
        ]);

        $updateResponse->assertRedirect(route('bmns-tik.index'));
        $this->assertDatabaseHas('bmns', [
            'id' => $bmn->id,
            'kode_barang' => '3080305001',
            'nama_barang' => 'PC Laboratorium TIK Updated',
        ]);
        $this->assertDatabaseHas('bmns_tik', [
            'id' => $bmnTik->id,
            'kode_barang' => '3080305001',
            'nama_barang' => 'PC Laboratorium TIK Updated',
        ]);
    }

    public function test_bmn_lab_store_and_update_sanitizes_and_enforces_10_digits(): void
    {
        // Store via bmns-lab.store with dots
        $storeResponse = $this->actingAs($this->operatorUser)->post(route('bmns-lab.store'), [
            'kode_barang' => '3.08.01.01.009',
            'nup' => '20',
            'nama_barang' => 'Spectrophotometer',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Laboran Kimia',
            'kondisi' => 'Baik',
            'kategori_lab' => 'Kimia',
        ]);

        $storeResponse->assertRedirect(route('bmns-lab.index'));
        $this->assertDatabaseHas('bmns', [
            'kode_barang' => '3080101009',
            'nup' => '20',
        ]);

        $bmn = Bmn::where('kode_barang', '3080101009')->first();
        $bmnLab = BmnLab::where('bmn_id', $bmn->id)->first();

        // Update via bmns-lab.update with pure 10 digits
        $updateResponse = $this->actingAs($this->operatorUser)->put(route('bmns-lab.update', $bmnLab->id), [
            'kode_barang' => '3080101009',
            'nup' => '20',
            'nama_barang' => 'Spectrophotometer UV-Vis',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Laboran Kimia',
            'kondisi' => 'Baik',
            'kategori_lab' => 'Kimia',
        ]);

        $updateResponse->assertRedirect(route('bmns-lab.index'));
        $this->assertDatabaseHas('bmns', [
            'id' => $bmn->id,
            'nama_barang' => 'Spectrophotometer UV-Vis',
        ]);
    }
}
