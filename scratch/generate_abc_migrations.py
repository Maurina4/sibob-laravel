import json

with open("scratch/lokasi_reordered.json", "r", encoding="utf-8") as f:
    rooms = json.load(f)

with open("scratch/lokasi_id_map.json", "r", encoding="utf-8") as f:
    id_map = json.load(f)

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

print("Updated database/seeders/MasterLokasiSeeder.php")

# 2. Update migration 2026_10_20_100000_replace_dummy_master_lokasis_table.php
mig_100000 = [
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
    "        } catch (Throwable $e) {",
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
    mig_100000.append(f"            {r['id']} => ['kode_ruangan' => '{k}', 'nama' => '{n}'],")

mig_100000.extend([
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
    "        // Update BMN dummy locations so they point to proper new room records in A, B, C order",
    "        $mappings = [",
    "            'Ruang Tata Usaha' => ['id' => 93, 'nama' => 'C1.13 RUANG STAFF TATA USAHA'],",
    "            'Ruang Kepala Balai' => ['id' => 79, 'nama' => 'B2.10 RUANG KEPALA BALAI'],",
    "            'Laboratorium Pengujian Kimia' => ['id' => 1, 'nama' => 'A1.01 RUANG DISKUSI LAB KIMIA'],",
    "            'Laboratorium Pengujian Mikrobiologi' => ['id' => 44, 'nama' => 'A2.11 RUANG CUCI LAB MIKRO'],",
    "            'Laboratorium Kosmetik & Obat Bahan Alam' => ['id' => 2, 'nama' => 'A1.02 RUANG OLAH DATA OT KOS'],",
    "            'Ruang Server & TIK' => ['id' => 82, 'nama' => 'C1.02 RUANG MONITOR CCTV GEDUNG C'],",
    "            'Gudang BMN Banjarbaru' => ['id' => 111, 'nama' => 'C.Bs.05 GUDANG RUSAK BERAT'],",
    "            'Ruang Rapat Pimpinan' => ['id' => 78, 'nama' => 'B2.09 RUANG RAPAT KEPALA BALAI'],",
    "            'Ruang Pelayanan Publik / Sertifikasi' => ['id' => 60, 'nama' => 'B1.02 RUANG PELAYANAN/LOBBY'],",
    "            'Ruang Pengarsipan Dokumen' => ['id' => 87, 'nama' => 'C1.07 RUANG ARSIP AKTIF'],",
    "            'Gudang BMN' => ['id' => 107, 'nama' => 'C.Bs.01 GUDANG ATK'],",
    "            'Banjarbaru' => ['id' => 60, 'nama' => 'B1.02 RUANG PELAYANAN/LOBBY'],",
    "            'BJM' => ['id' => 60, 'nama' => 'B1.02 RUANG PELAYANAN/LOBBY'],",
    "            'BPOM' => ['id' => 60, 'nama' => 'B1.02 RUANG PELAYANAN/LOBBY'],",
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
    f.write("\n".join(mig_100000))

print("Updated database/migrations/2026_10_20_100000_replace_dummy_master_lokasis_table.php")

# 3. Create new migration 2026_10_20_110000_reorder_master_lokasis_a_b_c.php
mig_110000 = [
    "<?php",
    "",
    "use Illuminate\\Database\\Migrations\\Migration;",
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
    "        // Check if already ordered (ID 1 is A1.01)",
    "        $first = DB::table('lokasis')->where('id', 1)->first();",
    "        if ($first && isset($first->kode_ruangan) && $first->kode_ruangan === 'A1.01') {",
    "            return;",
    "        }",
    "",
    "        $driver = DB::getDriverName();",
    "        if ($driver === 'mysql') {",
    "            DB::statement('SET FOREIGN_KEY_CHECKS=0;');",
    "        }",
    "",
    "        // Step 1: Shift existing IDs by +10000 to prevent primary key collisions during swap",
    "        DB::table('lokasis')->update([",
    "            'id' => DB::raw('id + 10000'),",
    "        ]);",
    "",
    "        // Mapping from old ID to new ID",
    "        $idMap = ["
]

for old_id, new_id in sorted(id_map.items(), key=lambda x: int(x[0])):
    mig_110000.append(f"            {old_id} => {new_id},")

mig_110000.extend([
    "        ];",
    "",
    "        $rooms = ["
])

for r in rooms:
    k = r['kode_ruangan'].replace("'", "\\'")
    n = r['nama'].replace("'", "\\'")
    mig_110000.append(f"            {r['id']} => ['kode_ruangan' => '{k}', 'nama' => '{n}'],")

mig_110000.extend([
    "        ];",
    "",
    "        // Step 2: Update each shifted row to its target new ID and attributes",
    "        foreach ($idMap as $oldId => $newId) {",
    "            $data = $rooms[$newId] ?? null;",
    "            if (! $data) {",
    "                continue;",
    "            }",
    "",
    "            DB::table('lokasis')",
    "                ->where('id', 10000 + $oldId)",
    "                ->update([",
    "                    'id' => $newId,",
    "                    'kode_ruangan' => $data['kode_ruangan'],",
    "                    'nama' => $data['nama'],",
    "                    'updated_at' => now(),",
    "                ]);",
    "",
    "            // Step 3: Update foreign key references in related tables",
    "            if (Schema::hasTable('bmns')) {",
    "                DB::table('bmns')->where('lokasi_id', $oldId)->update([",
    "                    'lokasi_id' => $newId,",
    "                    'lokasi' => $data['kode_ruangan'] . ' ' . $data['nama'],",
    "                ]);",
    "            }",
    "",
    "            if (Schema::hasTable('distribusis')) {",
    "                DB::table('distribusis')->where('lokasi_asal_id', $oldId)->update(['lokasi_asal_id' => $newId]);",
    "                DB::table('distribusis')->where('lokasi_tujuan_id', $oldId)->update(['lokasi_tujuan_id' => $newId]);",
    "            }",
    "",
    "            if (Schema::hasTable('ba_perpindahans')) {",
    "                DB::table('ba_perpindahans')->where('lokasi_asal_id', $oldId)->update(['lokasi_asal_id' => $newId]);",
    "                DB::table('ba_perpindahans')->where('lokasi_tujuan_id', $oldId)->update(['lokasi_tujuan_id' => $newId]);",
    "            }",
    "",
    "            if (Schema::hasTable('laporan_kerusakans')) {",
    "                DB::table('laporan_kerusakans')->where('lokasi_id', $oldId)->update(['lokasi_id' => $newId]);",
    "            }",
    "        }",
    "",
    "        // Fallback cleanup: ensure no orphaned 10000+ IDs remain",
    "        $remaining = DB::table('lokasis')->where('id', '>=', 10000)->get();",
    "        foreach ($remaining as $rem) {",
    "            DB::table('lokasis')->where('id', $rem->id)->delete();",
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

with open("database/migrations/2026_10_20_110000_reorder_master_lokasis_a_b_c.php", "w", encoding="utf-8") as f:
    f.write("\n".join(mig_110000))

print("Created database/migrations/2026_10_20_110000_reorder_master_lokasis_a_b_c.php")
