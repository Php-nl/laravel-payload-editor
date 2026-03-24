<!DOCTYPE html>
<html lang="en" class="antialiased h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Payload Editor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'media',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Nunito', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
                    },
                    colors: {
                        primary: '#4F46E5', // Indigo-600
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Nunito', sans-serif; }
    </style>
    @livewireStyles
</head>
<body class="bg-gray-100 dark:bg-slate-900 text-gray-900 dark:text-gray-100 font-sans min-h-full flex flex-col">
    <!-- Header -->
    <header class="bg-white dark:bg-slate-800 shadow-[0_1px_3px_0_rgba(0,0,0,0.02)] border-b border-gray-200 dark:border-slate-700/60 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <h1 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Laravel Payload Editor</h1>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow max-w-7xl mx-auto w-full py-10 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700/60 mt-auto">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500 dark:text-slate-400">
                Laravel Payload Editor &nbsp;&middot;&nbsp; Proudly supporting the Dutch PHP community <a href="https://php.nl" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-semibold transition">php.nl</a>
            </p>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
