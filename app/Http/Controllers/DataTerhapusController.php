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
            return back(fallback: route('data-terhapus.index'))
                ->with('error', $result['message']);
        }

        return back(fallback: route('data-terhapus.index'))
            ->with('success', $result['message']);
    }

    public function forceDelete(DataTerhapus $dataTerhapus)
    {
        $result = $this->dataTerhapusService->forceDelete($dataTerhapus);

        if (! $result['success']) {
            return back(fallback: route('data-terhapus.index'))
                ->with('error', $result['message']);
        }

        return back(fallback: route('data-terhapus.index'))
            ->with('success', $result['message']);
    }

    public function forceDeleteAll(Request $request)
    {
        $ids = $request->input('ids');
        if (is_string($ids)) {
            $ids = array_filter(array_map('trim', explode(',', $ids)));
        }

        $result = $this->dataTerhapusService->forceDeleteAll($ids);

        if (! $result['success']) {
            return back(fallback: route('data-terhapus.index'))
                ->with('error', $result['message']);
        }

        return back(fallback: route('data-terhapus.index'))
            ->with('success', $result['message']);
    }
}
