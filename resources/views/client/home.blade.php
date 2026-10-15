@extends('layouts.client')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16 sm:space-y-24 pt-4 pb-12">

    <!-- 1. HERO BANNER SECTION (Matching Figma Page 1) -->
    <section class="relative bg-[#F4F5F7] rounded-3xl overflow-hidden min-h-[460px] sm:min-h-[520px] flex items-center p-8 sm:p-14 lg:p-16">
        <!-- Giant Watermark Typography in Background -->
        <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-gray-200/60 font-black text-6xl sm:text-9xl lg:text-[180px] tracking-widest select-none pointer-events-none uppercase">
            HEADPHONE
        </span>

        <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center w-full">
            <!-- Left Hero Content -->
            <div class="lg:col-span-7 space-y-4 sm:space-y-6">
                <span class="inline-block text-xs sm:text-sm font-bold tracking-widest uppercase text-gray-500">
                    HyperX Cloud • Wireless
                </span>
                
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-gray-900 tracking-tight leading-none uppercase">
                    HEADPHONE
                </h1>

                <p class="text-sm sm:text-base text-gray-500 max-w-lg leading-relaxed line-clamp-2 sm:line-clamp-3">
                    Đắm chìm trong âm thanh vòm sống động với màng loa kép 53mm, đệm tai mút hoạt tính êm ái cùng thời lượng pin lên đến 300 giờ.
                </p>

                <div class="pt-2 flex items-center gap-4">
                    <a href="{{ url('/shop?category=headphones') }}" class="inline-flex items-center justify-center px-7 py-3.5 rounded-full bg-primary hover:bg-primary-hover text-white text-sm font-bold shadow-lg shadow-primary/30 transition transform hover:-translate-y-0.5">
                        Shop by Category
                    </a>
                </div>
            </div>

            <!-- Right Hero Image -->
            <div class="lg:col-span-5 flex items-center justify-center">
                <img 
                    src="{{ asset('images/png-transparent-playstation-4-headphones-hyperx-cloud-audio-video-game-headphones-electronics-video-game-playstation-4.png') }}" 
                    alt="HyperX Cloud Wireless Headphone"
                    class="max-h-[320px] sm:max-h-[400px] w-auto object-contain drop-shadow-2xl transform hover:scale-105 transition duration-500"
                >
            </div>
        </div>
    </section>

    <!-- 2. 6 FEATURED CATEGORIES SHOWCASE GRID (Matching Figma Page 1) -->
    <section>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($featuredCategories as $cat)
                <div class="relative {{ $cat['bg'] }} rounded-3xl p-7 min-h-[220px] sm:min-h-[240px] flex flex-col justify-between overflow-hidden group transition duration-300 hover:shadow-xl">
                    <!-- Text & CTA -->
                    <div class="relative z-10 space-y-1 max-w-[55%]">
                        <span class="text-xs font-semibold tracking-wider uppercase {{ !empty($cat['isLight']) ? 'text-gray-600' : 'text-white/80' }}">
                            {{ $cat['title'] }}
                        </span>
                        <h3 class="text-xl sm:text-2xl font-black tracking-tight uppercase leading-tight {{ !empty($cat['isLight']) ? 'text-gray-900' : 'text-white' }}">
                            {{ $cat['name'] }}
                        </h3>
                    </div>

                    <div class="relative z-10 pt-4">
                        <a href="{{ url('/shop?category=' . $cat['slug']) }}" class="inline-flex items-center justify-center px-5 py-2 rounded-full text-xs font-bold shadow-sm transition transform group-hover:scale-105 {{ $cat['btnColor'] }}">
                            Browse
                        </a>
                    </div>

                    <!-- Category Floating Image -->
                    <div class="absolute -right-3 -bottom-3 w-[55%] h-[90%] flex items-end justify-end pointer-events-none">
                        <img 
                            src="{{ $cat['image'] }}" 
                            alt="{{ $cat['name'] }}" 
                            class="max-h-full max-w-full object-contain transform group-hover:scale-110 transition duration-500 drop-shadow-lg"
                        >
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- 3. SERVICES VALUE PROPOSITIONS (Matching Figma Page 1) -->
    <section class="border-y border-gray-100 py-10">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Free Shipping -->
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900">Free Shipping</h4>
                    <p class="text-xs text-gray-400 mt-0.5">Free Shipping On All Order</p>
                </div>
            </div>

            <!-- Money Guarantee -->
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900">Money Guarantee</h4>
                    <p class="text-xs text-gray-400 mt-0.5">30 Day On Back Guarantee</p>
                </div>
            </div>

            <!-- Online Support 24/7 -->
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.166a1 1 0 11-1.414 1.414" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900">Online Support 24/7</h4>
                    <p class="text-xs text-gray-400 mt-0.5">Technical Support 24/7</p>
                </div>
            </div>

            <!-- Secure Payment -->
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-gray-900">Secure Payment</h4>
                    <p class="text-xs text-gray-400 mt-0.5">All Cards Accepted & COD</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. PROMO BANNER: FINE SMILE / AUTUMN SALE (Matching Figma Page 1 & 3) -->
    <section class="relative bg-primary rounded-3xl p-8 sm:p-14 lg:p-16 text-white overflow-hidden shadow-2xl">
        <div class="relative z-10 grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
            
            <!-- Left: 20% OFF & FINE SMILE -->
            <div class="md:col-span-5 space-y-2">
                <span class="text-xs sm:text-sm font-bold tracking-widest uppercase opacity-90">20% OFF</span>
                <h2 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight leading-none uppercase">
                    FINE<br>SMILE
                </h2>
                <p class="text-xs sm:text-sm opacity-80 pt-2 font-medium">5 Apr to 22 Apr</p>
            </div>

            <!-- Center: Floating Headphone -->
            <div class="md:col-span-3 flex items-center justify-center -my-6 md:-my-12">
                <img 
                    src="{{ asset('images/hero-headphone.png') }}" 
                    alt="HyperX Headset Promo"
                    class="max-h-[220px] sm:max-h-[280px] object-contain drop-shadow-2xl transform hover:scale-110 transition duration-500"
                >
            </div>

            <!-- Right: Autumn Sale & CTA -->
            <div class="md:col-span-4 space-y-4 md:text-right">
                <span class="text-xs font-semibold tracking-wider uppercase opacity-90 block">HyperX Cloud</span>
                <h3 class="text-2xl sm:text-4xl font-black tracking-tight">Autumn Sale</h3>
                <p class="text-xs sm:text-sm opacity-85 leading-relaxed max-w-sm md:ml-auto">
                    Cơ hội nâng cấp dàn phụ kiện đỉnh cao với mức giá ưu đãi nhất mùa thu này.
                </p>
                <div class="pt-2">
                    <a href="{{ url('/shop') }}" class="inline-flex items-center justify-center px-8 py-3 rounded-full bg-white text-primary font-bold text-sm shadow-md hover:bg-gray-100 transition transform hover:scale-105">
                        Shop
                    </a>
                </div>
            </div>

        </div>
    </section>

    <!-- 5. BEST SELLER PRODUCTS SECTION (Matching Figma Page 1) -->
    <section class="space-y-8">
        <!-- Section Header -->
        <div class="text-center max-w-2xl mx-auto space-y-2">
            <h2 class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight">
                Best Seller Products
            </h2>
            <p class="text-xs sm:text-sm text-gray-500">
                Sản phẩm được cộng đồng game thủ và lập trình viên tin tưởng lựa chọn nhiều nhất
            </p>
        </div>

        <!-- Product Grid (8 products from Figma Page 1) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6 lg:gap-8">
            @foreach ($bestSellerProducts as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </section>

    <!-- 6. BRAND PARTNERS LOGO BAR (Matching Figma Page 1) -->
    <section class="pt-8 pb-4">
        <div class="flex items-center justify-around flex-wrap gap-8 opacity-40 grayscale hover:grayscale-0 transition-all duration-300">
            <span class="text-2xl font-black tracking-tight text-gray-700">Daraz</span>
            <span class="text-2xl font-black tracking-tight text-gray-700">aws</span>
            <span class="text-2xl font-black tracking-tight text-gray-700">shopify</span>
            <span class="text-2xl font-black tracking-tight text-gray-700">AliExpress</span>
            <span class="text-2xl font-black tracking-tight text-gray-700">LOGITECH</span>
            <span class="text-2xl font-black tracking-tight text-gray-700">RAZER</span>
        </div>
    </section>

</div>
@endsection
