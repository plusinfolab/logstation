<?php

use Illuminate\Support\Facades\Route;
use PlusinfoLab\Logstation\Http\Controllers\DashboardController;
use PlusinfoLab\Logstation\Http\Controllers\ExportController;
use PlusinfoLab\Logstation\Http\Controllers\LogEntryController;
use PlusinfoLab\Logstation\Http\Controllers\SearchController;
use PlusinfoLab\Logstation\Http\Controllers\SnippetController;

$path = config('logstation.path', 'logstation');
$middleware = config('logstation.middleware', ['web']);

Route::prefix($path)
    ->middleware($middleware)
    ->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('logstation.dashboard');

        // Log Entries
        Route::get('/logs', [LogEntryController::class, 'index'])->name('logstation.logs.index');
        Route::get('/logs/stream', [LogEntryController::class, 'stream'])->name('logstation.logs.stream');
        Route::get('/logs/{id}', [LogEntryController::class, 'show'])->name('logstation.logs.show');
        Route::delete('/logs/{id}', [LogEntryController::class, 'destroy'])->name('logstation.logs.destroy');

        // Search
        Route::get('/search', [SearchController::class, 'index'])->name('logstation.search');
        Route::get('/search/autocomplete', [SearchController::class, 'autocomplete'])->name('logstation.search.autocomplete');

        // Export
        Route::post('/export', [ExportController::class, 'export'])->name('logstation.export');

        // Snippets
        Route::get('/snippets', [SnippetController::class, 'index'])->name('logstation.snippets.index');
        Route::post('/snippets', [SnippetController::class, 'store'])->name('logstation.snippets.store');
        Route::get('/snippets/{id}', [SnippetController::class, 'show'])->name('logstation.snippets.show');
        Route::put('/snippets/{id}', [SnippetController::class, 'update'])->name('logstation.snippets.update');
        Route::delete('/snippets/{id}', [SnippetController::class, 'destroy'])->name('logstation.snippets.destroy');

    });
