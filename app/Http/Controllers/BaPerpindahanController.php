<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaPerpindahan\StoreBaPerpindahanRequest;
use App\Http\Requests\BaPerpindahan\UpdateBaPerpindahanRequest;
use App\Models\BaPerpindahan;
use App\Models\Bmn;
use App\Models\Lokasi;
use App\Models\Pegawai;
use App\Services\BaPerpindahanService;
use Illuminate\Http\Request;

class BaPerpindahanController extends Controller
{
    public function __construct(
        protected BaPerpindahanService $baPerpindahanService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        $items = $this->baPerpindahanService->getPaginated($filters);

        return view('ba-perpindahan.index', compact('items'));
    }

    public function create()
    {
        $bmns = Bmn::active()
            ->orderBy('kode_barang')
            ->orderBy('nup')
            ->get(['id', 'kode_barang', 'nup', 'nama_barang', 'lokasi', 'penanggungjawab', 'penanggung_jawab_id']);
        $pegawais = Pegawai::orderBy('nama')->get(['id', 'nama', 'nip', 'jabatan', 'unit_kerja']);
        $lokasis = Lokasi::orderedByGedung()->get(['id', 'kode_ruangan', 'nama']);

        return view('ba-perpindahan.create', compact('bmns', 'pegawais', 'lokasis'));
    }

    public function store(StoreBaPerpindahanRequest $request)
    {
        $this->baPerpindahanService->store($request->validated(), auth()->user());

        return redirect()
            ->route('ba-perpindahan.index')
            ->with('success', 'Pengajuan BA perpindahan berhasil dibuat.');
    }

    public function show(BaPerpindahan $baPerpindahan)
    {
        $baPerpindahan->load(['bmn', 'pegawai', 'pengajuPegawai', 'pihakPertamaPegawai']);
        $pegawais = Pegawai::orderBy('nama')->get(['id', 'nama', 'nip', 'jabatan', 'unit_kerja']);

        return view('ba-perpindahan.show', compact('baPerpindahan', 'pegawais'));
    }

    public function edit(Request $request, BaPerpindahan $baPerpindahan)
    {
        $returnQuery = array_filter(
            $request->query(),
            fn ($value) => $value !== null && $value !== ''
        );

        session()->put('ba_perpindahan_edit_return_query.'.$baPerpindahan->id, $returnQuery);

        $bmns = Bmn::active()
            ->orderBy('kode_barang')
            ->orderBy('nup')
            ->get(['id', 'kode_barang', 'nup', 'nama_barang', 'lokasi', 'penanggungjawab', 'penanggung_jawab_id']);
        $pegawais = Pegawai::orderBy('nama')->get(['id', 'nama', 'nip', 'jabatan', 'unit_kerja']);
        $lokasis = Lokasi::orderedByGedung()->get(['id', 'kode_ruangan', 'nama']);

        return view('ba-perpindahan.edit', compact('baPerpindahan', 'bmns', 'pegawais', 'lokasis'));
    }

    public function update(UpdateBaPerpindahanRequest $request, BaPerpindahan $baPerpindahan)
    {
        $this->baPerpindahanService->update($baPerpindahan, $request->validated(), auth()->user());

        $returnQuery = session()->pull('ba_perpindahan_edit_return_query.'.$baPerpindahan->id, []);

        return redirect()
            ->route('ba-perpindahan.index', $returnQuery)
            ->with('success', 'BA perpindahan berhasil diperbarui.');
    }

    public function destroy(BaPerpindahan $baPerpindahan)
    {
        if ($baPerpindahan->status === 'Disetujui') {
            abort(403, 'BA Perpindahan yang sudah disetujui tidak dapat dihapus.');
        }

        $this->baPerpindahanService->delete($baPerpindahan, auth()->user()?->email);

        return redirect()
            ->route('ba-perpindahan.index')
            ->with('success', 'BA perpindahan berhasil dihapus.');
    }

