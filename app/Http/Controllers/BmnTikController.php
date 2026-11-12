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

    public function updatePenanggungJawab(Request $request, BmnTik $bmnTik)
    {
        abort_if($bmnTik->dihapus, 404);

        $pjBaru = trim((string) $request->input('penanggungjawab'));
        if ($pjBaru !== '') {
            $bmnTik->update(['penanggungjawab' => $pjBaru]);
            if ($bmnTik->bmn_id) {
                $bmn = Bmn::find($bmnTik->bmn_id);
                if ($bmn) {
                    $bmn->update([
                        'penanggungjawab' => $pjBaru,
                        'penanggung_jawab_id' => $request->input('penanggung_jawab_id'),
                        'penanggungjawab_nip' => $request->input('penanggungjawab_nip') ?: '-',
                    ]);
                }
            }
        }

        return back()->with('success', 'Penanggung jawab BMN TIK berhasil diperbarui.');
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
                $tikId = $item['id'] ?? null;
                if (! $tikId) continue;
                $bmnTik = BmnTik::find($tikId);
                if (! $bmnTik || $bmnTik->dihapus) continue;

                $updateData = [];
                if (! empty($item['lokasi'])) {
                    $updateData['lokasi'] = $item['lokasi'];
                }
                if (! empty($item['penanggungjawab'])) {
                    $updateData['penanggungjawab'] = $item['penanggungjawab'];
                }

                if (! empty($updateData)) {
                    $bmnTik->update($updateData);
                    if ($bmnTik->bmn_id) {
                        $bmn = Bmn::find($bmnTik->bmn_id);
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

        return back()->with('success', "Berhasil memperbarui {$count} data BMN TIK.");
    }

    public function destroy(BmnTik $bmnTik)
    {
        abort_if($bmnTik->dihapus, 404);

        $this->bmnTikService->delete($bmnTik, auth()->user()?->email);

        return back(fallback: route('bmns-tik.index'))
            ->with('success', 'Data BMN TIK dipindahkan ke Data Terhapus.');
    }
}
