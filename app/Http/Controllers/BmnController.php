<?php

namespace App\Http\Controllers;

use App\Http\Requests\Bmn\StoreBmnRequest;
use App\Http\Requests\Bmn\UpdateBmnLokasiRequest;
use App\Http\Requests\Bmn\UpdateBmnRequest;
use App\Models\Bmn;
use App\Models\Lokasi;
use App\Models\Pegawai;
use App\Services\BmnService;
use Illuminate\Http\Request;

class BmnController extends Controller
{
    public function __construct(
        protected BmnService $bmnService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'jenis', 'kondisi', 'lokasi', 'penanggungjawab', 'tahun', 'tahun_perolehan']);
        $bmns = $this->bmnService->getPaginated($filters);
        $filterData = $this->bmnService->getFilterData();

        return view('bmn.index', array_merge(['bmns' => $bmns], $filterData));
    }

    public function export(Request $request)
    {
        $filters = $request->only([
            'search',
            'jenis',
            'kondisi',
            'lokasi',
            'penanggungjawab',
            'tahun',
            'tahun_perolehan',
            'format',
        ]);

        return $this->bmnService->export($filters);
    }

    public function create()
    {
        $lokasis = Lokasi::orderedByGedung()->get();
        $pegawais = Pegawai::orderBy('nama')->get();
        $jenisBarang = ['BMN-NON TIK', 'BMN-LAB', 'BMN-TIK', 'BMN-TIK & LAB'];

        return view('bmn.create', compact('lokasis', 'pegawais', 'jenisBarang'));
    }

    public function store(StoreBmnRequest $request)
    {
        $this->bmnService->store($request->validated(), $request);

        return redirect()
            ->route('bmns.index')
            ->with('success', 'Data BMN dan dokumen pendukung berhasil disimpan.');
    }

    public function show(Bmn $bmn)
    {
        abort_if($bmn->dihapus, 404);

        $bmn->load('dokumens');

        return view('bmn.show', compact('bmn'));
    }

    public function edit(Request $request, Bmn $bmn)
    {
        abort_if($bmn->dihapus, 404);

        $returnQuery = array_filter(
            $request->only(['page', 'search', 'jenis', 'lokasi', 'penanggungjawab', 'kondisi']),
            fn ($value) => $value !== null && $value !== ''
        );

        session()->put('bmn_edit_return_query.'.$bmn->id, $returnQuery);

        $lokasis = Lokasi::orderedByGedung()->get();
        $pegawais = Pegawai::orderBy('nama')->get();
        $jenisBarang = ['BMN-NON TIK', 'BMN-LAB', 'BMN-TIK', 'BMN-TIK & LAB'];

        return view('bmn.edit', compact('bmn', 'lokasis', 'pegawais', 'jenisBarang'));
    }

    public function update(UpdateBmnRequest $request, Bmn $bmn)
    {
        abort_if($bmn->dihapus, 404);

        $this->bmnService->update($bmn, $request->validated());

        $returnQuery = session()->pull('bmn_edit_return_query.'.$bmn->id, []);

        return redirect()
            ->route('bmns.index', $returnQuery)
            ->with('success', 'Data BMN berhasil diperbarui.');
    }

    public function updateLokasi(UpdateBmnLokasiRequest $request, Bmn $bmn)
    {
        if ($bmn->dihapus) {
            return redirect()
                ->route('bmns.index')
                ->with('error', 'Data BMN tersebut sudah berada di Data Terhapus.');
        }

        $this->bmnService->updateLokasi($bmn, $request->validated('lokasi'));

        $query = array_filter([
            'page' => $request->input('page'),
            'search' => $request->input('search'),
            'jenis' => $request->input('jenis') ?: $request->input('old_filter_jenis'),
            'lokasi' => $request->input('old_filter_lokasi'),
            'penanggungjawab' => $request->input('penanggungjawab'),
            'kondisi' => $request->input('kondisi'),
        ], fn ($val) => ! empty($val));

        return redirect()
            ->route('bmns.index', $query)
            ->with('success', 'Lokasi BMN berhasil diperbarui.');
    }

    public function destroy(Bmn $bmn)
    {
        if ($bmn->dihapus) {
            return redirect()
                ->route('bmns.index')
                ->with('error', 'Data BMN tersebut sudah berada di Data Terhapus.');
        }

        $this->bmnService->delete($bmn, auth()->user()?->email);

        return back(fallback: route('bmns.index'))
            ->with('success', 'Data BMN berhasil dihapus dan dipindahkan ke Data Terhapus.');
    }
}
