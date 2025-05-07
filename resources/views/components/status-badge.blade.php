@props(['status'])

@php
$classes = match($status) {
    'waiting' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'in_progress' => 'bg-blue-100 text-blue-800 border-blue-200',
    'completed' => 'bg-green-100 text-green-800 border-green-200',
    'cancelled' => 'bg-red-100 text-red-800 border-red-200',
    default => 'bg-gray-100 text-gray-800 border-gray-200'
};

$labels = [
    'waiting' => 'Menunggu',
    'in_progress' => 'Sedang Dilayani',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ' . $classes]) }}>
    {{ $labels[$status] ?? $status }}
</span>
