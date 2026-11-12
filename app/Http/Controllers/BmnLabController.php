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

    public function updatePenanggungJawab(Request $request, BmnLab $bmnLab)
    {
        abort_if($bmnLab->dihapus, 404);

        $pjBaru = trim((string) $request->input('penanggungjawab'));
        if ($pjBaru !== '') {
            $bmnLab->update(['penanggungjawab' => $pjBaru]);
            if ($bmnLab->bmn_id) {
                $bmn = Bmn::find($bmnLab->bmn_id);
                if ($bmn) {
                    $bmn->update([
                        'penanggungjawab' => $pjBaru,
                        'penanggung_jawab_id' => $request->input('penanggung_jawab_id'),
                        'penanggungjawab_nip' => $request->input('penanggungjawab_nip') ?: '-',
                    ]);
                }
            }
        }

        return back()->with('success', 'Penanggung jawab BMN Lab berhasil diperbarui.');
    }

    public function batchUpdate(Request $request)
    {
        $items = $request->input('items');
        if (is_string($items)) {
            $items = json_decode($items, true);
        }
        if (! is_array($items) || empty($items)) {
            return back()->with('error', 'Tidak ada data perubahan yang dikirim.');
        }

        $count = 0;
        \Illuminate\Support\Facades\DB::transaction(function () use ($items, &$count) {
            foreach ($items as $item) {
                $labId = $item['id'] ?? null;
                if (! $labId) continue;
                $bmnLab = BmnLab::find($labId);
                if (! $bmnLab || $bmnLab->dihapus) continue;

                $updateData = [];
                if (! empty($item['lokasi'])) {
                    $updateData['lokasi'] = $item['lokasi'];
                }
                if (! empty($item['penanggungjawab'])) {
                    $updateData['penanggungjawab'] = $item['penanggungjawab'];
                }

                if (! empty($updateData)) {
                    $bmnLab->update($updateData);
                    if ($bmnLab->bmn_id) {
                        $bmn = Bmn::find($bmnLab->bmn_id);
                        if ($bmn) {
                            $bmnUpdate = $updateData;
                            if (isset($item['penanggung_jawab_id'])) {
                                $bmnUpdate['penanggung_jawab_id'] = $item['penanggung_jawab_id'];
                            }
                            if (isset($item['penanggungjawab_nip'])) {
                                $bmnUpdate['penanggungjawab_nip'] = $item['penanggungjawab_nip'];
                            }
                            $bmn->update($bmnUpdate);
                        }
                    }
                    $count++;
                }
            }
        });

        return back()->with('success', "Berhasil memperbarui {$count} data BMN Lab.");
    }

    public function destroy(BmnLab $bmnLab)
    {
        $this->bmnLabService->delete($bmnLab, auth()->user()?->email);

        return back(fallback: route('bmns-lab.index'))
            ->with('success', 'Data BMN Lab berhasil dipindahkan ke Data Terhapus.');
    }
}
