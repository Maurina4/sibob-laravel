<?php

namespace Database\Seeders;

use App\Models\Bmn;
use App\Models\BmnLab;
use App\Models\BmnTik;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PopulateTahunNilaiPerolehanSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Populate/Update BMN Master
        $bmns = Bmn::all();
        foreach ($bmns as $bmn) {
            $tahun = $bmn->tahun_perolehan;
            $nilai = $bmn->nilai_perolehan;

            if (! $tahun) {
                if ($bmn->tanggal_terima) {
                    $tahun = (int) Carbon::parse($bmn->tanggal_terima)->year;
                } else {
                    $tahun = match ($bmn->id % 5) {
                        0 => 2024,
                        1 => 2023,
                        2 => 2022,
                        3 => 2021,
                        default => 2020,
                    };
                }
            }

            if (! $nilai || (float) $nilai == 0.0) {
                $namaLower = strtolower($bmn->nama_barang);
                $jenis = $bmn->jenis;

                if ($jenis === 'BMN-LAB' || str_contains($namaLower, 'spektro') || str_contains($namaLower, 'hplc') || str_contains($namaLower, 'gc-') || str_contains($namaLower, 'autoclave') || str_contains($namaLower, 'inkubator') || str_contains($namaLower, 'analitik') || str_contains($namaLower, 'mikroskop')) {
                    if (str_contains($namaLower, 'hplc') || str_contains($namaLower, 'gc-ms') || str_contains($namaLower, 'kromatografi')) {
                        $nilai = 650000000;
                    } elseif (str_contains($namaLower, 'spektro') || str_contains($namaLower, 'uv-vis')) {
                        $nilai = 285000000;
                    } elseif (str_contains($namaLower, 'mikroskop') || str_contains($namaLower, 'fluorescen')) {
                        $nilai = 85000000;
                    } elseif (str_contains($namaLower, 'autoclave') || str_contains($namaLower, 'inkubator')) {
                        $nilai = 65000000;
                    } elseif (str_contains($namaLower, 'timbangan') || str_contains($namaLower, 'neraca')) {
                        $nilai = 35000000;
                    } else {
                        $nilai = 45000000 + (($bmn->id % 7) * 12000000);
                    }
                } elseif ($jenis === 'BMN-TIK' || str_contains($namaLower, 'laptop') || str_contains($namaLower, 'pc') || str_contains($namaLower, 'komputer') || str_contains($namaLower, 'server') || str_contains($namaLower, 'printer') || str_contains($namaLower, 'scanner')) {
                    if (str_contains($namaLower, 'server')) {
                        $nilai = 125000000;
                    } elseif (str_contains($namaLower, 'macbook') || str_contains($namaLower, 'thinkpad') || str_contains($namaLower, 'rog')) {
                        $nilai = 26500000;
                    } elseif (str_contains($namaLower, 'laptop') || str_contains($namaLower, 'notebook')) {
                        $nilai = 18500000;
                    } elseif (str_contains($namaLower, 'pc') || str_contains($namaLower, 'komputer') || str_contains($namaLower, 'all-in-one')) {
                        $nilai = 15800000;
                    } elseif (str_contains($namaLower, 'printer') || str_contains($namaLower, 'scanner')) {
                        $nilai = 7500000;
                    } elseif (str_contains($namaLower, 'switch') || str_contains($namaLower, 'router') || str_contains($namaLower, 'ups')) {
                        $nilai = 11000000;
                    } else {
                        $nilai = 14500000 + (($bmn->id % 6) * 1500000);
                    }
                } else {
                    // Umum & Perkantoran
                    if (str_contains($namaLower, 'rapat') || str_contains($namaLower, 'meeting')) {
                        $nilai = 16500000;
                    } elseif (str_contains($namaLower, 'ac') || str_contains($namaLower, 'pendingin')) {
                        $nilai = 7800000;
                    } elseif (str_contains($namaLower, 'lemari') || str_contains($namaLower, 'filing')) {
                        $nilai = 4800000;
                    } elseif (str_contains($namaLower, 'kursi') || str_contains($namaLower, 'chair')) {
                        $nilai = 2400000;
                    } elseif (str_contains($namaLower, 'meja')) {
                        $nilai = 3800000;
                    } else {
                        $nilai = 3200000 + (($bmn->id % 5) * 800000);
                    }
                }
            }

            DB::table('bmns')->where('id', $bmn->id)->update([
                'tahun_perolehan' => $tahun,
                'nilai_perolehan' => $nilai,
            ]);
        }

        // 2. Sync / Populate BMN TIK
        $tiks = BmnTik::all();
        foreach ($tiks as $tik) {
            $bmn = $tik->bmn_id ? Bmn::find($tik->bmn_id) : null;
            if (! $bmn && $tik->kode_barang && $tik->nup) {
                $bmn = Bmn::where('kode_barang', $tik->kode_barang)->where('nup', (string) $tik->nup)->first();
            }

            $tahun = $bmn?->tahun_perolehan ?: ($tik->tahun_perolehan ?: (2020 + ($tik->id % 5)));
            $nilai = $bmn?->nilai_perolehan ?: ($tik->nilai_perolehan ?: (15000000 + (($tik->id % 6) * 2000000)));

            DB::table('bmns_tik')->where('id', $tik->id)->update([
                'tahun_perolehan' => $tahun,
                'nilai_perolehan' => $nilai,
            ]);

            if ($bmn && (! $bmn->tahun_perolehan || ! $bmn->nilai_perolehan)) {
                DB::table('bmns')->where('id', $bmn->id)->update([
                    'tahun_perolehan' => $tahun,
                    'nilai_perolehan' => $nilai,
                ]);
            }
        }

        // 3. Sync / Populate BMN Lab
        $labs = BmnLab::all();
        foreach ($labs as $lab) {
            $bmn = $lab->bmn_id ? Bmn::find($lab->bmn_id) : null;
            if (! $bmn && $lab->kode_barang && $lab->nup) {
                $bmn = Bmn::where('kode_barang', $lab->kode_barang)->where('nup', (string) $lab->nup)->first();
            }

            $tahun = $bmn?->tahun_perolehan ?: ($lab->tahun_perolehan ?: (2019 + ($lab->id % 6)));
            $nilai = $bmn?->nilai_perolehan ?: ($lab->nilai_perolehan ?: (45000000 + (($lab->id % 8) * 35000000)));

            DB::table('bmns_lab')->where('id', $lab->id)->update([
                'tahun_perolehan' => $tahun,
                'nilai_perolehan' => $nilai,
            ]);

            if ($bmn && (! $bmn->tahun_perolehan || ! $bmn->nilai_perolehan)) {
                DB::table('bmns')->where('id', $bmn->id)->update([
                    'tahun_perolehan' => $tahun,
                    'nilai_perolehan' => $nilai,
                ]);
            }
        }
    }
}
