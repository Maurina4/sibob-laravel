<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Yuli Supriyatno',
                'nip' => '198001012005011001',
                'jabatan' => 'Operator',
                'unit_kerja' => 'BPOM Banjarmasin',
            ],
            [
                'nama' => 'Ade Rahma Yanti',
                'nip' => '198502152010012002',
                'jabatan' => 'Pengelola BMN',
                'unit_kerja' => 'BPOM Banjarmasin',
            ],
            [
                'nama' => 'Petugas Pengaju',
                'nip' => '198703202012031003',
                'jabatan' => 'Petugas',
                'unit_kerja' => 'BPOM Banjarmasin',
            ],
            [
                'nama' => 'Kepala Tata Usaha',
                'nip' => '197905102003121004',
                'jabatan' => 'Kepala Tata Usaha',
                'unit_kerja' => 'BPOM Banjarmasin',
            ],
        ];

        foreach ($data as $pegawai) {
            Pegawai::updateOrCreate(
                ['nip' => $pegawai['nip']],
                $pegawai
            );
        }
    }
}