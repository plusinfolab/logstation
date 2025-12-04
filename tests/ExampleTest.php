<?php

use Illuminate\Support\Str;
use PlusinfoLab\Logstation\Facades\Logstation;
use PlusinfoLab\Logstation\Models\LogEntry;

it('can record a log entry', function () {
    Logstation::recordLog([
        'batch_id' => Str::uuid()->toString(),
        'type' => 'log',
        'level' => 400,
        'level_name' => 'error',
        'channel' => 'testing',
        'message' => 'Test error message',
        'context' => ['test' => 'data'],
        'created_at' => now(),
    ]);

    expect(LogEntry::count())->toBe(1);

    $log = LogEntry::first();
    expect($log->message)->toBe('Test error message');
    expect($log->level_name)->toBe('error');
    expect($log->channel)->toBe('testing');
});

it('can search log entries', function () {
    Logstation::recordLog([
        'batch_id' => Str::uuid()->toString(),
        'type' => 'log',
        'level' => 200,
        'level_name' => 'info',
        'channel' => 'testing',
        'message' => 'First log message',
        'created_at' => now(),
    ]);

    Logstation::recordLog([
        'batch_id' => Str::uuid()->toString(),
        'type' => 'log',
        'level' => 400,
        'level_name' => 'error',
        'channel' => 'testing',
        'message' => 'Second log message',
        'created_at' => now(),
    ]);

    $results = Logstation::search(['search' => 'First'], 10);
    expect($results)->toHaveCount(1);
    expect($results->first()->message)->toBe('First log message');
});

it('can filter logs by level', function () {
    Logstation::recordLog([
        'batch_id' => Str::uuid()->toString(),
        'type' => 'log',
        'level' => 200,
        'level_name' => 'info',
        'channel' => 'testing',
        'message' => 'Info message',
        'created_at' => now(),
    ]);

    Logstation::recordLog([
        'batch_id' => Str::uuid()->toString(),
        'type' => 'log',
        'level' => 400,
        'level_name' => 'error',
        'channel' => 'testing',
        'message' => 'Error message',
        'created_at' => now(),
    ]);

    $results = Logstation::search(['level' => 'error'], 10);
    expect($results)->toHaveCount(1);
    expect($results->first()->level_name)->toBe('error');
});
