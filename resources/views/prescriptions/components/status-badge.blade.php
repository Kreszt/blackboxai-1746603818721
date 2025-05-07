@props(['status'])

@php
$classes = match($status) {
    'draft' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'revised' => 'bg-orange-100 text-orange-800 border-orange-200',
    'final' => 'bg-green-100 text-green-800 border-green-200',
    default => 'bg-gray-100 text-gray-800 border-gray-200'
};

$labels = [
    'draft' => 'Draft',
    'revised' => 'Direvisi',
    'final' => 'Final'
];

$icons = [
    'draft' => <<<'SVG'
        <svg class="mr-1.5 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
        </svg>
    SVG,
    'revised' => <<<'SVG'
        <svg class="mr-1.5 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
        </svg>
    SVG,
    'final' => <<<'SVG'
        <svg class="mr-1.5 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
