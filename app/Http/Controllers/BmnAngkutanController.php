<?php

namespace App\Http\Controllers;

use App\Http\Requests\Bmn\UpdateBmnLokasiRequest;
use App\Http\Requests\BmnAngkutan\StoreBmnAngkutanRequest;
use App\Http\Requests\BmnAngkutan\UpdateBmnAngkutanRequest;
use App\Models\Bmn;
use App\Models\BmnAngkutan;
use App\Models\Lokasi;
use App\Models\Pegawai;
use App\Services\BmnAngkutanService;
use Illuminate\Http\Request;

class BmnAngkutanController extends Controller
{
    public function __construct(
        protected BmnAngkutanService $bmnAngkutanService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'search', 'lokasi', 'penanggungjawab', 'kondisi']);
        $bmnsAngkutan = $this->bmnAngkutanService->getPaginated($filters);
        $filterData = $this->bmnAngkutanService->getFilterData();

        return view('bmn-angkutan.index', array_merge(['bmnsAngkutan' => $bmnsAngkutan], $filterData));
    }

    public function create()
    {
        $lokasis = Lokasi::orderBy('nama')->get();
        $pegawais = Pegawai::orderBy('nama')->get();

        return view('bmn-angkutan.create', compact('lokasis', 'pegawais'));
    }

    public function store(StoreBmnAngkutanRequest $request)
    {
        $this->bmnAngkutanService->store($request->validated());

        return redirect()
            ->route('bmns-angkutan.index')
            ->with('success', 'Data Alat Angkutan Bermotor berhasil ditambahkan.');
    }

    public function show(BmnAngkutan $bmnAngkutan)
    {
        abort_if($bmnAngkutan->dihapus, 404);

        return view('bmn-angkutan.show', compact('bmnAngkutan'));
    }

    public function edit(Request $request, BmnAngkutan $bmnAngkutan)
    {
        abort_if($bmnAngkutan->dihapus, 404);

        $returnQuery = array_filter(
            $request->only(['page', 'q', 'search', 'lokasi', 'penanggungjawab', 'kondisi']),
            fn ($value) => $value !== null && $value !== ''
        );

        session()->put('bmn_angkutan_edit_return_query.'.$bmnAngkutan->id, $returnQuery);

        $lokasis = Lokasi::orderBy('nama')->get();
        $pegawais = Pegawai::orderBy('nama')->get();

        return view('bmn-angkutan.edit', compact('bmnAngkutan', 'lokasis', 'pegawais'));
    }

    public function update(UpdateBmnAngkutanRequest $request, BmnAngkutan $bmnAngkutan)
    {
        abort_if($bmnAngkutan->dihapus, 404);

        $this->bmnAngkutanService->update($bmnAngkutan, $request->validated());

        $returnQuery = session()->pull('bmn_angkutan_edit_return_query.'.$bmnAngkutan->id, []);

        return redirect()
            ->route('bmns-angkutan.index', $returnQuery)
            ->with('success', 'Data Alat Angkutan Bermotor berhasil diperbarui.');
    }

    public function updateLokasi(UpdateBmnLokasiRequest $request, BmnAngkutan $bmnAngkutan)
    {
        abort_if($bmnAngkutan->dihapus, 404);

        $lokasiBaru = $request->validated('lokasi');
        $bmnAngkutan->update(['lokasi' => $lokasiBaru]);

        if ($bmnAngkutan->bmn_id) {
            Bmn::where('id', $bmnAngkutan->bmn_id)->update(['lokasi' => $lokasiBaru]);
        }

        return back()->with('success', 'Lokasi Alat Angkutan Bermotor berhasil diperbarui.');
    }

    public function destroy(BmnAngkutan $bmnAngkutan)
    {
        abort_if($bmnAngkutan->dihapus, 404);

        $userEmail = auth()->user()?->email;
        $this->bmnAngkutanService->delete($bmnAngkutan, $userEmail);

        return redirect()
            ->route('bmns-angkutan.index')
            ->with('success', 'Data Alat Angkutan Bermotor berhasil dihapus.');
    }
}
