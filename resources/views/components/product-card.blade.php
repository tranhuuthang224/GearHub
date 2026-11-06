@props([
    'product' => null,
    'id' => null,
    'name' => 'Tên sản phẩm',
    'slug' => '#',
    'price' => 0,
    'salePrice' => null,
    'thumbnail' => null,
    'rating' => '4.8',
    'reviewCount' => null,
    'category' => null,
])

@php
    // If a product object/array is passed, extract attributes
    if ($product) {
        $id = is_array($product) ? ($product['id'] ?? null) : $product->id;
        $name = is_array($product) ? ($product['name'] ?? $name) : $product->name;
        $slug = is_array($product) ? ($product['slug'] ?? $slug) : ($product->slug ?? '#');
        $price = is_array($product) ? ($product['price'] ?? 0) : $product->price;
        $salePrice = is_array($product) ? ($product['sale_price'] ?? null) : ($product->sale_price ?? null);
        $thumbnail = is_array($product) ? ($product['thumbnail'] ?? null) : ($product->thumbnail ?? null);
        $category = is_array($product) ? ($product['category'] ?? null) : ($product->category?->name ?? null);
    }

    $thumbSrc = null;
    if ($thumbnail) {
        if (\Illuminate\Support\Str::startsWith($thumbnail, ['http://', 'https://', 'data:'])) {
            $thumbSrc = $thumbnail;
        } elseif (\Illuminate\Support\Str::startsWith($thumbnail, ['images/', 'storage/'])) {
            $thumbSrc = asset($thumbnail);
        } else {
            $thumbSrc = asset('storage/' . $thumbnail);
        }
    }

    $finalPrice = $salePrice ?? $price;
    $hasDiscount = $salePrice && $salePrice < $price;
    $url = $slug !== '#' ? route('shop.show', $slug) : '#';
@endphp

<div 
    x-data="{ isCompared: false }" 
    x-on:compare-active-ids.window="isCompared = ($event.detail.ids || []).includes({{ $id ?? 0 }})"
    {{ $attributes->merge(['class' => 'group flex flex-col bg-white rounded-2xl transition duration-300 hover:-translate-y-1 hover:shadow-lg']) }}
>
    <!-- Image Box with soft grey background -->
    <div class="relative aspect-square w-full bg-[#F4F5F7] rounded-2xl overflow-hidden flex items-center justify-center p-6">
        @if ($hasDiscount)
            <div class="absolute top-3 left-3 z-10">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-primary text-white shadow-sm">
                    Sale
                </span>
            </div>
        @endif

        <a href="{{ $url }}" class="w-full h-full flex items-center justify-center">
            @if ($thumbSrc)
                <img
                    src="{{ $thumbSrc }}"
                    alt="{{ $name }}"
                    class="max-h-full max-w-full object-contain transition-transform duration-500 group-hover:scale-105"
                    loading="lazy"
                >
            @else
                <div class="w-24 h-24 rounded-xl bg-gray-200/70 flex items-center justify-center text-gray-400 group-hover:scale-105 transition-transform">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            @endif
        </a>

        <!-- Quick Action Buttons: So sánh & Thêm vào giỏ -->
        <div class="absolute bottom-3 right-3 flex items-center gap-1.5 z-10 sm:opacity-0 sm:group-hover:opacity-100 opacity-90 transition-all duration-300">
            <!-- Nút So sánh (Icon cân) -->
            <button
                type="button"
                :class="isCompared ? 'bg-primary text-white ring-2 ring-primary/40' : 'bg-white text-gray-700 hover:bg-[#18181B] hover:text-white'"
                class="w-9 h-9 rounded-full shadow-md flex items-center justify-center transition-all duration-200 transform hover:scale-105 active:scale-95"
                :title="isCompared ? 'Đang chọn so sánh (bấm để bỏ)' : 'Thêm vào so sánh'"
                onclick="event.preventDefault(); event.stopPropagation(); window.dispatchEvent(new CustomEvent('toggle-compare', { detail: { id: {{ $id ?? 0 }} } }))"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                </svg>
            </button>

            <!-- Nút Thêm vào giỏ (Icon túi) -->
            <button
                type="button"
                class="w-9 h-9 rounded-full bg-white text-gray-800 shadow-md flex items-center justify-center hover:bg-primary hover:text-white transition-all duration-200 transform hover:scale-105 active:scale-95"
                title="Thêm vào giỏ"
                onclick="event.preventDefault(); event.stopPropagation(); window.dispatchEvent(new CustomEvent('add-to-cart', { detail: { id: {{ $id ?? 0 }} } }))"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Product Info -->
    <div class="pt-3 pb-2 px-1 flex flex-col flex-grow">
        @if ($category)
            <span class="text-xs text-gray-400 font-normal mb-0.5 line-clamp-1">{{ $category }}</span>
        @endif

        <a href="{{ $url }}" class="text-sm font-semibold text-gray-900 group-hover:text-primary transition line-clamp-1">
            {{ $name }}
        </a>

        <!-- Price and Rating Row -->
        <div class="mt-2 flex items-center justify-between gap-2">
            <div class="flex items-baseline gap-1.5 flex-wrap">
                <span class="text-sm sm:text-base font-bold text-gray-900">
                    {{ number_format($finalPrice, 0, ',', '.') }}đ
                </span>
                @if ($hasDiscount)
                    <span class="text-xs text-gray-400 line-through">
                        {{ number_format($price, 0, ',', '.') }}đ
                    </span>
                @endif
            </div>

            @if ($rating)
                <div class="flex items-center gap-1 text-xs text-gray-600 font-medium">
                    <span class="text-amber-400">★</span>
                    <span>{{ $rating }}</span>
                </div>
            @endif
        </div>
    </div>
</div>
