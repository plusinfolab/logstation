<?php

namespace PlusinfoLab\Logstation\Storage;

use Illuminate\Support\Collection;
use PlusinfoLab\Logstation\Models\LogEntry;

abstract class StorageDriver
{
    /**
     * Store a log entry.
     */
    abstract public function store(array $entry): void;

    /**
     * Store multiple log entries.
     */
    abstract public function storeBatch(array $entries): void;

    /**
     * Find a log entry by ID.
     */
    abstract public function find(string $id): ?LogEntry;

    /**
     * Search log entries with filters.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    abstract public function search(array $filters = [], int $perPage = 50);

    /**
     * Delete a log entry.
     */
    abstract public function delete(string $id): bool;

    /**
     * Prune old log entries.
     */
    abstract public function prune(\DateTimeInterface $before): int;

    /**
     * Clear all log entries.
     */
    abstract public function clear(): int;

    /**
     * Get statistics about log entries.
     */
    abstract public function getStatistics(): array;
}
