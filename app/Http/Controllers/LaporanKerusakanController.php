<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaporanKerusakan\StoreLaporanKerusakanRequest;
use App\Http\Requests\LaporanKerusakan\UpdateLaporanKerusakanRequest;
use App\Models\Bmn;
use App\Models\LaporanKerusakan;
use App\Models\Pegawai;
use App\Services\LaporanKerusakanService;
use Illuminate\Http\Request;

class LaporanKerusakanController extends Controller
{
    public function __construct(
        protected LaporanKerusakanService $laporanKerusakanService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        $items = $this->laporanKerusakanService->getPaginated($filters);

        return view('laporan-kerusakan.index', compact('items'));
    }

    public function create()
    {
        $bmns = Bmn::active()
            ->orderBy('kode_barang')
            ->orderBy('nup')
            ->get(['id', 'kode_barang', 'nup', 'nama_barang', 'lokasi', 'lokasi_id']);
        $pegawais = Pegawai::orderBy('nama')->get(['id', 'nama', 'nip', 'jabatan', 'unit_kerja']);

        return view('laporan-kerusakan.create', compact('bmns', 'pegawais'));
    }

    public function store(StoreLaporanKerusakanRequest $request)
    {
        $this->laporanKerusakanService->store($request->validated(), auth()->user());

        return redirect()
            ->route('laporan-kerusakan.index')
            ->with('success', 'Laporan kerusakan berhasil dibuat.');
    }

    public function show(LaporanKerusakan $laporanKerusakan)
    {
        $laporanKerusakan->load(['bmn', 'lokasiRef', 'pelaporPegawai', 'pelaporUser']);

        return view('laporan-kerusakan.show', compact('laporanKerusakan'));
    }

    public function edit(Request $request, LaporanKerusakan $laporanKerusakan)
    {
        if (auth()->user()->role !== 'Operator' && $laporanKerusakan->status !== 'Dilaporkan') {
            return redirect()
                ->route('laporan-kerusakan.show', $laporanKerusakan)
                ->with('error', 'Laporan yang sudah diterima atau diproses tidak dapat diedit lagi.');
        }

        $returnQuery = array_filter(
            $request->query(),
            fn ($value) => $value !== null && $value !== ''
        );

        session()->put('laporan_kerusakan_edit_return_query.'.$laporanKerusakan->id, $returnQuery);

        $bmns = Bmn::active()
            ->orderBy('kode_barang')
            ->orderBy('nup')
            ->get(['id', 'kode_barang', 'nup', 'nama_barang', 'kondisi', 'lokasi', 'lokasi_id']);
        $pegawais = Pegawai::orderBy('nama')->get(['id', 'nama', 'nip', 'jabatan', 'unit_kerja']);
        $laporanKerusakan->load(['bmn', 'pelaporPegawai', 'pelaporUser']);

        return view('laporan-kerusakan.edit', compact('laporanKerusakan', 'bmns', 'pegawais'));
    }

    public function update(UpdateLaporanKerusakanRequest $request, LaporanKerusakan $laporanKerusakan)
    {
        if (auth()->user()->role !== 'Operator' && $laporanKerusakan->status !== 'Dilaporkan') {
            abort(403, 'Laporan yang sudah diproses tidak dapat diubah.');
        }

        $validated = $request->validated();
        if (auth()->user()->role !== 'Operator') {
            unset($validated['status'], $validated['tanggapan']);
        }

        $this->laporanKerusakanService->update($laporanKerusakan, $validated, auth()->user());

        $returnQuery = session()->pull('laporan_kerusakan_edit_return_query.'.$laporanKerusakan->id, []);

        $successMessage = 'Laporan kerusakan berhasil diperbarui.';
        if (isset($validated['status'])) {
            $freshLaporan = $laporanKerusakan->fresh();
            if (in_array($validated['status'], ['Diterima', 'Diproses'])) {
                $kondisiTerkait = match ($freshLaporan->tingkat) {
                    'Rusak Berat', 'Berat' => 'Rusak Berat',
                    'Sedang', 'Rusak Sedang' => 'Rusak Sedang',
                    default => 'Rusak Berat',
                };
                $successMessage = "Laporan berhasil {$validated['status']}. Kondisi barang BMN otomatis diubah menjadi '{$kondisiTerkait}'.";
            } elseif ($validated['status'] === 'Selesai') {
                $successMessage = "Laporan berhasil diselesaikan. Kondisi barang BMN otomatis kembali 'Baik'.";
            } elseif ($validated['status'] === 'Ditolak') {
                $successMessage = "Laporan ditolak. Kondisi barang BMN tetap/kembali 'Baik'.";
            }
        }

        if ($request->input('redirect_to') === 'show') {
            return redirect()
                ->route('laporan-kerusakan.show', $laporanKerusakan)
                ->with('success', $successMessage);
        }

        return redirect()
            ->route('laporan-kerusakan.index', $returnQuery)
            ->with('success', $successMessage);
    }

    public function destroy(LaporanKerusakan $laporanKerusakan)
    {
        if (auth()->user()->role !== 'Operator' && $laporanKerusakan->status !== 'Dilaporkan') {
            abort(403, 'Laporan yang sudah diproses tidak dapat dibatalkan.');
        }

        $this->laporanKerusakanService->delete($laporanKerusakan, auth()->user()?->email);

        $message = auth()->user()->role === 'Operator'
            ? 'Laporan dipindahkan ke Data Terhapus.'
            : 'Laporan kerusakan berhasil dibatalkan.';

        return back(fallback: route('laporan-kerusakan.index'))
            ->with('success', $message);
    }
}
