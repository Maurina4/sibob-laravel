<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Memindahkan data lama:
     *
     * 3090405012-NUP.1
     *
     * menjadi:
     *
     * kode_barang = 3090405012
     * nup         = 1
     */
    public function up(): void
    {
        if (! Schema::hasTable('bmns_tik')) {
            return;
        }

        if (! Schema::hasColumn('bmns_tik', 'kode_nup')) {
            return;
        }

        if (! Schema::hasColumn('bmns_tik', 'nup')) {
            return;
        }

        $data = DB::table('bmns_tik')
            ->select('id', 'kode_nup')
            ->orderBy('id')
            ->get();

        foreach ($data as $item) {
            $kodeNup = trim((string) $item->kode_nup);

            if ($kodeNup === '') {
                continue;
            }

            /*
             * Format utama yang digunakan SIBOB:
             *
             * 3090405012-NUP.1
             */
            if (preg_match(
                '/^(.*?)\s*-\s*NUP\.\s*(\d+)$/i',
                $kodeNup,
                $matches
            )) {
                $kodeBarang = trim($matches[1]);
                $nup = (int) $matches[2];

                DB::table('bmns_tik')
                    ->where('id', $item->id)
                    ->update([
                        'kode_nup' => $kodeBarang,
                        'nup' => $nup,
                    ]);

                continue;
            }

            /*
             * Jika data lama ternyata tidak memiliki
             * format -NUP.1, kita tidak memaksakan
             * pemisahan agar data tidak rusak.
             */
            DB::table('bmns_tik')
                ->where('id', $item->id)
                ->update([
                    'kode_nup' => $kodeNup,
                ]);
        }
    }

    /**
     * Migration ini tidak menggabungkan kembali data.
     *
     * Data lama tetap dipertahankan karena kolom kode_nup
     * masih digunakan sementara sampai tahap berikutnya.
     */
    public function down(): void
    {
        // Tidak melakukan rollback otomatis terhadap data.
        // Kolom kode_nup masih menyimpan kode barang lama.
    }
};
