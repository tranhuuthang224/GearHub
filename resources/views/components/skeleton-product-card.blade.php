@props([
    'count' => 1,
])

@for ($i = 0; $i < $count; $i++)
    <div {{ $attributes->merge(['class' => 'group flex flex-col bg-white rounded-2xl animate-pulse p-1 border border-gray-100/80 shadow-xs']) }}>
        <!-- Image Box Skeleton with Shimmer effect -->
        <div class="relative aspect-square w-full bg-gradient-to-tr from-gray-100 via-gray-200 to-gray-100 rounded-2xl overflow-hidden flex items-center justify-center p-6">
            <div class="w-20 h-20 rounded-2xl bg-gray-300/40"></div>
            
            <!-- Action Buttons Placeholder -->
            <div class="absolute bottom-3 right-3 flex items-center gap-1.5 opacity-60">
                <div class="w-8 h-8 rounded-full bg-gray-300/60 shadow-xs"></div>
                <div class="w-8 h-8 rounded-full bg-gray-300/60 shadow-xs"></div>
            </div>

            <!-- Shimmer Highlight Sweep -->
            <div class="absolute inset-0 -translate-x-full animate-[shimmer_2s_infinite] bg-gradient-to-r from-transparent via-white/40 to-transparent"></div>
        </div>

        <!-- Info Section Skeleton -->
        <div class="pt-3.5 pb-2 px-2 flex flex-col flex-grow space-y-2.5">
            <!-- Category Tag -->
            <div class="h-2.5 w-16 bg-gray-200 rounded-full"></div>

            <!-- Product Name (2 lines) -->
            <div class="space-y-1.5">
                <div class="h-3.5 w-5/6 bg-gray-200 rounded-md"></div>
                <div class="h-3.5 w-3/5 bg-gray-200 rounded-md"></div>
            </div>

            <!-- Price & Star Rating Row -->
            <div class="mt-2 pt-1 flex items-center justify-between gap-2">
                <div class="h-4 w-24 bg-gray-300/80 rounded-md"></div>
                <div class="h-3 w-8 bg-gray-200 rounded-md"></div>
            </div>
        </div>
    </div>
@endfor
