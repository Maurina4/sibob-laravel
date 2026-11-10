<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PengajuanPengembalian\TolakPengajuanRequest;
use App\Models\PengajuanPengembalian;
use App\Services\PengajuanPengembalianService;
use Exception;
use Illuminate\Http\Request;

class PengajuanPengembalianController extends Controller
{
    public function __construct(
        protected PengajuanPengembalianService $service
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'alasan', 'bidang']);
        $pengajuans = $this->service->getPaginatedForAdmin($filters);

        return view('admin.pengajuan-pengembalian.index', compact('pengajuans'));
    }

    public function show(PengajuanPengembalian $pengajuanPengembalian)
    {
        $pengajuanPengembalian->load(['items.bmn', 'items.laporanKerusakan', 'pegawai', 'pemohonUser', 'penyetujuUser']);

        return view('admin.pengajuan-pengembalian.show', [
            'pengajuan' => $pengajuanPengembalian,
        ]);
    }

    public function setujui(Request $request, PengajuanPengembalian $pengajuanPengembalian)
    {
        try {
            $this->service->setujui($pengajuanPengembalian, auth()->user());

            if ($request->filled('redirect_to')) {
                return redirect($request->input('redirect_to'))
                    ->with('success', 'Pengajuan pengembalian berhasil disetujui. Berita Acara resmi telah diterbitkan dan langsung masuk ke BA Pengembalian.');
            }

            $nomorBa = $pengajuanPengembalian->fresh()->nomor_ba;

            return redirect()
                ->route('pengembalians.index')
                ->with('success', "Pengajuan pengembalian berhasil disetujui. Berita Acara resmi ({$nomorBa}) telah diterbitkan dan langsung masuk ke BA Pengembalian.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function tolak(TolakPengajuanRequest $request, PengajuanPengembalian $pengajuanPengembalian)
    {
        try {
            $this->service->tolak($pengajuanPengembalian, $request->input('catatan_penolakan'), auth()->user());

            return redirect()
                ->route('admin.pengajuan-pengembalian.show', $pengajuanPengembalian)
                ->with('success', 'Pengajuan pengembalian telah ditolak dan catatan telah dikirimkan ke pemohon.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function batalkan(Request $request, PengajuanPengembalian $pengajuanPengembalian)
    {
        try {
            $msg = $this->service->batalkan($pengajuanPengembalian, auth()->user());

            return back()->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cetakBa(PengajuanPengembalian $pengajuanPengembalian)
    {
        abort_if($pengajuanPengembalian->status !== 'disetujui', 403, 'Berita Acara hanya dapat dicetak setelah pengajuan disetujui oleh pengelola BMN.');

        $pengajuanPengembalian->load(['items.bmn', 'items.laporanKerusakan', 'pegawai', 'pemohonUser', 'penyetujuUser']);

        return view('admin.pengajuan-pengembalian.print-ba', [
            'pengajuan' => $pengajuanPengembalian,
        ]);
    }
}
