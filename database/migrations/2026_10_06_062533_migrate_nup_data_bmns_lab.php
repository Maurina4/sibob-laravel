<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Memindahkan data lama BMN Lab:
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
        if (!Schema::hasTable('bmns_lab')) {
            return;
        }

        if (!Schema::hasColumn('bmns_lab', 'kode_barang')) {
            return;
        }

        if (!Schema::hasColumn('bmns_lab', 'nup')) {
            return;
        }

        $data = DB::table('bmns_lab')
            ->select('id', 'kode_barang')
            ->orderBy('id')
            ->get();

        foreach ($data as $item) {
            $kodeBarangLama = trim((string) $item->kode_barang);

            if ($kodeBarangLama === '') {
                continue;
            }

            /*
             * Format utama:
             *
             * 3090405012-NUP.1
             */
            if (preg_match(
                '/^(.*?)\s*-\s*NUP\.\s*(\d+)$/i',
                $kodeBarangLama,
                $matches
            )) {
                $kodeBarang = trim($matches[1]);
                $nup = (int) $matches[2];

                DB::table('bmns_lab')
                    ->where('id', $item->id)
                    ->update([
                        'kode_barang' => $kodeBarang,
                        'nup' => $nup,
                    ]);
            }
        }
    }

    /**
     * Tidak mengembalikan data secara otomatis.
     */
    public function down(): void
    {
        // Tidak ada rollback otomatis.
    }
};