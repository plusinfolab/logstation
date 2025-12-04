<?php

namespace PlusinfoLab\Logstation\Watchers;

use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Support\Str;
use PlusinfoLab\Logstation\Facades\Logstation;
use Throwable;

class ExceptionWatcher
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
        // Hook into exception handler
        app()->singleton(\Illuminate\Contracts\Debug\ExceptionHandler::class, function ($app) {
            return new class($app) extends Handler
            {
                public function report(Throwable $exception)
                {
                    if ($this->shouldReport($exception)) {
                        app(ExceptionWatcher::class)->recordException($exception);
                    }

                    return parent::report($exception);
                }
            };
        });
    }

    /**
     * Record an exception.
     */
    public function recordException(Throwable $exception): void
    {
        if (! $this->shouldRecord($exception)) {
            return;
        }

        $entry = [
            'id' => (string) Str::uuid(),
            'batch_id' => (string) Str::uuid(),
            'type' => 'error',
            'channel' => 'exceptions',
            'level' => 400,
            'level_name' => 'error',
            'message' => $exception->getMessage(),
            'context' => [
                'code' => $exception->getCode(),
            ],
            'extra' => [],
            'exception_class' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_trace' => $exception->getTraceAsString(),
            'exception_file' => $exception->getFile(),
            'exception_line' => $exception->getLine(),
            'created_at' => now(),
        ];

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
            $entry['user_id'] = auth()->id();
            $entry['user_email'] = auth()->user()->email ?? null;
        }

        // Add tags
        $entry['tags'] = [
            'exception',
            'exception:'.class_basename($exception),
        ];

        Logstation::recordLog($entry);
    }

    /**
     * Determine if the exception should be recorded.
     */
    protected function shouldRecord(Throwable $exception): bool
    {
        $ignore = $this->config['ignore'] ?? [];

        foreach ($ignore as $type) {
            if ($exception instanceof $type) {
                return false;
            }
        }

        return true;
    }
}
