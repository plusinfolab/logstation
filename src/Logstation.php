<?php

namespace PlusinfoLab\Logstation;

use PlusinfoLab\Logstation\Storage\StorageDriver;

class Logstation
{
    protected StorageDriver $storage;

    public function __construct(StorageDriver $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Record a log entry.
     */
    public function recordLog(array $entry): void
    {
        if (!config('logstation.enabled', true)) {
            return;
        }

        $this->storage->store($entry);
    }

    /**
     * Record multiple log entries.
     */
    public function recordBatch(array $entries): void
    {
        if (!config('logstation.enabled', true)) {
            return;
        }

        $this->storage->storeBatch($entries);
    }

    /**
     * Find a log entry by ID.
     */
    public function find(string $id)
    {
        return $this->storage->find($id);
    }

    /**
     * Search log entries.
     */
    public function search(array $filters = [], int $perPage = 50)
    {
        return $this->storage->search($filters, $perPage);
    }

    /**
     * Delete a log entry.
     */
    public function delete(string $id): bool
    {
        return $this->storage->delete($id);
    }

    /**
     * Prune old log entries.
     */
    public function prune(\DateTimeInterface $before): int
    {
        return $this->storage->prune($before);
    }

    /**
     * Clear all log entries.
     */
    public function clear(): int
    {
        return $this->storage->clear();
    }

    /**
     * Get statistics.
     */
    public function getStatistics(): array
    {
        return $this->storage->getStatistics();
    }

    /**
     * Check if LogStation is enabled.
     */
    public function isEnabled(): bool
    {
        $enabled = config('logstation.enabled');
        if ($enabled === null) {
            return !app()->environment('production');
        }
        return (bool) $enabled;
    }
}
