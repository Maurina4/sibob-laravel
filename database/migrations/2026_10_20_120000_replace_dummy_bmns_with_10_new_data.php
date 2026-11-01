<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Don't run in unit/feature tests where RefreshDatabase runs clean factories
        if (app()->environment('testing')) {
            return;
        }

        if (! Schema::hasTable('bmns')) {
            return;
        }

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        // 1. Clean existing dummy BMNs and their dependent records
        if (Schema::hasTable('bmn_dokumens')) {
            DB::table('bmn_dokumens')->truncate();
        }
        if (Schema::hasTable('pengembalian_items')) {
            DB::table('pengembalian_items')->truncate();
        }
        if (Schema::hasTable('pengembalians')) {
            DB::table('pengembalians')->truncate();
        }
        if (Schema::hasTable('distribusi_items')) {
            DB::table('distribusi_items')->truncate();
        }
        if (Schema::hasTable('distribusis')) {
            DB::table('distribusis')->truncate();
        }
        if (Schema::hasTable('ba_perpindahans')) {
            DB::table('ba_perpindahans')->truncate();
        }
        if (Schema::hasTable('laporan_kerusakans')) {
            DB::table('laporan_kerusakans')->truncate();
        }
        if (Schema::hasTable('data_terhapus')) {
            DB::table('data_terhapus')->truncate();
        }
        if (Schema::hasTable('bmns_tik')) {
            DB::table('bmns_tik')->truncate();
        }
        if (Schema::hasTable('bmns_lab')) {
            DB::table('bmns_lab')->truncate();
        }
        DB::table('bmns')->truncate();

        // 2. Resolve Master Lokasi & Pegawai IDs
        $lokasiMap = [];
        if (Schema::hasTable('lokasis')) {
            $allLokasi = DB::table('lokasis')->get();
            foreach ($allLokasi as $lok) {
                $lokasiMap[$lok->nama] = $lok->id;
                if (! empty($lok->kode_ruangan)) {
                    $lokasiMap[$lok->kode_ruangan] = $lok->id;
                    $lokasiMap[$lok->kode_ruangan.' '.$lok->nama] = $lok->id;
                    $lokasiMap[$lok->kode_ruangan.' - '.$lok->nama] = $lok->id;
                }
            }
        }

        $pegawaiMap = [];
        if (Schema::hasTable('pegawais')) {
            $pegawaiMap = DB::table('pegawais')->pluck('id', 'nip')->toArray();
        }

        // 3. Define the 10 New BMN Items (Distributed across Gedung A, B, C)
        $items = [
            [
                'id' => 1,
                'kode_barang' => '3100102001',
                'nup' => 1,
                'no_seri' => 'LN-TP-T14G4-001',
                'nama_barang' => 'Laptop Lenovo ThinkPad T14 Gen 4 Intel Core i7',
                'merek' => 'Lenovo ThinkPad',
                'tahun_perolehan' => 2024,
                'nilai_perolehan' => 26500000.00,
                'tanggal_terima' => '2024-02-15',
                'jenis' => 'BMN-TIK',
                'satuan' => 'unit',
                'lokasi' => 'A1.01 RUANG DISKUSI LAB KIMIA',
                'nip' => '198804122014021001',
                'penanggungjawab' => 'Ahmad Fauzi, S.Kom. (198804122014021001)',
                'kondisi' => 'Baik',
                'jadwal_pemeliharaan' => '2026-11-15',
                'spesifikasi' => 'Intel Core i7-1360P vPro, RAM 32GB DDR5, SSD 1TB NVMe PCIe 4.0, Layar 14" WUXGA IPS, Windows 11 Pro 64-bit.',
                'kategori_bmn_id' => 3,
                'sub' => 'tik',
                'jenis_tik' => 'Laptop / Notebook',
            ],
            [
                'id' => 2,
                'kode_barang' => '3100102002',
                'nup' => 1,
                'no_seri' => 'DL-OPT-7410-001',
                'nama_barang' => 'PC All-in-One Dell OptiPlex 7410 Plus 24 Inch',
                'merek' => 'Dell OptiPlex',
                'tahun_perolehan' => 2023,
                'nilai_perolehan' => 22800000.00,
                'tanggal_terima' => '2023-05-10',
                'jenis' => 'BMN-TIK',
                'satuan' => 'unit',
                'lokasi' => 'B1.06 RUANG STAFF INFOKOM',
                'nip' => '198001012005011001',
                'penanggungjawab' => 'Yuli Supriyatno (198001012005011001)',
                'kondisi' => 'Baik',
                'jadwal_pemeliharaan' => '2026-12-01',
                'spesifikasi' => 'Intel Core i7-13700, RAM 16GB DDR5, SSD 512GB NVMe, Layar 23.8" FHD IPS Touch, Keyboard & Mouse Wireless.',
                'kategori_bmn_id' => 3,
                'sub' => 'tik',
                'jenis_tik' => 'Komputer PC',
            ],
            [
                'id' => 3,
                'kode_barang' => '3100101001',
                'nup' => 1,
                'no_seri' => 'DL-R760-SRV-2023',
                'nama_barang' => 'Server Rackmount Dell PowerEdge R760 2U Dual Xeon',
                'merek' => 'Dell PowerEdge',
                'tahun_perolehan' => 2023,
                'nilai_perolehan' => 185000000.00,
                'tanggal_terima' => '2023-08-20',
                'jenis' => 'BMN-TIK',
                'satuan' => 'unit',
                'lokasi' => 'C1.02 RUANG MONITOR CCTV GEDUNG C',
                'nip' => '198804122014021001',
                'penanggungjawab' => 'Ahmad Fauzi, S.Kom. (198804122014021001)',
                'kondisi' => 'Baik',
                'jadwal_pemeliharaan' => '2026-11-30',
                'spesifikasi' => '2x Intel Xeon Silver 4410Y, RAM 128GB ECC Registered, 4x 1.92TB SSD SAS RAID 5, Redundant PSU 800W.',
                'kategori_bmn_id' => 3,
                'sub' => 'tik',
                'jenis_tik' => 'Server',
            ],
            [
                'id' => 4,
                'kode_barang' => '3100203001',
                'nup' => 1,
                'no_seri' => 'HP-MFP-M528-09',
                'nama_barang' => 'Printer Laser Multifungsi HP LaserJet Enterprise MFP M528dn',
                'merek' => 'HP LaserJet',
                'tahun_perolehan' => 2022,
                'nilai_perolehan' => 28500000.00,
                'tanggal_terima' => '2022-09-14',
                'jenis' => 'BMN-TIK',
                'satuan' => 'unit',
                'lokasi' => 'C1.14 RUANG BENDAHARA',
                'nip' => '198703202012031003',
                'penanggungjawab' => 'M. Ridwan Prasetyo, S.E. (198703202012031003)',
                'kondisi' => 'Baik',
                'jadwal_pemeliharaan' => '2026-10-28',
                'spesifikasi' => 'Kecepatan cetak hingga 45 ppm, Duplex otomatis, Layar sentuh warna 8 inch, Ethernet Gigabit.',
                'kategori_bmn_id' => 3,
                'sub' => 'tik',
                'jenis_tik' => 'Printer',
            ],
            [
                'id' => 5,
                'kode_barang' => '3070101001',
                'nup' => 1,
                'no_seri' => 'SHM-UV1900I-8821',
                'nama_barang' => 'Spektrofotometer UV-Vis Double Beam Shimadzu UV-1900i',
                'merek' => 'Shimadzu',
                'tahun_perolehan' => 2023,
                'nilai_perolehan' => 245000000.00,
                'tanggal_terima' => '2023-04-12',
                'jenis' => 'BMN-LAB',
                'satuan' => 'unit',
                'lokasi' => 'A1.10 RUANG INSTRUMEN 1.1',
                'nip' => '198502152010012002',
                'penanggungjawab' => 'Ade Rahma Yanti, S.Farm., Apt. (198502152010012002)',
                'kondisi' => 'Baik',
                'jadwal_pemeliharaan' => '2026-11-10',
                'spesifikasi' => 'Rentang panjang gelombang 190-1100 nm, Spectral bandwidth 1 nm, Software LabSolutions UV-Vis.',
                'kategori_bmn_id' => 2,
                'sub' => 'lab',
                'status_kalibrasi' => 'Terkalibrasi',
                'tanggal_kalibrasi' => '2024-01-15',
            ],
            [
                'id' => 6,
                'kode_barang' => '3070101002',
                'nup' => 1,
                'no_seri' => 'WAT-ARC-HPLC-2023',
                'nama_barang' => 'Kromatografi Cair Kinerja Tinggi (HPLC) Waters Arc System Quaternary',
                'merek' => 'Waters',
                'tahun_perolehan' => 2023,
                'nilai_perolehan' => 890000000.00,
                'tanggal_terima' => '2023-06-05',
                'jenis' => 'BMN-LAB',
                'satuan' => 'set',
                'lokasi' => 'A1.16 RUANG INSTRUMEN 1.5',
                'nip' => '199008202015032003',
                'penanggungjawab' => 'Siti Nurhaliza, S.Si. (199008202015032003)',
                'kondisi' => 'Baik',
                'jadwal_pemeliharaan' => '2026-12-15',
                'spesifikasi' => 'Quaternary pump max pressure 9500 psi, Autosampler flow-through needle, PDA Detector 2998.',
                'kategori_bmn_id' => 2,
                'sub' => 'lab',
                'status_kalibrasi' => 'Terkalibrasi',
                'tanggal_kalibrasi' => '2024-02-20',
            ],
            [
                'id' => 7,
                'kode_barang' => '3070102001',
                'nup' => 1,
                'no_seri' => 'HRY-HVE50-4102',
                'nama_barang' => 'Autoclave Sterilisasi Vertikal Hirayama HVE-50 50 Liter',
                'merek' => 'Hirayama',
                'tahun_perolehan' => 2022,
                'nilai_perolehan' => 115000000.00,
                'tanggal_terima' => '2022-10-18',
                'jenis' => 'BMN-LAB',
                'satuan' => 'unit',
                'lokasi' => 'A2.10 RUANG UJI STERIL',
                'nip' => '199201152019021002',
                'penanggungjawab' => 'Bambang Hendrawan, S.Farm. (199201152019021002)',
                'kondisi' => 'Baik',
                'jadwal_pemeliharaan' => '2026-11-25',
                'spesifikasi' => 'Kapasitas ruang 50 Liter, Suhu operasional 105 - 135°C, Dual interlock safety lid system.',
                'kategori_bmn_id' => 2,
                'sub' => 'lab',
                'status_kalibrasi' => 'Terkalibrasi',
                'tanggal_kalibrasi' => '2023-11-10',
            ],
            [
                'id' => 8,
                'kode_barang' => '3050104001',
                'nup' => 1,
                'no_seri' => 'MRT-JPR-2023-01',
                'nama_barang' => 'Meja Rapat Oval Kayu Jati Solid 14 Kursi Eksklusif',
                'merek' => 'Custom Jati Jepara',
                'tahun_perolehan' => 2023,
                'nilai_perolehan' => 42000000.00,
                'tanggal_terima' => '2023-07-10',
                'jenis' => 'BMN-NON TIK',
                'satuan' => 'set',
                'lokasi' => 'B2.03 RUANG RAPAT BESAR',
                'nip' => '197505102000032001',
                'penanggungjawab' => 'Dra. Hj. Wahyuni, Apt. (197505102000032001)',
                'kondisi' => 'Baik',
                'jadwal_pemeliharaan' => '2026-12-20',
                'spesifikasi' => 'Dimensi 450x160x76 cm, Kayu jati TPK perhutani oven, finishing poly-urethane doff, panel pop-up electrical socket.',
                'kategori_bmn_id' => 1,
                'sub' => 'none',
            ],
            [
                'id' => 9,
                'kode_barang' => '3050104002',
                'nup' => 1,
                'no_seri' => 'SFA-VIP-B210-01',
                'nama_barang' => 'Sofa Tamu Eksekutif Kulit Asli Set 3+2+1 plus Coffee Table',
                'merek' => 'Cellini Genuine Leather',
                'tahun_perolehan' => 2023,
                'nilai_perolehan' => 38500000.00,
                'tanggal_terima' => '2023-03-22',
                'jenis' => 'BMN-NON TIK',
                'satuan' => 'set',
                'lokasi' => 'B2.10 RUANG KEPALA BALAI',
                'nip' => '197505102000032001',
                'penanggungjawab' => 'Dra. Hj. Wahyuni, Apt. (197505102000032001)',
                'kondisi' => 'Baik',
                'jadwal_pemeliharaan' => '2026-11-05',
                'spesifikasi' => 'Top grain Italian leather warna camel brown, bantalan high density foam, rangka kayu solid antirayap.',
                'kategori_bmn_id' => 1,
                'sub' => 'none',
            ],
            [
                'id' => 10,
                'kode_barang' => '3050206001',
                'nup' => 1,
                'no_seri' => 'PRK-SLNT-60KVA-22',
                'nama_barang' => 'Genset Silent Diesel Perkins 60 kVA 3-Phase Auto ATS',
                'merek' => 'Perkins Stamford',
                'tahun_perolehan' => 2022,
                'nilai_perolehan' => 210000000.00,
                'tanggal_terima' => '2022-11-12',
                'jenis' => 'BMN-NON TIK',
                'satuan' => 'unit',
                'lokasi' => 'C.Bs.01 GUDANG ATK',
                'nip' => '198703202012031003',
                'penanggungjawab' => 'M. Ridwan Prasetyo, S.E. (198703202012031003)',
                'kondisi' => 'Baik',
                'jadwal_pemeliharaan' => '2026-11-01',
                'spesifikasi' => 'Output 60 kVA / 48 kW, Kanopi super silent soundproof 65 dB @ 7m, Panel AMF-ATS auto start 5 detik.',
                'kategori_bmn_id' => 1,
                'sub' => 'none',
            ],
        ];

        foreach ($items as $item) {
            $lokasiId = null;
            if (preg_match('/^([A-Za-z]{1,2}(?:\.[A-Za-z]+)?\d*\.\d+)/i', $item['lokasi'], $m)) {
                $lokasiId = $lokasiMap[$m[1]] ?? null;
            }
            if (! $lokasiId) {
                $lokasiId = $lokasiMap[$item['lokasi']] ?? null;
            }

            $targetLokasi = isset($allLokasi) ? $allLokasi->firstWhere('id', $lokasiId) : null;
            $namaLokasi = $targetLokasi ? trim($targetLokasi->kode_ruangan.' '.$targetLokasi->nama) : $item['lokasi'];

            $pegawaiId = $pegawaiMap[$item['nip']] ?? null;

            DB::table('bmns')->insert([
                'id' => $item['id'],
                'kode_barang' => $item['kode_barang'],
                'nup' => $item['nup'],
                'no_seri' => $item['no_seri'],
                'nama_barang' => $item['nama_barang'],
                'merek' => $item['merek'],
                'tahun_perolehan' => $item['tahun_perolehan'],
                'nilai_perolehan' => $item['nilai_perolehan'],
                'tanggal_terima' => $item['tanggal_terima'],
                'jenis' => $item['jenis'],
                'satuan' => $item['satuan'],
                'lokasi' => $namaLokasi,
                'lokasi_id' => $lokasiId,
                'penanggungjawab' => $item['penanggungjawab'],
                'penanggungjawab_nip' => $item['nip'],
                'penanggung_jawab_id' => $pegawaiId,
                'kondisi' => $item['kondisi'],
                'jadwal_pemeliharaan' => $item['jadwal_pemeliharaan'],
                'spesifikasi' => $item['spesifikasi'],
                'kategori_bmn_id' => $item['kategori_bmn_id'],
                'dihapus' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($item['sub'] === 'tik') {
                DB::table('bmns_tik')->insert([
                    'bmn_id' => $item['id'],
                    'kode_barang' => $item['kode_barang'],
                    'nup' => (int) $item['nup'],
                    'no_seri' => $item['no_seri'],
                    'nama_barang' => $item['nama_barang'],
                    'tahun_perolehan' => $item['tahun_perolehan'],
                    'nilai_perolehan' => $item['nilai_perolehan'],
                    'jenis' => $item['jenis_tik'] ?? 'Lainnya',
                    'lokasi' => $namaLokasi,
                    'penanggungjawab' => $item['penanggungjawab'],
                    'kondisi' => $item['kondisi'],
                    'dihapus' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif ($item['sub'] === 'lab') {
                DB::table('bmns_lab')->insert([
                    'bmn_id' => $item['id'],
                    'kode_barang' => $item['kode_barang'],
                    'nup' => (int) $item['nup'],
                    'no_seri' => $item['no_seri'],
                    'nama_barang' => $item['nama_barang'],
                    'merek' => $item['merek'],
                    'tahun_perolehan' => $item['tahun_perolehan'],
                    'nilai_perolehan' => $item['nilai_perolehan'],
                    'lokasi' => $namaLokasi,
                    'penanggungjawab' => $item['penanggungjawab'],
                    'kondisi' => $item['kondisi'],
                    'status_kalibrasi' => $item['status_kalibrasi'] ?? 'Terkalibrasi',
                    'tanggal_kalibrasi' => $item['tanggal_kalibrasi'] ?? null,
                    'dihapus' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Sample Transactions linked to the new 10 BMNs
        if (Schema::hasTable('distribusis')) {
            DB::table('distribusis')->insert([
                [
                    'nomor' => 'DST-2024-001',
                    'tanggal' => '2024-02-20',
                    'bmn_id' => 1,
                    'nama_barang' => 'Laptop Lenovo ThinkPad T14 Gen 4 Intel Core i7',
                    'jumlah' => 1,
                    'satuan' => 'unit',
                    'lokasi_asal' => 'C.Bs.01 GUDANG ATK',
                    'lokasi_asal_id' => $lokasiMap['C.Bs.01 GUDANG ATK'] ?? null,
                    'lokasi_tujuan' => 'A1.01 RUANG DISKUSI LAB KIMIA',
                    'lokasi_tujuan_id' => $lokasiMap['A1.01 RUANG DISKUSI LAB KIMIA'] ?? null,
                    'penerima' => 'Ahmad Fauzi, S.Kom. (198804122014021001)',
                    'penerima_id' => $pegawaiMap['198804122014021001'] ?? null,
                    'keterangan' => 'Penyerahan laptop operasional lab kimia',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'nomor' => 'DST-2023-002',
                    'tanggal' => '2023-05-15',
                    'bmn_id' => 2,
                    'nama_barang' => 'PC All-in-One Dell OptiPlex 7410 Plus 24 Inch',
                    'jumlah' => 1,
                    'satuan' => 'unit',
                    'lokasi_asal' => 'C.Bs.01 GUDANG ATK',
                    'lokasi_asal_id' => $lokasiMap['C.Bs.01 GUDANG ATK'] ?? null,
                    'lokasi_tujuan' => 'B1.06 RUANG STAFF INFOKOM',
                    'lokasi_tujuan_id' => $lokasiMap['B1.06 RUANG STAFF INFOKOM'] ?? null,
                    'penerima' => 'Yuli Supriyatno (198001012005011001)',
                    'penerima_id' => $pegawaiMap['198001012005011001'] ?? null,
                    'keterangan' => 'Distribusi unit PC kerja Infokom',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        if (Schema::hasTable('ba_perpindahans')) {
            DB::table('ba_perpindahans')->insert([
                [
                    'nomor_ba' => 'BA/001/BMN/BBPOM/2024',
                    'tanggal' => '2024-03-01',
                    'bmn_id' => 2,
                    'nama_barang' => 'PC All-in-One Dell OptiPlex 7410 Plus 24 Inch',
                    'dari' => 'C.Bs.01 GUDANG ATK',
                    'lokasi_asal_id' => $lokasiMap['C.Bs.01 GUDANG ATK'] ?? null,
                    'ke' => 'B1.06 RUANG STAFF INFOKOM',
                    'lokasi_tujuan_id' => $lokasiMap['B1.06 RUANG STAFF INFOKOM'] ?? null,
                    'alasan' => 'Penempatan komputer kerja staf administrasi tata usaha',
                    'pihak_pertama' => 'M. Ridwan Prasetyo, S.E. (198703202012031003)',
                    'pihak_pertama_id' => $pegawaiMap['198703202012031003'] ?? null,
                    'pihak_kedua' => 'Yuli Supriyatno (198001012005011001)',
                    'pegawai_id' => $pegawaiMap['198001012005011001'] ?? null,
                    'pengaju_email' => 'yuli.tu@pom.go.id',
                    'pengaju_nama' => 'Yuli Supriyatno',
                    'status' => 'Disetujui',
                    'tanggal_keputusan' => '2024-03-02',
                    'diputuskan_oleh' => 'Operator BMN',
                    'catatan_operator' => 'Aset telah dipindahkan dan diserahterimakan dalam kondisi baik.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        if (Schema::hasTable('laporan_kerusakans')) {
            DB::table('laporan_kerusakans')->insert([
                [
                    'nomor' => 'LK-2024-001',
                    'tanggal' => '2024-05-10',
                    'bmn_id' => 4,
                    'nama_barang' => 'Printer Laser Multifungsi HP LaserJet Enterprise MFP M528dn',
                    'lokasi' => 'C1.14 RUANG BENDAHARA',
                    'lokasi_id' => $lokasiMap['C1.14 RUANG BENDAHARA'] ?? null,
                    'tingkat' => 'Rusak Ringan',
                    'deskripsi' => 'Roller penarik kertas agak aus sehingga sesekali mengalami paper jam.',
                    'pelapor_email' => 'ridwan.tu@pom.go.id',
                    'pelapor_nama' => 'M. Ridwan Prasetyo, S.E.',
                    'pelapor_pegawai_id' => $pegawaiMap['198703202012031003'] ?? null,
                    'status' => 'Diproses',
                    'tanggapan' => 'Sudah dipesankan replacement pickup roller kit resmi HP.',
                    'tanggal_update' => '2024-05-11',
                    'ditanggapi_oleh' => 'Operator BMN',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    public function down(): void {}
};
