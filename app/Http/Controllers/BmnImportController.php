<?php

namespace App\Http\Controllers;

use App\Models\ImportLog;
use App\Services\Import\BmnImportService;
use App\Services\Import\BmnTemplateService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BmnImportController extends Controller
{
    public function __construct(
        protected BmnImportService $importService,
        protected BmnTemplateService $templateService
    ) {}

    public function index()
    {
        $recentLogs = ImportLog::query()
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('bmn.import.index', compact('recentLogs'));
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return $this->templateService->generateTemplate();
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120'], // 5 MB
        ], [
            'file.required' => 'Silakan pilih berkas Excel yang ingin diunggah.',
            'file.max' => 'Ukuran berkas tidak boleh melebihi 5 MB.',
        ]);

        $result = $this->importService->processPreview($request->file('file'), (int) auth()->id());

        if (! $result['success']) {
            return back()
                ->with('error', $result['message'])
                ->with('header_errors', $result['header_errors'] ?? []);
        }

        return view('bmn.import.preview', [
            'token' => $result['token'],
            'logId' => $result['log_id'],
            'summary' => $result['summary'],
            'rows' => $result['rows'],
        ]);
    }

    public function confirm(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $result = $this->importService->confirmImport($validated['token'], (int) auth()->id());

        if (! $result['success']) {
            return redirect()
                ->route('bmns.import.index')
                ->with('error', $result['message']);
        }

        return redirect()
            ->route('bmns.index')
            ->with('success', $result['message']);
    }

    public function downloadErrorReport(ImportLog $importLog): BinaryFileResponse
    {
        return $this->importService->generateErrorReport($importLog);
    }
}
