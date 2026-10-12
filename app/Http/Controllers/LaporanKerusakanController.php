<?php

namespace App\Http\Controllers;

use App\Http\Requests\LaporanKerusakan\StoreLaporanKerusakanRequest;
use App\Http\Requests\LaporanKerusakan\UpdateLaporanKerusakanRequest;
use App\Models\LaporanKerusakan;
use App\Services\LaporanKerusakanService;

class LaporanKerusakanController extends Controller
{
    public function __construct(
        protected LaporanKerusakanService $laporanKerusakanService
    ) {}

    public function index()
    {
        $items = $this->laporanKerusakanService->getPaginated();

        return view('laporan-kerusakan.index', compact('items'));
    }

    public function create()
    {
        return view('laporan-kerusakan.create');
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
        return view('laporan-kerusakan.show', compact('laporanKerusakan'));
    }

    public function edit(LaporanKerusakan $laporanKerusakan)
    {
        return view('laporan-kerusakan.edit', compact('laporanKerusakan'));
    }

    public function update(UpdateLaporanKerusakanRequest $request, LaporanKerusakan $laporanKerusakan)
    {
        $this->laporanKerusakanService->update($laporanKerusakan, $request->validated(), auth()->user());

        return redirect()
            ->route('laporan-kerusakan.index')
            ->with('success', 'Laporan kerusakan berhasil diperbarui.');
    }

    public function destroy(LaporanKerusakan $laporanKerusakan)
    {
        $this->laporanKerusakanService->delete($laporanKerusakan, auth()->user()?->email);

        return redirect()
            ->route('laporan-kerusakan.index')
            ->with('success', 'Laporan dipindahkan ke Data Terhapus.');
    }
}