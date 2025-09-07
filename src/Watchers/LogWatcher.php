<?php

namespace PlusinfoLab\Logstation\Watchers;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use PlusinfoLab\Logstation\Facades\Logstation;

class LogWatcher
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
        Event::listen(MessageLogged::class, [$this, 'recordLog']);
    }

    /**
     * Record a log message.
     */
    public function recordLog(MessageLogged $event): void
    {
        if (!$this->shouldRecord($event)) {
            return;
        }

        $entry = [
            'id' => (string) Str::uuid(),
            'batch_id' => (string) Str::uuid(),
            'type' => $event->level,
            'channel' => $event->context['channel'] ?? app('log')->getDefaultDriver(),
            'level' => $this->getLevelValue($event->level),
            'level_name' => $event->level,
            'message' => $event->message,
            'context' => $this->filterContext($event->context),
            'extra' => [],
            'created_at' => now(),
        ];

        // Add exception details if present
        if (isset($event->context['exception']) && $event->context['exception'] instanceof \Throwable) {
            $exception = $event->context['exception'];
            $entry['exception_class'] = get_class($exception);
            $entry['exception_message'] = $exception->getMessage();
            $entry['exception_trace'] = $exception->getTraceAsString();
            $entry['exception_file'] = $exception->getFile();
            $entry['exception_line'] = $exception->getLine();
        }

        // Add request details if available
        if (app()->bound('request') && request()) {
            $request = request();
            $entry['request_method'] = $request->method();
            $entry['request_url'] = $request->fullUrl();
            $entry['request_ip'] = $request->ip();
            $entry['request_user_agent'] = $request->userAgent();
            $entry['request_id'] = $request->header('X-Request-ID') ?? (string) Str::uuid();
        }

        // Add user details if authenticated
        if (auth()->check()) {
            $user = auth()->user();
            $entry['user_id'] = $user?->id ?? $user?->getAuthIdentifier();
            $entry['user_email'] = $user?->email ?? null;
        }else{
            $entry['user_id'] = null;
            $entry['user_email'] = null;
        }

        // Add session ID if available
        if (session()->isStarted()) {
            $entry['session_id'] = session()->getId();
        }

        // Add tags
        $entry['tags'] = $this->generateTags($entry);

        Logstation::recordLog($entry);
    }

    protected function shouldRecord(MessageLogged $event): bool
    {
        // Check if level is enabled
        $levels = $this->config['levels'] ?? [];
        if (!empty($levels) && !in_array($event->level, $levels)) {
            return false;
        }

        // Check if channel is enabled (only if channel is explicitly provided in context)
        $channels = $this->config['channels'] ?? [];
        if (!empty($channels) && isset($event->context['channel'])) {
            $channel = $event->context['channel'];
            if (!in_array($channel, $channels)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Filter context to remove sensitive data.
     */
    protected function filterContext(array $context): array
    {
        // Remove exception from context as it's stored separately
        unset($context['exception']);

        // Remove channel as it's stored separately
        unset($context['channel']);

        return $context;
    }

    /**
     * Generate tags for the log entry.
     */
    protected function generateTags(array $entry): array
    {
        $tags = [];
        $tagConfig = config('logstation.tags', []);

        if ($tagConfig['user_id'] ?? false && !empty($entry['user_id'])) {
            $tags[] = 'user:' . $entry['user_id'];
        }

        if ($tagConfig['request_id'] ?? false && !empty($entry['request_id'])) {
            $tags[] = 'request:' . $entry['request_id'];
        }

        if ($tagConfig['session_id'] ?? false && !empty($entry['session_id'])) {
            $tags[] = 'session:' . $entry['session_id'];
        }

        if ($tagConfig['ip_address'] ?? false && !empty($entry['request_ip'])) {
            $tags[] = 'ip:' . $entry['request_ip'];
        }

        return $tags;
    }

    /**
     * Get numeric level value.
     */
    protected function getLevelValue(string $level): int
    {
        return match ($level) {
            'emergency' => 600,
            'alert' => 550,
            'critical' => 500,
            'error' => 400,
            'warning' => 300,
            'notice' => 250,
            'info' => 200,
            'debug' => 100,
            default => 0,
        };
    }
}
