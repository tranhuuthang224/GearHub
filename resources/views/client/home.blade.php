@extends('layouts.client')

@section('title', 'GearHub - Thế Giới Gaming Gear & Setup Đỉnh Cao')
@section('meta_description', 'Khám phá thế giới gaming gear, bàn phím cơ, chuột không dây, tai nghe chính hãng và diễn đàn chia sẻ góc máy setup hiện đại tại GearHub.')
@section('og_image', asset('images/hero-banner-clean.png'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16 sm:space-y-24 pt-4 pb-12">

    <!-- 1. HERO BANNER CAROUSEL SLIDER (Hỗ trợ lướt nhiều ảnh sắc nét 1920x520, auto-slide & swipe) -->
    <section 
        x-data="heroSlider()" 
        x-init="startTimer()"
        @mouseenter="pauseTimer()"
        @mouseleave="startTimer()"
        @touchstart="touchStart($event)"
        @touchend="touchEnd($event)"
        class="relative block w-full rounded-2xl sm:rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 group select-none"
        :class="currentSlide === 0 ? 'bg-[#E6E6E6]' : 'bg-gray-950'"
    >
        <!-- Slide Container: tỷ lệ chuẩn 1920/520 khớp kích thước ảnh, nền đồng bộ cho từng slide -->
        <div 
            class="relative w-full overflow-hidden rounded-2xl sm:rounded-3xl flex items-center justify-center transition-colors duration-500 min-h-[160px] sm:min-h-[220px] md:min-h-[280px] lg:min-h-[340px]"
            :class="currentSlide === 0 ? 'bg-[#E6E6E6]' : 'bg-gray-950'"
            style="aspect-ratio: 1920 / 520;"
        >
            @foreach ($heroSlides as $index => $slide)
                <div 
                    x-show="currentSlide === {{ $index }}" 
                    x-transition:enter="transition ease-out duration-700"
                    x-transition:enter-start="opacity-0 scale-[1.01]"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-500 absolute inset-0"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-98"
                    class="w-full h-full flex items-center justify-center"
                    style="{{ $index === 0 ? '' : 'display: none;' }}"
                >
                    <a 
                        href="{{ $slide['link'] }}" 
                        class="block w-full h-full relative"
                        title="{{ $slide['title'] }}"
                    >
                        <img 
                            src="{{ $slide['image'] }}" 
                            alt="{{ $slide['alt'] }}" 
                            {{ $index === 0 ? 'fetchpriority="high"' : 'loading="lazy"' }}
                            decoding="async"
                            class="w-full h-full object-contain object-center rounded-2xl sm:rounded-3xl transition duration-500"
                            style="image-rendering: -webkit-optimize-contrast;"
                        >
                        <span class="sr-only">{{ $slide['title'] }}</span>
                    </a>
                </div>
            @endforeach

            <!-- Nút Prev (<) -->
            <button 
                type="button" 
                @click.prevent="prevSlide()" 
                class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 w-8 h-8 sm:w-11 sm:h-11 rounded-full bg-black/40 hover:bg-black/80 text-white backdrop-blur-md flex items-center justify-center transition shadow-lg opacity-0 group-hover:opacity-100 focus:opacity-100 hover:scale-105 z-20 cursor-pointer"
                title="Banner trước"
            >
                <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Nút Next (>) -->
            <button 
                type="button" 
                @click.prevent="nextSlide()" 
                class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 w-8 h-8 sm:w-11 sm:h-11 rounded-full bg-black/40 hover:bg-black/80 text-white backdrop-blur-md flex items-center justify-center transition shadow-lg opacity-0 group-hover:opacity-100 focus:opacity-100 hover:scale-105 z-20 cursor-pointer"
                title="Banner tiếp theo"
            >
                <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <!-- Indicator Dots (Thanh chỉ số chuyển ảnh) -->
            <div class="absolute bottom-2 sm:bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-1.5 sm:gap-2 z-20 bg-black/40 backdrop-blur-md px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-full">
                @foreach ($heroSlides as $index => $slide)
                    <button 
                        type="button" 
                        @click="goToSlide({{ $index }})" 
                        class="h-1.5 sm:h-2 rounded-full transition-all duration-300 cursor-pointer"
                        :class="currentSlide === {{ $index }} ? 'w-5 sm:w-6 bg-primary shadow-sm' : 'w-1.5 sm:w-2 bg-white/60 hover:bg-white'"
                        title="Đến banner {{ $index + 1 }}"
                    ></button>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 2. 6 FEATURED CATEGORIES SHOWCASE GRID (Matching Figma Screenshot Exactly) -->
    <style>
        @media (min-width: 640px) {
            .card-flex-400 { flex: 400 1 0% !important; min-width: 0 !important; }
            .card-flex-640 { flex: 640 1 0% !important; min-width: 0 !important; }
        }
    </style>
    <section class="space-y-6">
        <!-- Row 1: 1.png (Earphone) | 2.png (Mouse) | 3.png (Laptop) -->
        <div class="flex flex-col sm:flex-row gap-6 items-stretch">
            <a 
                href="{{ route('shop.index') }}" 
                class="card-flex-400 group block w-full rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition duration-500 transform hover:-translate-y-1 bg-white"
            >
                <img 
                    src="{{ asset('images/1.png') }}" 
                    alt="Enjoy With EARPHONE" 
                    loading="lazy"
                    decoding="async"
                    class="w-full h-full object-cover block rounded-3xl transform group-hover:scale-[1.02] transition duration-500"
                >
            </a>

            <a 
                href="{{ route('shop.index') }}" 
                class="card-flex-400 group block w-full rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition duration-500 transform hover:-translate-y-1 bg-white"
            >
                <img 
                    src="{{ asset('images/2.png') }}" 
                    alt="New Wireless MOUSE" 
                    loading="lazy"
                    decoding="async"
                    class="w-full h-full object-cover block rounded-3xl transform group-hover:scale-[1.02] transition duration-500"
                >
            </a>

            <a 
                href="{{ route('shop.index') }}" 
                class="card-flex-640 group block w-full rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition duration-500 transform hover:-translate-y-1 bg-white"
            >
                <img 
                    src="{{ asset('images/3.png') }}" 
                    alt="Best Gaming LAPTOP" 
                    loading="lazy"
                    decoding="async"
                    class="w-full h-full object-cover block rounded-3xl transform group-hover:scale-[1.02] transition duration-500"
                >
            </a>
        </div>

        <!-- Row 2: 4.png (Console) | 5.png (Deck) | 6.png (Speaker) -->
        <div class="flex flex-col sm:flex-row gap-6 items-stretch">
            <a 
                href="{{ route('shop.index') }}" 
                class="card-flex-640 group block w-full rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition duration-500 transform hover:-translate-y-1 bg-white"
            >
                <img 
                    src="{{ asset('images/4.png') }}" 
                    alt="New Trend CONSOLE" 
                    loading="lazy"
                    decoding="async"
                    class="w-full h-full object-cover block rounded-3xl transform group-hover:scale-[1.02] transition duration-500"
                >
            </a>

            <a 
                href="{{ route('shop.index') }}" 
                class="card-flex-400 group block w-full rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition duration-500 transform hover:-translate-y-1 bg-white"
            >
                <img 
                    src="{{ asset('images/5.png') }}" 
                    alt="Mechanical Elite DECK" 
                    loading="lazy"
                    decoding="async"
                    class="w-full h-full object-cover block rounded-3xl transform group-hover:scale-[1.02] transition duration-500"
                >
            </a>

            <a 
                href="{{ route('shop.index') }}" 
                class="card-flex-400 group block w-full rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition duration-500 transform hover:-translate-y-1 bg-white"
            >
                <img 
                    src="{{ asset('images/6.png') }}" 
                    alt="New Hyperx SPEAKER" 
                    loading="lazy"
                    decoding="async"
                    class="w-full h-full object-cover block rounded-3xl transform group-hover:scale-[1.02] transition duration-500"
                >
            </a>
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
                    <h4 class="text-sm font-bold text-gray-900">Giao hàng miễn phí</h4>
                    <p class="text-xs text-gray-400 mt-0.5">Miễn phí cho đơn từ 500.000đ</p>
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
                    <h4 class="text-sm font-bold text-gray-900">Bảo hành 1 đổi 1</h4>
                    <p class="text-xs text-gray-400 mt-0.5">Đổi mới trong 30 ngày nếu lỗi</p>
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
                    <h4 class="text-sm font-bold text-gray-900">Hỗ trợ kỹ thuật 24/7</h4>
                    <p class="text-xs text-gray-400 mt-0.5">Tư vấn setup & chọn gear tận tâm</p>
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
                    <h4 class="text-sm font-bold text-gray-900">Thanh toán an toàn COD</h4>
                    <p class="text-xs text-gray-400 mt-0.5">Đồng kiểm hàng trước khi thanh toán</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. PROMO BANNER: FINE SMILE / AUTUMN SALE (Matching Figma Screenshot Exactly) -->
    <section class="relative mt-8 sm:mt-16 bg-gradient-to-r from-[#E22A2D] to-[#7C1719] rounded-3xl p-8 sm:p-12 lg:p-14 text-white shadow-2xl">
        <div class="relative z-10 grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
            
            <!-- Left: 20% OFF & FINE SMILE -->
            <div class="md:col-span-4 space-y-1 sm:space-y-2">
                <span class="text-xs sm:text-sm font-semibold tracking-wider uppercase opacity-90 block">GIẢM ĐẾN 20%</span>
                <h2 class="text-5xl sm:text-7xl lg:text-8xl font-black tracking-tight leading-none uppercase text-white select-none">
                    FINE<br>SMILE
                </h2>
                <p class="text-xs sm:text-sm opacity-80 pt-1 font-medium">Áp dụng từ 01/11 đến 30/11</p>
            </div>

            <!-- Center: Floating Headphone (Overflowing on top like Figma) -->
            <div class="md:col-span-4 flex items-center justify-center relative z-20 -my-6 md:-my-16 lg:-my-20">
                <img 
                    src="{{ asset('images/png-transparent-playstation-4-headphones-hyperx-cloud-audio-video-game-headphones-electronics-video-game-playstation-4.png') }}" 
                    alt="HyperX Cloud Headset"
                    loading="lazy"
                    decoding="async"
                    class="max-h-[260px] sm:max-h-[320px] lg:max-h-[380px] w-auto object-contain drop-shadow-2xl transform hover:scale-105 transition duration-500"
                >
            </div>

            <!-- Right: Autumn Sale & CTA (Left-aligned as in Figma screenshot) -->
            <div class="md:col-span-4 space-y-2.5 sm:space-y-3 text-left">
                <span class="text-xs sm:text-sm font-medium tracking-wide opacity-90 block">Ưu Đãi Mùa Thu</span>
                <h3 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-white leading-tight">
                    Autumn Sale
                </h3>
                <p class="text-xs sm:text-sm opacity-85 leading-relaxed max-w-sm">
                    Nâng tầm góc máy của bạn với bộ sưu tập bàn phím cơ, chuột gaming và màn hình đồ họa chính hãng.
                </p>
                <div class="pt-2">
                    <a href="{{ url('/shop') }}" class="inline-flex items-center justify-center px-8 py-2.5 rounded-full bg-white text-[#E22A2D] font-bold text-sm shadow-md hover:bg-gray-50 transition transform hover:scale-105">
                        Khám Phá Cửa Hàng
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
                Sản Phẩm Bán Chạy Nhất
            </h2>
            <p class="text-xs sm:text-sm text-gray-500">
                Top gaming gear và màn hình máy tính được cộng đồng tin tưởng lựa chọn nhiều nhất
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

<script>
function heroSlider() {
    return {
        currentSlide: 0,
        totalSlides: {{ count($heroSlides) }},
        timer: null,
        touchStartX: 0,
        touchEndX: 0,
        startTimer() {
            this.pauseTimer();
            this.timer = setInterval(() => {
                this.nextSlide();
            }, 5000);
        },
        pauseTimer() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },
        nextSlide() {
            this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
        },
        prevSlide() {
            this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
        },
        goToSlide(index) {
            this.currentSlide = index;
            this.startTimer();
        },
        touchStart(e) {
            if (e.changedTouches && e.changedTouches.length > 0) {
                this.touchStartX = e.changedTouches[0].screenX;
            }
        },
        touchEnd(e) {
            if (e.changedTouches && e.changedTouches.length > 0) {
                this.touchEndX = e.changedTouches[0].screenX;
                const diff = this.touchEndX - this.touchStartX;
                if (Math.abs(diff) > 40) {
                    if (diff < 0) {
                        this.nextSlide();
                    } else {
                        this.prevSlide();
                    }
                    this.startTimer();
                }
            }
        }
    };
}
</script>
@endsection
