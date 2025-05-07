@props(['type'])

@php
$classes = match($type) {
    'new' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
    'returning' => 'bg-purple-100 text-purple-800 border-purple-200',
    'control' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
    default => 'bg-gray-100 text-gray-800 border-gray-200'
};

$labels = [
    'new' => 'Baru',
    'returning' => 'Kunjungan Ulang',
    'control' => 'Kontrol'
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ' . $classes]) }}>
    {{ $labels[$type] ?? $type }}
</span>
