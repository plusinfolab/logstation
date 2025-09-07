<?php

namespace PlusinfoLab\Logstation\Watchers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use PlusinfoLab\Logstation\Facades\Logstation;

class QueryWatcher
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Register the watcher.
     */
    public function register(): void
    {
        Event::listen(QueryExecuted::class, [$this, 'recordQuery']);
    }

    /**
     * Record a database query.
     */
    public function recordQuery(QueryExecuted $event): void
    {
        $slowThreshold = $this->config['slow'] ?? 100;

        // Only log slow queries
        if ($event->time < $slowThreshold) {
            return;
        }

        $entry = [
            'id' => (string) Str::uuid(),
            'batch_id' => (string) Str::uuid(),
            'type' => 'warning',
            'channel' => 'database',
            'level' => 300,
            'level_name' => 'warning',
            'message' => 'Slow query detected',
            'context' => [
                'sql' => $event->sql,
                'bindings' => $event->bindings,
                'time' => $event->time,
                'connection' => $event->connectionName,
            ],
            'extra' => [],
            'created_at' => now(),
        ];

        // Add request details if available
        if (app()->bound('request') && request()) {
            $request = request();
            $entry['request_method'] = $request->method();
            $entry['request_url'] = $request->fullUrl();
            $entry['request_ip'] = $request->ip();
            $entry['request_id'] = $request->header('X-Request-ID') ?? (string) Str::uuid();
        }

        // Add tags
        $entry['tags'] = [
            'slow-query',
            'database',
        ];

        Logstation::recordLog($entry);
    }
}
