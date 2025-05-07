<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hospital Information System - @yield('title')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom Styles -->
    <style>
        [x-cloak] { display: none !important; }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-xl font-bold text-gray-800">Hospital IS</span>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="{{ route('patients.index') }}" 
                           class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Patients
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Page Heading -->
        <div class="px-4 sm:px-0">
            <h1 class="text-2xl font-semibold text-gray-900">
                @yield('header')
            </h1>
        </div>

        <!-- Alert Component -->
        <div x-data="{ show: false, message: '', type: '' }" 
             x-show="show" 
             x-cloak
             x-transition
             @notify.window="
                show = true;
                message = $event.detail.message;
                type = $event.detail.type;
                setTimeout(() => show = false, 3000)
             "
             class="mt-4 px-4 sm:px-0">
            <div x-show="type === 'success'" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span x-text="message" class="block sm:inline"></span>
            </div>
            <div x-show="type === 'error'" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span x-text="message" class="block sm:inline"></span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="mt-4">
            @yield('content')
        </div>
    </main>

    @stack('modals')
    @stack('scripts')

    <script>
        function notify(message, type = 'success') {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { message, type }
            }));
        }
    </script>
</body>
</html>
