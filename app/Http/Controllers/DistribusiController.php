<?php

namespace App\Http\Controllers;

use App\Http\Requests\Distribusi\StoreDistribusiRequest;
use App\Http\Requests\Distribusi\UpdateDistribusiRequest;
use App\Models\Distribusi;
use App\Models\Lokasi;
use App\Models\Pegawai;
use App\Services\DistribusiService;
use Illuminate\Http\Request;

class DistribusiController extends Controller
{
    public function __construct(
        protected DistribusiService $distribusiService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        $items = $this->distribusiService->getPaginated($filters);

        return view('distribusi.index', compact('items'));
    }

    public function create()
    {
        $lokasis = Lokasi::orderedByGedung()->get();
        $pegawais = Pegawai::orderBy('nama')->get();

        return view('distribusi.create', compact('lokasis', 'pegawais'));
    }

    public function store(StoreDistribusiRequest $request)
    {
        $this->distribusiService->store($request->validated());

        return redirect()
            ->route('distribusi.index')
            ->with('success', 'Data distribusi berhasil ditambahkan.');
    }

    public function show(Distribusi $distribusi)
    {
        $distribusi->load(['bmn', 'items.bmn', 'lokasiAsalRef', 'lokasiTujuanRef', 'penerimaPegawai']);

        return view('distribusi.show', compact('distribusi'));
    }

    public function print(Distribusi $distribusi)
    {
        if ($distribusi->is_pending) {
            return redirect()
                ->route('distribusi.show', $distribusi)
                ->with('error', 'Barang belum didistribusikan. Silakan tentukan lokasi ruangan dan penerima terlebih dahulu sebelum mencetak Berita Acara.');
        }

        $distribusi->load(['bmn', 'items.bmn', 'lokasiAsalRef', 'lokasiTujuanRef', 'penerimaPegawai']);

        // Resolusi Pihak Pertama (Pengelola BMN / Penyerah)
        $pengelolaBmn = Pegawai::where('nama', 'like', '%Yuli%')
            ->orWhere('jabatan', 'like', '%Pengelola BMN%')
            ->orWhere('jabatan', 'like', '%Operator BMN%')
            ->orWhere('jabatan', 'like', '%Gudang%')
            ->orWhere('jabatan', 'like', '%Distribusi%')
            ->first();

        $pihakPertama = [
            'nama' => $pengelolaBmn?->nama ?: 'Yuli Supriyatno, A. Md.',
            'nip' => $pengelolaBmn?->nip ?: '198407222023211010',
            'jabatan' => $pengelolaBmn?->jabatan ?: 'Pengelola BMN',
            'unit_kerja' => $pengelolaBmn?->unit_kerja ?: 'Balai Besar POM di Banjarbaru',
        ];

        // Resolusi Pihak Kedua (Pegawai Penerima)
        $p2 = $distribusi->penerimaPegawai;
        if (! $p2 && ! empty($distribusi->penerima)) {
            $namaClean = trim(preg_replace('/\s*\([^)]*\)/', '', $distribusi->penerima));
            $p2 = Pegawai::where('nama', $namaClean)
                ->orWhere('nama', $distribusi->penerima)
                ->first();
            if (! $p2 && preg_match('/\(([\d\s]+)\)/', $distribusi->penerima, $matches)) {
                $p2 = Pegawai::where('nip', str_replace(' ', '', $matches[1]))->first();
            }
        }

        $nipPenerima = $p2?->nip;
        if (! $nipPenerima && preg_match('/\(([\d\s]+)\)/', $distribusi->penerima ?? '', $m)) {
            $nipPenerima = str_replace(' ', '', $m[1]);
        }
        if (! $nipPenerima) {
            $nipPenerima = $distribusi->bmn?->penanggungjawab_nip ?: '-';
        }

        $namaPenerimaClean = trim(preg_replace('/\s*\([^)]*\)/', '', $distribusi->penerima ?? ''));

        $pihakKedua = [
            'nama' => $p2?->nama ?: ($namaPenerimaClean ?: ($distribusi->penerima ?: '....................................................')),
            'nip' => $nipPenerima,
            'jabatan' => $p2?->jabatan ?: 'Penanggung Jawab / Pengguna Barang',
            'unit_kerja' => $p2?->unit_kerja ?: 'Balai Besar POM di Banjarbaru',
        ];

        // Resolusi Kepala Balai (Mengetahui)
        $kabalai = Pegawai::where('jabatan', 'like', '%Kepala Balai%')->first();
        $kabalaiNama = request('kabalai_nama') ?: ($kabalai?->nama ?: 'Ary Yustantingsih,S.Si., Apt.');
        $kabalaiNip = request('kabalai_nip') ?: ($kabalai?->nip ?: '-');

        // Resolusi Kasubag TU (Mengetahui)
        $kasubag = Pegawai::where('jabatan', 'like', '%Kepala Tata Usaha%')
            ->orWhere('jabatan', 'like', '%Kasubag TU%')
            ->first();

        $kasubagNama = $kasubag?->nama ?: 'Kepala Sub Bagian Tata Usaha';
        $kasubagNip = $kasubag?->nip ?: '-';

        return view('distribusi.print', compact('distribusi', 'pihakPertama', 'pihakKedua', 'kabalaiNama', 'kabalaiNip', 'kasubagNama', 'kasubagNip'));
    }

    public function edit(Request $request, Distribusi $distribusi)
    {
        $returnQuery = array_filter(
            $request->query(),
            fn ($value) => $value !== null && $value !== ''
        );

        session()->put('distribusi_edit_return_query.'.$distribusi->id, $returnQuery);

        $distribusi->load(['bmn', 'items.bmn']);
        $lokasis = Lokasi::orderedByGedung()->get();
        $pegawais = Pegawai::orderBy('nama')->get();

        return view('distribusi.edit', compact('distribusi', 'lokasis', 'pegawais'));
    }

    public function update(UpdateDistribusiRequest $request, Distribusi $distribusi)
    {
        $this->distribusiService->update($distribusi, $request->validated());

        $returnQuery = session()->pull('distribusi_edit_return_query.'.$distribusi->id, []);

        return redirect()
            ->route('distribusi.index', $returnQuery)
            ->with('success', 'Data distribusi berhasil diperbarui.');
    }

    public function destroy(Distribusi $distribusi)
    {
        $this->distribusiService->delete($distribusi, auth()->user()?->email);

        return back(fallback: route('distribusi.index'))
            ->with('success', 'Data distribusi dipindahkan ke Data Terhapus.');
    }
}
