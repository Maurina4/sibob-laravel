<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaPerpindahan\StoreBaPerpindahanRequest;
use App\Http\Requests\BaPerpindahan\UpdateBaPerpindahanRequest;
use App\Models\BaPerpindahan;
use App\Services\BaPerpindahanService;
use Illuminate\Http\Request;

class BaPerpindahanController extends Controller
{
    public function __construct(
        protected BaPerpindahanService $baPerpindahanService
    ) {}

    public function index()
    {
        $items = $this->baPerpindahanService->getPaginated();

        return view('ba-perpindahan.index', compact('items'));
    }

    public function create()
    {
        return view('ba-perpindahan.create');
    }

    public function store(StoreBaPerpindahanRequest $request)
    {
        $this->baPerpindahanService->store($request->validated(), auth()->user());

        return redirect()
            ->route('ba-perpindahan.index')
            ->with('success', 'Pengajuan BA perpindahan berhasil dibuat.');
    }

    public function show(BaPerpindahan $baPerpindahan)
    {
        return view('ba-perpindahan.show', compact('baPerpindahan'));
    }

    public function edit(BaPerpindahan $baPerpindahan)
    {
        return view('ba-perpindahan.edit', compact('baPerpindahan'));
    }

    public function update(UpdateBaPerpindahanRequest $request, BaPerpindahan $baPerpindahan)
    {
        $this->baPerpindahanService->update($baPerpindahan, $request->validated());

        return redirect()
            ->route('ba-perpindahan.index')
            ->with('success', 'BA perpindahan berhasil diperbarui.');
    }

    public function destroy(BaPerpindahan $baPerpindahan)
    {
        $this->baPerpindahanService->delete($baPerpindahan, auth()->user()?->email);

        return redirect()
            ->route('ba-perpindahan.index')
            ->with('success', 'BA perpindahan berhasil dihapus.');
    }

    public function approve(Request $request, BaPerpindahan $baPerpindahan)
    {
        $this->baPerpindahanService->approve($baPerpindahan, auth()->user(), $request->input('catatan'));

        return redirect()
            ->route('ba-perpindahan.index')
            ->with('success', 'BA perpindahan berhasil disetujui.');
    }

    public function reject(Request $request, BaPerpindahan $baPerpindahan)
    {
        $this->baPerpindahanService->reject($baPerpindahan, auth()->user(), $request->input('catatan'));

        return redirect()
            ->route('ba-perpindahan.index')
            ->with('success', 'BA perpindahan ditolak.');
    }

    public function print(BaPerpindahan $baPerpindahan)
    {
        return view('ba-perpindahan.print', compact('baPerpindahan'));
    }
}