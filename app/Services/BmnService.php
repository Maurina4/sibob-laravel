<?php

namespace App\Services;

use App\Models\Bmn;
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
            $lokasiName = trim($validated['lokasi'] ?? '');
            $lokasiModel = $lokasiName !== '' ? Lokasi::findOrCreateByName($lokasiName) : null;

            $dataBmn = [
                'kode_barang' => $validated['kode_barang'],
                'nup' => $validated['nup'],
                'no_seri' => $validated['no_seri'] ?? null,
                'nama_barang' => $validated['nama_barang'],
                'merek' => $validated['merek'] ?? null,
                'tahun_perolehan' => $validated['tahun_perolehan'] ?? null,
                'nilai_perolehan' => $validated['nilai_perolehan'] ?? null,
                'tanggal_terima' => $validated['tanggal_terima'] ?? null,
                'jenis' => $validated['jenis'],
                'satuan' => $validated['satuan'] ?? null,
                'lokasi' => $lokasiModel ? $lokasiModel->nama : $validated['lokasi'],
                'lokasi_id' => $lokasiModel?->id,
                'penanggungjawab' => $validated['penanggungjawab'],
                'penanggungjawab_nip' => $validated['penanggungjawab_nip'] ?? null,
                'kondisi' => $validated['kondisi'],
                'jadwal_pemeliharaan' => $validated['jadwal_pemeliharaan'] ?? null,
                'spesifikasi' => $validated['spesifikasi'] ?? null,
                'dihapus' => false,
                'dihapus_pada' => null,
                'dihapus_oleh' => null,
            ];

            $bmn = Bmn::create($dataBmn);

            if ($bmn->jenis === 'BMN-TIK') {
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
                    'dihapus' => false,
                ]);
            } elseif ($bmn->jenis === 'BMN-LAB') {
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
                    'kondisi' => $bmn->kondisi,
                    'status_kalibrasi' => 'Belum Dikalibrasi',
                    'dihapus' => false,
                ]);
            }

            foreach (['manual_pengguna', 'panduan_perbaikan', 'instruksi_kerja', 'foto_barang'] as $jenisDoc) {
                if ($request->file($jenisDoc)) {
                    $this->simpanDokumen($bmn, $request->file($jenisDoc), $jenisDoc);
                }
            }

            $this->createAutomaticDistribusiIfNeeded($bmn);

            return $bmn;
        });
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
            }

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

            $kategoriLabel = match ($bmn->jenis) {
                'BMN-TIK' => 'BMN TIK',
                'BMN-LAB' => 'BMN Lab',
                default => 'BMN',
            };

            $dataId = match ($bmn->jenis) {
                'BMN-TIK' => $bmn->tik?->id ?? $bmn->id,
                'BMN-LAB' => $bmn->lab?->id ?? $bmn->id,
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

    public function createAutomaticDistribusiIfNeeded(Bmn $bmn): ?Distribusi
    {
        $lokasiNormalized = strtolower(trim($bmn->lokasi ?? ''));
        $pjNormalized = strtolower(trim($bmn->penanggungjawab ?? ''));

        $isGudang = str_contains($lokasiNormalized, 'gudang');
        $isPengelolaBmn = str_contains($pjNormalized, 'pengelola bmn') || empty($pjNormalized);

        if (! $isGudang && ! $isPengelolaBmn) {
            return null;
        }

        $datePart = now()->format('Ymd');
        $countToday = Distribusi::whereDate('created_at', today())->count() + 1;
        $nomorBa = 'BA-DIST/'.$datePart.'/'.str_pad((string) $countToday, 3, '0', STR_PAD_LEFT);
        while (Distribusi::where('nomor', $nomorBa)->exists()) {
            $countToday++;
            $nomorBa = 'BA-DIST/'.$datePart.'/'.str_pad((string) $countToday, 3, '0', STR_PAD_LEFT);
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
