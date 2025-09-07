@extends('logstation::layout')

@section('title', 'Logs - LogStation')

@section('content')
    <div class="space-y-6" x-data="logsViewer('{{ $logs->first()?->created_at?->toIso8601String() ?? now()->toIso8601String() }}')">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Logs</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Browse and search application logs</p>
            </div>
            <div class="flex items-center space-x-3">
                <!-- Live Toggle -->
                <button @click="liveMode ? stopLive() : startLive()"
                    :class="liveMode ? 'bg-green-600 hover:bg-green-700' :
                        'bg-gray-200 dark:bg-dark-800 hover:bg-gray-300 dark:hover:bg-dark-700'"
                    class="inline-flex items-center px-4 py-2 text-white dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                    <span x-show="liveMode" class="flex items-center">
                        <span class="w-2 h-2 bg-white rounded-full mr-2 pulse-dot"></span>
                        Live
                        <span x-show="newLogsCount > 0" class="ml-2 px-2 py-0.5 bg-white/20 rounded-full text-xs"
                            x-text="'+' + newLogsCount"></span>
                    </span>
                    <span x-show="!liveMode" class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Start Live
                    </span>
                </button>

                <button @click="showFilters = !showFilters"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filters
                </button>

                <button @click="showExport = true"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-dark-800 hover:bg-gray-300 dark:hover:bg-dark-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export
                </button>
            </div>
        </div>

        <!-- Filters Panel -->
        <div x-show="showFilters" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            class="glass dark:glass border border-gray-200 dark:border-dark-800 rounded-xl p-6 shadow-lg">
            <form method="GET" action="{{ url(config('logstation.path', 'logstation') . '/logs') }}">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-2">Search</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                                placeholder="Search logs..."
                                class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-dark-900 border border-gray-300 dark:border-dark-700 rounded-lg text-sm text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <!-- Level -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-2">Level</label>
                        <select name="level"
                            class="w-full px-4 py-2.5 bg-white dark:bg-dark-900 border border-gray-300 dark:border-dark-700 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Levels</option>
                            <option value="emergency" {{ ($filters['level'] ?? '') === 'emergency' ? 'selected' : '' }}>
                                Emergency</option>
                            <option value="alert" {{ ($filters['level'] ?? '') === 'alert' ? 'selected' : '' }}>Alert
                            </option>
                            <option value="critical" {{ ($filters['level'] ?? '') === 'critical' ? 'selected' : '' }}>
                                Critical</option>
                            <option value="error" {{ ($filters['level'] ?? '') === 'error' ? 'selected' : '' }}>Error
                            </option>
                            <option value="warning" {{ ($filters['level'] ?? '') === 'warning' ? 'selected' : '' }}>Warning
                            </option>
                            <option value="notice" {{ ($filters['level'] ?? '') === 'notice' ? 'selected' : '' }}>Notice
                            </option>
                            <option value="info" {{ ($filters['level'] ?? '') === 'info' ? 'selected' : '' }}>Info
                            </option>
                            <option value="debug" {{ ($filters['level'] ?? '') === 'debug' ? 'selected' : '' }}>Debug
                            </option>
                        </select>
                    </div>

                    <!-- Channel -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-2">Channel</label>
                        <input type="text" name="channel" value="{{ $filters['channel'] ?? '' }}"
                            placeholder="e.g., stack"
                            class="w-full px-4 py-2.5 bg-white dark:bg-dark-900 border border-gray-300 dark:border-dark-700 rounded-lg text-sm text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-2">Start Date</label>
                        <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}"
                            class="w-full px-4 py-2.5 bg-white dark:bg-dark-900 border border-gray-300 dark:border-dark-700 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- End Date -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-2">End Date</label>
                        <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}"
                            class="w-full px-4 py-2.5 bg-white dark:bg-dark-900 border border-gray-300 dark:border-dark-700 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Tag -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-2">Tag</label>
                        <input type="text" name="tag" value="{{ $filters['tag'] ?? '' }}" placeholder="Tag name"
                            class="w-full px-4 py-2.5 bg-white dark:bg-dark-900 border border-gray-300 dark:border-dark-700 rounded-lg text-sm text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Per Page -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-2">Per Page</label>
                        <select name="per_page"
                            class="w-full px-4 py-2.5 bg-white dark:bg-dark-900 border border-gray-300 dark:border-dark-700 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="25">25</option>
                            <option value="50" selected>50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center space-x-3 mt-6">
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Apply Filters
                    </button>
                    <a href="{{ url(config('logstation.path', 'logstation') . '/logs') }}"
                        class="px-5 py-2.5 bg-gray-200 dark:bg-dark-800 hover:bg-gray-300 dark:hover:bg-dark-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Logs List -->
        <div id="logs-container" class="space-y-2">
            @forelse($logs as $log)
                <a href="{{ url(config('logstation.path', 'logstation') . '/logs/' . $log->id) }}"
                    class="block glass dark:glass border border-gray-200 dark:border-dark-800 rounded-xl p-4 hover:border-gray-300 dark:hover:border-dark-700 hover:shadow-lg transition-all group">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-3 flex-1 min-w-0">
                            <!-- Level Badge -->
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium uppercase flex-shrink-0 border
                            @if ($log->level_name === 'emergency') bg-purple-500/10 text-purple-400 dark:text-purple-400 border-purple-500/20
                            @elseif($log->level_name === 'alert') bg-red-500/10 text-red-400 dark:text-red-400 border-red-500/20
                            @elseif($log->level_name === 'critical') bg-red-500/10 text-red-400 dark:text-red-400 border-red-500/20
                            @elseif($log->level_name === 'error') bg-red-500/10 text-red-400 dark:text-red-400 border-red-500/20
                            @elseif($log->level_name === 'warning') bg-yellow-500/10 text-yellow-400 dark:text-yellow-400 border-yellow-500/20
                            @elseif($log->level_name === 'notice') bg-blue-500/10 text-blue-400 dark:text-blue-400 border-blue-500/20
                            @elseif($log->level_name === 'info') bg-green-500/10 text-green-400 dark:text-green-400 border-green-500/20
                            @else bg-gray-500/10 text-gray-400 dark:text-gray-400 border-gray-500/20 @endif">
                                {{ $log->level_name }}
                            </span>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white mb-2 line-clamp-1">
                                    {{ $log->message }}</p>

                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    @if ($log->channel)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-dark-700">
                                            <span class="text-gray-500 mr-1">channel:</span>{{ $log->channel }}
                                        </span>
                                    @endif

                                    @if ($log->exception_class)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-dark-700">
                                            <span
                                                class="text-gray-500 mr-1">source:</span>{{ class_basename($log->exception_class) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Timestamp & Arrow -->
                        <div class="flex items-center space-x-3 flex-shrink-0 ml-4">
                            <time
                                class="text-xs text-gray-500 dark:text-gray-500">{{ $log->created_at->format('Y-m-d H:i:s') }}</time>
                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-600 group-hover:text-gray-600 dark:group-hover:text-gray-400 transition-colors"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </a>
            @empty
                <div class="glass dark:glass border border-gray-200 dark:border-dark-800 rounded-xl p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-4 text-sm font-medium text-gray-700 dark:text-gray-400">No logs found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-500">Try adjusting your filters or generate some
                        test logs</p>
                    <a href="{{ url(config('logstation.path', 'logstation') . '/test') }}"
                        class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Generate Test Logs
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($logs->hasPages())
            <div
                class="flex items-center justify-between glass dark:glass border border-gray-200 dark:border-dark-800 rounded-xl px-6 py-4 shadow-lg">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Showing <span class="font-medium text-gray-900 dark:text-white">{{ $logs->firstItem() }}</span> to
                    <span class="font-medium text-gray-900 dark:text-white">{{ $logs->lastItem() }}</span> of
                    <span class="font-medium text-gray-900 dark:text-white">{{ $logs->total() }}</span> results
                </div>

                <div class="flex items-center space-x-1">
                    {{-- Previous Button --}}
                    @if ($logs->onFirstPage())
                        <span
                            class="px-3 py-2 bg-gray-100 dark:bg-dark-800 text-gray-400 dark:text-gray-600 rounded-lg text-sm cursor-not-allowed">Previous</span>
                    @else
                        <a href="{{ $logs->previousPageUrl() }}"
                            class="px-3 py-2 bg-gray-200 dark:bg-dark-800 hover:bg-gray-300 dark:hover:bg-dark-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm transition-colors">Previous</a>
                    @endif

                    {{-- Page Numbers --}}
                    @php
                        $currentPage = $logs->currentPage();
                        $lastPage = $logs->lastPage();
                        $start = max(1, $currentPage - 2);
                        $end = min($lastPage, $currentPage + 2);
                    @endphp

                    @if ($start > 1)
                        <a href="{{ $logs->url(1) }}"
                            class="px-3 py-2 bg-gray-200 dark:bg-dark-800 hover:bg-gray-300 dark:hover:bg-dark-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm transition-colors">1</a>
                        @if ($start > 2)
                            <span class="px-2 text-gray-500 dark:text-gray-500">...</span>
                        @endif
                    @endif

                    @for ($i = $start; $i <= $end; $i++)
                        @if ($i == $currentPage)
                            <span
                                class="px-3 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium">{{ $i }}</span>
                        @else
                            <a href="{{ $logs->url($i) }}"
                                class="px-3 py-2 bg-gray-200 dark:bg-dark-800 hover:bg-gray-300 dark:hover:bg-dark-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm transition-colors">{{ $i }}</a>
                        @endif
                    @endfor

                    @if ($end < $lastPage)
                        @if ($end < $lastPage - 1)
                            <span class="px-2 text-gray-500 dark:text-gray-500">...</span>
                        @endif
                        <a href="{{ $logs->url($lastPage) }}"
                            class="px-3 py-2 bg-gray-200 dark:bg-dark-800 hover:bg-gray-300 dark:hover:bg-dark-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm transition-colors">{{ $lastPage }}</a>
                    @endif

                    {{-- Next Button --}}
                    @if ($logs->hasMorePages())
                        <a href="{{ $logs->nextPageUrl() }}"
                            class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition-colors">Next</a>
                    @else
                        <span
                            class="px-3 py-2 bg-gray-100 dark:bg-dark-800 text-gray-400 dark:text-gray-600 rounded-lg text-sm cursor-not-allowed">Next</span>
                    @endif
                </div>
            </div>
        @endif

        <!-- Export Modal -->
        <div x-show="showExport" x-cloak @click.self="showExport = false"
            class="fixed inset-0 z-50 overflow-y-auto bg-black/50 dark:bg-black/75 flex items-center justify-center px-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            <div class="glass-light dark:glass border border-gray-200 dark:border-dark-800 rounded-xl max-w-md w-full p-6 shadow-2xl"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Export Logs</h3>
                    <button @click="showExport = false"
                        class="text-gray-400 dark:text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ url(config('logstation.path', 'logstation') . '/export') }}"
                    class="space-y-4">
                    @csrf
                    @foreach ($filters as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-400 mb-2">Format</label>
                        <select name="format"
                            class="w-full px-4 py-2.5 bg-white dark:bg-dark-900 border border-gray-300 dark:border-dark-700 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="json">JSON</option>
                            <option value="csv">CSV</option>
                            <option value="txt">Text</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="showExport = false"
                            class="px-4 py-2.5 bg-gray-200 dark:bg-dark-800 hover:bg-gray-300 dark:hover:bg-dark-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Export
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function logsViewer(initialTimestamp) {
            return {
                showFilters: false,
                showExport: false,
                liveMode: false,
                lastTimestamp: initialTimestamp,
                newLogsCount: 0,
                pollInterval: null,
                audioContext: null,

                init() {
                    // Initialize Web Audio API context
                    this.audioContext = new(window.AudioContext || window.webkitAudioContext)();
                },

                startLive() {
                    this.liveMode = true;
                    if (!this.lastTimestamp) {
                        this.lastTimestamp = new Date().toISOString();
                    }
                    console.log('[LogStation] Starting live mode with timestamp:', this.lastTimestamp);
                    this.pollLogs();
                    this.pollInterval = setInterval(() => this.pollLogs(), 3000);
                },

                stopLive() {
                    this.liveMode = false;
                    this.newLogsCount = 0;
                    if (this.pollInterval) {
                        clearInterval(this.pollInterval);
                        this.pollInterval = null;
                    }
                    console.log('[LogStation] Stopped live mode');
                },

                async pollLogs() {
                    const params = new URLSearchParams(window.location.search);
                    params.set('since', this.lastTimestamp);

                    try {
                        console.log('[LogStation] Polling for logs since:', this.lastTimestamp);
                        const response = await fetch(
                            '{{ url(config('logstation.path', 'logstation') . '/logs/stream') }}?' +
                            params.toString());

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const data = await response.json();
                        console.log('[LogStation] Received:', data.count, 'logs');

                        if (data.logs && data.logs.length > 0) {
                            console.log('[LogStation] Processing', data.logs.length, 'new logs');
                            this.prependLogs(data.logs);
                            this.newLogsCount += data.logs.length;

                            // Play notification sound for the most recent log
                            this.playNotificationSound(data.logs[0].level_name);
                        }

                        this.lastTimestamp = data.timestamp;
                    } catch (error) {
                        console.error('[LogStation] Failed to fetch logs:', error);
                    }
                },

                prependLogs(logs) {
                    const container = document.getElementById('logs-container');
                    logs.reverse().forEach(log => {
                        const logHtml = this.createLogElement(log);
                        container.insertAdjacentHTML('afterbegin', logHtml);
                    });
                },

                playNotificationSound(level) {
                    if (!this.audioContext) {
                        this.init();
                    }

                    const ctx = this.audioContext;
                    const now = ctx.currentTime;

                    // Create oscillator and gain nodes
                    const oscillator = ctx.createOscillator();
                    const gainNode = ctx.createGain();

                    oscillator.connect(gainNode);
                    gainNode.connect(ctx.destination);

                    // Configure sound based on log level
                    let frequency, duration, type;

                    switch (level) {
                        case 'emergency':
                        case 'alert':
                        case 'critical':
                        case 'error':
                            // Error: Low urgent beep
                            frequency = 200;
                            duration = 0.15;
                            type = 'square';
                            // Play twice for urgency
                            this.playBeep(ctx, frequency, duration, type, now);
                            this.playBeep(ctx, frequency, duration, type, now + 0.2);
                            return;

                        case 'warning':
                            // Warning: Medium pitch alert
                            frequency = 440;
                            duration = 0.1;
                            type = 'sine';
                            break;

                        case 'info':
                        case 'notice':
                            // Info: Gentle notification
                            frequency = 800;
                            duration = 0.08;
                            type = 'sine';
                            break;

                        case 'debug':
                        default:
                            // Debug: Soft click
                            frequency = 1200;
                            duration = 0.05;
                            type = 'sine';
                            break;
                    }

                    this.playBeep(ctx, frequency, duration, type, now);
                },

                playBeep(ctx, frequency, duration, type, startTime) {
                    const oscillator = ctx.createOscillator();
                    const gainNode = ctx.createGain();

                    oscillator.connect(gainNode);
                    gainNode.connect(ctx.destination);

                    oscillator.type = type;
                    oscillator.frequency.value = frequency;

                    // Envelope for smooth sound
                    gainNode.gain.setValueAtTime(0, startTime);
                    gainNode.gain.linearRampToValueAtTime(0.3, startTime + 0.01);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, startTime + duration);

                    oscillator.start(startTime);
                    oscillator.stop(startTime + duration);
                },

                createLogElement(log) {
                    const levelColors = {
                        emergency: 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                        alert: 'bg-red-500/10 text-red-400 border-red-500/20',
                        critical: 'bg-red-500/10 text-red-400 border-red-500/20',
                        error: 'bg-red-500/10 text-red-400 border-red-500/20',
                        warning: 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                        notice: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                        info: 'bg-green-500/10 text-green-400 border-green-500/20',
                        debug: 'bg-gray-500/10 text-gray-400 border-gray-500/20'
                    };

                    const levelClass = levelColors[log.level_name] || levelColors.debug;
                    const timestamp = new Date(log.created_at).toLocaleString();

                    // Using template literals here is safe because we are inside a script tag
                    return `
                 <a href="{{ url(config('logstation.path', 'logstation') . '/logs') }}/${log.id}" 
                    class="block glass dark:glass border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-lg transition-all group log-entry-new">
                     <div class="flex items-start justify-between">
                         <div class="flex items-start space-x-3 flex-1 min-w-0">
                             <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium uppercase flex-shrink-0 border ${levelClass}">
                                 ${log.level_name}
                             </span>
                             <div class="flex-1 min-w-0">
                                 <p class="text-sm font-medium text-gray-900 dark:text-white mb-2 line-clamp-1">${log.message}</p>
                                 <div class="flex flex-wrap items-center gap-2 text-xs">
                                     ${log.channel ? `<span class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-100 dark:bg-dark-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-dark-700"><span class="text-gray-500 mr-1">channel:</span>${log.channel}</span>` : ''}
                                 </div>
                             </div>
                         </div>
                         <div class="flex items-center space-x-3 flex-shrink-0 ml-4">
                             <time class="text-xs text-gray-500 dark:text-gray-500">${timestamp}</time>
                             <svg class="w-5 h-5 text-gray-400 dark:text-gray-600 group-hover:text-gray-600 dark:group-hover:text-gray-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                             </svg>
                         </div>
                     </div>
                 </a>
             `;
                }
            }
        }
    </script>
@endsection
