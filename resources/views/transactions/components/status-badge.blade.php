@props(['status'])

@php
$classes = match($status) {
    'unpaid' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'paid' => 'bg-green-100 text-green-800 border-green-200',
    'canceled' => 'bg-red-100 text-red-800 border-red-200',
    default => 'bg-gray-100 text-gray-800 border-gray-200'
};

$labels = [
    'unpaid' => 'Belum Dibayar',
    'paid' => 'Lunas',
    'canceled' => 'Dibatalkan'
];

$icons = [
    'unpaid' => <<<'SVG'
        <svg class="mr-1.5 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    SVG,
    'paid' => <<<'SVG'
        <svg class="mr-1.5 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    SVG,
    'canceled' => <<<'SVG'
        <svg class="mr-1.5 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    SVG,
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ' . $classes]) }}>
    @if(isset($icons[$status]))
        {!! $icons[$status] !!}
    @endif
    {{ $labels[$status] ?? $status }}
</span>
