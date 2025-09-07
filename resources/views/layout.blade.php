<!DOCTYPE html>
<html lang="en" x-data="{ theme: localStorage.getItem('theme') || 'dark' }" x-init="$watch('theme', val => localStorage.setItem('theme', val))" :class="theme">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LogStation')</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            50: '#f8f9fa',
                            100: '#e9ecef',
                            200: '#dee2e6',
                            300: '#ced4da',
                            400: '#adb5bd',
                            500: '#6c757d',
                            600: '#495057',
                            700: '#343a40',
                            800: '#212529',
                            900: '#0d1117',
                            950: '#010409',
                        }
                    },
                    backdropBlur: {
                        xs: '2px',
                    }
                }
            }
        }
    </script>

    <style>
        /* Glassmorphism styles */
        /* Glassmorphism styles */
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .dark .glass {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid #e5e7eb;
        }

        .glass-light {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .dark .glass-light {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid #e5e7eb;
        }

        /* Smooth theme transition */
        * {
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }

        /* Animation for new logs */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .log-entry-new {
            animation: slideInDown 0.4s ease-out;
        }

        /* Pulse animation for live indicator */
        @keyframes pulse-dot {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .pulse-dot {
            animation: pulse-dot 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-dark-950 text-gray-900 dark:text-gray-100 min-h-screen">
    <!-- Header -->
    <header class="glass dark:glass border-b border-gray-200 dark:border-dark-800 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-8">
                    <a href="{{ url(config('logstation.path', 'logstation')) }}" class="flex items-center space-x-2">
                        @if (config('logstation.logo_url'))
                            <img src="{{ config('logstation.logo_url') }}" alt="LogStation" class="h-8 w-auto">
                        @else
                            <svg class="w-8 h-8 text-blue-600 dark:text-blue-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-xl font-bold text-gray-900 dark:text-white">LogStation</span>
                        @endif
                    </a>

                    <!-- Navigation -->
                    <nav class="hidden md:flex space-x-1">
                        <a href="{{ url(config('logstation.path', 'logstation')) }}"
                            class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->is(config('logstation.path', 'logstation')) ? 'bg-gray-200 dark:bg-dark-800 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-dark-800' }}">
                            Dashboard
                        </a>
                        <a href="{{ url(config('logstation.path', 'logstation') . '/logs') }}"
                            class="px-3 py-2 rounded-lg text-sm font-medium {{ request()->is(config('logstation.path', 'logstation') . '/logs*') ? 'bg-gray-200 dark:bg-dark-800 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-dark-800' }}">
                            Logs
                        </a>
                    </nav>
                </div>

                <!-- Theme Toggle -->
                <button @click="theme = theme === 'dark' ? 'light' : 'dark'"
                    class="p-2 rounded-lg bg-gray-200 dark:bg-dark-800 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-dark-700 transition-colors">
                    <svg x-show="theme === 'dark'" class="w-5 h-5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg x-show="theme === 'light'" class="w-5 h-5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>
</body>

</html>
