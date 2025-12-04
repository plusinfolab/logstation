<?php

namespace PlusinfoLab\Logstation\Storage;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PlusinfoLab\Logstation\Models\LogEntry;
use PlusinfoLab\Logstation\Models\LogTag;

class DatabaseStorage extends StorageDriver
{
    /**
     * Store a log entry.
     */
    public function store(array $entry): void
    {
        $connection = config('logstation.database.connection') ?: config('database.default');

        DB::connection($connection)->transaction(function () use ($entry) {
            // Extract tags if present
            $tags = $entry['tags'] ?? [];
            unset($entry['tags']);

            // Ensure UUID
            if (! isset($entry['id'])) {
                $entry['id'] = (string) Str::uuid();
            }

            // Create log entry
            $logEntry = LogEntry::create($entry);

            // Create tags
            if (! empty($tags)) {
                foreach ($tags as $tag) {
                    LogTag::create([
                        'entry_id' => $logEntry->id,
                        'tag' => $tag,
                    ]);
                }
            }
        });
    }

    /**
     * Store multiple log entries.
     */
    public function storeBatch(array $entries): void
    {
        $connection = config('logstation.database.connection') ?: config('database.default');
        $chunk = config('logstation.database.chunk', 1000);

        collect($entries)->chunk($chunk)->each(function ($chunk) use ($connection) {
            DB::connection($connection)->transaction(function () use ($chunk) {
                foreach ($chunk as $entry) {
                    $this->store($entry);
                }
            });
        });
    }

    /**
     * Find a log entry by ID.
     */
    public function find(string $id): ?LogEntry
    {
        return LogEntry::with('tags')->find($id);
    }

    /**
     * Search log entries with filters.
     */
    public function search(array $filters = [], int $perPage = 50)
    {
        $query = LogEntry::query()->with('tags');

        // Filter by search term
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Filter by level
        if (! empty($filters['level'])) {
            if (is_array($filters['level'])) {
                $query->whereIn('level_name', $filters['level']);
            } else {
                $query->byLevel($filters['level']);
            }
        }

        // Filter by channel
        if (! empty($filters['channel'])) {
            $query->byChannel($filters['channel']);
        }

        // Filter by date range
        if (! empty($filters['start_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date'] ?? null);
        }

        // Filter by tag
        if (! empty($filters['tag'])) {
            $query->withTag($filters['tag']);
        }

        // Filter by user
        if (! empty($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        // Filter by batch
        if (! empty($filters['batch_id'])) {
            $query->where('batch_id', $filters['batch_id']);
        }

        // Filter by request ID
        if (! empty($filters['request_id'])) {
            $query->where('request_id', $filters['request_id']);
        }

        // Filter by timestamp (for live streaming)
        if (! empty($filters['since'])) {
            $query->bySince($filters['since']);
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDirection = $filters['order_direction'] ?? 'desc';
        $query->orderBy($orderBy, $orderDirection);

        // Paginate or get all
        if ($perPage > 0) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    /**
     * Delete a log entry.
     */
    public function delete(string $id): bool
    {
        $entry = LogEntry::find($id);

        if (! $entry) {
            return false;
        }

        return $entry->delete();
    }

    /**
     * Prune old log entries.
     */
    public function prune(\DateTimeInterface $before): int
    {
        return LogEntry::where('created_at', '<', $before)->delete();
    }

    /**
     * Clear all log entries.
     */
    public function clear(): int
    {
        return LogEntry::query()->delete();
    }

    /**
     * Get statistics about log entries.
     */
    public function getStatistics(): array
    {
        $connection = config('logstation.database.connection') ?: config('database.default');

        return [
            'total' => LogEntry::count(),
            'by_level' => LogEntry::select('level_name', DB::raw('count(*) as count'))
                ->groupBy('level_name')
                ->pluck('count', 'level_name')
                ->toArray(),
            'by_channel' => LogEntry::select('channel', DB::raw('count(*) as count'))
                ->groupBy('channel')
                ->pluck('count', 'channel')
                ->toArray(),
            'today' => LogEntry::whereDate('created_at', today())->count(),
            'this_week' => LogEntry::where('created_at', '>=', now()->startOfWeek())->count(),
            'this_month' => LogEntry::where('created_at', '>=', now()->startOfMonth())->count(),
        ];
    }
}
