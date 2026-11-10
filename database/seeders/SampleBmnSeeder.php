<?php

namespace Database\Seeders;

use App\Models\Bmn;
use App\Models\BmnAngkutan;
use App\Models\BmnLab;
use App\Models\BmnTik;
use App\Models\Lokasi;
use App\Models\Pegawai;
use App\Services\BmnService;
use Illuminate\Database\Seeder;

class SampleBmnSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'kode_barang' => '3050204001',
                'nup' => '1',
                'nama_barang' => 'Laptop Lenovo ThinkPad T14 Gen 4',
                'merek' => 'Lenovo',
                'no_seri' => 'PF-4X8891',
                'tahun_perolehan' => 2024,
                'nilai_perolehan' => 18500000,
                'jenis' => 'BMN-TIK',
                'satuan' => 'Unit',
                'lokasi' => 'RUANG STAFF TATA USAHA',
                'penanggungjawab' => 'Ahmad Fauzi, S.Kom.',
                'kondisi' => 'Baik',
                'spesifikasi' => 'Intel Core i7-1365U, 16GB RAM, 512GB SSD NVMe, Windows 11 Pro',
            ],
            [
                'kode_barang' => '3050201002',
                'nup' => '1',
                'nama_barang' => 'Server Rack HPE ProLiant DL380 Gen10',
                'merek' => 'HPE',
                'no_seri' => 'SGH82310XZ',
                'tahun_perolehan' => 2023,
                'nilai_perolehan' => 85000000,
                'jenis' => 'BMN-TIK',
                'satuan' => 'Unit',
                'lokasi' => 'RUANG MONITORING CCTV GEDUNG A',
                'penanggungjawab' => 'Ahmad Fauzi, S.Kom.',
                'kondisi' => 'Baik',
                'spesifikasi' => '2x Intel Xeon Silver 4210R, 64GB ECC RAM, 4x 1.2TB SAS HDD RAID 5',
            ],
            [
                'kode_barang' => '3080101001',
                'nup' => '1',
                'nama_barang' => 'Spektrofotometer UV-Vis Double Beam',
                'merek' => 'Shimadzu UV-2600i',
                'no_seri' => 'A12055600213',
                'tahun_perolehan' => 2023,
                'nilai_perolehan' => 320000000,
                'jenis' => 'BMN-LAB',
                'satuan' => 'Unit',
                'lokasi' => 'RUANG INSTRUMEN 1.1',
                'penanggungjawab' => 'Siti Nurhaliza, S.Si.',
                'kondisi' => 'Baik',
                'spesifikasi' => 'Double Beam Optical System, Range 185 - 900 nm, Software LabSolutions UV-Vis',
            ],
            [
                'kode_barang' => '3080102005',
                'nup' => '1',
                'nama_barang' => 'Mikroskop Fluoresensi Binokuler',
                'merek' => 'Olympus BX53',
                'no_seri' => 'OLY-BX53-9021',
                'tahun_perolehan' => 2022,
                'nilai_perolehan' => 145000000,
                'jenis' => 'BMN-LAB',
                'satuan' => 'Unit',
                'lokasi' => 'RUANG DISKUSI LAB KIMIA',
                'penanggungjawab' => 'Bambang Hendrawan, S.Farm.',
                'kondisi' => 'Baik',
                'spesifikasi' => 'Pembesaran 40x - 1000x, Filter Cube DAPI/FITC/TRITC, Kamera Digital DP74',
            ],
            [
                'kode_barang' => '3080105003',
                'nup' => '1',
                'nama_barang' => 'Timbangan Analitik Presisi (Micro Balance)',
                'merek' => 'Mettler Toledo XPR205',
                'no_seri' => 'MT-XPR-4458',
                'tahun_perolehan' => 2024,
                'nilai_perolehan' => 78000000,
                'jenis' => 'BMN-LAB',
                'satuan' => 'Unit',
                'lokasi' => 'RUANG ICPMS',
                'penanggungjawab' => 'Ade Rahma Yanti, S.Farm., Apt.',
                'kondisi' => 'Baik',
                'spesifikasi' => 'Kapasitas 220 g, Ketelitian 0.01 mg, Kalibrasi Internal Otomatis ProFACT',
            ],
            [
                'kode_barang' => '3010101001',
                'nup' => '1',
                'nama_barang' => 'Mobil Dinas Operasional MPV',
                'merek' => 'Toyota Kijang Innova Zenix 2.0 V',
                'no_seri' => 'MHFA1BA3P801234',
                'no_polisi' => 'DA 1234 BP',
                'tahun_perolehan' => 2023,
                'nilai_perolehan' => 425000000,
                'jenis' => 'BMN-ANGKUTAN',
                'satuan' => 'Unit',
                'lokasi' => 'Area Parkir & Garasi Dinas',
                'penanggungjawab' => 'M. Ridwan Prasetyo, S.E.',
                'kondisi' => 'Baik',
                'spesifikasi' => 'Mesin 2.0L M20A-FKS CVT, Bahan Bakar Bensin, Warna Hitam Metalik',
            ],
            [
                'kode_barang' => '3010201002',
                'nup' => '1',
                'nama_barang' => 'Sepeda Motor Dinas Operasional Lapangan',
                'merek' => 'Honda Vario 160 CBS',
                'no_seri' => 'MH1KF7118PK90876',
                'no_polisi' => 'DA 5678 QA',
                'tahun_perolehan' => 2024,
                'nilai_perolehan' => 28500000,
                'jenis' => 'BMN-ANGKUTAN',
                'satuan' => 'Unit',
                'lokasi' => 'Area Parkir & Garasi Dinas',
                'penanggungjawab' => 'Yuli Supriyatno',
                'kondisi' => 'Baik',
                'spesifikasi' => 'Mesin 160cc 4-Katup eSP+, PGM-FI, Warna Putih Mutiara',
            ],
            [
                'kode_barang' => '3050101003',
                'nup' => '1',
                'nama_barang' => 'Meja Rapat Pimpinan Eksekutif Oval',
                'merek' => 'Chitose',
                'no_seri' => null,
                'tahun_perolehan' => 2023,
                'nilai_perolehan' => 15000000,
                'jenis' => 'BMN-NON TIK',
                'satuan' => 'Buah',
                'lokasi' => 'RUANG RAPAT BESAR',
                'penanggungjawab' => 'Kepala Tata Usaha',
                'kondisi' => 'Baik',
                'spesifikasi' => 'Dimensi 360 x 140 x 75 cm, Bahan Kayu Mahoni Finishing Melamine Walnut',
            ],
            [
                'kode_barang' => '3050102004',
                'nup' => '1',
                'nama_barang' => 'Lemari Arsip Besi 4 Pintu Swing Door',
                'merek' => 'Lion Steel',
                'no_seri' => null,
                'tahun_perolehan' => 2023,
                'nilai_perolehan' => 6500000,
                'jenis' => 'BMN-NON TIK',
                'satuan' => 'Unit',
                'lokasi' => 'RUANG KEPALA BAGAIN TATA USAHA',
                'penanggungjawab' => 'Rina Kartika, A.Md.',
                'kondisi' => 'Baik',
                'spesifikasi' => 'Ketebalan plat 0.8 mm, Powder Coating Anti Karat, Kunci Sentral',
            ],
            [
                'kode_barang' => '3050203009',
                'nup' => '1',
                'nama_barang' => 'Workstation Analisis Data Spektrometri & Lab',
                'merek' => 'Dell Precision 3660 Tower',
                'no_seri' => 'DL-PREC-77124',
                'tahun_perolehan' => 2024,
                'nilai_perolehan' => 42000000,
                'jenis' => 'BMN-TIK & LAB',
                'satuan' => 'Unit',
                'lokasi' => 'RUANG OLAH DATA OT KOS',
                'penanggungjawab' => 'Ade Rahma Yanti, S.Farm., Apt.',
                'kondisi' => 'Baik',
                'spesifikasi' => 'Dual Tracking TIK + Lab: Intel Core i9-13900K, 64GB DDR5 RAM, NVIDIA RTX A4000 16GB, Terintegrasi Software Analitik',
            ],
        ];

        /** @var BmnService $bmnService */
        $bmnService = app(BmnService::class);

        foreach ($items as $data) {
            $bmnService->storeRecord($data);
        }
    }
}
