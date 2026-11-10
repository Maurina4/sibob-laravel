<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengajuanPengembalian\StorePengajuanPengembalianRequest;
use App\Models\Pegawai;
use App\Models\PengajuanPengembalian;
use App\Services\PengajuanPengembalianService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PengajuanPengembalianController extends Controller
{
    public const BIDANGS = [
        'Informasi dan Komunikasi',
        'Pengujian',
        'Pemeriksaan',
        'Tata Usaha',
    ];

    public function __construct(
        protected PengajuanPengembalianService $service
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'alasan', 'bidang']);
        $user = auth()->user();

        if ($user && $user->role === 'Operator') {
            $pengajuans = $this->service->getPaginatedForAdmin($filters);
        } else {
            $pengajuans = $this->service->getPaginatedForUser($user ? $user->id : 0, $filters);
        }

        return view('pengajuan-pengembalian.index', compact('pengajuans'));
    }

    public function create()
    {
        $user = auth()->user();
        $pegawai = Pegawai::where('nama', $user->name)->first();
        $bidangs = self::BIDANGS;
        $initialBmns = $this->service->getAvailableBmns($user, old('alasan', 'pengembalian_biasa'));

        return view('pengajuan-pengembalian.create', compact('user', 'pegawai', 'bidangs', 'initialBmns'));
    }

    public function store(StorePengajuanPengembalianRequest $request)
    {
        $pengajuan = $this->service->ajukan($request->validated(), auth()->user());

        return redirect()
            ->route('pengajuan-pengembalian.show', $pengajuan)
            ->with('success', 'Pengajuan pengembalian berhasil diajukan dan sedang menunggu peninjauan pengelola BMN.');
    }

    public function show(PengajuanPengembalian $pengajuanPengembalian)
    {
        $user = auth()->user();
        if ($user->role !== 'Operator' && $pengajuanPengembalian->diajukan_oleh !== $user->id) {
            abort(403, 'Anda tidak memiliki hak untuk mengakses pengajuan ini.');
        }

        if ($user->role === 'Operator') {
            return redirect()->route('admin.pengajuan-pengembalian.show', $pengajuanPengembalian);
        }

        $pengajuanPengembalian->load(['items.bmn', 'items.laporanKerusakan', 'pegawai', 'pemohonUser', 'penyetujuUser']);

        return view('pengajuan-pengembalian.show', [
            'pengajuan' => $pengajuanPengembalian,
        ]);
    }

    public function batalkan(PengajuanPengembalian $pengajuanPengembalian)
    {
        try {
            $msg = $this->service->batalkan($pengajuanPengembalian, auth()->user());

            return back()->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function apiSearchBmn(Request $request): JsonResponse
    {
        $alasan = $request->input('alasan', 'pengembalian_biasa');
        $search = $request->input('q', '');

        $bmns = $this->service->getAvailableBmns(auth()->user(), $alasan, $search);

        return response()->json($bmns);
    }

    public function cetakBa(PengajuanPengembalian $pengajuanPengembalian)
    {
        $user = auth()->user();
        if ($user->role !== 'Operator' && $pengajuanPengembalian->diajukan_oleh !== $user->id) {
            abort(403, 'Anda tidak memiliki hak untuk mengakses dokumen ini.');
        }

        abort_if($pengajuanPengembalian->status !== 'disetujui', 403, 'Berita Acara hanya dapat dicetak setelah pengajuan disetujui.');

        $pengajuanPengembalian->load(['items.bmn', 'items.laporanKerusakan', 'pegawai', 'pemohonUser', 'penyetujuUser']);

        return view('admin.pengajuan-pengembalian.print-ba', [
            'pengajuan' => $pengajuanPengembalian,
        ]);
    }
}
