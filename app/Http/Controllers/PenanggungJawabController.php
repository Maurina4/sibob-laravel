<?php

namespace App\Http\Controllers;

use App\Models\PenanggungJawab;
use Illuminate\Http\Request;

class PenanggungJawabController extends Controller
{
    /**
     * Menampilkan daftar penanggung jawab.
     */
    public function index(Request $request)
    {
        $query = PenanggungJawab::query();

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'nama',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'nip',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'jabatan',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'unit_kerja',
                    'like',
                    "%{$search}%"
                );

            });
        }

        $penanggungJawabs = $query
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view(
            'penanggung-jawab.index',
            compact('penanggungJawabs')
        );
    }


    /**
     * Form tambah penanggung jawab.
     */
    public function create()
    {
        return view('penanggung-jawab.create');
    }


    /**
     * Simpan penanggung jawab.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
            ],

            'nip' => [
                'nullable',
                'string',
                'max:255',
            ],

            'jabatan' => [
                'nullable',
                'string',
                'max:255',
            ],

            'unit_kerja' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        PenanggungJawab::create($validated);

        return redirect()
            ->route('penanggung-jawabs.index')
            ->with(
                'success',
                'Penanggung jawab berhasil ditambahkan.'
            );
    }


    /**
     * Detail penanggung jawab.
     */
    public function show(PenanggungJawab $penanggungJawab)
    {
        return view(
            'penanggung-jawab.show',
            compact('penanggungJawab')
        );
    }


    /**
     * Form edit.
     */
    public function edit(PenanggungJawab $penanggungJawab)
    {
        return view(
            'penanggung-jawab.edit',
            compact('penanggungJawab')
        );
    }


    /**
     * Update penanggung jawab.
     */
    public function update(
        Request $request,
        PenanggungJawab $penanggungJawab
    ) {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
            ],

            'nip' => [
                'nullable',
                'string',
                'max:255',
            ],

            'jabatan' => [
                'nullable',
                'string',
                'max:255',
            ],

            'unit_kerja' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $penanggungJawab->update($validated);

        return redirect()
            ->route('penanggung-jawabs.index')
            ->with(
                'success',
                'Penanggung jawab berhasil diperbarui.'
            );
    }


    /**
     * Hapus penanggung jawab.
     */
    public function destroy(
        PenanggungJawab $penanggungJawab
    ) {
        $penanggungJawab->delete();

        return redirect()
            ->route('penanggung-jawabs.index')
            ->with(
                'success',
                'Penanggung jawab berhasil dihapus.'
            );
    }
}