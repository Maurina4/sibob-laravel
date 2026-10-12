<?php

namespace Database\Seeders;

use App\Models\BaPerpindahan;
use App\Models\Bmn;
use App\Models\BmnLab;
use App\Models\BmnTik;
use App\Models\Distribusi;
use App\Models\KategoriBmn;
use App\Models\LaporanKerusakan;
use App\Models\Lokasi;
use App\Models\Pegawai;
use App\Models\Pengembalian;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================
        // 1. KATEGORI BMN
        // =====================================================
        $kategoris = [
            [
                'id' => 1,
                'kode' => 'UMUM',
                'nama' => 'BMN Umum & Perkantoran',
                'deskripsi' => 'Fasilitas kerja kantor, perlengkapan ruang, dan mebeler.',
            ],
            [
                'id' => 2,
                'kode' => 'LAB',
                'nama' => 'BMN Laboratorium',
                'deskripsi' => 'Instrumen analitik dan instrumen pengujian mutu obat & makanan.',
            ],
            [
                'id' => 3,
                'kode' => 'TIK',
                'nama' => 'BMN Perangkat TIK',
                'deskripsi' => 'Komputer, server, perangkat jaringan, scanner, dan printer.',
            ],
        ];

        foreach ($kategoris as $kategori) {
            KategoriBmn::updateOrCreate(['id' => $kategori['id']], $kategori);
        }

        // =====================================================
        // 2. MASTER LOKASI
        // =====================================================
        $lokasiList = [
            'Ruang Tata Usaha',
            'Ruang Kepala Balai',
            'Laboratorium Pengujian Kimia',
            'Laboratorium Pengujian Mikrobiologi',
            'Laboratorium Kosmetik & Obat Bahan Alam',
            'Ruang Server & TIK',
            'Gudang BMN Banjarbaru',
            'Ruang Rapat Pimpinan',
            'Ruang Pelayanan Publik / Sertifikasi',
            'Ruang Pengarsipan Dokumen',
        ];

        foreach ($lokasiList as $namaLokasi) {
            Lokasi::updateOrCreate(['nama' => $namaLokasi]);
        }

        // =====================================================
        // 3. PEGAWAI BBPOM BANJARMASIN (DENGAN NIP LENGKAP)
        // =====================================================
        $pegawaiList = [
            [
                'nama' => 'Yuli Supriyatno',
                'nip' => '198001012005011001',
                'jabatan' => 'Operator BMN',
                'unit_kerja' => 'Subbag Tata Usaha',
            ],
            [
                'nama' => 'Ade Rahma Yanti, S.Farm., Apt.',
                'nip' => '198502152010012002',
                'jabatan' => 'Pengelola BMN Lab',
                'unit_kerja' => 'Laboratorium Pengujian Kimia',
            ],
            [
                'nama' => 'Ahmad Fauzi, S.Kom.',
                'nip' => '198804122014021001',
                'jabatan' => 'Pranata Komputer / Pengelola TIK',
                'unit_kerja' => 'Subbag Tata Usaha',
            ],
            [
                'nama' => 'Siti Nurhaliza, S.Si.',
                'nip' => '199008202015032003',
                'jabatan' => 'Penguji Kimia Ahli Muda',
                'unit_kerja' => 'Laboratorium Pengujian Kimia',
            ],
            [
                'nama' => 'M. Ridwan Prasetyo, S.E.',
                'nip' => '198703202012031003',
                'jabatan' => 'Petugas Distribusi & Perlengkapan',
                'unit_kerja' => 'Subbag Tata Usaha',
            ],
            [
                'nama' => 'Dra. Hj. Wahyuni, Apt.',
                'nip' => '197505102000032001',
                'jabatan' => 'Kepala Bagian Tata Usaha',
                'unit_kerja' => 'BBPOM di Banjarmasin',
            ],
            [
                'nama' => 'Bambang Hendrawan, S.Farm.',
                'nip' => '199201152019021002',
                'jabatan' => 'Penguji Mikrobiologi Ahli Pertama',
                'unit_kerja' => 'Laboratorium Pengujian Mikrobiologi',
            ],
            [
                'nama' => 'Rina Kartika, A.Md.',
                'nip' => '199411032020122004',
                'jabatan' => 'Pengadministrasi Pelayanan Publik',
                'unit_kerja' => 'Pelayanan Publik & Sertifikasi',
            ],
        ];

        foreach ($pegawaiList as $p) {
            Pegawai::updateOrCreate(['nip' => $p['nip']], $p);
        }

        // =====================================================
        // 4. USERS (LOGIN SISTEM SIBOB)
        // =====================================================
        $userList = [
            [
                'email' => 'admin@pom.go.id',
                'name' => 'Yuli Supriyatno',
                'role' => 'Operator',
                'password' => Hash::make('password'),
            ],
            [
                'email' => 'distribusi@pom.go.id',
                'name' => 'M. Ridwan Prasetyo, S.E.',
                'role' => 'User',
                'password' => Hash::make('password'),
            ],
            [
                'email' => 'ade.rahma@pom.go.id',
                'name' => 'Ade Rahma Yanti, S.Farm., Apt.',
                'role' => 'Operator',
                'password' => Hash::make('password'),
            ],
            [
                'email' => 'fauzi.tik@pom.go.id',
                'name' => 'Ahmad Fauzi, S.Kom.',
                'role' => 'Operator',
                'password' => Hash::make('password'),
            ],
            [
                'email' => 'siti.lab@pom.go.id',
                'name' => 'Siti Nurhaliza, S.Si.',
                'role' => 'User',
                'password' => Hash::make('password'),
            ],
            [
                'email' => 'bambang.mikro@pom.go.id',
                'name' => 'Bambang Hendrawan, S.Farm.',
                'role' => 'User',
                'password' => Hash::make('password'),
            ],
            [
                'email' => 'wahyuni.tu@pom.go.id',
                'name' => 'Dra. Hj. Wahyuni, Apt.',
                'role' => 'User',
                'password' => Hash::make('password'),
            ],
            [
                'email' => 'rina.adm@pom.go.id',
                'name' => 'Rina Kartika, A.Md.',
                'role' => 'User',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($userList as $u) {
            User::updateOrCreate(['email' => $u['email']], $u);
        }

        // =====================================================
        // 5. DUMMY BMN MASTER (UMUM & PERKANTORAN)
        // Format penanggung jawab: Nama (NIP)
        // =====================================================
        $bmnList = [
            [
                'kode_barang' => '3050104001',
                'nup' => '1',
                'nama_barang' => 'Kursi Kerja Putar Ergonomis Hidrolik',
                'merek' => 'Chitose Alba',
                'tanggal_terima' => '2023-03-15',
                'jenis' => 'BMN-NON TIK',
                'satuan' => 'unit',
                'lokasi' => 'Ruang Tata Usaha',
                'penanggungjawab' => 'Yuli Supriyatno (198001012005011001)',
                'penanggungjawab_nip' => '198001012005011001',
                'kondisi' => 'Baik',
                'jadwal_pemeliharaan' => '2026-11-20',
                'spesifikasi' => 'Busa cetak high density, sandaran jaring mesh breathable, roda nilon 5 titik.',
                'kategori_bmn_id' => 1,
            ],
            [
                'kode_barang' => '3050104002',
                'nup' => '1',
                'nama_barang' => 'Meja Rapat Kayu Jati Oval 12 Kursi',
                'merek' => 'Custom Jepara',
                'tanggal_terima' => '2022-08-10',
                'jenis' => 'BMN-NON TIK',
                'satuan' => 'set',
                'lokasi' => 'Ruang Rapat Pimpinan',
                'penanggungjawab' => 'Dra. Hj. Wahyuni, Apt. (197505102000032001)',
                'penanggungjawab_nip' => '197505102000032001',
                'kondisi' => 'Baik',
                'jadwal_pemeliharaan' => '2026-12-05',
                'spesifikasi' => 'Dimensi 400x150x75 cm, finishing melamic doff, dilengkapi jalur kabel socket terpadu.',
                'kategori_bmn_id' => 1,
            ],
            [
                'kode_barang' => '3050104003',
                'nup' => '2',
                'nama_barang' => 'Lemari Arsip Besi 4 Laci Fireproof',
                'merek' => 'Lion Steel',
                'tanggal_terima' => '2021-06-20',
                'jenis' => 'BMN-NON TIK',
                'satuan' => 'unit',
                'lokasi' => 'Ruang Pengarsipan Dokumen',
                'penanggungjawab' => 'Rina Kartika, A.Md. (199411032020122004)',
                'penanggungjawab_nip' => '199411032020122004',
                'kondisi' => 'Baik',
                'jadwal_pemeliharaan' => '2026-10-30',
                'spesifikasi' => 'Ketahanan api 2 jam, central lock dengan kunci kombinasi putar dan anak kunci.',
                'kategori_bmn_id' => 1,
            ],
            [
                'kode_barang' => '3050201004',
                'nup' => '1',
                'nama_barang' => 'AC Split Wall 2 PK Inverter',
                'merek' => 'Daikin FTKQ50',
                'tanggal_terima' => '2023-01-12',
                'jenis' => 'BMN-NON TIK',
                'satuan' => 'unit',
                'lokasi' => 'Laboratorium Pengujian Kimia',
                'penanggungjawab' => 'Ade Rahma Yanti, S.Farm., Apt. (198502152010012002)',
                'penanggungjawab_nip' => '198502152010012002',
                'kondisi' => 'Baik',
                'jadwal_pemeliharaan' => '2026-10-25',
                'spesifikasi' => 'Kapasitas 18.000 BTU, Refrigerant R32 ramah lingkungan, hemat listrik 1.450 W.',
                'kategori_bmn_id' => 1,
            ],
            [
                'kode_barang' => '3050206001',
                'nup' => '1',
                'nama_barang' => 'Genset Silent Diesel 50 kVA',
                'merek' => 'Perkins Stamford',
                'tanggal_terima' => '2020-11-04',
                'jenis' => 'BMN-NON TIK',
                'satuan' => 'unit',
                'lokasi' => 'Gudang BMN Banjarbaru',
                'penanggungjawab' => 'M. Ridwan Prasetyo, S.E. (198703202012031003)',
                'penanggungjawab_nip' => '198703202012031003',
                'kondisi' => 'Baik',
                'jadwal_pemeliharaan' => '2026-11-15',
                'spesifikasi' => 'Mesin 4 silinder turbo, kanopi kedap suara 68 dB pada 7 meter, panel ATS otomatis.',
                'kategori_bmn_id' => 1,
            ],
            [
                'kode_barang' => '3050104005',
                'nup' => '1',
                'nama_barang' => 'Sofa Tamu Eksekutif Kulit 3+1+1',
                'merek' => 'Informa Leatherette',
                'tanggal_terima' => '2022-04-18',
                'jenis' => 'BMN-NON TIK',
                'satuan' => 'set',
                'lokasi' => 'Ruang Kepala Balai',
                'penanggungjawab' => 'Dra. Hj. Wahyuni, Apt. (197505102000032001)',
                'penanggungjawab_nip' => '197505102000032001',
                'kondisi' => 'Baik',
                'jadwal_pemeliharaan' => '2026-12-10',
                'spesifikasi' => 'Lapisan kulit sintetis oscar hitam premium, rangka kayu mahoni oven antikarat.',
                'kategori_bmn_id' => 1,
            ],
            [
                'kode_barang' => '3050104006',
                'nup' => '3',
                'nama_barang' => 'Filling Cabinet 3 Laci Baja',
                'merek' => 'VIP Steel',
                'tanggal_terima' => '2019-09-14',
                'jenis' => 'BMN-NON TIK',
                'satuan' => 'unit',
                'lokasi' => 'Ruang Tata Usaha',
                'penanggungjawab' => 'Yuli Supriyatno (198001012005011001)',
                'penanggungjawab_nip' => '198001012005011001',
                'kondisi' => 'Rusak',
                'jadwal_pemeliharaan' => '2026-10-18',
                'spesifikasi' => 'Kunci laci bawah macet dan rel geser aus, perlu penggantian rel bearing.',
                'kategori_bmn_id' => 1,
            ],
        ];

        foreach ($bmnList as $b) {
            Bmn::updateOrCreate(
                ['kode_barang' => $b['kode_barang'], 'nup' => $b['nup']],
                $b
            );
        }

        // =====================================================
        // 6. DUMMY BMN TIK (`bmns_tik`)
        // Format penanggung jawab: Nama (NIP)
        // =====================================================
        $bmnTikList = [
            [
                'kode_barang' => '3100102001',
                'nup' => 1,
                'nama_barang' => 'PC Workstation All-in-One 24 Inch',
                'jenis' => 'Komputer PC',
                'lokasi' => 'Ruang Tata Usaha',
                'penanggungjawab' => 'Yuli Supriyatno (198001012005011001)',
            ],
            [
                'kode_barang' => '3100102002',
                'nup' => 1,
                'nama_barang' => 'Laptop ThinkPad T14 Gen 3 Core i7',
                'jenis' => 'Laptop / Notebook',
                'lokasi' => 'Subbag Tata Usaha',
                'penanggungjawab' => 'Ahmad Fauzi, S.Kom. (198804122014021001)',
            ],
            [
                'kode_barang' => '3100101001',
                'nup' => 1,
                'nama_barang' => 'Server Rackmount Dell PowerEdge R750',
                'jenis' => 'Server',
                'lokasi' => 'Ruang Server & TIK',
                'penanggungjawab' => 'Ahmad Fauzi, S.Kom. (198804122014021001)',
            ],
            [
                'kode_barang' => '3100203001',
                'nup' => 1,
                'nama_barang' => 'Switch Managed 48-Port Cisco Catalyst 2960-X',
                'jenis' => 'Perangkat Jaringan',
                'lokasi' => 'Ruang Server & TIK',
                'penanggungjawab' => 'Ahmad Fauzi, S.Kom. (198804122014021001)',
            ],
            [
                'kode_barang' => '3100201001',
                'nup' => 1,
                'nama_barang' => 'Printer HP LaserJet Pro MFP 4103fdw',
                'jenis' => 'Printer',
                'lokasi' => 'Ruang Pelayanan Publik / Sertifikasi',
                'penanggungjawab' => 'Rina Kartika, A.Md. (199411032020122004)',
            ],
            [
                'kode_barang' => '3100202001',
                'nup' => 1,
                'nama_barang' => 'Scanner High Speed Fujitsu fi-7160 Duplex',
                'jenis' => 'Scanner',
                'lokasi' => 'Ruang Pengarsipan Dokumen',
                'penanggungjawab' => 'Rina Kartika, A.Md. (199411032020122004)',
            ],
            [
                'kode_barang' => '3100204001',
                'nup' => 1,
                'nama_barang' => 'UPS Online APC Smart-UPS RT 5000VA',
                'jenis' => 'UPS',
                'lokasi' => 'Ruang Server & TIK',
                'penanggungjawab' => 'Ahmad Fauzi, S.Kom. (198804122014021001)',
            ],
            [
                'kode_barang' => '3100205001',
                'nup' => 1,
                'nama_barang' => 'Proyektor Epson EB-2250U Full HD 5000 Lumens',
                'jenis' => 'Multimedia',
                'lokasi' => 'Ruang Rapat Pimpinan',
                'penanggungjawab' => 'M. Ridwan Prasetyo, S.E. (198703202012031003)',
            ],
        ];

        foreach ($bmnTikList as $tik) {
            BmnTik::updateOrCreate(
                ['kode_barang' => $tik['kode_barang'], 'nup' => $tik['nup']],
                $tik
            );
        }

        // =====================================================
        // 7. DUMMY BMN LABORATORIUM (`bmns_lab`)
        // Format penanggung jawab: Nama (NIP)
        // =====================================================
        $bmnLabList = [
            [
                'kode_barang' => '3080101001',
                'nup' => 1,
                'nama_barang' => 'Spectrophotometer UV-Vis Double Beam',
                'merek' => 'Shimadzu UV-1900i',
                'lokasi' => 'Laboratorium Pengujian Kimia',
                'penanggungjawab' => 'Ade Rahma Yanti, S.Farm., Apt. (198502152010012002)',
                'kondisi' => 'Baik',
                'status_kalibrasi' => 'Berlaku',
                'tanggal_kalibrasi' => '2026-04-12',
            ],
            [
                'kode_barang' => '3080101002',
                'nup' => 1,
                'nama_barang' => 'High Performance Liquid Chromatography (HPLC)',
                'merek' => 'Shimadzu Prominence-i LC-2030C 3D Plus',
                'lokasi' => 'Laboratorium Pengujian Kimia',
                'penanggungjawab' => 'Siti Nurhaliza, S.Si. (199008202015032003)',
                'kondisi' => 'Baik',
                'status_kalibrasi' => 'Perlu Kalibrasi',
                'tanggal_kalibrasi' => '2025-10-10',
            ],
            [
                'kode_barang' => '3080102001',
                'nup' => 1,
                'nama_barang' => 'Timbangan Analitik Presisi Mikro 5 Desimal',
                'merek' => 'Mettler Toledo XPR205',
                'lokasi' => 'Laboratorium Pengujian Kimia',
                'penanggungjawab' => 'Ade Rahma Yanti, S.Farm., Apt. (198502152010012002)',
                'kondisi' => 'Baik',
                'status_kalibrasi' => 'Berlaku',
                'tanggal_kalibrasi' => '2026-06-18',
            ],
            [
                'kode_barang' => '3080103001',
                'nup' => 1,
                'nama_barang' => 'Autoclave Digital Vertical 85 Liter',
                'merek' => 'Tuttnauer 3870EL',
                'lokasi' => 'Laboratorium Pengujian Mikrobiologi',
                'penanggungjawab' => 'Bambang Hendrawan, S.Farm. (199201152019021002)',
                'kondisi' => 'Baik',
                'status_kalibrasi' => 'Berlaku',
                'tanggal_kalibrasi' => '2026-05-20',
            ],
            [
                'kode_barang' => '3080102002',
                'nup' => 1,
                'nama_barang' => 'pH Meter Benchtop dengan Probe Kaca Elektroda',
                'merek' => 'Metrohm 913 pH/DO',
                'lokasi' => 'Laboratorium Pengujian Kimia',
                'penanggungjawab' => 'Siti Nurhaliza, S.Si. (199008202015032003)',
                'kondisi' => 'Baik',
                'status_kalibrasi' => 'Jatuh Tempo',
                'tanggal_kalibrasi' => '2025-08-15',
            ],
            [
                'kode_barang' => '3080104001',
                'nup' => 1,
                'nama_barang' => 'Incubator Bakteriologis Stainless Steel 108 L',
                'merek' => 'Memmert IN110',
                'lokasi' => 'Laboratorium Pengujian Mikrobiologi',
                'penanggungjawab' => 'Bambang Hendrawan, S.Farm. (199201152019021002)',
                'kondisi' => 'Baik',
                'status_kalibrasi' => 'Berlaku',
                'tanggal_kalibrasi' => '2026-07-05',
            ],
            [
                'kode_barang' => '3080105001',
                'nup' => 1,
                'nama_barang' => 'Biosafety Cabinet Class II Type A2 4 Feet',
                'merek' => 'Esco Airstream AC2-4E8',
                'lokasi' => 'Laboratorium Pengujian Mikrobiologi',
                'penanggungjawab' => 'Bambang Hendrawan, S.Farm. (199201152019021002)',
                'kondisi' => 'Baik',
                'status_kalibrasi' => 'Berlaku',
                'tanggal_kalibrasi' => '2026-03-30',
            ],
            [
                'kode_barang' => '3080101003',
                'nup' => 1,
                'nama_barang' => 'Refractometer Otomatis Digital Brix / RI',
                'merek' => 'Rudolph Research J357',
                'lokasi' => 'Laboratorium Kosmetik & Obat Bahan Alam',
                'penanggungjawab' => 'Siti Nurhaliza, S.Si. (199008202015032003)',
                'kondisi' => 'Baik',
                'status_kalibrasi' => 'Belum Ada Data',
                'tanggal_kalibrasi' => null,
            ],
        ];

        foreach ($bmnLabList as $lab) {
            BmnLab::updateOrCreate(
                ['kode_barang' => $lab['kode_barang'], 'nup' => $lab['nup']],
                $lab
            );
        }

        // =====================================================
        // 8. DUMMY BA PERPINDAHAN
        // =====================================================
        $baList = [
            [
                'nomor_ba' => 'BA/001/BMN/BBPOM/X/2026',
                'tanggal' => '2026-10-06',
                'nama_barang' => 'PC Workstation All-in-One 24 Inch',
                'dari' => 'Ruang Tata Usaha',
                'ke' => 'Ruang Pelayanan Publik / Sertifikasi',
                'alasan' => 'Reorganisasi loket pendaftaran sertifikasi UMKM produk pangan.',
                'pihak_kedua' => 'Rina Kartika, A.Md. (199411032020122004)',
                'pengaju_email' => 'distribusi@pom.go.id',
                'pengaju_nama' => 'M. Ridwan Prasetyo, S.E.',
                'status' => 'Diajukan',
            ],
            [
                'nomor_ba' => 'BA/002/BMN/BBPOM/X/2026',
                'tanggal' => '2026-10-04',
                'nama_barang' => 'Laptop ThinkPad T14 Gen 3 Core i7',
                'dari' => 'Subbag Tata Usaha',
                'ke' => 'Laboratorium Pengujian Kimia',
                'alasan' => 'Dukungan input data aplikasi pelaporan hasil uji LIMS Balai.',
                'pihak_kedua' => 'Siti Nurhaliza, S.Si. (199008202015032003)',
                'pengaju_email' => 'fauzi.tik@pom.go.id',
                'pengaju_nama' => 'Ahmad Fauzi, S.Kom.',
                'status' => 'Disetujui',
                'tanggal_keputusan' => '2026-10-05',
                'diputuskan_oleh' => 'admin@pom.go.id',
                'catatan_operator' => 'Disetujui untuk operasional input data lab kimia.',
            ],
            [
                'nomor_ba' => 'BA/003/BMN/BBPOM/X/2026',
                'tanggal' => '2026-10-02',
                'nama_barang' => 'Kursi Kerja Putar Ergonomis Hidrolik',
                'dari' => 'Gudang BMN Banjarbaru',
                'ke' => 'Ruang Rapat Pimpinan',
                'alasan' => 'Penambahan kursi cadangan untuk rapat koordinasi lintas satker.',
                'pihak_kedua' => 'Dra. Hj. Wahyuni, Apt. (197505102000032001)',
                'pengaju_email' => 'distribusi@pom.go.id',
                'pengaju_nama' => 'M. Ridwan Prasetyo, S.E.',
                'status' => 'Disetujui',
                'tanggal_keputusan' => '2026-10-03',
                'diputuskan_oleh' => 'admin@pom.go.id',
                'catatan_operator' => 'Aset telah diserahkan dalam kondisi baik.',
            ],
        ];

        foreach ($baList as $ba) {
            BaPerpindahan::updateOrCreate(['nomor_ba' => $ba['nomor_ba']], $ba);
        }

        // =====================================================
        // 9. DUMMY DISTRIBUSI BMN
        // =====================================================
        $distribusiList = [
            [
                'nomor' => 'DST-2026-001',
                'tanggal' => '2026-10-07',
                'nama_barang' => 'Kursi Kerja Putar Ergonomis Hidrolik',
                'jumlah' => 4,
                'satuan' => 'unit',
                'lokasi_asal' => 'Gudang BMN Banjarbaru',
                'lokasi_tujuan' => 'Ruang Pelayanan Publik / Sertifikasi',
                'penerima' => 'Rina Kartika, A.Md. (199411032020122004)',
                'keterangan' => 'Pengadaan triwulan III untuk peningkatan kenyamanan ruang pelayanan.',
                'status' => 'Diajukan',
            ],
            [
                'nomor' => 'DST-2026-002',
                'tanggal' => '2026-10-05',
                'nama_barang' => 'Printer HP LaserJet Pro MFP 4103fdw',
                'jumlah' => 1,
                'satuan' => 'unit',
                'lokasi_asal' => 'Ruang Server & TIK',
                'lokasi_tujuan' => 'Ruang Tata Usaha',
                'penerima' => 'Yuli Supriyatno (198001012005011001)',
                'keterangan' => 'Distribusi unit printer pengganti unit lama yang rusak berat.',
                'status' => 'Disetujui',
            ],
            [
                'nomor' => 'DST-2026-003',
                'tanggal' => '2026-10-03',
                'nama_barang' => 'UPS Online APC Smart-UPS RT 5000VA',
                'jumlah' => 1,
                'satuan' => 'unit',
                'lokasi_asal' => 'Gudang BMN Banjarbaru',
                'lokasi_tujuan' => 'Laboratorium Pengujian Mikrobiologi',
                'penerima' => 'Bambang Hendrawan, S.Farm. (199201152019021002)',
                'keterangan' => 'Pengamanan pasokan listrik instrumen inkubator dan autoclave.',
                'status' => 'Disetujui',
            ],
        ];

        foreach ($distribusiList as $dst) {
            Distribusi::updateOrCreate(['nomor' => $dst['nomor']], $dst);
        }

        // =====================================================
        // 10. DUMMY LAPORAN KERUSAKAN
        // =====================================================
        $laporanList = [
            [
                'nomor' => 'LK-2026-001',
                'tanggal' => '2026-10-08',
                'nama_barang' => 'AC Split Wall 2 PK Inverter',
                'lokasi' => 'Laboratorium Pengujian Kimia',
                'tingkat' => 'Sedang',
                'deskripsi' => 'Udara pendingin keluar tidak maksimal dan terdengar suara getaran dari outdoor unit.',
                'pelapor_email' => 'siti.lab@pom.go.id',
                'pelapor_nama' => 'Siti Nurhaliza, S.Si.',
                'status' => 'Dilaporkan',
            ],
            [
                'nomor' => 'LK-2026-002',
                'tanggal' => '2026-10-06',
                'nama_barang' => 'Filling Cabinet 3 Laci Baja',
                'lokasi' => 'Ruang Tata Usaha',
                'tingkat' => 'Ringan',
                'deskripsi' => 'Rel laci bagian bawah terlepas dari jalur pengunci sehingga sulit ditutup.',
                'pelapor_email' => 'admin@pom.go.id',
                'pelapor_nama' => 'Yuli Supriyatno',
                'status' => 'Diproses',
                'tanggapan' => 'Sudah dijadwalkan teknisi mebel rekanan untuk perbaikan rel geser.',
                'tanggal_update' => '2026-10-07',
                'ditanggapi_oleh' => 'admin@pom.go.id',
            ],
            [
                'nomor' => 'LK-2026-003',
                'tanggal' => '2026-09-28',
                'nama_barang' => 'Switch Managed 48-Port Cisco Catalyst',
                'lokasi' => 'Ruang Server & TIK',
                'tingkat' => 'Berat',
                'deskripsi' => 'Port ethernet 1-12 mati total akibat lonjakan arus petir pada panel distribusi.',
                'pelapor_email' => 'fauzi.tik@pom.go.id',
                'pelapor_nama' => 'Ahmad Fauzi, S.Kom.',
                'status' => 'Selesai',
                'tanggapan' => 'Modul power supply internal dan surge protector diganti baru melalui garansi resmi.',
                'tanggal_update' => '2026-10-01',
                'ditanggapi_oleh' => 'admin@pom.go.id',
            ],
        ];

        foreach ($laporanList as $lk) {
            LaporanKerusakan::updateOrCreate(['nomor' => $lk['nomor']], $lk);
        }

        // =====================================================
        // 11. DUMMY PENGEMBALIAN BMN
        // =====================================================
        $pengembalianList = [
            [
                'nomor_pengajuan' => 'KMB-2026-001',
                'tanggal' => '2026-10-08',
                'nama_pengaju' => 'Bambang Hendrawan, S.Farm. (199201152019021002)',
                'bidang' => 'Pengujian',
                'status' => 'Diajukan',
            ],
            [
                'nomor_pengajuan' => 'KMB-2026-002',
                'tanggal' => '2026-10-03',
                'nama_pengaju' => 'Rina Kartika, A.Md. (199411032020122004)',
                'bidang' => 'Pelayanan Publik & Sertifikasi',
                'status' => 'Disetujui',
            ],
        ];

        foreach ($pengembalianList as $kmb) {
            Pengembalian::updateOrCreate(['nomor_pengajuan' => $kmb['nomor_pengajuan']], $kmb);
        }
    }
}
