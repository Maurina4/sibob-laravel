<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pengembalian\StorePengembalianRequest;
use App\Http\Requests\Pengembalian\UpdatePengembalianRequest;
use App\Models\Bmn;
use App\Models\Pegawai;
use App\Models\Pengembalian;
use App\Services\PengembalianService;
use Illuminate\Http\Request;

class PengembalianController extends Controller
{
    public function __construct(
        protected PengembalianService $pengembalianService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'bidang']);
        $pengembalians = $this->pengembalianService->getPaginated($filters);
        $filterData = $this->pengembalianService->getFilterData();

        return view('pengembalian.index', array_merge(['pengembalians' => $pengembalians], $filterData));
    }

    public function create()
    {
        $pegawais = Pegawai::orderBy('nama')->get();
        $bidangs = [
            'Informasi dan Komunikasi',
            'Pengujian',
            'Pemeriksaan',
            'Tata Usaha',
        ];
        $bmns = Bmn::active()->orderBy('nama_barang')->get(['id', 'nama_barang', 'merek', 'nup', 'kode_barang', 'kondisi']);

        return view('pengembalian.create', compact('pegawais', 'bidangs', 'bmns'));
    }

    public function store(StorePengembalianRequest $request)
    {
        $this->pengembalianService->store($request->validated());

        return redirect()
            ->route('pengembalians.index')
            ->with('success', 'Pengajuan pengembalian BMN berhasil disimpan.');
    }

    public function show(Pengembalian $pengembalian)
    {
        abort_if($pengembalian->dihapus, 404);

        $pengembalian->load(['items.bmn', 'pengajuPegawai']);

        return view('pengembalian.show', compact('pengembalian'));
    }

    public function edit(Request $request, Pengembalian $pengembalian)
    {
        abort_if($pengembalian->dihapus, 404);

        $returnQuery = array_filter(
            $request->only(['page', 'search', 'bidang']),
            fn ($value) => $value !== null && $value !== ''
        );

        session()->put('pengembalian_edit_return_query.'.$pengembalian->id, $returnQuery);

        $pengembalian->load('items.bmn');
        $pegawais = Pegawai::orderBy('nama')->get();
        $bidangs = [
            'Informasi dan Komunikasi',
            'Pengujian',
            'Pemeriksaan',
            'Tata Usaha',
        ];
        $bmns = Bmn::active()->orderBy('nama_barang')->get(['id', 'nama_barang', 'merek', 'nup', 'kode_barang', 'kondisi']);

        return view('pengembalian.edit', compact('pengembalian', 'pegawais', 'bidangs', 'bmns'));
    }

    public function update(UpdatePengembalianRequest $request, Pengembalian $pengembalian)
    {
        abort_if($pengembalian->dihapus, 404);

        $this->pengembalianService->update($pengembalian, $request->validated());

        $returnQuery = session()->pull('pengembalian_edit_return_query.'.$pengembalian->id, []);

        return redirect()
            ->route('pengembalians.index', $returnQuery)
            ->with('success', 'Pengajuan pengembalian BMN berhasil diperbarui.');
    }

    public function destroy(Pengembalian $pengembalian)
    {
        abort_if($pengembalian->dihapus, 404);

        $this->pengembalianService->delete($pengembalian, auth()->user()?->email);

        return back(fallback: route('pengembalians.index'))
            ->with('success', 'Pengajuan pengembalian BMN berhasil dipindahkan ke Data Terhapus.');
    }

    public function print(Pengembalian $pengembalian)
    {
        abort_if($pengembalian->dihapus, 404);

        $pengembalian->loadMissing(['items.bmn', 'pengajuPegawai']);

        // Resolusi Pihak Pertama (Pegawai Pengembali)
        $p1 = $pengembalian->pengajuPegawai;
        $pihakPertama = [
            'nama' => $p1?->nama ?: ($pengembalian->nama_pengaju ?: '....................................................'),
            'nip' => $p1?->nip ?: '-',
            'jabatan' => $p1?->jabatan ?: 'Pegawai Pemakai BMN',
            'unit_kerja' => $p1?->unit_kerja ?: ($pengembalian->bidang ?: 'Balai Besar POM di Banjarbaru'),
        ];

        // Resolusi Pihak Kedua (Pengelola BMN / Penerima)
        $pengelolaBmn = Pegawai::where('jabatan', 'like', '%Operator BMN%')
            ->orWhere('jabatan', 'like', '%Pengelola BMN%')
            ->orWhere('jabatan', 'like', '%Gudang%')
            ->first();

        $pihakKedua = [
            'nama' => $pengelolaBmn?->nama ?: 'Yuli Supriyatno,A.Md.',
            'nip' => $pengelolaBmn?->nip ?: '19840722 202321 1 010',
            'jabatan' => $pengelolaBmn?->jabatan ?: 'Pengelola Barang Milik Negara (BMN)',
            'unit_kerja' => $pengelolaBmn?->unit_kerja ?: 'Balai Besar POM di Banjarbaru',
        ];

        // Resolusi Kepala Balai (Mengetahui)
        $kabalai = Pegawai::where('jabatan', 'like', '%Kepala Balai%')->first();
        $kabalaiNama = request('kabalai_nama') ?: ($kabalai?->nama ?: 'Ary Yustantiningsih,S.Si.,Apt.');
        $kabalaiNip = request('kabalai_nip') ?: ($kabalai?->nip ?: '-');

        // Resolusi Kasubag TU (Mengetahui)
        $kasubag = Pegawai::where('jabatan', 'like', '%Kepala Tata Usaha%')
            ->orWhere('jabatan', 'like', '%Kasubag TU%')
            ->first();

        $kasubagNama = $kasubag?->nama ?: 'Kepala Sub Bagian Tata Usaha';
        $kasubagNip = $kasubag?->nip ?: '-';

        // Resolusi Kondisi Pengembalian BMN
        $kondisiGeneral = $pengembalian->kondisi_pengembalian;
        if (empty($kondisiGeneral) && $pengembalian->items && $pengembalian->items->isNotEmpty()) {
            $uniqueConditions = $pengembalian->items->pluck('kondisi')->filter()->unique();
            if ($uniqueConditions->isNotEmpty()) {
                $kondisiGeneral = $uniqueConditions->implode(' / ');
            }
        }
        if (empty($kondisiGeneral)) {
            $kondisiGeneral = 'Baik';
        }

        return view('pengembalian.print', compact('pengembalian', 'pihakPertama', 'pihakKedua', 'kabalaiNama', 'kabalaiNip', 'kasubagNama', 'kasubagNip', 'kondisiGeneral'));
    }
}
