<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lokasis')) {
            return;
        }

        try {
            Schema::table('lokasis', function (Blueprint $table) {
                $table->dropUnique('lokasis_nama_unique');
            });
        } catch (Throwable $e) {
            // Index already dropped or not present
        }

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        $locations = [
            1 => ['kode_ruangan' => 'A1.01', 'nama' => 'RUANG DISKUSI LAB KIMIA'],
            2 => ['kode_ruangan' => 'A1.02', 'nama' => 'RUANG OLAH DATA OT KOS'],
            3 => ['kode_ruangan' => 'A1.03', 'nama' => 'RUANG KERJA KECIL 1'],
            4 => ['kode_ruangan' => 'A1.04', 'nama' => 'RUANG KERJA KECIL 2'],
            5 => ['kode_ruangan' => 'A1.05', 'nama' => 'RUANG MUSHOLA PENGUJIA'],
            6 => ['kode_ruangan' => 'A1.06', 'nama' => 'RUANG ANTARA'],
            7 => ['kode_ruangan' => 'A1.07', 'nama' => 'RUANG GANTI WANITA'],
            8 => ['kode_ruangan' => 'A1.08', 'nama' => 'RUANG GANTI PRIA'],
            9 => ['kode_ruangan' => 'A1.09', 'nama' => 'KORIDOOR'],
            10 => ['kode_ruangan' => 'A1.10', 'nama' => 'RUANG INSTRUMEN 1.1'],
            11 => ['kode_ruangan' => 'A1.11', 'nama' => 'RUANG INSTRUMENT 1.2'],
            12 => ['kode_ruangan' => 'A1.12', 'nama' => 'RUANG FOOD SECURTY'],
            13 => ['kode_ruangan' => 'A1.13', 'nama' => 'RUANG INSTRUMEN 1.3'],
            14 => ['kode_ruangan' => 'A1.14', 'nama' => 'RUANG INSTRUMEN 1.4'],
            15 => ['kode_ruangan' => 'A1.15', 'nama' => 'RUANG PREPARASI 1.3'],
            16 => ['kode_ruangan' => 'A1.16', 'nama' => 'RUANG INSTRUMEN 1.5'],
            17 => ['kode_ruangan' => 'A1.17', 'nama' => 'RUANG ICPMS'],
            18 => ['kode_ruangan' => 'A1.18', 'nama' => 'RUANG MONITORING CCTV GEDUNG A'],
            19 => ['kode_ruangan' => 'A1.19', 'nama' => 'PANTRY'],
            20 => ['kode_ruangan' => 'A1.20', 'nama' => 'RUANG CUCI GELAS OT KOS'],
            21 => ['kode_ruangan' => 'A1.22', 'nama' => 'RUANG PREPARASI 1.1'],
            22 => ['kode_ruangan' => 'A1.23', 'nama' => 'RUANG LEMARI ASAM 1.1'],
            23 => ['kode_ruangan' => 'A1.24', 'nama' => 'RUANG PENYIMPANAN SAMPLE OT KOS'],
            24 => ['kode_ruangan' => 'A1.25', 'nama' => 'RUANG TIMBANG 1.1'],
            25 => ['kode_ruangan' => 'A1.26', 'nama' => 'RUANG TIMBANG 1.2'],
            26 => ['kode_ruangan' => 'A1.27', 'nama' => 'GUDANG LAB GEDUNG A'],
            27 => ['kode_ruangan' => 'A1.28', 'nama' => 'RUANG PREPARASI 1.2'],
            28 => ['kode_ruangan' => 'A1.29', 'nama' => 'RUANG LEMARI ASAM 1.2'],
            29 => ['kode_ruangan' => 'A1.30', 'nama' => 'RUANG SAMPEL PANGAN'],
            30 => ['kode_ruangan' => 'A1.31', 'nama' => 'RUANG CUCI LAB PANGAN'],
            31 => ['kode_ruangan' => 'A1.32', 'nama' => 'RUANG TABUNG GAS'],
            32 => ['kode_ruangan' => 'A1.33', 'nama' => 'TOILET WANITA'],
            33 => ['kode_ruangan' => 'A1.34', 'nama' => 'TOILET PRIA'],
            34 => ['kode_ruangan' => 'A1.35', 'nama' => 'PANTRY'],
            35 => ['kode_ruangan' => 'A2.01', 'nama' => 'RUANG OLAH DATA MIKRO 1'],
            36 => ['kode_ruangan' => 'A2.02', 'nama' => 'RUANG OLAH DATA MIKRO 2'],
            37 => ['kode_ruangan' => 'A2.03', 'nama' => 'RUANG SAMPEL MIKRO'],
            38 => ['kode_ruangan' => 'A2.04', 'nama' => 'RUANG OLAH DATA UTAMA MIKRO'],
            39 => ['kode_ruangan' => 'A2.05', 'nama' => 'RUANG ANTARA'],
            40 => ['kode_ruangan' => 'A2.06', 'nama' => 'RUANG GANTI WANITA'],
            41 => ['kode_ruangan' => 'A2.07', 'nama' => 'RUANG GANTI PRIA'],
            42 => ['kode_ruangan' => 'A2.08', 'nama' => 'KOORIDOR'],
            43 => ['kode_ruangan' => 'A2.10', 'nama' => 'RUANG UJI STERIL'],
            44 => ['kode_ruangan' => 'A2.11', 'nama' => 'RUANG CUCI LAB MIKRO'],
            45 => ['kode_ruangan' => 'A2.12', 'nama' => 'RUANG UJI POTENSI ANTIBIOTIK/PEMBUATAN MEDIA'],
            46 => ['kode_ruangan' => 'A2.13', 'nama' => 'RUANG TIMBANG OBAT NAPZA'],
            47 => ['kode_ruangan' => 'A2.14', 'nama' => 'RUANG PREPARASI II.1'],
            48 => ['kode_ruangan' => 'A2.15', 'nama' => 'RUANG PREPARASI II.2'],
            49 => ['kode_ruangan' => 'A2.16', 'nama' => 'RUANG OLAH DATA LAB NAPZA'],
            50 => ['kode_ruangan' => 'A2.17', 'nama' => 'RUANG SAMPEL NAPZA'],
            51 => ['kode_ruangan' => 'A2.18', 'nama' => 'RUANG AKK'],
            52 => ['kode_ruangan' => 'A2.19', 'nama' => 'RUANG ALT'],
            53 => ['kode_ruangan' => 'A2.20', 'nama' => 'RUANG INKUBASI'],
            54 => ['kode_ruangan' => 'A2.21', 'nama' => 'RUANG INSTRUMEN 2 NAPZA'],
            55 => ['kode_ruangan' => 'A2.22', 'nama' => 'RUANG PREPARASI SAMPEL NAPZA'],
            56 => ['kode_ruangan' => 'A2.23', 'nama' => 'RUANG DISOLASI OBAT NAPZA'],
            57 => ['kode_ruangan' => 'A2.24', 'nama' => 'RUANG CUCI OBAT NAPZA'],
            58 => ['kode_ruangan' => 'A2.25', 'nama' => 'PANTRY'],
            59 => ['kode_ruangan' => 'B1.01', 'nama' => 'RUANG TAMU UTAMA'],
            60 => ['kode_ruangan' => 'B1.02', 'nama' => 'RUANG PELAYANAN/LOBBY'],
            61 => ['kode_ruangan' => 'B1.03', 'nama' => 'RUANG TENANG UTAMA'],
            62 => ['kode_ruangan' => 'B1.05', 'nama' => 'RUANG MEDIA CENTER'],
            63 => ['kode_ruangan' => 'B1.06', 'nama' => 'RUANG STAFF INFOKOM'],
            64 => ['kode_ruangan' => 'B1.07', 'nama' => 'RUANG KONSULTASI DAN PENGADUAN'],
            65 => ['kode_ruangan' => 'B1.09', 'nama' => 'RUANG LAYANAN INFORMASI DAN PPID'],
            66 => ['kode_ruangan' => 'B1.10', 'nama' => 'RUANG KA TIM INFOKOM'],
            67 => ['kode_ruangan' => 'B1.11', 'nama' => 'RUANG LAKTASI'],
            68 => ['kode_ruangan' => 'B1.12', 'nama' => 'RUANG PENGASUH ANAK'],
            69 => ['kode_ruangan' => 'B1.13', 'nama' => 'GUDANG GIMMICK'],
            70 => ['kode_ruangan' => 'B2.01', 'nama' => 'RUANG TENANG 1'],
            71 => ['kode_ruangan' => 'B2.02', 'nama' => 'RUANG TENANG 2'],
            72 => ['kode_ruangan' => 'B2.03', 'nama' => 'RUANG RAPAT BESAR'],
            73 => ['kode_ruangan' => 'B2.04', 'nama' => 'RUANG PERLENGKAPAN'],
            74 => ['kode_ruangan' => 'B2.05', 'nama' => 'RUANG UMUM'],
            75 => ['kode_ruangan' => 'B2.06', 'nama' => 'RUANG SEKRETARIS'],
            76 => ['kode_ruangan' => 'B2.07', 'nama' => 'RUANG KEPALA BAGAIN TATA USAHA'],
            77 => ['kode_ruangan' => 'B2.08', 'nama' => 'RUANG RAPAT KECIL'],
            78 => ['kode_ruangan' => 'B2.09', 'nama' => 'RUANG RAPAT KEPALA BALAI'],
            79 => ['kode_ruangan' => 'B2.10', 'nama' => 'RUANG KEPALA BALAI'],
            80 => ['kode_ruangan' => 'B.BS.01', 'nama' => 'RUANG MUSHOLA UTAMA'],
            81 => ['kode_ruangan' => 'C1.01', 'nama' => 'RUANG GUDANG SEAMPEL'],
            82 => ['kode_ruangan' => 'C1.02', 'nama' => 'RUANG MONITOR CCTV GEDUNG C'],
            83 => ['kode_ruangan' => 'C1.03', 'nama' => 'RUANG GUDANG PEMERIKSAAN'],
            84 => ['kode_ruangan' => 'C1.04', 'nama' => 'RUANG KA TIM PENINDAKAN'],
            85 => ['kode_ruangan' => 'C1.05', 'nama' => 'RUANG STAFF PENINDAKAN'],
            86 => ['kode_ruangan' => 'C1.06', 'nama' => 'RUANG TRANSIT ARSIP'],
            87 => ['kode_ruangan' => 'C1.07', 'nama' => 'RUANG ARSIP AKTIF'],
            88 => ['kode_ruangan' => 'C1.08', 'nama' => 'RUANG RECORD CENTER ARSIP'],
            89 => ['kode_ruangan' => 'C1.09', 'nama' => 'RUANG KA TIM SERTIFIKASI'],
            90 => ['kode_ruangan' => 'C1.10', 'nama' => 'RUANG KA TIM INSPEKSI'],
            91 => ['kode_ruangan' => 'C1.11', 'nama' => 'RUANG STAFF PEMERIKSAAN'],
            92 => ['kode_ruangan' => 'C1.12', 'nama' => 'RUANG KA TIM PEMERIKSAAN'],
            93 => ['kode_ruangan' => 'C1.13', 'nama' => 'RUANG STAFF TATA USAHA'],
            94 => ['kode_ruangan' => 'C1.14', 'nama' => 'RUANG BENDAHARA'],
            95 => ['kode_ruangan' => 'C1.15', 'nama' => 'RUANG KORIDOOR'],
            96 => ['kode_ruangan' => 'C1.16', 'nama' => 'RUANG BAP PENINDAKAN'],
            97 => ['kode_ruangan' => 'C1.17', 'nama' => 'RUANG PANTRY'],
            98 => ['kode_ruangan' => 'C2.01', 'nama' => 'RUANG ARSIF AKTIF PENGUJIAN'],
            99 => ['kode_ruangan' => 'C2.02', 'nama' => 'RUANG STAFF PENGUJIAN 1'],
            100 => ['kode_ruangan' => 'C2.03', 'nama' => 'RUANG KATIM PENGUJIAN'],
            101 => ['kode_ruangan' => 'C2.04', 'nama' => 'RUANG AOC DAN PODCAST'],
            102 => ['kode_ruangan' => 'C2.05', 'nama' => 'RUANG TAMU VIP'],
            103 => ['kode_ruangan' => 'C2.06', 'nama' => 'RUANG KONTROL AUDIO AULA'],
            104 => ['kode_ruangan' => 'C2.07', 'nama' => 'RUANG AULA UTAMA'],
            105 => ['kode_ruangan' => 'C2.08', 'nama' => 'RUANG MUTU'],
            106 => ['kode_ruangan' => 'C2.09', 'nama' => 'PANTRY'],
            107 => ['kode_ruangan' => 'C.Bs.01', 'nama' => 'GUDANG ATK'],
            108 => ['kode_ruangan' => 'C.Bs.02', 'nama' => 'RUANG DRIVER'],
            109 => ['kode_ruangan' => 'C.Bs.03', 'nama' => 'PANTRY'],
            110 => ['kode_ruangan' => 'C.Bs.04', 'nama' => 'GUDANG RAEGENT'],
            111 => ['kode_ruangan' => 'C.Bs.05', 'nama' => 'GUDANG RUSAK BERAT'],
            112 => ['kode_ruangan' => 'C.Bs.06', 'nama' => 'GUDANG BB PENINDAKAN'],
            113 => ['kode_ruangan' => 'C.BS.07', 'nama' => 'RUANG UJI MOLEKULER'],
            114 => ['kode_ruangan' => 'C.Bs.08', 'nama' => 'RUANG STORAGE'],
            115 => ['kode_ruangan' => 'C.Bs.09', 'nama' => 'RUANG PENAMBAHAN TEMPLET'],
            116 => ['kode_ruangan' => 'C.Bs.10', 'nama' => 'RUANG MASTER MIX'],
            117 => ['kode_ruangan' => 'C.Bs.11', 'nama' => 'RUANG LOKER'],
            118 => ['kode_ruangan' => 'C.Bs.12', 'nama' => 'PANTRY'],
            119 => ['kode_ruangan' => 'C.Bs.13', 'nama' => 'RUANG SETRIKA'],
            120 => ['kode_ruangan' => 'C.Bs.14', 'nama' => 'RUANG CUCI DAN REDUKSI'],
            121 => ['kode_ruangan' => 'C.Bs.15', 'nama' => 'RUANG GANTI'],
            122 => ['kode_ruangan' => 'C.Bs.16', 'nama' => 'RUANG SHOWER'],
            123 => ['kode_ruangan' => 'C.Bs.17', 'nama' => 'RUANG EKSTRAKSI'],
            124 => ['kode_ruangan' => 'C.Bs.18', 'nama' => 'RUANG PENERIMA SAMPEL'],
            125 => ['kode_ruangan' => 'C.Bs.19', 'nama' => 'RUANG PCR'],
            126 => ['kode_ruangan' => 'C.Bs.20', 'nama' => 'RUANG KOORIDOR'],
            127 => ['kode_ruangan' => 'C.Bs.21', 'nama' => 'RUANG CUCI PCR'],
            128 => ['kode_ruangan' => 'C.Bs.22', 'nama' => 'RUANG DISKUSI'],
        ];

        foreach ($locations as $id => $item) {
            DB::table('lokasis')->updateOrInsert(
                ['id' => $id],
                [
                    'kode_ruangan' => $item['kode_ruangan'],
                    'nama' => $item['nama'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        // Update BMN dummy locations so they point to proper new room records in A, B, C order
        $mappings = [
            'Ruang Tata Usaha' => ['id' => 93, 'nama' => 'C1.13 RUANG STAFF TATA USAHA'],
            'Ruang Kepala Balai' => ['id' => 79, 'nama' => 'B2.10 RUANG KEPALA BALAI'],
            'Laboratorium Pengujian Kimia' => ['id' => 1, 'nama' => 'A1.01 RUANG DISKUSI LAB KIMIA'],
            'Laboratorium Pengujian Mikrobiologi' => ['id' => 44, 'nama' => 'A2.11 RUANG CUCI LAB MIKRO'],
            'Laboratorium Kosmetik & Obat Bahan Alam' => ['id' => 2, 'nama' => 'A1.02 RUANG OLAH DATA OT KOS'],
            'Ruang Server & TIK' => ['id' => 82, 'nama' => 'C1.02 RUANG MONITOR CCTV GEDUNG C'],
            'Gudang BMN Banjarbaru' => ['id' => 111, 'nama' => 'C.Bs.05 GUDANG RUSAK BERAT'],
            'Ruang Rapat Pimpinan' => ['id' => 78, 'nama' => 'B2.09 RUANG RAPAT KEPALA BALAI'],
            'Ruang Pelayanan Publik / Sertifikasi' => ['id' => 60, 'nama' => 'B1.02 RUANG PELAYANAN/LOBBY'],
            'Ruang Pengarsipan Dokumen' => ['id' => 87, 'nama' => 'C1.07 RUANG ARSIP AKTIF'],
            'Gudang BMN' => ['id' => 107, 'nama' => 'C.Bs.01 GUDANG ATK'],
            'Banjarbaru' => ['id' => 60, 'nama' => 'B1.02 RUANG PELAYANAN/LOBBY'],
            'BJM' => ['id' => 60, 'nama' => 'B1.02 RUANG PELAYANAN/LOBBY'],
            'BPOM' => ['id' => 60, 'nama' => 'B1.02 RUANG PELAYANAN/LOBBY'],
        ];

        if (Schema::hasTable('bmns')) {
            foreach ($mappings as $oldName => $target) {
                DB::table('bmns')
                    ->where('lokasi', $oldName)
                    ->update([
                        'lokasi' => $target['nama'],
                        'lokasi_id' => $target['id'],
                    ]);
            }
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    public function down(): void
    {
        // rollback is no-op to preserve master data
    }
};
