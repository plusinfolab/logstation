<?php

namespace PlusinfoLab\Logstation\Http\Controllers;

use Illuminate\Http\Request;
use PlusinfoLab\Logstation\Facades\Logstation;
use PlusinfoLab\Logstation\Models\LogEntry;

class SearchController
{
    /**
     * Search logs.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'level', 'channel', 'start_date', 'end_date', 'tag', 'user_id']);
        $perPage = $request->input('per_page', 50);

        $logs = Logstation::search($filters, $perPage);

        return response()->json($logs);
    }

    /**
     * Autocomplete for search.
     */
    public function autocomplete(Request $request)
    {
        $type = $request->input('type'); // 'channel', 'tag'
        $query = $request->input('query');

        $results = match ($type) {
            'channel' => LogEntry::select('channel')
                ->distinct()
                ->where('channel', 'like', "%{$query}%")
                ->limit(10)
                ->pluck('channel'),
            'tag' => LogEntry::with('tags')
                ->get()
                ->pluck('tags')
                ->flatten()
                ->pluck('tag')
                ->unique()
                ->filter(fn($tag) => str_contains($tag, $query))
                ->take(10)
                ->values(),
            default => [],
        };

        return response()->json($results);
    }
}
