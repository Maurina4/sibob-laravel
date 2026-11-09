<?php

namespace Tests\Feature;

use App\Models\BaPerpindahan;
use App\Models\Bmn;
use App\Models\Distribusi;
use App\Models\Pegawai;
use App\Models\PengajuanPengembalian;
use App\Models\Pengembalian;
use App\Models\User;
use App\Support\NaskahDinasHelper;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeritaAcaraNaskahDinasFormatTest extends TestCase
{
    use RefreshDatabase;

    protected User $operatorUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operatorUser = User::factory()->create([
            'name' => 'Operator BMN',
            'email' => 'operator@pom.go.id',
            'role' => 'Operator',
        ]);
    }

    public function test_naskah_dinas_helper_formats_nip_correctly(): void
    {
        $formatted = NaskahDinasHelper::formatNip('198407222023211010');
        $this->assertSame('19840722 202321 1 010', $formatted);

        $this->assertSame('-', NaskahDinasHelper::formatNip(''));
        $this->assertSame('-', NaskahDinasHelper::formatNip('-'));
        $this->assertSame('-', NaskahDinasHelper::formatNip(null));

        // Format yang sudah ada spasi tetap dibersihkan dan diformat seragam
        $this->assertSame('19840722 202321 1 010', NaskahDinasHelper::formatNip('19840722 202321 1 010'));
    }

    public function test_naskah_dinas_helper_generates_full_tanggal_terbilang(): void
    {
        $date = Carbon::create(2026, 3, 25); // Rabu, 25 Maret 2026
        $terbilang = NaskahDinasHelper::tanggalTerbilang($date);

        $this->assertSame(
            'Pada hari ini Rabu tanggal Dua puluh lima bulan Maret tahun Dua ribu dua puluh enam yang bertanda tangan di bawah ini:',
            $terbilang
        );
    }

    public function test_ba_distribusi_print_view_matches_naskah_dinas_standard(): void
    {
        $bmn = Bmn::create([
            'kode_barang' => '3050201003',
            'nup' => '658',
            'nama_barang' => 'Kursi Besi/Metal',
            'merek' => 'Neo Flyx Chrome',
            'tahun_perolehan' => '2026',
            'jenis' => 'BMN-NON TIK',
            'lokasi' => 'Gudang BMN',
            'penanggungjawab' => 'Risma Wulandari,A.Md., Kes.',
            'penanggungjawab_nip' => '199705162025212009',
            'kondisi' => 'Baik',
        ]);

        $distribusi = Distribusi::create([
            'nomor' => 'BA-DIST/20260325/001',
            'tanggal' => '2026-03-25',
            'bmn_id' => $bmn->id,
            'nama_barang' => $bmn->nama_barang,
            'jumlah' => 1,
            'satuan' => 'unit',
            'lokasi_asal' => 'Gudang BMN',
            'lokasi_tujuan' => 'R.Mutu (C2.08)',
            'penerima' => 'Risma Wulandari,A.Md., Kes.',
            'penerima_id' => null,
            'keterangan' => 'Distribusi awal',
        ]);

        $response = $this->actingAs($this->operatorUser)
            ->get(route('distribusi.print', $distribusi));

        $response->assertOk();

        // 1. Tanggal Terbilang
        $response->assertSee('Pada hari ini Rabu tanggal Dua puluh lima bulan Maret tahun Dua ribu dua puluh enam yang bertanda tangan di bawah ini:');

        // 2. Format NIP Pihak Kedua dengan Spasi Resmi
        $response->assertSee('19970516 202521 2 009');

        // 3. Kalimat Tanggung Jawab Tepat di Atas Tabel
        $response->assertSee('PIHAK KEDUA bertanggung jawab segala sesuatu atas barang-barang tersebut.');
        $response->assertSee('Apabila terjadi kehilangan menjadi tanggung jawab petugas/penanggung jawab ruangan yang diserahkan barang.');

        // 4. Tabel Kolom Sesuai Naskah Dinas Resmi Distribusi
        $response->assertDontSee('Ruang Asal');
        $response->assertSee('Ket');

        // 5. Penutup Standar Halaman 2
        $response->assertSee('Demikian Berita Acara ini dibuat dengan benar untuk dipergunakan sebagaimana mestinya.');

        // 6. Susunan Tanda Tangan: Pihak Kedua (Kiri), Pihak Pertama (Kanan), Mengetahui (Tengah Bawah)
        $response->assertSee('${ttd_pengirim1}', false);
        $response->assertSee('${ttd_pengirim2}', false);
        $response->assertSee('${ttd_pengirim3}', false);
        $response->assertSee('Kepala Balai Besar POM di Banjarbaru');
        $response->assertSee('Selaku Kuasa Pengguna Barang');

        // 7. Kode Dokumen di Pojok Kiri Bawah
        $response->assertSee(NaskahDinasHelper::KODE_FORM_BAST);
    }

    public function test_ba_perpindahan_print_view_matches_naskah_dinas_standard(): void
    {
        $penyerah = Pegawai::create([
            'nama' => 'Yuli Supriyatno, A. Md',
            'nip' => '198407222023211010',
            'jabatan' => 'Pengelola BMN',
            'unit_kerja' => 'Balai Besar POM di Banjarbaru',
        ]);

        $penerima = Pegawai::create([
            'nama' => 'Risma Wulandari,A.Md., Kes.',
            'nip' => '199705162025212009',
            'jabatan' => 'Pengelola Layanan Operasional',
            'unit_kerja' => 'Balai Besar POM di Banjarbaru',
        ]);

        $bmn = Bmn::create([
            'kode_barang' => '3050201003',
            'nup' => '658',
            'nama_barang' => 'Kursi Besi/Metal',
            'merek' => 'Neo Flyx Chrome',
            'tahun_perolehan' => '2026',
            'jenis' => 'BMN-NON TIK',
            'lokasi' => 'R.TU',
            'penanggungjawab' => $penyerah->nama,
            'kondisi' => 'Baik',
        ]);

        $ba = BaPerpindahan::create([
            'nomor_ba' => 'PL.03.03.17A.03.26.246',
            'tanggal' => '2026-03-25',
            'bmn_id' => $bmn->id,
            'nama_barang' => $bmn->nama_barang,
            'dari' => 'R.TU',
            'ke' => 'R.Mutu (C2.08)',
            'alasan' => 'Penataan ruangan kantor',
            'pengaju_nama' => $this->operatorUser->name,
            'pengaju_email' => $this->operatorUser->email,
            'pengaju_id' => $this->operatorUser->id,
            'pihak_pertama' => $penyerah->nama,
            'pihak_pertama_id' => $penyerah->id,
            'pihak_kedua' => $penerima->nama,
            'status' => 'disetujui',
        ]);

        $response = $this->actingAs($this->operatorUser)
            ->get(route('ba-perpindahan.print', $ba));

        $response->assertOk();

        // 1. NIP diformat dengan spasi
        $response->assertSee('19840722 202321 1 010');
        $response->assertSee('19970516 202521 2 009');

        // 2. Transisi Serah Terima
        $response->assertSee('PIHAK PERTAMA menyerahkan dan PIHAK KEDUA menerima penyerahan perpindahan Barang Milik Negara (BMN) dengan Spesifikasi sebagai berikut :');

        // 3. Kalimat Tanggung Jawab Tepat di Atas Tabel
        $response->assertSee('PIHAK KEDUA bertanggung jawab segala sesuatu atas barang-barang tersebut.');
        $response->assertSee('Apabila terjadi kehilangan menjadi tanggung jawab petugas/penanggung jawab ruangan yang diserahkan barang.');

        // 4. Tabel 9 Kolom Sesuai Naskah Dinas Resmi
        $response->assertSee('Ruang Asal');
        $response->assertSee('Ruang Baru');

        // 5. Penutup Standar Halaman 2
        $response->assertSee('Demikian Berita Acara ini dibuat dengan benar untuk dipergunakan sebagaimana mestinya.');

        // 6. Susunan TTD
        $response->assertSee('${ttd_pengirim1}', false);
        $response->assertSee('${ttd_pengirim2}', false);
        $response->assertSee('${ttd_pengirim3}', false);
        $response->assertSee('Kepala Balai Besar POM di Banjarbaru');
        $response->assertSee('Selaku Kuasa Pengguna Barang');

        // 7. Kode Dokumen di Pojok Kiri Bawah
        $response->assertSee(NaskahDinasHelper::KODE_FORM_BAST);
    }

    public function test_ba_pengajuan_pengembalian_print_view_matches_naskah_dinas_standard(): void
    {
        $userPegawai = Pegawai::create([
            'nama' => 'Risma Wulandari,A.Md., Kes.',
            'nip' => '199705162025212009',
            'jabatan' => 'Pengelola Layanan Operasional',
            'unit_kerja' => 'Subbag Tata Usaha',
        ]);

        $pengajuan = PengajuanPengembalian::create([
            'nomor_pengajuan' => 'PGM/2026/001',
            'nomor_ba' => 'BA-PGM/20260325/001',
            'tanggal' => '2026-03-25',
            'diajukan_oleh' => $this->operatorUser->id,
            'pegawai_id' => $userPegawai->id,
            'nama_pegawai' => $userPegawai->nama,
            'nip_pegawai' => $userPegawai->nip,
            'bidang' => 'Tata Usaha',
            'alasan' => 'selesai_pakai',
            'status' => 'disetujui',
            'penyetuju_id' => $this->operatorUser->id,
            'disetujui_pada' => now(),
        ]);

        $bmn = Bmn::create([
            'kode_barang' => '3050201003',
            'nup' => '658',
            'nama_barang' => 'Kursi Besi/Metal',
            'jenis' => 'BMN-NON TIK',
            'lokasi' => 'R.Mutu',
            'penanggungjawab' => 'Risma Wulandari,A.Md., Kes.',
            'kondisi' => 'Baik',
        ]);

        $pengajuan->items()->create([
            'bmn_id' => $bmn->id,
            'nama_barang' => $bmn->nama_barang,
            'kode_barang' => $bmn->kode_barang,
            'nup' => $bmn->nup,
            'kondisi' => 'Baik',
            'keterangan' => 'Selesai pemakaian',
        ]);

        $response = $this->actingAs($this->operatorUser)
            ->get(route('pengajuan-pengembalian.cetak-ba', $pengajuan));

        $response->assertOk();

        // 1. Tanggal Terbilang
        $response->assertSee('Pada hari ini Rabu tanggal Dua puluh lima bulan Maret tahun Dua ribu dua puluh enam yang bertanda tangan di bawah ini:');

        // 2. Format NIP
        $response->assertSee('19970516 202521 2 009');

        // 3. Transisi Serah Terima
        $response->assertSee('PIHAK PERTAMA menyerahkan dan PIHAK KEDUA menerima penyerahan Barang Milik Negara (BMN) dengan kondisi Baik/ Rusak Spesifikasi sebagai berikut :');

        // 4. Tanggung Jawab (Di Atas Tabel)
        $response->assertSee('PIHAK KEDUA bertanggung jawab segala sesuatu atas barang-barang tersebut.');
        $response->assertSee('Apabila terjadi kehilangan menjadi tanggung jawab petugas yang diserahkan barang.');

        // 5. Tabel 7 Kolom
        $response->assertSee('Merk/Type');
        $response->assertSee('Jumlah (unit)');
        $response->assertSee('Tahun Perolehan');

        // 6. Penutup Standar Halaman 2
        $response->assertSee('Demikian Berita Acara ini dibuat dengan benar untuk dipergunakan sebagaimana mestinya.');

        // 7. Susunan TTD (Pihak Kedua TTD 2, Pihak Pertama TTD 1, Mengetahui TTD 3)
        $response->assertSee('${ttd_pengirim1}', false);
        $response->assertSee('${ttd_pengirim2}', false);
        $response->assertSee('${ttd_pengirim3}', false);
        $response->assertSee('Kepala Balai Besar POM di Banjarbaru');
        $response->assertSee('Selaku Kuasa Pengguna Barang');

        // 8. Kode Dokumen
        $response->assertSee(NaskahDinasHelper::KODE_FORM_BAST);
    }

    public function test_ba_pengembalian_legacy_print_view_matches_naskah_dinas_standard(): void
    {
        $pengembalian = Pengembalian::create([
            'nomor_pengajuan' => 'BA-KEMBALI/2026/001',
            'tanggal' => '2026-03-25',
            'nama_pengaju' => 'Risma Wulandari,A.Md., Kes.',
            'bidang' => 'Tata Usaha',
            'kondisi_pengembalian' => 'Baik',
            'status' => 'Diajukan',
        ]);

        $response = $this->actingAs($this->operatorUser)
            ->get(route('pengembalians.print', $pengembalian));

        $response->assertOk();

        // 1. Tanggal Terbilang
        $response->assertSee('Pada hari ini Rabu tanggal Dua puluh lima bulan Maret tahun Dua ribu dua puluh enam yang bertanda tangan di bawah ini:');

        // 2. Transisi Serah Terima
        $response->assertSee('PIHAK PERTAMA menyerahkan dan PIHAK KEDUA menerima penyerahan Barang Milik Negara (BMN) dengan kondisi Baik/ Rusak Spesifikasi sebagai berikut :');

        // 3. Tanggung Jawab (Di Atas Tabel)
        $response->assertSee('PIHAK KEDUA bertanggung jawab segala sesuatu atas barang-barang tersebut.');
        $response->assertSee('Apabila terjadi kehilangan menjadi tanggung jawab petugas yang diserahkan barang.');

        // 4. Tabel 7 Kolom
        $response->assertSee('Merk/Type');
        $response->assertSee('Jumlah (unit)');
        $response->assertSee('Tahun Perolehan');

        // 5. Penutup Standar Halaman 2
        $response->assertSee('Demikian Berita Acara ini dibuat dengan benar untuk dipergunakan sebagaimana mestinya.');

        // 6. Susunan TTD
        $response->assertSee('${ttd_pengirim1}', false);
        $response->assertSee('${ttd_pengirim2}', false);
        $response->assertSee('${ttd_pengirim3}', false);
        $response->assertSee('Kepala Balai Besar POM di Banjarbaru');
        $response->assertSee('Selaku Kuasa Pengguna Barang');

        // 7. Kode Dokumen
        $response->assertSee(NaskahDinasHelper::KODE_FORM_BAST);
    }
}
