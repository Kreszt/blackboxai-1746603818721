@props(['status'])

@php
$classes = match($status) {
    'ongoing' => 'bg-blue-100 text-blue-800 border-blue-200',
    'completed' => 'bg-green-100 text-green-800 border-green-200',
    'referred' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
    default => 'bg-gray-100 text-gray-800 border-gray-200'
};

$labels = [
    'ongoing' => 'Sedang Berlangsung',
    'completed' => 'Selesai',
    'referred' => 'Dirujuk'
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ' . $classes]) }}>
    {{ $labels[$status] ?? $status }}
</span>
