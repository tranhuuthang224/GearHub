@extends('layouts.client')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-20">

    <!-- Breadcrumbs (Figma Page 5) -->
    <nav class="flex items-center gap-2 text-xs text-gray-400 mb-8">
        <a href="{{ url('/') }}" class="hover:text-primary transition">Home</a>
        <span>/</span>
        <a href="{{ url('/shop') }}" class="hover:text-primary transition">Shop</a>
        <span>/</span>
        <a href="{{ url('/shop?category=Headphones') }}" class="hover:text-primary transition">{{ $product['category'] }}</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">{{ $product['name'] }}</span>
    </nav>

    <!-- Main Product Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">
        
        <!-- Left: Product Image Gallery (Figma Page 5) -->
        <div class="lg:col-span-6 space-y-4">
            <!-- Main Hero Image -->
            <div class="aspect-square w-full bg-[#F4F5F7] rounded-3xl p-8 sm:p-12 flex items-center justify-center relative overflow-hidden">
                <img 
                    src="{{ $product['thumbnail'] }}" 
                    alt="{{ $product['name'] }}"
                    class="max-h-full max-w-full object-contain drop-shadow-xl hover:scale-105 transition duration-500"
                >
            </div>

            <!-- Thumbnail Carousel / Strip -->
            <div class="grid grid-cols-4 gap-3 sm:gap-4">
                <div class="aspect-square bg-[#F4F5F7] rounded-2xl p-2.5 flex items-center justify-center border-2 border-primary cursor-pointer">
                    <img src="{{ $product['thumbnail'] }}" alt="thumb" class="max-h-full max-w-full object-contain">
                </div>
                <div class="aspect-square bg-[#F4F5F7] rounded-2xl p-2.5 flex items-center justify-center border border-gray-200 cursor-pointer opacity-70 hover:opacity-100 transition">
                    <img src="{{ asset('images/hero-headphone.png') }}" alt="thumb" class="max-h-full max-w-full object-contain">
                </div>
                <div class="aspect-square bg-[#F4F5F7] rounded-2xl p-2.5 flex items-center justify-center border border-gray-200 cursor-pointer opacity-70 hover:opacity-100 transition">
                    <img src="{{ asset('images/images (1).png') }}" alt="thumb" class="max-h-full max-w-full object-contain">
                </div>
                <div class="aspect-square bg-[#F4F5F7] rounded-2xl p-2.5 flex items-center justify-center border border-gray-200 cursor-pointer opacity-70 hover:opacity-100 transition">
                    <img src="{{ asset('images/png-transparent-playstation-4-headphones-hyperx-cloud-audio-video-game-headphones-electronics-video-game-playstation-4.png') }}" alt="thumb" class="max-h-full max-w-full object-contain">
                </div>
            </div>
        </div>

        <!-- Right: Product Purchase Details -->
        <div class="lg:col-span-6 space-y-6">
            <span class="text-xs font-bold uppercase tracking-wider text-primary">
                {{ $product['category'] }}
            </span>

            <h1 class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight leading-tight">
                {{ $product['name'] }}
            </h1>

            <!-- Rating & Reviews -->
            <div class="flex items-center gap-3 text-sm">
                <div class="flex items-center text-amber-400">
                    <span>★★★★★</span>
                </div>
                <span class="font-bold text-gray-900">{{ $product['rating'] }}</span>
                <span class="text-gray-400">({{ $product['reviews_count'] }} reviews)</span>
            </div>

            <!-- Price -->
            <div class="flex items-baseline gap-3 pt-2">
                <span class="text-3xl font-black text-primary">
                    {{ number_format($product['sale_price'] ?? $product['price'], 0, ',', '.') }}đ
                </span>
                @if (!empty($product['sale_price']))
                    <span class="text-lg text-gray-400 line-through">
                        {{ number_format($product['price'], 0, ',', '.') }}đ
                    </span>
                @endif
            </div>

            <!-- Description -->
            <p class="text-sm text-gray-600 leading-relaxed">
                {{ $product['description'] }}
            </p>

            <hr class="border-gray-100">

            <!-- Color Options -->
            <div class="space-y-2.5">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-700">Color</span>
                <div class="flex items-center gap-3">
                    <button type="button" class="w-7 h-7 rounded-full bg-black ring-2 ring-offset-2 ring-primary focus:outline-none" title="Black"></button>
                    <button type="button" class="w-7 h-7 rounded-full bg-primary hover:scale-105 transition focus:outline-none" title="Red"></button>
                    <button type="button" class="w-7 h-7 rounded-full bg-gray-300 hover:scale-105 transition focus:outline-none" title="White/Silver"></button>
                </div>
            </div>

            <!-- Quantity & Actions (Figma Page 5) -->
            <div class="pt-4 space-y-4" x-data="{ qty: 1 }">
                
                <div class="space-y-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-700">Quantity</span>
                    <div class="flex items-center border border-gray-200 rounded-xl w-32 bg-[#F4F5F7] p-1">
                        <button type="button" @click="qty = Math.max(1, qty - 1)" class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-black font-bold">
                            –
                        </button>
                        <span class="flex-1 text-center font-bold text-sm text-gray-900" x-text="qty"></span>
                        <button type="button" @click="qty++" class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-black font-bold">
                            +
                        </button>
                    </div>
                </div>

                <!-- Add to cart & Buy now buttons -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    <button 
                        type="button" 
                        onclick="alert('Đã thêm {{ $product['name'] }} vào giỏ hàng!')"
                        class="py-3.5 px-6 rounded-2xl bg-primary hover:bg-primary-hover text-white font-bold text-sm shadow-lg shadow-primary/25 transition transform hover:-translate-y-0.5"
                    >
                        Add to cart
                    </button>
                    <a 
                        href="{{ url('/cart') }}"
                        class="py-3.5 px-6 rounded-2xl bg-secondary hover:bg-black text-white font-bold text-sm text-center shadow-md transition transform hover:-translate-y-0.5"
                    >
                        Buy now
                    </a>
                </div>

            </div>

            <!-- Guarantees Bar (Figma Page 5) -->
            <div class="pt-4 text-xs text-gray-500 flex flex-wrap gap-4 border-t border-gray-100">
                <span>✓ Miễn phí vận chuyển từ 500.000đ</span>
                <span>✓ Bảo hành 12 tháng chính hãng</span>
                <span>✓ Đổi trả 7 ngày miễn phí</span>
            </div>

        </div>

    </div>

    <!-- Details / Specifications Tabs (Figma Page 5) -->
    <div class="mt-20 pt-10 border-t border-gray-100" x-data="{ activeTab: 'desc' }">
        <div class="flex items-center gap-8 border-b border-gray-200 pb-4 text-sm font-bold">
            <button 
                @click="activeTab = 'desc'" 
                :class="activeTab === 'desc' ? 'text-primary border-b-2 border-primary -mb-4 pb-4' : 'text-gray-400 hover:text-gray-700'"
                class="transition"
            >
                Description
            </button>
            <button 
                @click="activeTab = 'specs'" 
                :class="activeTab === 'specs' ? 'text-primary border-b-2 border-primary -mb-4 pb-4' : 'text-gray-400 hover:text-gray-700'"
                class="transition"
            >
                Specifications
            </button>
            <button 
                @click="activeTab = 'reviews'" 
                :class="activeTab === 'reviews' ? 'text-primary border-b-2 border-primary -mb-4 pb-4' : 'text-gray-400 hover:text-gray-700'"
                class="transition"
            >
                Reviews ({{ $product['reviews_count'] }})
            </button>
        </div>

        <div class="pt-8 text-sm text-gray-600 leading-relaxed max-w-3xl">
            <div x-show="activeTab === 'desc'">
                <p>
                    {{ $product['description'] }}
                </p>
                <p class="mt-4">
                    Tai nghe chơi game HyperX Cloud III kế thừa và phát triển từ mẫu Cloud II huyền thoại, vốn nổi tiếng về sự thoải mái, chất lượng âm thanh và độ bền. Với đệm mút hoạt tính đặc trưng của HyperX trên headband và đệm tai, sản phẩm mang lại cảm giác thoải mái vừa vặn, hoàn hảo cho những buổi chơi game kéo dài.
                </p>
            </div>

            <div x-show="activeTab === 'specs'" style="display: none;">
                <dl class="divide-y divide-gray-100">
                    <div class="py-2.5 grid grid-cols-3">
                        <dt class="font-semibold text-gray-900">Driver âm thanh</dt>
                        <dd class="col-span-2 text-gray-600">Dynamic 53mm với nam châm Neodymium</dd>
                    </div>
                    <div class="py-2.5 grid grid-cols-3">
                        <dt class="font-semibold text-gray-900">Tần số đáp ứng</dt>
                        <dd class="col-span-2 text-gray-600">10Hz – 21kHz</dd>
                    </div>
                    <div class="py-2.5 grid grid-cols-3">
                        <dt class="font-semibold text-gray-900">Kết nối</dt>
                        <dd class="col-span-2 text-gray-600">USB-C, USB-A, Jack 3.5mm</dd>
                    </div>
                    <div class="py-2.5 grid grid-cols-3">
                        <dt class="font-semibold text-gray-900">Trọng lượng</dt>
                        <dd class="col-span-2 text-gray-600">308g (kèm mic 320g)</dd>
                    </div>
                </dl>
            </div>

            <div x-show="activeTab === 'reviews'" style="display: none;">
                <div class="space-y-4">
                    <div class="p-4 bg-gray-50 rounded-2xl">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-bold text-gray-900">Trần Hữu Thắng</span>
                            <span class="text-amber-400">★★★★★</span>
                        </div>
                        <p class="text-xs text-gray-600">Âm thanh cực kỳ chi tiết, đeo lâu không bị đau tai. Rất đáng tiền!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
