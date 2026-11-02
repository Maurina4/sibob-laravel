import json

with open("scratch/lokasi_parsed.json", "r", encoding="utf-8") as f:
    rooms = json.load(f)

# 1. Generate MasterLokasiSeeder.php
seeder_lines = [
    "<?php",
    "",
    "namespace Database\\Seeders;",
    "",
    "use App\\Models\\Lokasi;",
    "use Illuminate\\Database\\Seeder;",
    "use Illuminate\\Support\\Facades\\DB;",
    "",
    "class MasterLokasiSeeder extends Seeder",
    "{",
    "    public function run(): void",
    "    {",
    "        $locations = ["
]

for r in rooms:
    k = r['kode_ruangan'].replace("'", "\\'")
    n = r['nama'].replace("'", "\\'")
    seeder_lines.append(f"            {r['id']} => ['kode_ruangan' => '{k}', 'nama' => '{n}'],")

seeder_lines.extend([
    "        ];",
    "",
    "        $driver = DB::getDriverName();",
    "        if ($driver === 'mysql') {",
    "            DB::statement('SET FOREIGN_KEY_CHECKS=0;');",
    "        }",
    "",
    "        foreach ($locations as $id => $item) {",
    "            Lokasi::updateOrCreate(",
    "                ['id' => $id],",
    "                [",
    "                    'kode_ruangan' => $item['kode_ruangan'],",
    "                    'nama' => $item['nama'],",
    "                ]",
    "            );",
    "        }",
    "",
    "        if ($driver === 'mysql') {",
    "            DB::statement('SET FOREIGN_KEY_CHECKS=1;');",
    "        }",
    "    }",
    "}",
    ""
])

with open("database/seeders/MasterLokasiSeeder.php", "w", encoding="utf-8") as f:
    f.write("\n".join(seeder_lines))

print("Created database/seeders/MasterLokasiSeeder.php")

# 2. Generate migration 2026_10_20_100000_replace_dummy_master_lokasis_table.php
migration_lines = [
    "<?php",
    "",
    "use Illuminate\\Database\\Migrations\\Migration;",
    "use Illuminate\\Database\\Schema\\Blueprint;",
    "use Illuminate\\Support\\Facades\\DB;",
    "use Illuminate\\Support\\Facades\\Schema;",
    "",
    "return new class extends Migration",
    "{",
    "    public function up(): void",
    "    {",
    "        if (! Schema::hasTable('lokasis')) {",
    "            return;",
    "        }",
    "",
    "        try {",
    "            Schema::table('lokasis', function (Blueprint $table) {",
    "                $table->dropUnique('lokasis_nama_unique');",
    "            });",
    "        } catch (\\Throwable $e) {",
    "            // Index already dropped or not present",
    "        }",
    "",
    "        $driver = DB::getDriverName();",
    "        if ($driver === 'mysql') {",
    "            DB::statement('SET FOREIGN_KEY_CHECKS=0;');",
    "        }",
    "",
    "        $locations = ["
]

for r in rooms:
    k = r['kode_ruangan'].replace("'", "\\'")
    n = r['nama'].replace("'", "\\'")
    migration_lines.append(f"            {r['id']} => ['kode_ruangan' => '{k}', 'nama' => '{n}'],")

migration_lines.extend([
    "        ];",
    "",
    "        foreach ($locations as $id => $item) {",
    "            DB::table('lokasis')->updateOrInsert(",
    "                ['id' => $id],",
    "                [",
    "                    'kode_ruangan' => $item['kode_ruangan'],",
    "                    'nama' => $item['nama'],",
    "                    'updated_at' => now(),",
    "                    'created_at' => now(),",
    "                ]",
    "            );",
    "        }",
    "",
    "        // Update BMN dummy locations so they point to proper new room records",
    "        $mappings = [",
    "            'Ruang Tata Usaha' => ['id' => 35, 'nama' => 'C1.13 RUANG STAFF TATA USAHA'],",
    "            'Ruang Kepala Balai' => ['id' => 21, 'nama' => 'B2.10 RUANG KEPALA BALAI'],",
    "            'Laboratorium Pengujian Kimia' => ['id' => 71, 'nama' => 'A1.01 RUANG DISKUSI LAB KIMIA'],",
    "            'Laboratorium Pengujian Mikrobiologi' => ['id' => 114, 'nama' => 'A2.11 RUANG CUCI LAB MIKRO'],",
    "            'Laboratorium Kosmetik & Obat Bahan Alam' => ['id' => 72, 'nama' => 'A1.02 RUANG OLAH DATA OT KOS'],",
    "            'Ruang Server & TIK' => ['id' => 24, 'nama' => 'C1.02 RUANG MONITOR CCTV GEDUNG C'],",
    "            'Gudang BMN Banjarbaru' => ['id' => 53, 'nama' => 'C.Bs.05 GUDANG RUSAK BERAT'],",
    "            'Ruang Rapat Pimpinan' => ['id' => 20, 'nama' => 'B2.09 RUANG RAPAT KEPALA BALAI'],",
    "            'Ruang Pelayanan Publik / Sertifikasi' => ['id' => 2, 'nama' => 'B1.02 RUANG PELAYANAN/LOBBY'],",
    "            'Ruang Pengarsipan Dokumen' => ['id' => 29, 'nama' => 'C1.07 RUANG ARSIP AKTIF'],",
    "            'Gudang BMN' => ['id' => 49, 'nama' => 'C.Bs.01 GUDANG ATK'],",
    "            'Banjarbaru' => ['id' => 2, 'nama' => 'B1.02 RUANG PELAYANAN/LOBBY'],",
    "            'BJM' => ['id' => 2, 'nama' => 'B1.02 RUANG PELAYANAN/LOBBY'],",
    "            'BPOM' => ['id' => 2, 'nama' => 'B1.02 RUANG PELAYANAN/LOBBY'],",
    "        ];",
    "",
    "        if (Schema::hasTable('bmns')) {",
    "            foreach ($mappings as $oldName => $target) {",
    "                DB::table('bmns')",
    "                    ->where('lokasi', $oldName)",
    "                    ->update([",
    "                        'lokasi' => $target['nama'],",
    "                        'lokasi_id' => $target['id'],",
    "                    ]);",
    "            }",
    "        }",
    "",
    "        if ($driver === 'mysql') {",
    "            DB::statement('SET FOREIGN_KEY_CHECKS=1;');",
    "        }",
    "    }",
    "",
    "    public function down(): void",
    "    {",
    "        // rollback is no-op to preserve master data",
    "    }",
    "};",
    ""
])

with open("database/migrations/2026_10_20_100000_replace_dummy_master_lokasis_table.php", "w", encoding="utf-8") as f:
    f.write("\n".join(migration_lines))

print("Created database/migrations/2026_10_20_100000_replace_dummy_master_lokasis_table.php")
