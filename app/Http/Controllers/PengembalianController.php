<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pengembalian\StorePengembalianRequest;
use App\Http\Requests\Pengembalian\UpdatePengembalianRequest;
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

        return view('pengembalian.create', compact('pegawais', 'bidangs'));
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

        return view('pengembalian.show', compact('pengembalian'));
    }

    public function edit(Request $request, Pengembalian $pengembalian)
    {
        abort_if($pengembalian->dihapus, 404);

        $returnQuery = array_filter(
            $request->only(['search', 'bidang']),
            fn ($value) => $value !== null && $value !== ''
        );

        session()->put('pengembalian_edit_return_query.' . $pengembalian->id, $returnQuery);

        $pegawais = Pegawai::orderBy('nama')->get();
        $bidangs = [
            'Informasi dan Komunikasi',
            'Pengujian',
            'Pemeriksaan',
            'Tata Usaha',
        ];

        return view('pengembalian.edit', compact('pengembalian', 'pegawais', 'bidangs'));
    }

    public function update(UpdatePengembalianRequest $request, Pengembalian $pengembalian)
    {
        abort_if($pengembalian->dihapus, 404);

        $this->pengembalianService->update($pengembalian, $request->validated());

        $returnQuery = session()->pull('pengembalian_edit_return_query.' . $pengembalian->id, []);

        return redirect()
            ->route('pengembalians.index', $returnQuery)
            ->with('success', 'Pengajuan pengembalian BMN berhasil diperbarui.');
    }

    public function destroy(Pengembalian $pengembalian)
    {
        abort_if($pengembalian->dihapus, 404);

        $this->pengembalianService->delete($pengembalian, auth()->user()?->email);

        return redirect()
            ->route('pengembalians.index')
            ->with('success', 'Pengajuan pengembalian BMN berhasil dipindahkan ke Data Terhapus.');
    }

    public function print(Pengembalian $pengembalian)
    {
        abort_if($pengembalian->dihapus, 404);

        return view('pengembalian.print', compact('pengembalian'));
    }
}