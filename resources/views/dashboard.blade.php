@extends('logstation::layout')

@section('title', 'Dashboard - LogStation')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div>
            <h1 class="text-3xl font-bold">Dashboard</h1>
            <p class="mt-1 ">Monitor your application logs in real-time</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class=" rounded-lg border dark:border-gray-700  p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 rounded-lg bg-blue-200">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium">Total Logs</p>
                        <p class="text-2xl font-bold ">{{ number_format($stats['total'] ?? 0) }}</p>
                    </div>
                </div>
            </div>

            <div class=" rounded-lg border dark:border-gray-700  p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 rounded-lg bg-green-200">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium ">Today</p>
                        <p class="text-2xl font-bold ">{{ number_format($stats['today'] ?? 0) }}</p>
                    </div>
                </div>
            </div>

            <div class=" rounded-lg border dark:border-gray-700  p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 rounded-lg bg-purple-200">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium ">This Week</p>
                        <p class="text-2xl font-bold ">{{ number_format($stats['this_week'] ?? 0) }}</p>
                    </div>
                </div>
            </div>

            <div class=" rounded-lg border dark:border-gray-700  p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 rounded-lg bg-orange-200">
                        <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium ">This Month</p>
                        <p class="text-2xl font-bold ">{{ number_format($stats['this_month'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class=" rounded-lg border dark:border-gray-700 ">
            <div class="p-6 border-b dark:border-b-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold ">Recent Logs</h3>
                    <a href="{{ url(config('logstation.path', 'logstation') . '/logs') }}"
                        class="text-blue-500 hover:text-blue-400 text-sm font-medium">
                        View All →
                    </a>
                </div>
            </div>
            <div class="divide-y ">
                @forelse($recentLogs as $log)
                    <a href="{{ url(config('logstation.path', 'logstation') . '/logs/' . $log->id) }}"
                        class="block p-4  transition-colors">
                        <div class="flex items-start space-x-4">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if ($log->level_name === 'emergency') bg-purple-900 text-purple-200
                            @elseif($log->level_name === 'alert') bg-red-900 text-red-200
                            @elseif($log->level_name === 'critical') bg-red-800 text-red-200
                            @elseif($log->level_name === 'error') bg-orange-900 text-orange-200
                            @elseif($log->level_name === 'warning') bg-yellow-900 text-yellow-200
                            @elseif($log->level_name === 'notice') bg-blue-900 text-blue-200
                            @elseif($log->level_name === 'info') bg-green-900 text-green-200
                            @else bg-gray-800 text-gray-300 @endif">
                                {{ $log->level_name }}
                            </span>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="text-sm font-medium  truncate">{{ $log->message }}</p>
                                    <time class="text-xs text-gray-500 ml-2">{{ $log->created_at->diffForHumans() }}</time>
                                </div>

                                <div class="flex items-center space-x-4 text-xs ">
                                    @if ($log->channel)
                                        <span>{{ $log->channel }}</span>
                                    @endif
                                    @if ($log->exception_class)
                                        <span class="text-red-400">{{ class_basename($log->exception_class) }}</span>
                                    @endif
                                    @if ($log->user_id)
                                        <span>User #{{ $log->user_id }}</span>
                                    @endif
                                </div>
                            </div>

                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <p>No logs found</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
