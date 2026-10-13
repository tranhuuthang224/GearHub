@props([
    'variant' => 'default',
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'text-[11px] px-2 py-0.5',
        'md' => 'text-xs px-2.5 py-1',
        'lg' => 'text-sm px-3 py-1.5',
    ];

    $variants = [
        'default' => 'bg-gray-100 text-gray-800 border border-gray-200',
        'sale' => 'bg-primary text-white font-bold shadow-sm',
        'active' => 'bg-emerald-50 text-emerald-700 border border-emerald-200 font-medium',
        'in_stock' => 'bg-emerald-50 text-emerald-700 border border-emerald-200 font-medium',
        'delivered' => 'bg-emerald-50 text-emerald-700 border border-emerald-200 font-medium',
        'confirmed' => 'bg-amber-50 text-amber-700 border border-amber-200 font-medium',
        'shipping' => 'bg-amber-50 text-amber-700 border border-amber-200 font-medium',
        'low' => 'bg-amber-50 text-amber-700 border border-amber-200 font-medium',
        'pending' => 'bg-rose-50 text-rose-700 border border-rose-200 font-medium',
        'cancelled' => 'bg-gray-100 text-gray-600 border border-gray-200 font-medium',
        'out_of_stock' => 'bg-rose-100 text-rose-800 border border-rose-200 font-medium',
        'hidden' => 'bg-gray-100 text-gray-500 border border-gray-200 font-medium',
    ];

    $classes = 'inline-flex items-center gap-1 rounded-full ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['default']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
