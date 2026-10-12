<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Buat Tabel Master Kategori BMN jika belum ada
        if (!Schema::hasTable('kategori_bmns')) {
            Schema::create('kategori_bmns', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 50)->unique(); // UMUM, LAB, TIK
                $table->string('nama', 100);
                $table->text('deskripsi')->nullable();
                $table->timestamps();
            });
        }

        // 2. Normalisasi Tabel bmns (Tambahkan kolom FK dan soft deletes standar Laravel)
        Schema::table('bmns', function (Blueprint $table) {
            if (!Schema::hasColumn('bmns', 'kategori_bmn_id')) {
                $table->foreignId('kategori_bmn_id')->nullable()->after('id')->constrained('kategori_bmns')->nullOnDelete();
            }
            if (!Schema::hasColumn('bmns', 'lokasi_id')) {
                $table->foreignId('lokasi_id')->nullable()->after('lokasi')->constrained('lokasis')->nullOnDelete();
            }
            if (!Schema::hasColumn('bmns', 'penanggung_jawab_id')) {
                $table->foreignId('penanggung_jawab_id')->nullable()->after('penanggungjawab')->constrained('pegawais')->nullOnDelete();
            }
            if (!Schema::hasColumn('bmns', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
            if (!Schema::hasColumn('bmns', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->after('deleted_at')->constrained('users')->nullOnDelete();
            }
        });

        // 3. Buat Tabel Ekstensi bmn_lab_details (1:1 untuk data khusus alat laboratorium)
        if (!Schema::hasTable('bmn_lab_details')) {
            Schema::create('bmn_lab_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bmn_id')->unique()->constrained('bmns')->cascadeOnDelete();
                $table->string('status_kalibrasi')->nullable()->default('Belum Dikalibrasi');
                $table->date('tanggal_kalibrasi')->nullable();
                $table->string('lembaga_kalibrasi')->nullable();
                $table->string('nomor_sertifikat')->nullable();
                $table->timestamps();
            });
        }

        // 4. Tabel Detail Transaksi Distribusi (1:N)
        if (!Schema::hasTable('distribusi_items')) {
            Schema::create('distribusi_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('distribusi_id')->constrained('distribusis')->cascadeOnDelete();
                $table->foreignId('bmn_id')->nullable()->constrained('bmns')->nullOnDelete();
                $table->string('nama_barang_snapshot')->nullable();
                $table->integer('jumlah')->default(1);
                $table->string('satuan', 50)->default('unit');
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }

        // Tambah kolom FK pada distribusis
        Schema::table('distribusis', function (Blueprint $table) {
            if (!Schema::hasColumn('distribusis', 'lokasi_asal_id')) {
                $table->foreignId('lokasi_asal_id')->nullable()->after('nomor')->constrained('lokasis')->nullOnDelete();
            }
            if (!Schema::hasColumn('distribusis', 'lokasi_tujuan_id')) {
                $table->foreignId('lokasi_tujuan_id')->nullable()->after('lokasi_asal_id')->constrained('lokasis')->nullOnDelete();
            }
            if (!Schema::hasColumn('distribusis', 'penerima_id')) {
                $table->foreignId('penerima_id')->nullable()->after('lokasi_tujuan_id')->constrained('pegawais')->nullOnDelete();
            }
            if (!Schema::hasColumn('distribusis', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // 5. Tabel Detail Berita Acara Perpindahan (1:N)
        if (!Schema::hasTable('ba_perpindahan_items')) {
            Schema::create('ba_perpindahan_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ba_perpindahan_id')->constrained('ba_perpindahans')->cascadeOnDelete();
                $table->foreignId('bmn_id')->nullable()->constrained('bmns')->nullOnDelete();
                $table->string('nama_barang_snapshot')->nullable();
                $table->text('kondisi_saat_pindah')->nullable();
                $table->timestamps();
            });
        }

        // Tambah kolom FK pada ba_perpindahans
        Schema::table('ba_perpindahans', function (Blueprint $table) {
            if (!Schema::hasColumn('ba_perpindahans', 'lokasi_asal_id')) {
                $table->foreignId('lokasi_asal_id')->nullable()->after('tanggal')->constrained('lokasis')->nullOnDelete();
            }
            if (!Schema::hasColumn('ba_perpindahans', 'lokasi_tujuan_id')) {
                $table->foreignId('lokasi_tujuan_id')->nullable()->after('lokasi_asal_id')->constrained('lokasis')->nullOnDelete();
            }
            if (!Schema::hasColumn('ba_perpindahans', 'pengaju_id')) {
                $table->foreignId('pengaju_id')->nullable()->after('pihak_kedua')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('ba_perpindahans', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // 6. Normalisasi Laporan Kerusakan
        Schema::table('laporan_kerusakans', function (Blueprint $table) {
            if (!Schema::hasColumn('laporan_kerusakans', 'bmn_id')) {
                $table->foreignId('bmn_id')->nullable()->after('nomor')->constrained('bmns')->nullOnDelete();
            }
            if (!Schema::hasColumn('laporan_kerusakans', 'lokasi_id')) {
                $table->foreignId('lokasi_id')->nullable()->after('bmn_id')->constrained('lokasis')->nullOnDelete();
            }
            if (!Schema::hasColumn('laporan_kerusakans', 'pelapor_id')) {
                $table->foreignId('pelapor_id')->nullable()->after('lokasi_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('laporan_kerusakans', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // 7. Tabel Detail Pengembalian (1:N)
        if (!Schema::hasTable('pengembalian_items')) {
            Schema::create('pengembalian_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pengembalian_id')->constrained('pengembalians')->cascadeOnDelete();
                $table->foreignId('bmn_id')->nullable()->constrained('bmns')->nullOnDelete();
                $table->string('nama_barang_snapshot')->nullable();
                $table->string('kondisi')->default('Baik');
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        // Tambah kolom FK pada pengembalians
        Schema::table('pengembalians', function (Blueprint $table) {
            if (!Schema::hasColumn('pengembalians', 'pengaju_id')) {
                $table->foreignId('pengaju_id')->nullable()->after('tanggal')->constrained('pegawais')->nullOnDelete();
            }
            if (!Schema::hasColumn('pengembalians', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengembalian_items');
        Schema::dropIfExists('ba_perpindahan_items');
        Schema::dropIfExists('distribusi_items');
        Schema::dropIfExists('bmn_lab_details');

        Schema::table('pengembalians', function (Blueprint $table) {
            $table->dropForeign(['pengaju_id']);
            $table->dropColumn(['pengaju_id', 'deleted_at']);
        });

        Schema::table('laporan_kerusakans', function (Blueprint $table) {
            $table->dropForeign(['bmn_id']);
            $table->dropForeign(['lokasi_id']);
            $table->dropForeign(['pelapor_id']);
            $table->dropColumn(['bmn_id', 'lokasi_id', 'pelapor_id', 'deleted_at']);
        });

        Schema::table('ba_perpindahans', function (Blueprint $table) {
            $table->dropForeign(['lokasi_asal_id']);
            $table->dropForeign(['lokasi_tujuan_id']);
            $table->dropForeign(['pengaju_id']);
            $table->dropColumn(['lokasi_asal_id', 'lokasi_tujuan_id', 'pengaju_id', 'deleted_at']);
        });

        Schema::table('distribusis', function (Blueprint $table) {
            $table->dropForeign(['lokasi_asal_id']);
            $table->dropForeign(['lokasi_tujuan_id']);
            $table->dropForeign(['penerima_id']);
            $table->dropColumn(['lokasi_asal_id', 'lokasi_tujuan_id', 'penerima_id', 'deleted_at']);
        });

        Schema::table('bmns', function (Blueprint $table) {
            $table->dropForeign(['kategori_bmn_id']);
            $table->dropForeign(['lokasi_id']);
            $table->dropForeign(['penanggung_jawab_id']);
            $table->dropForeign(['deleted_by']);
            $table->dropColumn(['kategori_bmn_id', 'lokasi_id', 'penanggung_jawab_id', 'deleted_at', 'deleted_by']);
        });

        Schema::dropIfExists('kategori_bmns');
    }
};
