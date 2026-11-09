<?php

namespace Tests\Feature;

use App\Models\Bmn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BmnExportFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function createBmn(array $attributes = []): Bmn
    {
        static $counter = 1;
        $counter++;

        return Bmn::create(array_merge([
            'kode_barang' => 'KD-EXP-'.str_pad((string) $counter, 3, '0', STR_PAD_LEFT),
            'nup' => (string) $counter,
            'nama_barang' => 'Barang Uji '.$counter,
            'merek' => 'Merek Uji',
            'jenis' => 'BMN-TIK',
            'lokasi' => 'Ruang Server',
            'penanggungjawab' => 'Petugas BMN',
            'kondisi' => 'Baik',
            'tahun_perolehan' => 2024,
            'nilai_perolehan' => 10000000.00,
            'dihapus' => false,
        ], $attributes));
    }

    public function test_bmn_index_page_displays_monev_export_button_and_year_filter(): void
    {
        $user = User::factory()->create([
            'role' => 'Operator',
        ]);

        $this->createBmn([
            'nama_barang' => 'Laptop Dell Latitude',
            'tahun_perolehan' => (int) date('Y'),
            'nilai_perolehan' => 15000000,
        ]);

        $response = $this->actingAs($user)->get(route('bmns.index'));

        $response->assertStatus(200);
        $response->assertSee('Export Excel');
        $response->assertSee('Tahun Berjalan ('.date('Y').')');
        $response->assertSee('Tahun Sebelumnya ('.(date('Y') - 1).')');
    }

    public function test_bmn_export_xlsx_endpoint_downloads_valid_spreadsheet(): void
    {
        $user = User::factory()->create([
            'role' => 'Operator',
        ]);

        $this->createBmn([
            'nama_barang' => 'Laptop ThinkPad X1',
            'kode_barang' => '3.05.01.04.001',
            'nup' => '00010',
            'merek' => 'Lenovo',
            'lokasi' => 'Ruang Server IT',
            'tahun_perolehan' => 2024,
            'nilai_perolehan' => 26500000,
        ]);

        $response = $this->actingAs($user)->get(route('bmns.export'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('content-disposition'));
    }

    public function test_bmn_export_filtered_by_current_year(): void
    {
        $user = User::factory()->create([
            'role' => 'Operator',
        ]);

        $currentYear = (int) date('Y');

        $this->createBmn([
            'nama_barang' => 'Item Tahun Berjalan',
            'tahun_perolehan' => $currentYear,
            'nilai_perolehan' => 5000000,
        ]);

        $this->createBmn([
            'nama_barang' => 'Item Tahun Lalu',
            'tahun_perolehan' => $currentYear - 1,
            'nilai_perolehan' => 4000000,
        ]);

        $response = $this->actingAs($user)->get(route('bmns.export', ['tahun' => 'current']));

        $response->assertStatus(200);
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('content-disposition'));
        $this->assertStringContainsString('Tahun_Berjalan_'.$currentYear, (string) $response->headers->get('content-disposition'));
    }

    public function test_bmn_export_filtered_by_previous_year(): void
    {
        $user = User::factory()->create([
            'role' => 'Operator',
        ]);

        $previousYear = (int) date('Y') - 1;

        $this->createBmn([
            'nama_barang' => 'Item Tahun Lalu',
            'tahun_perolehan' => $previousYear,
            'nilai_perolehan' => 7500000,
        ]);

        $response = $this->actingAs($user)->get(route('bmns.export', ['tahun' => 'previous']));

        $response->assertStatus(200);
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('content-disposition'));
        $this->assertStringContainsString('Tahun_Sebelumnya_'.$previousYear, (string) $response->headers->get('content-disposition'));
    }

    public function test_bmn_export_csv_contains_all_required_columns(): void
    {
        $user = User::factory()->create([
            'role' => 'Operator',
        ]);

        $this->createBmn([
            'nama_barang' => 'Spektrofotometer UV-Vis',
            'kode_barang' => '3.08.01.40.005',
            'nup' => '00005',
            'merek' => 'Shimadzu UV-1900i',
            'lokasi' => 'Laboratorium Kimia Makanan',
            'tahun_perolehan' => 2023,
            'nilai_perolehan' => 185000000,
        ]);

        $response = $this->actingAs($user)->get(route('bmns.export', ['format' => 'csv']));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));

        $content = $response->streamedContent();

        // Verifikasi semua kolom yang diminta user ada di header
        $this->assertStringContainsString('Nama Barang', $content);
        $this->assertStringContainsString('Kode Barang', $content);
        $this->assertStringContainsString('NUP', $content);
        $this->assertStringContainsString('Merek', $content);
        $this->assertStringContainsString('Kondisi', $content);
        $this->assertStringContainsString('Lokasi', $content);
        $this->assertStringContainsString('Tahun Perolehan', $content);
        $this->assertStringContainsString('Nilai Perolehan (Rp)', $content);

        // Verifikasi isi datanya
        $this->assertStringContainsString('Spektrofotometer UV-Vis', $content);
        $this->assertStringContainsString('3.08.01.40.005', $content);
        $this->assertStringContainsString('00005', $content);
        $this->assertStringContainsString('Shimadzu UV-1900i', $content);
        $this->assertStringContainsString('Baik', $content);
        $this->assertStringContainsString('Laboratorium Kimia Makanan', $content);
        $this->assertStringContainsString('2023', $content);
        $this->assertStringContainsString('185000000', $content);
    }
}
