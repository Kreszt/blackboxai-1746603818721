@props([
    'title',
    'size' => 'md', // sm, md, lg, xl, full
    'showClose' => true
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    'full' => 'sm:max-w-full'
][$size] ?? 'sm:max-w-md';
@endphp

<div x-cloak
     x-show="open"
     class="fixed inset-0 z-50 overflow-hidden"
     role="dialog"
     aria-modal="true">
    
    <!-- Background overlay -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
         x-show="open"
         x-transition:enter="ease-in-out duration-500"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in-out duration-500"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false">
    </div>

    <!-- Panel -->
    <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
        <div class="w-screen {{ $maxWidth }}"
             x-show="open"
             x-transition:enter="transform transition ease-in-out duration-500"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-in-out duration-500"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full">
            
            <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                <!-- Header -->
                <div class="bg-gray-50 px-4 py-6 sm:px-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-medium text-gray-900" id="slide-over-title">
                            {{ $title }}
                        </h2>
                        @if($showClose)
                            <div class="ml-3 flex h-7 items-center">
                                <button type="button"
                                        class="rounded-md text-gray-400 hover:text-gray-500 focus:outline-none"
                                        @click="open = false">
                                    <span class="sr-only">Close panel</span>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Content -->
                <div class="relative flex-1 px-4 py-6 sm:px-6">
                    {{ $slot }}
                </div>

                <!-- Footer -->
                @if(isset($footer))
                    <div class="flex-shrink-0 border-t border-gray-200 px-4 py-5 sm:px-6">
                        {{ $footer }}
                    </div>
                @endif
            </div>

            <!-- Loading Overlay -->
            <div x-show="loading"
                 class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
        </div>
    </div>
</div>
