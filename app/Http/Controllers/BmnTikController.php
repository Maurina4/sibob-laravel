<?php

namespace App\Http\Controllers;

use App\Http\Requests\Bmn\UpdateBmnLokasiRequest;
use App\Http\Requests\BmnTik\StoreBmnTikRequest;
use App\Http\Requests\BmnTik\UpdateBmnTikRequest;
use App\Models\Bmn;
use App\Models\BmnTik;
use App\Models\Lokasi;
use App\Models\Pegawai;
use App\Services\BmnTikService;
use Illuminate\Http\Request;

class BmnTikController extends Controller
{
    public function __construct(
        protected BmnTikService $bmnTikService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'jenis', 'lokasi', 'penanggungjawab', 'kondisi']);
        $bmnsTik = $this->bmnTikService->getPaginated($filters);
        $filterData = $this->bmnTikService->getFilterData();

        return view('bmn-tik.index', array_merge(['bmnsTik' => $bmnsTik], $filterData));
    }

    public function create()
    {
        $lokasis = Lokasi::orderBy('nama')->get();
        $pegawais = Pegawai::orderBy('nama')->get();

        return view('bmn-tik.create', compact('lokasis', 'pegawais'));
    }

    public function store(StoreBmnTikRequest $request)
    {
        $this->bmnTikService->store($request->validated());

        return redirect()
            ->route('bmns-tik.index')
            ->with('success', 'Data BMN TIK berhasil ditambahkan.');
    }

    public function show(BmnTik $bmnTik)
    {
        abort_if($bmnTik->dihapus, 404);

        return view('bmn-tik.show', compact('bmnTik'));
    }

    public function edit(Request $request, BmnTik $bmnTik)
    {
        abort_if($bmnTik->dihapus, 404);

        $returnQuery = array_filter(
            $request->only(['page', 'q', 'jenis', 'lokasi', 'penanggungjawab', 'kondisi']),
            fn ($value) => $value !== null && $value !== ''
        );

        session()->put('bmn_tik_edit_return_query.'.$bmnTik->id, $returnQuery);

        $lokasis = Lokasi::orderBy('nama')->get();
        $pegawais = Pegawai::orderBy('nama')->get();

        return view('bmn-tik.edit', compact('bmnTik', 'lokasis', 'pegawais'));
    }

    public function update(UpdateBmnTikRequest $request, BmnTik $bmnTik)
    {
        abort_if($bmnTik->dihapus, 404);

        $this->bmnTikService->update($bmnTik, $request->validated());

        $returnQuery = session()->pull('bmn_tik_edit_return_query.'.$bmnTik->id, []);

        return redirect()
            ->route('bmns-tik.index', $returnQuery)
            ->with('success', 'Data BMN TIK berhasil diperbarui.');
    }

    public function updateLokasi(UpdateBmnLokasiRequest $request, BmnTik $bmnTik)
    {
        abort_if($bmnTik->dihapus, 404);

        $lokasiBaru = $request->validated('lokasi');
        $bmnTik->update(['lokasi' => $lokasiBaru]);

        if ($bmnTik->bmn_id) {
            Bmn::where('id', $bmnTik->bmn_id)->update(['lokasi' => $lokasiBaru]);
        }

        return back()->with('success', 'Lokasi BMN TIK berhasil diperbarui.');
    }

    public function destroy(BmnTik $bmnTik)
    {
        abort_if($bmnTik->dihapus, 404);

        $this->bmnTikService->delete($bmnTik, auth()->user()?->email);

        return back(fallback: route('bmns-tik.index'))
            ->with('success', 'Data BMN TIK dipindahkan ke Data Terhapus.');
    }
}