    public function assignRecipient(Request $request, BaPerpindahan $baPerpindahan)
    {
        $validated = $request->validate([
            'pihak_kedua' => ['required', 'string', 'max:255'],
        ]);

        $penyerah = $baPerpindahan->pihak_pertama ?: ($baPerpindahan->bmn?->penanggungjawab);
        $penyerahId = $baPerpindahan->pihak_pertama_id ?: ($baPerpindahan->bmn?->penanggung_jawab_id);
        if (BaPerpindahan::isSamePerson($penyerah, $validated['pihak_kedua'], $penyerahId)) {
            return back()->withInput()->withErrors([
                'pihak_kedua' => 'Penerima (Pihak Kedua) tidak boleh sama dengan Penyerah (Pihak Pertama).',
            ]);
        }

        $pengajuNama = $baPerpindahan->pengajuPegawai?->nama ?: ($baPerpindahan->pengaju_nama ?? '');
        $pengajuId = $baPerpindahan->pengaju_pegawai_id ?: ($baPerpindahan->pegawai_id ?? null);
        if (BaPerpindahan::isSamePerson($pengajuNama, $validated['pihak_kedua'], $pengajuId)) {
            return back()->withInput()->withErrors([
                'pihak_kedua' => 'Penerima (Pihak Kedua) tidak boleh sama dengan Pegawai Pengaju.',
            ]);
        }

        $baPerpindahan->update([
            'pihak_kedua' => $validated['pihak_kedua'],
        ]);

        return redirect()
            ->route('ba-perpindahan.show', $baPerpindahan)
            ->with('success', 'Pegawai Penerima Baru (Pihak Kedua) berhasil ditentukan.');
    }

