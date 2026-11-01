<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Utama: pengajuan_pengembalians
        Schema::create('pengajuan_pengembalians', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan')->unique();
            $table->string('nomor_sipanda')->nullable();
            $table->string('bidang');
            $table->date('tanggal');
            $table->foreignId('pegawai_id')->nullable()->constrained('pegawais')->nullOnDelete();
            $table->string('nama_pegawai');
            $table->string('nip_pegawai')->nullable();
            $table->foreignId('diajukan_oleh')->constrained('users')->cascadeOnDelete();
            $table->string('alasan'); // 'pengembalian_biasa', 'rusak_berat'
            $table->string('status')->default('diajukan'); // 'diajukan', 'disetujui', 'ditolak', 'dibatalkan'
            $table->text('catatan_pemohon')->nullable();
            $table->text('catatan_penolakan')->nullable();
            $table->string('nomor_ba')->nullable()->unique();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_pada')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Tabel Detail Item: pengajuan_pengembalian_items
        Schema::create('pengajuan_pengembalian_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_pengembalians')->cascadeOnDelete();
            $table->foreignId('bmn_id')->constrained('bmns')->cascadeOnDelete();

            // Snapshot data barang saat pengajuan
            $table->string('kode_barang');
            $table->integer('nup');
            $table->string('nama_barang');
            $table->string('merek')->nullable();
            $table->string('lokasi_awal')->nullable();
            $table->string('kondisi_awal')->default('Baik');
            $table->string('kondisi')->default('Baik');

            $table->text('keterangan')->nullable();

            // Khusus alasan rusak berat
            $table->date('tanggal_temuan')->nullable();
            $table->text('deskripsi_kerusakan')->nullable();
            $table->foreignId('laporan_kerusakan_id')->nullable()->constrained('laporan_kerusakans')->nullOnDelete();

            $table->timestamps();

            $table->unique(['pengajuan_id', 'bmn_id']);
        });

        // 3. Tambah Kolom Nullable pada Tabel Eksisting laporan_kerusakans
        Schema::table('laporan_kerusakans', function (Blueprint $table) {
            if (! Schema::hasColumn('laporan_kerusakans', 'sumber')) {
                $table->string('sumber')->nullable()->after('status'); // 'pengajuan' atau null (manual)
            }
            if (! Schema::hasColumn('laporan_kerusakans', 'pengajuan_id')) {
                $table->foreignId('pengajuan_id')->nullable()->after('sumber')->constrained('pengajuan_pengembalians')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('laporan_kerusakans', function (Blueprint $table) {
            if (Schema::hasColumn('laporan_kerusakans', 'pengajuan_id')) {
                $table->dropForeign(['pengajuan_id']);
                $table->dropColumn('pengajuan_id');
            }
            if (Schema::hasColumn('laporan_kerusakans', 'sumber')) {
                $table->dropColumn('sumber');
            }
        });

        Schema::dropIfExists('pengajuan_pengembalian_items');
        Schema::dropIfExists('pengajuan_pengembalians');
    }
};
