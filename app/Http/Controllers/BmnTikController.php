<?php

namespace App\Http\Controllers;

use App\Http\Requests\BmnTik\StoreBmnTikRequest;
use App\Http\Requests\BmnTik\UpdateBmnTikRequest;
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
        $filters = $request->only(['q', 'jenis', 'lokasi', 'penanggungjawab']);
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

    public function edit(BmnTik $bmnTik)
    {
        abort_if($bmnTik->dihapus, 404);

        $lokasis = Lokasi::orderBy('nama')->get();
        $pegawais = Pegawai::orderBy('nama')->get();

        return view('bmn-tik.edit', compact('bmnTik', 'lokasis', 'pegawais'));
    }

    public function update(UpdateBmnTikRequest $request, BmnTik $bmnTik)
    {
        abort_if($bmnTik->dihapus, 404);

        $this->bmnTikService->update($bmnTik, $request->validated());

        return redirect()
            ->route('bmns-tik.index')
            ->with('success', 'Data BMN TIK berhasil diperbarui.');
    }

    public function destroy(BmnTik $bmnTik)
    {
        abort_if($bmnTik->dihapus, 404);

        $this->bmnTikService->delete($bmnTik, auth()->user()?->email);

        return redirect()
            ->route('bmns-tik.index')
            ->with('success', 'Data BMN TIK dipindahkan ke Data Terhapus.');
    }
}