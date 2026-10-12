-- ==============================================================================
-- SIBOB DATA MIGRATION SCRIPT (v2 - Normalized Architecture)
-- Tujuan: Memindahkan data dari bmns_lab, bmns_tik, dan teks manual ke struktur
--         relasional baru tanpa kehilangan data (Non-destructive).
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- 0. PENYESUAIAN STRUKTUR KOLOM & INDEX AGAR FLEKSIBEL UNTUK SEMUA JENIS ASET
-- ------------------------------------------------------------------------------
ALTER TABLE `bmns` MODIFY COLUMN `nup` VARCHAR(255) NULL;
ALTER TABLE `bmns` MODIFY COLUMN `kode_barang` VARCHAR(255) NULL;

-- Perbarui index unik agar mencakup kategori_bmn_id (abaikan jika sudah pernah dijalankan)
-- ALTER TABLE `bmns` DROP INDEX `bmns_kode_barang_nup_unique`;
-- ALTER TABLE `bmns` ADD UNIQUE KEY `bmns_kode_nup_kategori_unique` (`kode_barang`, `nup`, `kategori_bmn_id`);

START TRANSACTION;

-- ------------------------------------------------------------------------------
-- 1. SEED DATA MASTER KATEGORI BMN
-- ------------------------------------------------------------------------------
INSERT INTO `kategori_bmns` (`id`, `kode`, `nama`, `deskripsi`, `created_at`, `updated_at`)
VALUES
  (1, 'UMUM', 'BMN Umum / Perkantoran', 'Aset barang kantor non-laboratorium dan non-TIK', NOW(), NOW()),
  (2, 'LAB', 'BMN Laboratorium', 'Peralatan dan instrumen laboratorium yang membutuhkan kalibrasi', NOW(), NOW()),
  (3, 'TIK', 'BMN Teknologi Informasi', 'Perangkat keras, komputer, server, dan infrastruktur jaringan', NOW(), NOW())
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`);

-- ------------------------------------------------------------------------------
-- 2. SINKRONISASI DATA MASTER (LOKASI & PEGAWAI) DARI STRING EKSISTING
-- ------------------------------------------------------------------------------

-- Pastikan semua nama lokasi dari tabel-tabel lama terdaftar di tabel `lokasis`
INSERT IGNORE INTO `lokasis` (`nama`, `created_at`, `updated_at`)
SELECT DISTINCT all_lokasi.nama, NOW(), NOW()
FROM (
    SELECT DISTINCT TRIM(`lokasi`) AS nama FROM `bmns` WHERE `lokasi` IS NOT NULL AND TRIM(`lokasi`) != ''
    UNION
    SELECT DISTINCT TRIM(`lokasi`) FROM `bmns_lab` WHERE `lokasi` IS NOT NULL AND TRIM(`lokasi`) != ''
    UNION
    SELECT DISTINCT TRIM(`lokasi`) FROM `bmns_tik` WHERE `lokasi` IS NOT NULL AND TRIM(`lokasi`) != ''
    UNION
    SELECT DISTINCT TRIM(`lokasi_asal`) FROM `distribusis` WHERE `lokasi_asal` IS NOT NULL AND TRIM(`lokasi_asal`) != ''
    UNION
    SELECT DISTINCT TRIM(`lokasi_tujuan`) FROM `distribusis` WHERE `lokasi_tujuan` IS NOT NULL AND TRIM(`lokasi_tujuan`) != ''
    UNION
    SELECT DISTINCT TRIM(`dari`) FROM `ba_perpindahans` WHERE `dari` IS NOT NULL AND TRIM(`dari`) != ''
    UNION
    SELECT DISTINCT TRIM(`ke`) FROM `ba_perpindahans` WHERE `ke` IS NOT NULL AND TRIM(`ke`) != ''
) AS all_lokasi;

-- Gabungkan data dari `penanggung_jawabs` ke `pegawais` jika belum ada
INSERT INTO `pegawais` (`nama`, `nip`, `jabatan`, `unit_kerja`, `created_at`, `updated_at`)
SELECT pj.`nama`, pj.`nip`, pj.`jabatan`, pj.`unit_kerja`, NOW(), NOW()
FROM `penanggung_jawabs` pj
LEFT JOIN `pegawais` p ON p.`nama` = pj.`nama` OR (pj.`nip` IS NOT NULL AND p.`nip` = pj.`nip`)
WHERE p.`id` IS NULL;

-- ------------------------------------------------------------------------------
-- 3. PERBAHARUI DATA DI TABEL `bmns` EKSISTING
-- ------------------------------------------------------------------------------

-- Update referensi kategori, lokasi, dan penanggung jawab
UPDATE `bmns` b
LEFT JOIN `lokasis` l ON l.`nama` = b.`lokasi`
LEFT JOIN `pegawais` p ON p.`nama` = b.`penanggungjawab` OR (b.`penanggungjawab_nip` IS NOT NULL AND p.`nip` = b.`penanggungjawab_nip`)
SET 
  b.`lokasi_id` = l.`id`,
  b.`penanggung_jawab_id` = p.`id`,
  b.`kategori_bmn_id` = CASE 
    WHEN b.`jenis` LIKE '%LAB%' THEN 2
    WHEN b.`jenis` LIKE '%TIK%' THEN 3
    ELSE 1
  END,
  b.`deleted_at` = CASE WHEN b.`dihapus` = 1 THEN COALESCE(b.`dihapus_pada`, NOW()) ELSE NULL END;

-- ------------------------------------------------------------------------------
-- 4. MIGRASIKAN DATA DARI `bmns_lab` KE `bmns` & `bmn_lab_details`
-- ------------------------------------------------------------------------------

-- Masukkan aset laboratorium ke tabel bmns
INSERT INTO `bmns` (
  `kategori_bmn_id`,
  `kode_barang`,
  `nup`,
  `nama_barang`,
  `merek`,
  `jenis`,
  `satuan`,
  `lokasi`,
  `lokasi_id`,
  `penanggungjawab`,
  `penanggung_jawab_id`,
  `kondisi`,
  `dihapus`,
  `dihapus_pada`,
  `dihapus_oleh`,
  `deleted_at`,
  `created_at`,
  `updated_at`
)
SELECT 
  2 AS `kategori_bmn_id`,
  lab.`kode_barang`,
  CASE WHEN lab.`nup` IS NULL THEN NULL ELSE CAST(lab.`nup` AS CHAR) END AS `nup`,
  lab.`nama_barang`,
  lab.`merek`,
  'Laboratorium' AS `jenis`,
  'unit' AS `satuan`,
  lab.`lokasi`,
  l.`id` AS `lokasi_id`,
  lab.`penanggungjawab`,
  p.`id` AS `penanggung_jawab_id`,
  lab.`kondisi`,
  lab.`dihapus`,
  lab.`dihapus_pada`,
  lab.`dihapus_oleh`,
  CASE WHEN lab.`dihapus` = 1 THEN COALESCE(lab.`dihapus_pada`, NOW()) ELSE NULL END AS `deleted_at`,
  lab.`created_at`,
  lab.`updated_at`
FROM `bmns_lab` lab
LEFT JOIN `lokasis` l ON l.`nama` = lab.`lokasi`
LEFT JOIN `pegawais` p ON p.`nama` = lab.`penanggungjawab`
WHERE NOT EXISTS (
  SELECT 1 FROM `bmns` existing 
  WHERE existing.`kategori_bmn_id` = 2 
    AND existing.`kode_barang` = lab.`kode_barang` 
    AND existing.`created_at` = lab.`created_at`
);

-- Hubungkan detail kalibrasi ke tabel bmn_lab_details
INSERT INTO `bmn_lab_details` (`bmn_id`, `status_kalibrasi`, `tanggal_kalibrasi`, `created_at`, `updated_at`)
SELECT 
  b.`id` AS `bmn_id`,
  lab.`status_kalibrasi`,
  lab.`tanggal_kalibrasi`,
  NOW(),
  NOW()
FROM `bmns_lab` lab
INNER JOIN `bmns` b ON b.`kategori_bmn_id` = 2 
  AND b.`kode_barang` = lab.`kode_barang` 
  AND b.`created_at` = lab.`created_at`
ON DUPLICATE KEY UPDATE 
  `status_kalibrasi` = VALUES(`status_kalibrasi`),
  `tanggal_kalibrasi` = VALUES(`tanggal_kalibrasi`);

-- ------------------------------------------------------------------------------
-- 5. MIGRASIKAN DATA DARI `bmns_tik` KE `bmns`
-- ------------------------------------------------------------------------------
INSERT INTO `bmns` (
  `kategori_bmn_id`,
  `kode_barang`,
  `nup`,
  `nama_barang`,
  `merek`,
  `jenis`,
  `satuan`,
  `lokasi`,
  `lokasi_id`,
  `penanggungjawab`,
  `penanggung_jawab_id`,
  `kondisi`,
  `dihapus`,
  `dihapus_pada`,
  `dihapus_oleh`,
  `deleted_at`,
  `created_at`,
  `updated_at`
)
SELECT 
  3 AS `kategori_bmn_id`,
  tik.`kode_barang`,
  CASE WHEN tik.`nup` IS NULL THEN NULL ELSE CAST(tik.`nup` AS CHAR) END AS `nup`,
  tik.`nama_barang`,
  NULL AS `merek`,
  COALESCE(tik.`jenis`, 'TIK') AS `jenis`,
  'unit' AS `satuan`,
  tik.`lokasi`,
  l.`id` AS `lokasi_id`,
  tik.`penanggungjawab`,
  p.`id` AS `penanggung_jawab_id`,
  'Baik' AS `kondisi`,
  tik.`dihapus`,
  tik.`dihapus_pada`,
  tik.`dihapus_oleh`,
  CASE WHEN tik.`dihapus` = 1 THEN COALESCE(tik.`dihapus_pada`, NOW()) ELSE NULL END AS `deleted_at`,
  tik.`created_at`,
  tik.`updated_at`
FROM `bmns_tik` tik
LEFT JOIN `lokasis` l ON l.`nama` = tik.`lokasi`
LEFT JOIN `pegawais` p ON p.`nama` = tik.`penanggungjawab`
WHERE NOT EXISTS (
  SELECT 1 FROM `bmns` existing 
  WHERE existing.`kategori_bmn_id` = 3 
    AND (existing.`kode_barang` = tik.`kode_barang` OR (existing.`kode_barang` IS NULL AND tik.`kode_barang` IS NULL))
    AND existing.`created_at` = tik.`created_at`
);

-- ------------------------------------------------------------------------------
-- 6. NORMALISASI TRANSAKSI EKSISTING DENGAN FOREIGN KEY
-- ------------------------------------------------------------------------------

-- Update `distribusis`
UPDATE `distribusis` d
LEFT JOIN `lokasis` la ON la.`nama` = d.`lokasi_asal`
LEFT JOIN `lokasis` lt ON lt.`nama` = d.`lokasi_tujuan`
LEFT JOIN `pegawais` p ON p.`nama` = d.`penerima`
SET 
  d.`lokasi_asal_id` = la.`id`,
  d.`lokasi_tujuan_id` = lt.`id`,
  d.`penerima_id` = p.`id`,
  d.`deleted_at` = CASE WHEN d.`dihapus` = 1 THEN COALESCE(d.`dihapus_pada`, NOW()) ELSE NULL END;

-- Pindahkan data barang distribusi ke `distribusi_items`
INSERT INTO `distribusi_items` (`distribusi_id`, `bmn_id`, `nama_barang_snapshot`, `jumlah`, `satuan`, `created_at`, `updated_at`)
SELECT 
  d.`id` AS `distribusi_id`,
  b.`id` AS `bmn_id`,
  d.`nama_barang` AS `nama_barang_snapshot`,
  d.`jumlah`,
  d.`satuan`,
  d.`created_at`,
  d.`updated_at`
FROM `distribusis` d
LEFT JOIN `bmns` b ON b.`nama_barang` = d.`nama_barang`
WHERE NOT EXISTS (SELECT 1 FROM `distribusi_items` di WHERE di.`distribusi_id` = d.`id`);

-- Update `ba_perpindahans`
UPDATE `ba_perpindahans` ba
LEFT JOIN `lokasis` la ON la.`nama` = ba.`dari`
LEFT JOIN `lokasis` lt ON lt.`nama` = ba.`ke`
LEFT JOIN `users` u ON u.`email` = ba.`pengaju_email`
SET 
  ba.`lokasi_asal_id` = la.`id`,
  ba.`lokasi_tujuan_id` = lt.`id`,
  ba.`pengaju_id` = u.`id`,
  ba.`deleted_at` = CASE WHEN ba.`dihapus` = 1 THEN COALESCE(ba.`dihapus_pada`, NOW()) ELSE NULL END;

-- Pindahkan barang perpindahan ke `ba_perpindahan_items`
INSERT INTO `ba_perpindahan_items` (`ba_perpindahan_id`, `bmn_id`, `nama_barang_snapshot`, `kondisi_saat_pindah`, `created_at`, `updated_at`)
SELECT 
  ba.`id` AS `ba_perpindahan_id`,
  b.`id` AS `bmn_id`,
  ba.`nama_barang` AS `nama_barang_snapshot`,
  'Baik' AS `kondisi_saat_pindah`,
  ba.`created_at`,
  ba.`updated_at`
FROM `ba_perpindahans` ba
LEFT JOIN `bmns` b ON b.`nama_barang` = ba.`nama_barang`
WHERE NOT EXISTS (SELECT 1 FROM `ba_perpindahan_items` bai WHERE bai.`ba_perpindahan_id` = ba.`id`);

-- Update `laporan_kerusakans`
UPDATE `laporan_kerusakans` lk
LEFT JOIN `bmns` b ON b.`nama_barang` = lk.`nama_barang`
LEFT JOIN `lokasis` l ON l.`nama` = lk.`lokasi`
LEFT JOIN `users` u ON u.`email` = lk.`pelapor_email`
SET 
  lk.`bmn_id` = b.`id`,
  lk.`lokasi_id` = l.`id`,
  lk.`pelapor_id` = u.`id`,
  lk.`deleted_at` = CASE WHEN lk.`dihapus` = 1 THEN COALESCE(lk.`dihapus_pada`, NOW()) ELSE NULL END;

-- Update `pengembalians`
UPDATE `pengembalians` p
LEFT JOIN `pegawais` pg ON pg.`nama` = p.`nama_pengaju`
SET 
  p.`pengaju_id` = pg.`id`,
  p.`deleted_at` = CASE WHEN p.`dihapus` = 1 THEN COALESCE(p.`dihapus_pada`, NOW()) ELSE NULL END;

COMMIT;
