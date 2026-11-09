@props([
    'icon' => '📦',
    'title' => 'Không tìm thấy dữ liệu',
    'description' => '',
    'actionText' => null,
    'actionUrl' => null,
    'actionClick' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-3xl p-8 sm:p-14 border border-gray-200/80 shadow-xs text-center max-w-xl mx-auto space-y-5']) }}>
    
    <!-- Icon Container with soft pulse halo -->
    <div class="relative w-20 h-20 mx-auto flex items-center justify-center">
        <div class="absolute inset-0 rounded-full bg-red-100/60 animate-ping opacity-25"></div>
        <div class="relative w-18 h-18 rounded-3xl bg-gradient-to-tr from-gray-50 via-gray-100 to-red-50 border border-gray-200/80 shadow-inner flex items-center justify-center text-3xl sm:text-4xl select-none">
            {{ $icon }}
        </div>
    </div>

    <!-- Title and Description -->
    <div class="space-y-1.5">
        <h3 class="text-lg sm:text-xl font-black text-gray-900 tracking-tight">
            {{ $title }}
        </h3>
        @if ($description)
            <p class="text-xs sm:text-sm text-gray-500 max-w-md mx-auto leading-relaxed font-medium">
                {{ $description }}
            </p>
        @endif
    </div>

    <!-- Action Button (nếu có) -->
    @if ($actionText)
        <div class="pt-2">
            @if ($actionUrl)
                <a 
                    href="{{ $actionUrl }}" 
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-primary hover:bg-primary-hover text-white text-xs sm:text-sm font-bold shadow-md shadow-primary/25 transition transform hover:-translate-y-0.5 active:scale-95 group"
                >
                    <span>{{ $actionText }}</span>
                    <span class="text-base group-hover:translate-x-1 transition-transform">→</span>
                </a>
            @elseif ($actionClick)
                <button 
                    type="button" 
                    @click="{{ $actionClick }}"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-primary hover:bg-primary-hover text-white text-xs sm:text-sm font-bold shadow-md shadow-primary/25 transition transform hover:-translate-y-0.5 active:scale-95"
                >
                    <span>{{ $actionText }}</span>
                </button>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
