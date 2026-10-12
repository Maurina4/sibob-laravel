<?php

namespace App\Http\Controllers;

use App\Http\Requests\Distribusi\StoreDistribusiRequest;
use App\Http\Requests\Distribusi\UpdateDistribusiRequest;
use App\Models\Distribusi;
use App\Services\DistribusiService;

class DistribusiController extends Controller
{
    public function __construct(
        protected DistribusiService $distribusiService
    ) {}

    public function index()
    {
        $items = $this->distribusiService->getPaginated();

        return view('distribusi.index', compact('items'));
    }

    public function create()
    {
        return view('distribusi.create');
    }

    public function store(StoreDistribusiRequest $request)
    {
        $this->distribusiService->store($request->validated());

        return redirect()
            ->route('distribusi.index')
            ->with('success', 'Data distribusi berhasil ditambahkan.');
    }

    public function show(Distribusi $distribusi)
    {
        return view('distribusi.show', compact('distribusi'));
    }

    public function edit(Distribusi $distribusi)
    {
        return view('distribusi.edit', compact('distribusi'));
    }

    public function update(UpdateDistribusiRequest $request, Distribusi $distribusi)
    {
        $this->distribusiService->update($distribusi, $request->validated());

        return redirect()
            ->route('distribusi.index')
            ->with('success', 'Data distribusi berhasil diperbarui.');
    }

    public function destroy(Distribusi $distribusi)
    {
        $this->distribusiService->delete($distribusi, auth()->user()?->email);

        return redirect()
            ->route('distribusi.index')
            ->with('success', 'Data distribusi dipindahkan ke Data Terhapus.');
    }
}