<?php

namespace Tests\Feature;

use App\Models\Bmn;
use App\Models\Lokasi;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BmnModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_service_returns_correct_stats_structure(): void
    {
        $service = app(DashboardService::class);
        $stats = $service->getStats();

        $this->assertArrayHasKey('jumlahBmn', $stats);
        $this->assertArrayHasKey('jumlahBmnTik', $stats);
        $this->assertArrayHasKey('jumlahBmnLab', $stats);
        $this->assertArrayHasKey('jumlahKondisiBaik', $stats);
        $this->assertArrayHasKey('jumlahPerluKalibrasi', $stats);
        $this->assertArrayHasKey('jumlahKerusakan', $stats);
        $this->assertArrayHasKey('jumlahDistribusi', $stats);
        $this->assertArrayHasKey('jumlahBaPerpindahan', $stats);
    }

    public function test_bmn_index_page_is_accessible_by_operator(): void
    {
        $user = User::factory()->create([
            'role' => 'Operator',
        ]);

        $response = $this->actingAs($user)->get(route('bmns.index'));

        $response->assertStatus(200);
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

        $response = $this->actingAs($user)->get(route('lokasis.index'));

        $response->assertStatus(200);
        $response->assertSee('Ruang Server');
    }
}
