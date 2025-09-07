@extends('logstation::layout')

@section('title', 'Log Details - LogStation')

@section('content')
    <div class="space-y-6" x-data="{ showRaw: false }">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ url(config('logstation.path', 'logstation') . '/logs') }}" class="text-gray-400 hover:">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold ">Log Details</h1>
                    <p class="text-sm text-gray-400">{{ $log->id }}</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
          
                <form method="POST" action="{{ url(config('logstation.path', 'logstation') . '/logs/' . $log->id) }}"
                    onsubmit="return confirm('Are you sure you want to delete this log entry?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        Delete
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Info Card -->
        <div class=" dark:bg-dark-800 rounded-lg border dark:border-gray-700 p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium uppercase
                    @if ($log->level_name === 'emergency') bg-purple-900 text-purple-200
                    @elseif($log->level_name === 'alert') bg-red-900 text-red-200
                    @elseif($log->level_name === 'critical') bg-red-800 text-red-200
                    @elseif($log->level_name === 'error') bg-orange-900 text-orange-200
                    @elseif($log->level_name === 'warning') bg-yellow-900 text-yellow-200
                    @elseif($log->level_name === 'notice') bg-blue-900 text-blue-200
                    @elseif($log->level_name === 'info') bg-green-900 text-green-200
                    @else bg-gray-800 text-dark-600 @endif">
                        {{ $log->level_name }}
                    </span>
                    @if ($log->channel)
                        <span class="text-sm text-gray-400">Channel: <span
                                class="">{{ $log->channel }}</span></span>
                    @endif
                </div>
                <time class="text-sm text-gray-400">{{ $log->created_at->format('Y-m-d H:i:s') }}</time>
            </div>

            <h2 class="text-xl font-semibold  mb-4">{{ $log->message }}</h2>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                @if ($log->user_id)
                    <div>
                        <p class="text-gray-400">User ID</p>
                        <p class=" font-medium">{{ $log->user_id }}</p>
                        @if ($log->user_email)
                            <p class="text-gray-500 text-xs">{{ $log->user_email }}</p>
                        @endif
                    </div>
                @endif

                @if ($log->request_method)
                    <div>
                        <p class="text-gray-400">Request</p>
                        <p class=" font-medium">{{ $log->request_method }}</p>
                    </div>
                @endif

                @if ($log->request_ip)
                    <div>
                        <p class="text-gray-400">IP Address</p>
                        <p class=" font-medium">{{ $log->request_ip }}</p>
                    </div>
                @endif

                @if ($log->session_id)
                    <div>
                        <p class="text-gray-400">Session ID</p>
                        <p class=" font-mono text-xs">{{ substr($log->session_id, 0, 16) }}...</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Request URL -->
        @if ($log->request_url)
            <div class=" rounded-lg border dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold  mb-3">Request URL</h3>
                <div class=" rounded p-3 font-mono text-sm text-dark-600 break-all">
                    {{ $log->request_url }}
                </div>
            </div>
        @endif

        <!-- Exception Details -->
        @if ($log->exception_class)
            <div class=" rounded-lg border border-red-900/50 p-6">
                <h3 class="text-lg font-semibold text-red-400 mb-3">Exception</h3>

                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-400">Class</p>
                        <p class=" font-mono">{{ $log->exception_class }}</p>
                    </div>

                    @if ($log->exception_message)
                        <div>
                            <p class="text-sm text-gray-400">Message</p>
                            <p class="">{{ $log->exception_message }}</p>
                        </div>
                    @endif

                    @if ($log->exception_file)
                        <div>
                            <p class="text-sm text-gray-400">File</p>
                            <p class=" font-mono text-sm">{{ $log->exception_file }}:{{ $log->exception_line }}
                            </p>
                        </div>
                    @endif

                    @if ($log->exception_trace)
                        <div>
                            <p class="text-sm text-gray-400 mb-2">Stack Trace</p>
                            <div class=" rounded p-4 overflow-x-auto">
                                <pre class="text-xs text-dark-600 font-mono">{{ $log->exception_trace }}</pre>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Context -->
        @if ($log->context && count($log->context) > 0)
            <div class=" rounded-lg border dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold  mb-3">Context</h3>
                <div class=" rounded p-4 overflow-x-auto">
                    <pre class="text-sm text-dark-600 font-mono">{{ json_encode($log->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        @endif

        <!-- Extra Data -->
        @if ($log->extra && count($log->extra) > 0)
            <div class=" rounded-lg border dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold  mb-3">Extra Data</h3>
                <div class=" rounded p-4 overflow-x-auto">
                    <pre class="text-sm text-dark-600 font-mono">{{ json_encode($log->extra, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        @endif

        <!-- Tags -->
        @if ($log->tags && $log->tags->count() > 0)
            <div class=" rounded-lg border dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold  mb-3">Tags</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach ($log->tags as $tag)
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-900/30 text-blue-800 border border-blue-800">
                            {{ $tag->tag }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Raw JSON -->
        <div x-show="showRaw" x-cloak class=" rounded-lg border dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold  mb-3">Raw JSON</h3>
            <div class=" rounded p-4 overflow-x-auto">
                <pre class="text-sm text-dark-600 font-mono">{{ json_encode($log->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        </div>
    </div>
@endsection
