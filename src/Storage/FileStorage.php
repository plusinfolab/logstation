<?php

namespace PlusinfoLab\Logstation\Storage;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PlusinfoLab\Logstation\Models\LogEntry;

class FileStorage extends StorageDriver
{
    protected string $storagePath;
    protected int $maxFiles;

    public function __construct()
    {
        $path = config('logstation.storage.path', 'logstation');

        // Convert relative path to absolute storage path
        $this->storagePath = str_starts_with($path, '/')
            ? $path
            : storage_path($path);

        $this->maxFiles = config('logstation.storage.max_files', 30);

        // Ensure storage directory exists
        if (!File::exists($this->storagePath)) {
            File::makeDirectory($this->storagePath, 0755, true);
        }
    }

    /**
     * Store a log entry.
     */
    public function store(array $entry): void
    {
        $filename = $this->getCurrentFilename();
        $filepath = $this->storagePath . '/' . $filename;

        // Ensure UUID
        if (!isset($entry['id'])) {
            $entry['id'] = (string) Str::uuid();
        }

        // Append to file
        File::append($filepath, json_encode($entry) . PHP_EOL);

        // Rotate files if needed
        $this->rotateFiles();
    }

    /**
     * Store multiple log entries.
     */
    public function storeBatch(array $entries): void
    {
        foreach ($entries as $entry) {
            $this->store($entry);
        }
    }

    /**
     * Find a log entry by ID.
     */
    public function find(string $id): ?LogEntry
    {
        $files = $this->getLogFiles();

        foreach ($files as $file) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $entry = json_decode($line, true);

                if ($entry && $entry['id'] === $id) {
                    return new LogEntry($entry);
                }
            }
        }

        return null;
    }

    /**
     * Search log entries with filters.
     */
    public function search(array $filters = [], int $perPage = 50)
    {
        $entries = collect();
        $files = $this->getLogFiles();

        foreach ($files as $file) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $entry = json_decode($line, true);

                if ($entry && $this->matchesFilters($entry, $filters)) {
                    $entries->push(new LogEntry($entry));
                }
            }
        }

        // Sort by created_at descending
        $entries = $entries->sortByDesc('created_at')->values();

        // Apply pagination
        if ($perPage > 0) {
            $page = request()->input('page', 1);
            $offset = ($page - 1) * $perPage;

            return new \Illuminate\Pagination\LengthAwarePaginator(
                $entries->slice($offset, $perPage)->values(),
                $entries->count(),
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        return $entries;
    }

    /**
     * Delete a log entry.
     */
    public function delete(string $id): bool
    {
        // File storage doesn't support deletion
        // This would require rewriting files
        return false;
    }

    /**
     * Prune old log entries.
     */
    public function prune(\DateTimeInterface $before): int
    {
        $deleted = 0;
        $files = $this->getLogFiles();

        foreach ($files as $file) {
            $filename = basename($file);
            $date = $this->getDateFromFilename($filename);

            if ($date && $date < $before) {
                File::delete($file);
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Clear all log entries.
     */
    public function clear(): int
    {
        $files = $this->getLogFiles();
        $count = count($files);

        foreach ($files as $file) {
            File::delete($file);
        }

        return $count;
    }

    /**
     * Get statistics about log entries.
     */
    public function getStatistics(): array
    {
        $entries = $this->search([], 0);

        return [
            'total' => $entries->count(),
            'by_level' => $entries->groupBy('level_name')->map->count()->toArray(),
            'by_channel' => $entries->groupBy('channel')->map->count()->toArray(),
            'today' => $entries->filter(fn($e) => $e->created_at->isToday())->count(),
            'this_week' => $entries->filter(fn($e) => $e->created_at->isCurrentWeek())->count(),
            'this_month' => $entries->filter(fn($e) => $e->created_at->isCurrentMonth())->count(),
        ];
    }

    /**
     * Get current log filename.
     */
    protected function getCurrentFilename(): string
    {
        return 'logstation-' . date('Y-m-d') . '.log';
    }

    /**
     * Get all log files.
     */
    protected function getLogFiles(): array
    {
        $files = File::glob($this->storagePath . '/logstation-*.log');

        // Sort by modification time, newest first
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        return $files;
    }

    /**
     * Rotate log files.
     */
    protected function rotateFiles(): void
    {
        $files = $this->getLogFiles();

        if (count($files) > $this->maxFiles) {
            $filesToDelete = array_slice($files, $this->maxFiles);

            foreach ($filesToDelete as $file) {
                File::delete($file);
            }
        }
    }

    /**
     * Check if entry matches filters.
     */
    protected function matchesFilters(array $entry, array $filters): bool
    {
        if (!empty($filters['level']) && $entry['level_name'] !== $filters['level']) {
            return false;
        }

        if (!empty($filters['channel']) && $entry['channel'] !== $filters['channel']) {
            return false;
        }

        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $message = strtolower($entry['message'] ?? '');

            if (strpos($message, $search) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get date from filename.
     */
    protected function getDateFromFilename(string $filename): ?\DateTimeInterface
    {
        if (preg_match('/logstation-(\d{4}-\d{2}-\d{2})\.log/', $filename, $matches)) {
            return new \DateTime($matches[1]);
        }

        return null;
    }
}
