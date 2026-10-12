<?php

namespace App\Http\Controllers;

use App\Models\DataTerhapus;
use App\Services\DataTerhapusService;
use Illuminate\Http\Request;

class DataTerhapusController extends Controller
{
    public function __construct(
        protected DataTerhapusService $dataTerhapusService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'jenis']);
        $dataTerhapus = $this->dataTerhapusService->getPaginated($filters);
        $jenis = $this->dataTerhapusService->getJenisList();

        return view('data-terhapus.index', compact('dataTerhapus', 'jenis'));
    }

    public function restore(DataTerhapus $dataTerhapus)
    {
        $result = $this->dataTerhapusService->restore($dataTerhapus);

        if (! $result['success']) {
            return redirect()
                ->route('data-terhapus.index')
                ->with('error', $result['message']);
        }

        return redirect()
            ->route('data-terhapus.index')
            ->with('success', $result['message']);
    }
}