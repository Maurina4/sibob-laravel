<?php

namespace App\Services;

use App\Models\Bmn;
use App\Models\BmnAngkutan;
use App\Models\BmnDokumen;
use App\Models\BmnLab;
use App\Models\BmnTik;
use App\Models\DataTerhapus;
use App\Models\Distribusi;
use App\Models\DistribusiItem;
use App\Models\Lokasi;
use App\Models\Pegawai;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BmnService
{
    public function __construct(
        protected BmnExportService $exportService
    ) {}

    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Bmn::query()
            ->with(['lokasiRef', 'penanggungJawabPegawai'])
            ->active()
            ->filter($filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getFilterData(): array
    {
        return [
            'jenisFilters' => [
                'BMN-TIK & LAB',
                'BMN-TIK',
                'BMN-LAB',
                'BMN-NON TIK',
            ],
            'kondisis' => Bmn::query()
                ->active()
                ->whereNotNull('kondisi')
                ->where('kondisi', '!=', '')
                ->distinct()
                ->orderBy('kondisi')
                ->pluck('kondisi'),
            'lokasiFilters' => Bmn::query()
                ->active()
                ->whereNotNull('lokasi')
                ->where('lokasi', '!=', '')
                ->distinct()
                ->orderBy('lokasi')
                ->pluck('lokasi'),
            'lokasis' => Lokasi::orderedByGedung()->get(),
            'penanggungjawabs' => Pegawai::orderBy('nama')->get(),
            'tahunFilters' => Bmn::query()
                ->active()
                ->whereNotNull('tahun_perolehan')
                ->where('tahun_perolehan', '>', 0)
                ->distinct()
                ->orderByDesc('tahun_perolehan')
                ->pluck('tahun_perolehan'),
        ];
    }

    /**
     * Ekspor data BMN untuk monitoring dan evaluasi (monev) pengadaan.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): BinaryFileResponse|StreamedResponse
    {
        $bmns = Bmn::query()
            ->active()
            ->filter($filters)
            ->orderByDesc('tahun_perolehan')
            ->orderBy('kode_barang')
            ->orderBy('nup')
            ->get();

        $periodeLabel = 'Semua_Tahun';
        if (! empty($filters['tahun'])) {
            if ($filters['tahun'] === 'current') {
                $periodeLabel = 'Tahun_Berjalan_'.date('Y');
            } elseif ($filters['tahun'] === 'previous') {
                $periodeLabel = 'Tahun_Sebelumnya_'.(date('Y') - 1);
            } elseif ($filters['tahun'] === 'prior_all') {
                $periodeLabel = 'Tahun_Sebelumnya_sd_'.(date('Y') - 1);
            } else {
                $periodeLabel = 'Tahun_'.$filters['tahun'];
            }
        } elseif (! empty($filters['tahun_perolehan'])) {
            $periodeLabel = 'Tahun_'.$filters['tahun_perolehan'];
        }

        $format = strtolower($filters['format'] ?? 'xlsx');
        $dateStamp = date('Ymd_His');

        $metadata = [
            'periode' => str_replace('_', ' ', $periodeLabel),
            'total_items' => $bmns->count(),
        ];

        if ($format === 'csv') {
            $filename = "Monev_Pengadaan_BMN_{$periodeLabel}_{$dateStamp}.csv";

            return $this->exportService->exportCsv($bmns, $filename, $metadata);
        }

        $filename = "Monev_Pengadaan_BMN_{$periodeLabel}_{$dateStamp}.xlsx";

        return $this->exportService->exportXlsx($bmns, $filename, $metadata);
    }

    public function store(array $validated, Request $request): Bmn
    {
        return DB::transaction(function () use ($validated, $request) {
            $bmn = $this->storeRecord($validated);

            foreach (['manual_pengguna', 'panduan_perbaikan', 'instruksi_kerja', 'foto_barang'] as $jenisDoc) {
                if ($request->file($jenisDoc)) {
                    $this->simpanDokumen($bmn, $request->file($jenisDoc), $jenisDoc);
                }
            }

            return $bmn;
        });
    }

    /**
     * Mengecek apakah sebuah lokasi ruangan termasuk dalam lingkungan Laboratorium / Gedung A.
     */
    public function isLabLocation(?Lokasi $lokasiModel, string $rawLocation): bool
    {
        if ($lokasiModel) {
            $kode = strtoupper(trim((string) $lokasiModel->kode_ruangan));
            $nama = strtolower(trim((string) $lokasiModel->nama));

            // Kode gedung A (misal A1.01, A2.15, Ax.xx), kecualikan kode khusus non-gedung seperti 'BL'
            if ($kode !== 'BL' && (preg_match('/^A[\d.]/i', $kode) || str_starts_with($kode, 'A'))) {
                return true;
            }

            if (str_contains($nama, 'lab') || str_contains($nama, 'laboratorium')) {
                return true;
            }
        }

        $rawUpper = strtoupper(trim($rawLocation));
        $rawLower = strtolower(trim($rawLocation));

        if ($rawLower === 'belum berlokasi') {
            return false;
        }

        if (preg_match('/^A[\d.]/i', $rawUpper)) {
            return true;
        }

        if (str_contains($rawLower, 'lab') || str_contains($rawLower, 'laboratorium')) {
            return true;
        }

        return false;
    }

    /**
     * Membangun cache in-memory untuk entitas referensi (Lokasi, Pegawai, Distribusi)
     * guna mengoptimalkan import massal berkapasitas besar (hingga 5.000 data).
     *
     * @return array<string, mixed>
     */
    public function createImportCache(): array
    {
        $lokasis = Lokasi::all();
        $lokasiByName = [];
        $lokasiByCode = [];

        foreach ($lokasis as $lokasi) {
            $lokasiByName[strtolower(trim((string) $lokasi->nama))] = $lokasi;
            if (! empty($lokasi->kode_ruangan)) {
                $lokasiByCode[strtoupper(trim((string) $lokasi->kode_ruangan))] = $lokasi;
            }
        }

        $countToday = Distribusi::whereDate('created_at', today())->count();
        $existingToday = Distribusi::whereDate('created_at', today())->pluck('nomor')->flip()->toArray();

        return [
            'lokasi_by_name' => $lokasiByName,
            'lokasi_by_code' => $lokasiByCode,
            'pegawai' => Pegawai::all(),
            'distribusi_counter' => $countToday,
            'distribusi_existing' => $existingToday,
        ];
    }

    /**
     * Resolusi Lokasi dengan dukungan In-Memory Cache.
     */
    public function resolveLokasiCached(string $rawLocation, array &$cache): ?Lokasi
    {
        $trimmed = trim($rawLocation);
        if ($trimmed === '') {
            return null;
        }

        $lower = strtolower($trimmed);
        if (isset($cache['lokasi_by_name'][$lower])) {
            return $cache['lokasi_by_name'][$lower];
        }

        $upper = strtoupper($trimmed);
        if (isset($cache['lokasi_by_code'][$upper])) {
            return $cache['lokasi_by_code'][$upper];
        }

        // Cek format 'B1.01 - RUANG TAMU UTAMA'
        if (str_contains($trimmed, ' - ')) {
            [$code, $name] = explode(' - ', $trimmed, 2);
            $codeUpper = strtoupper(trim($code));
            $nameLower = strtolower(trim($name));
            if (isset($cache['lokasi_by_code'][$codeUpper])) {
                return $cache['lokasi_by_code'][$codeUpper];
            }
            if (isset($cache['lokasi_by_name'][$nameLower])) {
                return $cache['lokasi_by_name'][$nameLower];
            }
        }

        // Cek format 'B1.01 RUANG...' atau 'A1.01-00001'
        if (preg_match('/^([A-Za-z]{1,2}(?:\.[A-Za-z]+)?\d*\.\d+)[-\s]+(.*)$/i', $trimmed, $matches)) {
            $codeUpper = strtoupper(trim($matches[1]));
            $nameLower = strtolower(trim($matches[2]));
            if (isset($cache['lokasi_by_code'][$codeUpper])) {
                return $cache['lokasi_by_code'][$codeUpper];
            }
            if ($nameLower !== '' && isset($cache['lokasi_by_name'][$nameLower])) {
                return $cache['lokasi_by_name'][$nameLower];
            }
        }

        // Jika belum ada di cache, cari atau buat di database lalu masukkan ke cache
        $lokasi = Lokasi::findOrCreateByName($trimmed);
        if ($lokasi) {
            $cache['lokasi_by_name'][strtolower(trim((string) $lokasi->nama))] = $lokasi;
            if (! empty($lokasi->kode_ruangan)) {
                $cache['lokasi_by_code'][strtoupper(trim((string) $lokasi->kode_ruangan))] = $lokasi;
            }
            $cache['lokasi_by_name'][$lower] = $lokasi;
        }

        return $lokasi;
    }

    /**
     * Resolusi ID Pegawai penanggung jawab dengan dukungan In-Memory Cache.
     */
    public function resolvePegawaiCached(string $pjName, string $pjNip, array &$cache): ?int
    {
        $pjName = trim($pjName);
        $pjNip = trim($pjNip);

        if ($pjName === '' && $pjNip === '') {
            return null;
        }

        $pegawaiList = $cache['pegawai'] ?? null;
        if (! $pegawaiList) {
            return null;
        }

        $pjNameLower = strtolower($pjName);

        /** @var Pegawai|null $pegawai */
        $pegawai = $pegawaiList->first(function ($p) use ($pjNameLower, $pjNip) {
            if ($pjNameLower !== '' && strtolower(trim((string) $p->nama)) === $pjNameLower) {
                return true;
            }
            if ($pjNip !== '' && $pjNip !== '-' && trim((string) $p->nip) === $pjNip) {
                return true;
            }
            if ($pjNameLower !== '') {
                $formatted = strtolower(trim((string) $p->nama).' ('.trim((string) $p->nip).')');
                if ($formatted === $pjNameLower) {
                    return true;
                }
            }

            return false;
        });

        if (! $pegawai && str_contains($pjNameLower, 'pengelola bmn')) {
            $pegawai = $pegawaiList->first(fn ($p) => strtolower(trim((string) $p->jabatan)) === 'pengelola bmn');
        }

        return $pegawai?->id;
    }

    /**
     * Menyimpan satu data BMN beserta sinkronisasi tabel relasi & Berita Acara Distribusi Gudang.
     * Dapat digunakan bersama baik oleh input manual maupun import Excel massal.
     *
     * @param  array<string, mixed>  $validated
     * @param  array<string, mixed>|null  $cache
     * @param  bool  $isImport
     */
    public function storeRecord(array $validated, ?array &$cache = null, bool $isImport = false): Bmn
    {
        $lokasiName = trim($validated['lokasi'] ?? '');
        if ($cache !== null) {
            $lokasiModel = $lokasiName !== '' ? $this->resolveLokasiCached($lokasiName, $cache) : null;
        } else {
            $lokasiModel = $lokasiName !== '' ? Lokasi::findOrCreateByName($lokasiName) : null;
        }

        // Evaluasi kondisi Lab dan TIK untuk klasifikasi cerdas & Dual Tracking
        $isLabLocation = $this->isLabLocation($lokasiModel, $lokasiName);
        $rawJenis = strtoupper(trim($validated['jenis'] ?? ''));

        // Deteksi NON TIK (contoh: BMN-NON TIK, NON TIK, ALAT NON TIK)
        $isNonTik = str_contains($rawJenis, 'NON TIK') || str_contains($rawJenis, 'NON-TIK') || str_contains($rawJenis, 'NONTIK');
        $isPureTik = ! $isNonTik && str_contains($rawJenis, 'TIK');
        $isLab = $isLabLocation || str_contains($rawJenis, 'LAB');

        $createTik = false;
        $createLab = false;
        $createAngkutan = false;

        if ($isPureTik && $isLab) {
            $standarJenis = 'BMN-TIK & LAB';
            $kategoriId = 2; // Kategori utama Lab
            $createTik = true;
            $createLab = true;
        } elseif ($isPureTik) {
            $standarJenis = 'BMN-TIK';
            $kategoriId = 3;
            $createTik = true;
        } elseif ($isLab) {
            $standarJenis = 'BMN-LAB';
            $kategoriId = 2;
            $createLab = true;
        } elseif (str_contains($rawJenis, 'ANGKUTAN') || str_contains($rawJenis, 'KENDARAAN') || ! empty($validated['no_polisi'])) {
            $standarJenis = 'BMN-ANGKUTAN';
            $kategoriId = 1;
            $createAngkutan = true;
        } else {
            $standarJenis = 'BMN-NON TIK';
            $kategoriId = 1;
        }

        // Catat klasifikasi asal pada spesifikasi jika jenis aslinya bukan format standar
        $spesifikasi = $validated['spesifikasi'] ?? null;
        if (! in_array($rawJenis, ['BMN-TIK', 'BMN-LAB', 'BMN-NON TIK', 'BMN-TIK & LAB', ''], true)) {
            $spesifikasi = $spesifikasi ? "{$spesifikasi} (Klasifikasi Asal: {$rawJenis})" : "Klasifikasi Asal: {$rawJenis}";
        }

        // Resolve penanggung_jawab_id jika belum di-set eksplisit
        $penanggungJawabId = $validated['penanggung_jawab_id'] ?? null;
        $pjName = trim($validated['penanggungjawab'] ?? '');
        $pjNip = trim($validated['penanggungjawab_nip'] ?? '');

        if (! $penanggungJawabId && $pjName !== '') {
            if ($cache !== null) {
                $penanggungJawabId = $this->resolvePegawaiCached($pjName, $pjNip, $cache);
            } else {
                $concatSql = DB::getDriverName() === 'sqlite'
                    ? "nama || ' (' || nip || ')' = ?"
                    : "CONCAT(nama, ' (', nip, ')') = ?";

                $pegawai = Pegawai::where('nama', $pjName)
                    ->orWhere('nip', $pjNip !== '-' ? $pjNip : '___not_found___')
                    ->orWhereRaw($concatSql, [$pjName])
                    ->first();

                if (! $pegawai && str_contains(strtolower($pjName), 'pengelola bmn')) {
                    $pegawai = Pegawai::where('jabatan', 'Pengelola BMN')->first();
                }

                $penanggungJawabId = $pegawai?->id;
            }
        }

        $dataBmn = [
            'kategori_bmn_id' => $kategoriId,
            'kode_barang' => $validated['kode_barang'],
            'nup' => $validated['nup'],
            'no_seri' => $validated['no_seri'] ?? null,
            'nama_barang' => $validated['nama_barang'],
            'merek' => $validated['merek'] ?? null,
            'no_polisi' => $validated['no_polisi'] ?? null,
            'tahun_perolehan' => $validated['tahun_perolehan'] ?? null,
            'nilai_perolehan' => $validated['nilai_perolehan'] ?? null,
            'tanggal_terima' => $validated['tanggal_terima'] ?? null,
            'tanggal_buku_pertama' => $validated['tanggal_buku_pertama'] ?? null,
            'jenis' => $standarJenis,
            'satuan' => $validated['satuan'] ?? null,
            'lokasi' => $lokasiModel ? $lokasiModel->nama : ($validated['lokasi'] ?? 'Gudang BMN'),
            'lokasi_id' => $lokasiModel?->id,
            'penanggungjawab' => $validated['penanggungjawab'] ?? 'Pengelola BMN',
            'penanggung_jawab_id' => $penanggungJawabId,
            'penanggungjawab_nip' => $validated['penanggungjawab_nip'] ?? ($pjNip ?: '-'),
            'kondisi' => $validated['kondisi'] ?? 'Baik',
            'jadwal_pemeliharaan' => $validated['jadwal_pemeliharaan'] ?? null,
            'spesifikasi' => $spesifikasi,
            'dihapus' => false,
            'dihapus_pada' => null,
            'dihapus_oleh' => null,
        ];

        $bmn = Bmn::create($dataBmn);

        if ($createTik) {
            BmnTik::create([
                'bmn_id' => $bmn->id,
                'kode_barang' => $bmn->kode_barang,
                'nup' => (int) $bmn->nup,
                'no_seri' => $bmn->no_seri,
                'nama_barang' => $bmn->nama_barang,
                'tahun_perolehan' => $bmn->tahun_perolehan,
                'nilai_perolehan' => $bmn->nilai_perolehan,
                'jenis' => 'BMN-TIK',
                'lokasi' => $bmn->lokasi,
                'penanggungjawab' => $bmn->penanggungjawab,
                'kondisi' => $bmn->kondisi ?? 'Baik',
                'dihapus' => false,
            ]);
        }

        if ($createLab) {
            BmnLab::create([
                'bmn_id' => $bmn->id,
                'kode_barang' => $bmn->kode_barang,
                'nup' => (int) $bmn->nup,
                'no_seri' => $bmn->no_seri,
                'nama_barang' => $bmn->nama_barang,
                'merek' => $bmn->merek,
                'tahun_perolehan' => $bmn->tahun_perolehan,
                'nilai_perolehan' => $bmn->nilai_perolehan,
                'lokasi' => $bmn->lokasi,
                'penanggungjawab' => $bmn->penanggungjawab,
                'kondisi' => $bmn->kondisi ?? 'Baik',
                'status_kalibrasi' => 'Belum Dikalibrasi',
                'dihapus' => false,
            ]);
        }

        if ($createAngkutan) {
            BmnAngkutan::create([
                'bmn_id' => $bmn->id,
                'kode_barang' => $bmn->kode_barang,
                'nup' => (int) $bmn->nup,
                'nama_barang' => $bmn->nama_barang,
                'merek' => $bmn->merek,
                'no_polisi' => $bmn->no_polisi,
                'tahun_perolehan' => $bmn->tahun_perolehan,
                'nilai_perolehan' => $bmn->nilai_perolehan,
                'lokasi' => $bmn->lokasi,
                'penanggungjawab' => $bmn->penanggungjawab,
                'kondisi' => $bmn->kondisi ?? 'Baik',
                'dihapus' => false,
            ]);
        }

        $this->createAutomaticDistribusiIfNeeded($bmn, $cache, $isImport);

        return $bmn;
    }

    public function update(Bmn $bmn, array $validated): bool
    {
        return DB::transaction(function () use ($bmn, $validated) {
            if (! empty($validated['lokasi'])) {
                $lokasiModel = Lokasi::findOrCreateByName($validated['lokasi']);
                $validated['lokasi'] = $lokasiModel->nama;
                $validated['lokasi_id'] = $lokasiModel->id;
            }

            $bmn->update($validated);

            $createTik = in_array($bmn->jenis, ['BMN-TIK', 'BMN-TIK & LAB']);
            $createLab = in_array($bmn->jenis, ['BMN-LAB', 'BMN-TIK & LAB']);

            if ($createTik) {
                if ($bmn->tik) {
                    $bmn->tik->update(array_filter([
                        'kode_barang' => $bmn->kode_barang,
                        'nup' => (int) $bmn->nup,
                        'no_seri' => $bmn->no_seri,
                        'nama_barang' => $bmn->nama_barang,
                        'tahun_perolehan' => $bmn->tahun_perolehan,
                        'nilai_perolehan' => $bmn->nilai_perolehan,
                        'lokasi' => $bmn->lokasi,
                        'penanggungjawab' => $bmn->penanggungjawab,
                    ], fn ($v) => $v !== null));
                } else {
                    BmnTik::create([
                        'bmn_id' => $bmn->id,
                        'kode_barang' => $bmn->kode_barang,
                        'nup' => (int) $bmn->nup,
                        'no_seri' => $bmn->no_seri,
                        'nama_barang' => $bmn->nama_barang,
                        'tahun_perolehan' => $bmn->tahun_perolehan,
                        'nilai_perolehan' => $bmn->nilai_perolehan,
                        'jenis' => 'BMN-TIK',
                        'lokasi' => $bmn->lokasi,
                        'penanggungjawab' => $bmn->penanggungjawab,
                        'kondisi' => $bmn->kondisi ?? 'Baik',
                        'dihapus' => false,
                    ]);
                }
            } else {
                $bmn->tik()?->delete();
            }

            if ($createLab) {
                if ($bmn->lab) {
                    $bmn->lab->update(array_filter([
                        'kode_barang' => $bmn->kode_barang,
                        'nup' => (int) $bmn->nup,
                        'no_seri' => $bmn->no_seri,
                        'nama_barang' => $bmn->nama_barang,
                        'merek' => $bmn->merek,
                        'tahun_perolehan' => $bmn->tahun_perolehan,
                        'nilai_perolehan' => $bmn->nilai_perolehan,
                        'lokasi' => $bmn->lokasi,
                        'penanggungjawab' => $bmn->penanggungjawab,
                        'kondisi' => $bmn->kondisi,
                    ], fn ($v) => $v !== null));
                } else {
                    BmnLab::create([
                        'bmn_id' => $bmn->id,
                        'kode_barang' => $bmn->kode_barang,
                        'nup' => (int) $bmn->nup,
                        'no_seri' => $bmn->no_seri,
                        'nama_barang' => $bmn->nama_barang,
                        'merek' => $bmn->merek,
                        'tahun_perolehan' => $bmn->tahun_perolehan,
                        'nilai_perolehan' => $bmn->nilai_perolehan,
                        'lokasi' => $bmn->lokasi,
                        'penanggungjawab' => $bmn->penanggungjawab,
                        'kondisi' => $bmn->kondisi ?? 'Baik',
                        'status_kalibrasi' => 'Belum Dikalibrasi',
                        'dihapus' => false,
                    ]);
                }
            } else {
                $bmn->lab()?->delete();
            }

            return true;
        });
    }

    public function updateLokasi(Bmn $bmn, string $lokasi): bool
    {
        $lokasiModel = Lokasi::findOrCreateByName($lokasi);
        $bmn->update([
            'lokasi' => $lokasiModel->nama,
            'lokasi_id' => $lokasiModel->id,
        ]);

        if ($bmn->tik) {
            $bmn->tik->update(['lokasi' => $lokasiModel->nama]);
        }

        if ($bmn->lab) {
            $bmn->lab->update(['lokasi' => $lokasiModel->nama]);
        }

        return true;
    }

    public function delete(Bmn $bmn, ?string $userEmail): bool
    {
        $waktuHapus = now();
        $penghapus = $userEmail ?? 'system';

        return DB::transaction(function () use ($bmn, $waktuHapus, $penghapus) {
            $bmn->update([
                'dihapus' => true,
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);

            if ($bmn->tik) {
                $bmn->tik->update(['dihapus' => true, 'dihapus_pada' => $waktuHapus, 'dihapus_oleh' => $penghapus]);
            }

            if ($bmn->lab) {
                $bmn->lab->update(['dihapus' => true, 'dihapus_pada' => $waktuHapus, 'dihapus_oleh' => $penghapus]);
            }

            if ($bmn->angkutan) {
                $bmn->angkutan->update(['dihapus' => true, 'dihapus_pada' => $waktuHapus, 'dihapus_oleh' => $penghapus]);
            }

            $kategoriLabel = match ($bmn->jenis) {
                'BMN-TIK' => 'BMN TIK',
                'BMN-LAB' => 'BMN Lab',
                'BMN-ANGKUTAN' => 'Alat Angkutan Bermotor',
                default => 'BMN',
            };

            $dataId = match ($bmn->jenis) {
                'BMN-TIK' => $bmn->tik?->id ?? $bmn->id,
                'BMN-LAB' => $bmn->lab?->id ?? $bmn->id,
                'BMN-ANGKUTAN' => $bmn->angkutan?->id ?? $bmn->id,
                default => $bmn->id,
            };

            DataTerhapus::create([
                'jenis' => $kategoriLabel,
                'data_id' => $dataId,
                'bmn_id' => $bmn->id,
                'nama' => $bmn->nama_barang,
                'dihapus_pada' => $waktuHapus,
                'dihapus_oleh' => $penghapus,
            ]);

            return true;
        });
    }

    public function simpanDokumen(Bmn $bmn, $file, string $jenis): BmnDokumen
    {
        $namaFileAsli = $file->getClientOriginalName();
        $namaFileAman = preg_replace('/[^A-Za-z0-9.\_-]/', '_', $namaFileAsli);
        $namaFile = time().'_'.$namaFileAman;

        $path = $file->storeAs(
            'bmn-dokumen/'.$bmn->id,
            $namaFile,
            'public'
        );

        return BmnDokumen::create([
            'bmn_id' => $bmn->id,
            'jenis' => $jenis,
            'nama_file' => $namaFileAsli,
            'path' => $path,
            'ukuran' => $file->getSize(),
            'tipe_file' => $file->getClientMimeType(),
        ]);
    }

    public function createAutomaticDistribusiIfNeeded(Bmn $bmn, ?array &$cache = null, bool $isImport = false): ?Distribusi
    {
        $lokasiNormalized = strtolower(trim((string) ($bmn->lokasi ?? '')));
        $pjNormalized = strtolower(trim((string) ($bmn->penanggungjawab ?? '')));

        if ($isImport) {
            // Khusus Fitur Import Excel:
            // Hanya masuk BA Distribusi jika lokasi berstatus transit / belum ada penempatan definitif:
            // Kosong, '-', 'belum berlokasi', 'gudang bmn', atau 'gudang' murni.
            // Seluruh lokasi spesifik lainnya (baik berkode A/B/C/Angka maupun nama ruangan/gudang fungsional seperti
            // Gudang Rusak Berat, Gudang ATK, Ruang Aula, Pantry, DBL, dll.) TIDAK dibuatkan BA Distribusi.
            $isTransit = empty($lokasiNormalized)
                || in_array($lokasiNormalized, ['-', 'belum berlokasi', 'gudang bmn', 'gudang', 'gudang utama', 'belum ada lokasi'], true);

            if (! $isTransit) {
                return null;
            }
        } else {
            // Logika Lama (Input Manual):
            $isGudang = str_contains($lokasiNormalized, 'gudang');
            $isPengelolaBmn = str_contains($pjNormalized, 'pengelola bmn') || empty($pjNormalized);

            if (! $isGudang && ! $isPengelolaBmn) {
                return null;
            }
        }

        $datePart = now()->format('Ymd');
        if ($cache !== null && isset($cache['distribusi_counter'])) {
            $cache['distribusi_counter']++;
            $countToday = $cache['distribusi_counter'];
            $nomorBa = 'BA-DIST/'.$datePart.'/'.str_pad((string) $countToday, 3, '0', STR_PAD_LEFT);
            while (isset($cache['distribusi_existing'][$nomorBa])) {
                $countToday++;
                $cache['distribusi_counter'] = $countToday;
                $nomorBa = 'BA-DIST/'.$datePart.'/'.str_pad((string) $countToday, 3, '0', STR_PAD_LEFT);
            }
            $cache['distribusi_existing'][$nomorBa] = true;
        } else {
            $countToday = Distribusi::whereDate('created_at', today())->count() + 1;
            $nomorBa = 'BA-DIST/'.$datePart.'/'.str_pad((string) $countToday, 3, '0', STR_PAD_LEFT);
            while (Distribusi::where('nomor', $nomorBa)->exists()) {
                $countToday++;
                $nomorBa = 'BA-DIST/'.$datePart.'/'.str_pad((string) $countToday, 3, '0', STR_PAD_LEFT);
            }
        }

        $distribusi = Distribusi::create([
            'nomor' => $nomorBa,
            'tanggal' => now()->toDateString(),
            'bmn_id' => $bmn->id,
            'nama_barang' => $bmn->nama_barang,
            'jumlah' => 1,
            'satuan' => $bmn->satuan ?: 'unit',
            'lokasi_asal' => $bmn->lokasi ?: 'Gudang BMN',
            'lokasi_asal_id' => $bmn->lokasi_id,
            'lokasi_tujuan' => null,
            'lokasi_tujuan_id' => null,
            'penerima' => null,
            'penerima_id' => null,
            'keterangan' => 'Distribusi barang baru inventaris dari Gudang BMN ('.$bmn->kode_barang.').',
            'dihapus' => false,
        ]);

        DistribusiItem::create([
            'distribusi_id' => $distribusi->id,
            'bmn_id' => $bmn->id,
            'nama_barang_snapshot' => $bmn->nama_barang,
            'jumlah' => 1,
            'satuan' => $bmn->satuan ?: 'unit',
            'catatan' => 'Pendaftaran barang baru di gudang.',
        ]);

        return $distribusi;
    }
}
