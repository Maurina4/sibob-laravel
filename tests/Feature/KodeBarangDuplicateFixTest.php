<?php

namespace Tests\Feature;

use App\Models\Bmn;
use App\Models\BmnLab;
use App\Models\BmnTik;
use App\Models\Lokasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KodeBarangDuplicateFixTest extends TestCase
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
        Lokasi::create([
            'kode_ruangan' => 'LOK-02',
            'nama' => 'Laboratorium Kimia',
        ]);
    }

    public function test_can_update_bmn_when_another_bmn_shares_same_kode_barang_with_different_nup(): void
    {
        // 2 items sharing the same kode_barang but different NUP
        $bmn1 = Bmn::create([
            'kode_barang' => '3050104001',
            'nup' => '1',
            'nama_barang' => 'PC All-in-One Dell 1',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        $bmn2 = Bmn::create([
            'kode_barang' => '3050104001',
            'nup' => '2',
            'nama_barang' => 'PC All-in-One Dell 2',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        // Edit bmn1 without changing kode_barang or nup (e.g. changing kondisi / nama_barang)
        $response = $this->actingAs($this->operatorUser)->put(route('bmns.update', $bmn1), [
            'kode_barang' => '3050104001',
            'nup' => '1',
            'nama_barang' => 'PC All-in-One Dell 1 Renamed',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Rusak Ringan',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('bmns.index'));
        $this->assertDatabaseHas('bmns', [
            'id' => $bmn1->id,
            'nama_barang' => 'PC All-in-One Dell 1 Renamed',
            'kondisi' => 'Rusak Ringan',
        ]);
    }

    public function test_cannot_update_bmn_when_colliding_with_existing_kode_barang_and_nup(): void
    {
        $bmn1 = Bmn::create([
            'kode_barang' => '3050104001',
            'nup' => '1',
            'nama_barang' => 'PC Dell 1',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        $bmn2 = Bmn::create([
            'kode_barang' => '3050104001',
            'nup' => '2',
            'nama_barang' => 'PC Dell 2',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        // Attempt to change bmn1's NUP to '2' (collides with bmn2)
        $response = $this->actingAs($this->operatorUser)->put(route('bmns.update', $bmn1), [
            'kode_barang' => '3050104001',
            'nup' => '2',
            'nama_barang' => 'PC Dell 1',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        $response->assertSessionHasErrors(['kode_barang']);
    }

    public function test_can_store_bmn_sharing_kode_barang_with_different_nup(): void
    {
        Bmn::create([
            'kode_barang' => '3050104001',
            'nup' => '1',
            'nama_barang' => 'PC Dell 1',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        // Add 2nd PC with same kode_barang and NUP 2
        $response = $this->actingAs($this->operatorUser)->post(route('bmns.store'), [
            'kode_barang' => '3050104001',
            'nup' => '2',
            'nama_barang' => 'PC Dell 2',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('bmns.index'));
        $this->assertDatabaseHas('bmns', [
            'kode_barang' => '3050104001',
            'nup' => '2',
        ]);
    }

    public function test_can_update_bmn_tik_sharing_kode_barang_with_different_nup(): void
    {
        $bmn1 = Bmn::create([
            'kode_barang' => '3090405012',
            'nup' => '1',
            'nama_barang' => 'Switch Hub 1',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);
        $tik1 = BmnTik::create([
            'bmn_id' => $bmn1->id,
            'kode_barang' => '3090405012',
            'nup' => 1,
            'nama_barang' => 'Switch Hub 1',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        $bmn2 = Bmn::create([
            'kode_barang' => '3090405012',
            'nup' => '2',
            'nama_barang' => 'Switch Hub 2',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);
        $tik2 = BmnTik::create([
            'bmn_id' => $bmn2->id,
            'kode_barang' => '3090405012',
            'nup' => 2,
            'nama_barang' => 'Switch Hub 2',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        // Update tik1
        $response = $this->actingAs($this->operatorUser)->put(route('bmns-tik.update', $tik1), [
            'kode_barang' => '3090405012',
            'nup' => 1,
            'nama_barang' => 'Switch Hub 1 Updated',
            'lokasi' => 'Ruang Server TIK',
            'penanggungjawab' => 'Staff IT',
            'kondisi' => 'Baik',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('bmns-tik.index'));
    }

    public function test_can_update_bmn_lab_sharing_kode_barang_with_different_nup(): void
    {
        $bmn1 = Bmn::create([
            'kode_barang' => '3080101005',
            'nup' => '1',
            'nama_barang' => 'Mikroskop 1',
            'jenis' => 'BMN-LAB',
            'lokasi' => 'Laboratorium Kimia',
            'penanggungjawab' => 'Laboran',
            'kondisi' => 'Baik',
        ]);
        $lab1 = BmnLab::create([
            'bmn_id' => $bmn1->id,
            'kode_barang' => '3080101005',
            'nup' => 1,
            'nama_barang' => 'Mikroskop 1',
            'lokasi' => 'Laboratorium Kimia',
            'penanggungjawab' => 'Laboran',
            'kondisi' => 'Baik',
        ]);

        $bmn2 = Bmn::create([
            'kode_barang' => '3080101005',
            'nup' => '2',
            'nama_barang' => 'Mikroskop 2',
            'jenis' => 'BMN-LAB',
            'lokasi' => 'Laboratorium Kimia',
            'penanggungjawab' => 'Laboran',
            'kondisi' => 'Baik',
        ]);
        $lab2 = BmnLab::create([
            'bmn_id' => $bmn2->id,
            'kode_barang' => '3080101005',
            'nup' => 2,
            'nama_barang' => 'Mikroskop 2',
            'lokasi' => 'Laboratorium Kimia',
            'penanggungjawab' => 'Laboran',
            'kondisi' => 'Baik',
        ]);

        // Update lab1
        $response = $this->actingAs($this->operatorUser)->put(route('bmns-lab.update', $lab1), [
            'kode_barang' => '3080101005',
            'nup' => 1,
            'nama_barang' => 'Mikroskop 1 Updated',
            'lokasi' => 'Laboratorium Kimia',
            'penanggungjawab' => 'Laboran',
            'kondisi' => 'Baik',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('bmns-lab.index'));
    }
}