    public function processDecision(Request $request, BaPerpindahan $baPerpindahan)
    {
        $validated = $request->validate([
            'pihak_kedua' => ['required', 'string', 'max:255'],
            'keputusan' => ['required', 'in:Setuju,Ditolak'],
            'alasan_penolakan' => ['required_if:keputusan,Ditolak', 'nullable', 'string', 'max:1000'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ], [
            'pihak_kedua.required' => 'Penanggung Jawab Kedua (Pegawai Penerima Baru) wajib dipilih.',
            'keputusan.required' => 'Silakan pilih keputusan: Setuju atau Ditolak.',
            'alasan_penolakan.required_if' => 'Alasan penolakan wajib diisi jika Anda memilih menolak pengajuan ini.',
        ]);

        $penyerah = $baPerpindahan->pihak_pertama ?: ($baPerpindahan->bmn?->penanggungjawab);
        $penyerahId = $baPerpindahan->pihak_pertama_id ?: ($baPerpindahan->bmn?->penanggung_jawab_id);
        if (BaPerpindahan::isSamePerson($penyerah, $validated['pihak_kedua'], $penyerahId)) {
            return back()->withInput()->withErrors([
                'pihak_kedua' => 'Penerima (Pihak Kedua) tidak boleh sama dengan Penyerah (Pihak Pertama).',
            ]);
        }

        $pengajuNama = $baPerpindahan->pengajuPegawai?->nama ?: ($baPerpindahan->pengaju_nama ?? '');
        $pengajuId = $baPerpindahan->pengaju_pegawai_id ?: ($baPerpindahan->pegawai_id ?? null);
        if (BaPerpindahan::isSamePerson($pengajuNama, $validated['pihak_kedua'], $pengajuId)) {
            return back()->withInput()->withErrors([
                'pihak_kedua' => 'Penerima (Pihak Kedua) tidak boleh sama dengan Pegawai Pengaju.',
            ]);
        }

        if ($validated['keputusan'] === 'Setuju') {
            $this->baPerpindahanService->approve(
                $baPerpindahan,
                auth()->user(),
                $validated['catatan'] ?? null,
                $validated['pihak_kedua']
            );

            return redirect()
                ->route('ba-perpindahan.show', $baPerpindahan)
                ->with('success', 'BA perpindahan berhasil disetujui. Posisi aset dan penanggung jawab di master BMN telah diperbarui.');
        }

        // Keputusan Ditolak
        $this->baPerpindahanService->reject(
            $baPerpindahan,
            auth()->user(),
            $validated['alasan_penolakan'],
            $validated['pihak_kedua']
        );

        return redirect()
            ->route('ba-perpindahan.show', $baPerpindahan)
            ->with('success', 'BA perpindahan telah ditolak beserta alasan penolakan.');
    }

    public function approve(Request $request, BaPerpindahan $baPerpindahan)
    {
        $pihakKedua = $request->input('pihak_kedua') ?: $baPerpindahan->pihak_kedua;

        if (empty($pihakKedua)) {
            return back()->with('error', 'Pegawai Penerima Baru (Pihak Kedua) harus ditentukan terlebih dahulu sebelum disetujui.');
        }

        $penyerah = $baPerpindahan->pihak_pertama ?: ($baPerpindahan->bmn?->penanggungjawab);
        $penyerahId = $baPerpindahan->pihak_pertama_id ?: ($baPerpindahan->bmn?->penanggung_jawab_id);
        if (BaPerpindahan::isSamePerson($penyerah, $pihakKedua, $penyerahId)) {
            return back()->withInput()->withErrors([
                'pihak_kedua' => 'Penerima (Pihak Kedua) tidak boleh sama dengan Penyerah (Pihak Pertama).',
            ]);
        }

        $pengajuNama = $baPerpindahan->pengajuPegawai?->nama ?: ($baPerpindahan->pengaju_nama ?? '');
        $pengajuId = $baPerpindahan->pengaju_pegawai_id ?: ($baPerpindahan->pegawai_id ?? null);
        if (BaPerpindahan::isSamePerson($pengajuNama, $pihakKedua, $pengajuId)) {
            return back()->withInput()->withErrors([
                'pihak_kedua' => 'Penerima (Pihak Kedua) tidak boleh sama dengan Pegawai Pengaju.',
            ]);
        }

        $this->baPerpindahanService->approve(
            $baPerpindahan,
            auth()->user(),
            $request->input('catatan'),
            $pihakKedua
        );

        return redirect()
            ->route('ba-perpindahan.index')
            ->with('success', 'BA perpindahan berhasil disetujui.');
    }

    public function cancelDecision(Request $request, BaPerpindahan $baPerpindahan)
    {
        $this->baPerpindahanService->cancelDecision(
            $baPerpindahan,
            auth()->user(),
            $request->input('catatan')
        );

        return back(fallback: route('ba-perpindahan.show', $baPerpindahan))
            ->with('success', 'Keputusan BA perpindahan berhasil dibatalkan. Status diubah menjadi Dibatalkan dan Anda dapat memutuskan kembali.');
    }

    public function cancelApproval(Request $request, BaPerpindahan $baPerpindahan)
    {
        return $this->cancelDecision($request, $baPerpindahan);
    }

    public function reject(Request $request, BaPerpindahan $baPerpindahan)
    {
        $catatan = $request->input('alasan_penolakan') ?: $request->input('catatan');

        $this->baPerpindahanService->reject(
            $baPerpindahan,
            auth()->user(),
            $catatan,
            $request->input('pihak_kedua')
        );

        return redirect()
            ->route('ba-perpindahan.index')
            ->with('success', 'BA perpindahan ditolak.');
    }

    public function print(Request $request, BaPerpindahan $baPerpindahan)
    {
        if (! $baPerpindahan->canBePrinted()) {
            return redirect()
                ->route('ba-perpindahan.show', $baPerpindahan)
                ->with('error', 'Berita Acara Perpindahan belum dapat dicetak karena Pegawai Penerima (Pihak Kedua) belum ditentukan.');
        }

        $baPerpindahan->load([
            'bmn',
            'items.bmn',
            'pengajuPegawai',
            'pihakPertamaPegawai',
            'lokasiAsalRef',
            'lokasiTujuanRef',
        ]);

        $size = strtolower($request->query('size', 'a4'));
        if (! in_array($size, ['a4', 'f4'])) {
            $size = 'a4';
        }

        // Resolusi Pihak Pertama (Nama, NIP, Jabatan, Unit Kerja)
        $p1 = $baPerpindahan->pihakPertamaPegawai
            ?? Pegawai::where('nama', $baPerpindahan->pihak_pertama)->first();
        $pihakPertama = [
            'nama' => $p1?->nama ?: ($baPerpindahan->pihak_pertama ?: ($baPerpindahan->bmn?->penanggungjawab ?: 'Yuli Supriyatno, A. Md')),
            'nip' => $p1?->nip ?: ($baPerpindahan->bmn?->penanggungjawab_nip ?: '19840722 202321 1 010'),
            'jabatan' => $p1?->jabatan ?: 'Pengelola BMN',
            'unit_kerja' => $p1?->unit_kerja ?: 'Balai Besar POM di Banjarbaru',
        ];

        // Resolusi Pihak Kedua (Nama, NIP, Jabatan, Unit Kerja)
        $namaPihakKeduaClean = trim(preg_replace('/\s*\([^)]*\)/', '', $baPerpindahan->pihak_kedua ?? ''));
        $p2 = Pegawai::where('nama', $namaPihakKeduaClean)->first()
            ?? Pegawai::where('nama', $baPerpindahan->pihak_kedua)->first();
        $matches = [];
        if (! $p2 && preg_match('/\(([\d\s]+)\)/', $baPerpindahan->pihak_kedua ?? '', $matches)) {
            $p2 = Pegawai::where('nip', str_replace(' ', '', $matches[1]))->first();
        }

        $pihakKedua = [
            'nama' => $p2?->nama ?: ($namaPihakKeduaClean ?: ($baPerpindahan->pihak_kedua ?: 'Risma Wulandari,A.Md., Kes.')),
            'nip' => $p2?->nip ?: ($matches[1] ?? '19970516 202521 2 009'),
            'jabatan' => $p2?->jabatan ?: 'Pengelola Layanan Operasional',
            'unit_kerja' => $p2?->unit_kerja ?: 'Balai Besar POM di Banjarbaru',
        ];

        // Resolusi Kepala Balai (Mengetahui)
        $kabalai = Pegawai::where('jabatan', 'like', '%Kepala Balai%')->first();
        $kabalaiNama = $request->query('kabalai_nama') ?: ($kabalai?->nama ?: 'Ary Yustantiningsih,S.Si.,Apt.');
        $kabalaiNip = $request->query('kabalai_nip') ?: ($kabalai?->nip ?: '-');

        // Resolusi Items Barang
        $items = $baPerpindahan->items;
        if ($items->isEmpty() && $baPerpindahan->bmn) {
            $items = collect([
                (object) [
                    'nama_barang' => $baPerpindahan->nama_barang ?: $baPerpindahan->bmn->nama_barang,
                    'kode_barang' => $baPerpindahan->bmn->kode_barang,
                    'nup' => $baPerpindahan->bmn->nup,
                    'merk' => $baPerpindahan->bmn->merek ?: ($baPerpindahan->bmn->spesifikasi ?: '-'),
                    'jumlah' => 1,
                    'tahun_perolehan' => $baPerpindahan->bmn->tahun_perolehan ?: '-',
                    'kondisi' => $baPerpindahan->bmn->kondisi ?: 'Rusak',
                    'lokasi_asal' => $baPerpindahan->dari,
                    'lokasi_tujuan' => $baPerpindahan->ke,
                ],
            ]);
        } elseif ($items->isEmpty()) {
            $items = collect([
                (object) [
                    'nama_barang' => $baPerpindahan->nama_barang ?: 'Barang Milik Negara',
                    'kode_barang' => '-',
                    'nup' => '-',
                    'merk' => '-',
                    'jumlah' => 1,
                    'tahun_perolehan' => '-',
                    'kondisi' => 'Rusak',
                    'lokasi_asal' => $baPerpindahan->dari,
                    'lokasi_tujuan' => $baPerpindahan->ke,
                ],
            ]);
        } else {
            $items = $items->map(function ($it) use ($baPerpindahan) {
                return (object) [
                    'nama_barang' => $it->nama_barang_snapshot ?: ($it->bmn?->nama_barang ?: $baPerpindahan->nama_barang),
                    'kode_barang' => $it->bmn?->kode_barang ?: '-',
                    'nup' => $it->bmn?->nup ?: '-',
                    'merk' => $it->bmn?->merek ?: ($it->bmn?->spesifikasi ?: '-'),
                    'jumlah' => 1,
                    'tahun_perolehan' => $it->bmn?->tahun_perolehan ?: '-',
                    'kondisi' => $it->kondisi_saat_pindah ?: ($it->bmn?->kondisi ?: 'Rusak'),
                    'lokasi_asal' => $baPerpindahan->dari,
                    'lokasi_tujuan' => $baPerpindahan->ke,
                ];
            });
        }

        $kondisiGeneral = $items->first()->kondisi ?? 'Rusak';

        $logoPath = public_path('images/logo-badan-pom-hd.png');
        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

        return view('ba-perpindahan.print', compact(
            'baPerpindahan',
            'size',
            'pihakPertama',
            'pihakKedua',
            'kabalaiNama',
            'kabalaiNip',
            'items',
            'kondisiGeneral',
            'logoBase64'
        ));
    }
}
