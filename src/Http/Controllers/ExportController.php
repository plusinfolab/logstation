<?php

namespace PlusinfoLab\Logstation\Http\Controllers;

use Illuminate\Http\Request;
use PlusinfoLab\Logstation\Services\LogExportService;

class ExportController
{
    protected LogExportService $exportService;

    public function __construct(LogExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Export logs.
     */
    public function export(Request $request)
    {
        $filters = $request->only(['search', 'level', 'channel', 'start_date', 'end_date', 'tag', 'user_id']);
        $format = $request->input('format', 'json');

        $allowedFormats = config('logstation.export.formats', ['json', 'csv', 'txt']);

        if (! in_array($format, $allowedFormats)) {
            return response()->json(['error' => 'Invalid export format'], 400);
        }

        return $this->exportService->export($filters, $format);
    }
}
