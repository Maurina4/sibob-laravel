<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lokasi\StoreLokasiRequest;
use App\Http\Requests\Lokasi\UpdateLokasiRequest;
use App\Models\Lokasi;
use App\Services\LokasiService;
use Illuminate\Http\Request;

class LokasiController extends Controller
{
    public function __construct(
        protected LokasiService $lokasiService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        $lokasis = $this->lokasiService->getPaginated($filters);

        return view('lokasi.index', compact('lokasis'));
    }

    public function create()
    {
        return view('lokasi.create');
    }

    public function store(StoreLokasiRequest $request)
    {
        $this->lokasiService->store($request->validated());

        return redirect()
            ->route('lokasis.index')
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function show(Lokasi $lokasi)
    {
        return redirect()->route('lokasis.index');
    }

    public function edit(Lokasi $lokasi)
    {
        return redirect()->route('lokasis.index');
    }

    public function update(UpdateLokasiRequest $request, Lokasi $lokasi)
    {
        $updated = $this->lokasiService->update($lokasi, $request->validated('nama'));

        if (! $updated && $lokasi->nama === $request->validated('nama')) {
            return redirect()
                ->route('lokasis.index')
                ->with('success', 'Tidak ada perubahan pada nama lokasi.');
        }

        return redirect()
            ->route('lokasis.index')
            ->with('success', 'Lokasi berhasil diperbarui. Semua BMN yang menggunakan lokasi tersebut juga telah diperbarui.');
    }

    public function destroy(Lokasi $lokasi)
    {
        $result = $this->lokasiService->delete($lokasi);

        if (! $result['success']) {
            return redirect()
                ->route('lokasis.index')
                ->with('error', $result['message']);
        }

        return redirect()
            ->route('lokasis.index')
            ->with('success', $result['message']);
    }
}