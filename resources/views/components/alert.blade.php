@props([
    'type' => 'info',
    'message' => null,
    'dismissible' => true,
])

@php
    $types = [
        'success' => [
            'bg' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
            'iconColor' => 'text-emerald-500',
        ],
        'error' => [
            'bg' => 'bg-rose-50 border-rose-200 text-rose-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
            'iconColor' => 'text-rose-500',
        ],
        'warning' => [
            'bg' => 'bg-amber-50 border-amber-200 text-amber-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
            'iconColor' => 'text-amber-500',
        ],
        'info' => [
            'bg' => 'bg-blue-50 border-blue-200 text-blue-800',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
            'iconColor' => 'text-blue-500',
        ],
    ];

    $config = $types[$type] ?? $types['info'];
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="flex items-start gap-3 p-4 rounded-xl border {{ $config['bg'] }} text-sm transition"
    role="alert"
>
    <svg class="w-5 h-5 flex-shrink-0 mt-0.5 {{ $config['iconColor'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        {!! $config['icon'] !!}
    </svg>

    <div class="flex-grow">
        {{ $message ?? $slot }}
    </div>

    @if ($dismissible)
        <button
            type="button"
            @click="show = false"
            class="text-gray-400 hover:text-gray-600 transition -mr-1 -mt-1 p-1 rounded-md"
            aria-label="Đóng"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>
