<?php

namespace App\Http\Controllers;

use App\Http\Requests\Bmn\UpdateBmnLokasiRequest;
use App\Http\Requests\BmnLab\StoreBmnLabRequest;
use App\Http\Requests\BmnLab\UpdateBmnLabRequest;
use App\Models\Bmn;
use App\Models\BmnLab;
use App\Models\Lokasi;
use App\Models\Pegawai;
use App\Services\BmnLabService;
use Illuminate\Http\Request;

class BmnLabController extends Controller
{
    public function __construct(
        protected BmnLabService $bmnLabService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'lokasi', 'penanggungjawab', 'kondisi', 'status_kalibrasi']);
        $bmnsLab = $this->bmnLabService->getPaginated($filters);
        $filterData = $this->bmnLabService->getFilterData();

        return view('bmn-lab.index', array_merge([
            'bmnsLab' => $bmnsLab,
            'items' => $bmnsLab,
        ], $filterData));
    }

    public function create()
    {
        $lokasis = Lokasi::orderBy('nama')->get();
        $pegawais = Pegawai::orderBy('nama')->get();

        return view('bmn-lab.create', compact('lokasis', 'pegawais'));
    }

    public function store(StoreBmnLabRequest $request)
    {
        $this->bmnLabService->store($request->validated());

        return redirect()
            ->route('bmns-lab.index')
            ->with('success', 'Data BMN Lab berhasil ditambahkan.');
    }

    public function show(BmnLab $bmnLab)
    {
        return view('bmn-lab.show', compact('bmnLab'));
    }

    public function edit(Request $request, BmnLab $bmnLab)
    {
        $returnQuery = array_filter(
            $request->only(['page', 'search', 'lokasi', 'penanggungjawab', 'kondisi', 'status_kalibrasi']),
            fn ($value) => $value !== null && $value !== ''
        );

        session()->put('bmn_lab_edit_return_query.'.$bmnLab->id, $returnQuery);

        $lokasis = Lokasi::orderBy('nama')->get();
        $pegawais = Pegawai::orderBy('nama')->get();

        return view('bmn-lab.edit', [
            'bmnsLab' => $bmnLab,
            'lokasis' => $lokasis,
            'pegawais' => $pegawais,
        ]);
    }

    public function update(UpdateBmnLabRequest $request, BmnLab $bmnLab)
    {
        $this->bmnLabService->update($bmnLab, $request->validated());

        $returnQuery = session()->pull('bmn_lab_edit_return_query.'.$bmnLab->id, []);

        return redirect()
            ->route('bmns-lab.index', $returnQuery)
            ->with('success', 'Data BMN Lab berhasil diperbarui.');
    }

    public function updateLokasi(UpdateBmnLokasiRequest $request, BmnLab $bmnLab)
    {
        abort_if($bmnLab->dihapus, 404);

        $lokasiBaru = $request->validated('lokasi');
        $bmnLab->update(['lokasi' => $lokasiBaru]);

        if ($bmnLab->bmn_id) {
            Bmn::where('id', $bmnLab->bmn_id)->update(['lokasi' => $lokasiBaru]);
        }

        return back()->with('success', 'Lokasi BMN Lab berhasil diperbarui.');
    }

    public function destroy(BmnLab $bmnLab)
    {
        $this->bmnLabService->delete($bmnLab, auth()->user()?->email);

        return back(fallback: route('bmns-lab.index'))
            ->with('success', 'Data BMN Lab berhasil dipindahkan ke Data Terhapus.');
    }
}
