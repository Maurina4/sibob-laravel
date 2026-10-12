<?php

namespace App\Http\Controllers;

use App\Http\Requests\BmnLab\StoreBmnLabRequest;
use App\Http\Requests\BmnLab\UpdateBmnLabRequest;
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

        return view('bmn-lab.index', array_merge(['bmnsLab' => $bmnsLab], $filterData));
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

    public function edit(BmnLab $bmnLab)
    {
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

        return redirect()
            ->route('bmns-lab.index')
            ->with('success', 'Data BMN Lab berhasil diperbarui.');
    }

    public function destroy(BmnLab $bmnLab)
    {
        $this->bmnLabService->delete($bmnLab, auth()->user()?->email);

        return redirect()
            ->route('bmns-lab.index')
            ->with('success', 'Data BMN Lab berhasil dipindahkan ke Data Terhapus.');
    }
}