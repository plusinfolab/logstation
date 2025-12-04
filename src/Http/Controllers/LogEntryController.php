<?php

namespace PlusinfoLab\Logstation\Http\Controllers;

use Illuminate\Http\Request;
use PlusinfoLab\Logstation\Facades\Logstation;

class LogEntryController
{
    /**
     * Display a listing of log entries.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'level', 'channel', 'start_date', 'end_date', 'tag', 'user_id']);
        $perPage = $request->input('per_page', 50);

        $logs = Logstation::search($filters, $perPage);

        return view('logstation::logs.index', compact('logs', 'filters'));
    }

    /**
     * Stream new log entries (for live updates).
     */
    public function stream(Request $request)
    {
        $filters = $request->only(['search', 'level', 'channel', 'start_date', 'end_date', 'tag', 'user_id']);
        $since = $request->input('since'); // Timestamp of last fetch

        // Add timestamp filter for new logs only
        if ($since) {
            $since = \Carbon\Carbon::parse($since)->toDateTimeString();
            $filters['since'] = $since;
        }

        // Get latest logs without pagination (limit to 100 for safety)
        $logs = Logstation::search($filters, -1);

        // If we got a collection, limit it to 100 items
        if (is_object($logs) && method_exists($logs, 'take')) {
            $logs = $logs->take(100);
        }

        return response()->json([
            'logs' => $logs,
            'timestamp' => now()->toIso8601String(),
            'count' => is_countable($logs) ? count($logs) : 0,
        ]);
    }

    /**
     * Display the specified log entry.
     */
    public function show(string $id)
    {
        $log = Logstation::find($id);

        if (! $log) {
            abort(404, 'Log entry not found');
        }

        return view('logstation::logs.show', compact('log'));
    }

    /**
     * Remove the specified log entry.
     */
    public function destroy(string $id)
    {
        $deleted = Logstation::delete($id);

        if (! $deleted) {
            return response()->json(['error' => 'Log entry not found'], 404);
        }

        return response()->json(['message' => 'Log entry deleted successfully']);
    }
}
