<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Primary Meta SEO -->
    <title>@yield('title', $title ?? 'GearHub - Thế Giới Gaming Gear & Setup Đỉnh Cao')</title>
    <meta name="title" content="@yield('title', $title ?? 'GearHub - Thế Giới Gaming Gear & Setup Đỉnh Cao')">
    <meta name="description" content="@yield('meta_description', 'Khám phá thế giới gaming gear, bàn phím cơ, chuột không dây, tai nghe chính hãng và diễn đàn chia sẻ góc máy setup hiện đại tại GearHub.')">
    <meta name="keywords" content="@yield('meta_keywords', 'gearhub, gaming gear, bàn phím cơ, chuột gaming, tai nghe, màn hình, góc máy setup, đồ chơi công nghệ, phụ kiện pc')">
    <meta name="author" content="GearHub Team">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="@yield('canonical_url', url()->current())">
    <meta name="theme-color" content="#FF0000">

    <!-- Open Graph / Facebook / Zalo Preview Protocol -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="GearHub">
    <meta property="og:url" content="@yield('canonical_url', url()->current())">
    <meta property="og:title" content="@yield('og_title', View::yieldContent('title', $title ?? 'GearHub - Thế Giới Gaming Gear & Setup Đỉnh Cao'))">
    <meta property="og:description" content="@yield('meta_description', 'Khám phá thế giới gaming gear, bàn phím cơ, chuột không dây, tai nghe chính hãng và diễn đàn chia sẻ góc máy setup hiện đại tại GearHub.')">
    <meta property="og:image" content="@yield('og_image', asset('images/hero-banner-clean.png'))">
    <meta property="og:image:alt" content="GearHub Gaming Gear & Setup">
    <meta property="og:locale" content="vi_VN">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="@yield('canonical_url', url()->current())">
    <meta name="twitter:title" content="@yield('og_title', View::yieldContent('title', $title ?? 'GearHub - Thế Giới Gaming Gear & Setup Đỉnh Cao'))">
    <meta name="twitter:description" content="@yield('meta_description', 'Khám phá thế giới gaming gear, bàn phím cơ, chuột không dây, tai nghe chính hãng và diễn đàn chia sẻ góc máy setup hiện đại tại GearHub.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/hero-banner-clean.png'))">

    @stack('meta')

    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased bg-white text-gray-900 min-h-full flex flex-col selection:bg-primary selection:text-white overflow-x-hidden" x-data="{ mobileMenuOpen: false, searchOpen: false }">

    <!-- Top Banner Notification (Optional announcement) -->
    <div class="bg-secondary text-white text-xs py-2 px-4 text-center font-medium">
        <span>Miễn phí vận chuyển cho đơn hàng từ 500.000đ • Đổi trả dễ dàng trong 30 ngày</span>
    </div>

    <!-- Main Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-gray-100 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                
                <!-- Brand Logo (Figma Style) -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-1 group">
                        <span class="text-2xl sm:text-3xl font-black tracking-tighter text-primary group-hover:opacity-90 transition">
                            GEAR<span class="text-secondary">HUB</span>
                        </span>
                    </a>
                </div>

                <!-- Desktop Navigation Menu -->
                <nav class="hidden md:flex items-center gap-8 text-sm font-medium">
                    <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'text-primary font-bold' : 'text-gray-700 hover:text-primary' }} transition">
                        Trang chủ
                    </a>
                    <a href="{{ url('/shop') }}" class="{{ request()->is('shop*') ? 'text-primary font-bold' : 'text-gray-700 hover:text-primary' }} transition">
                        Cửa hàng
                    </a>
                    <a href="{{ route('compare.index') }}" class="{{ request()->is('compare*') ? 'text-primary font-bold' : 'text-gray-700 hover:text-primary' }} transition flex items-center gap-1.5">
                        <span>So sánh Gear</span>
                    </a>
                    <a href="{{ route('quiz.index') }}" class="{{ request()->is('quiz*') ? 'text-primary font-bold' : 'text-gray-700 hover:text-primary' }} transition flex items-center gap-1.5">
                        <span>Tư vấn chọn Gear</span>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200 uppercase leading-none">Quiz</span>
                    </a>
                    <a href="{{ route('community.index') }}" class="{{ request()->is('community*') ? 'text-primary font-bold' : 'text-gray-700 hover:text-primary' }} transition flex items-center gap-1.5">
                        <span>Khoe góc máy</span>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 uppercase leading-none">Mới</span>
                    </a>
                </nav>

                <!-- Right Actions: Search, Cart, Account -->
                <div class="flex items-center gap-4 sm:gap-6">
                    
                    <!-- Search Button / Toggle -->
                    <button 
                        type="button" 
                        @click="searchOpen = !searchOpen" 
                        class="text-gray-600 hover:text-primary transition p-1.5 rounded-full hover:bg-gray-100"
                        title="Tìm kiếm"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>

                    <!-- Cart Link with Badge -->
                    <a href="{{ route('cart.index') }}" class="relative text-gray-700 hover:text-primary transition flex items-center gap-1.5 p-1.5 rounded-full hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span class="hidden sm:inline text-xs font-semibold">Giỏ hàng</span>
                        @php
                            $cart = session('cart', []);
                            $cartCount = array_sum(array_column($cart, 'quantity'));
                        @endphp
                        <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white bg-primary rounded-full">
                            {{ $cartCount > 0 ? $cartCount : '0' }}
                        </span>
                    </a>

                    <!-- User Account / Login Button -->
                    @auth
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 text-sm font-semibold text-gray-700 hover:text-primary transition focus:outline-none">
                                @if (Auth::user()->avatar_url)
                                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" loading="lazy" decoding="async" class="w-8 h-8 rounded-full object-cover ring-2 ring-gray-100 shadow-sm">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-secondary text-white flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                                        {{ substr(Auth::user()->name, 0, 2) }}
                                    </div>
                                @endif
                                <span class="hidden sm:inline line-clamp-1 max-w-[120px]">{{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div 
                                x-show="open" 
                                @click.outside="open = false" 
                                x-transition:enter="transition ease-out duration-100" 
                                x-transition:enter-start="transform opacity-0 scale-95" 
                                x-transition:enter-end="transform opacity-100 scale-100" 
                                x-transition:leave="transition ease-in duration-75" 
                                x-transition:leave-start="transform opacity-100 scale-100" 
                                x-transition:leave-end="transform opacity-0 scale-95" 
                                class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 z-50 text-sm"
                                style="display: none;"
                            >
                                @if (Auth::user()->role === 'admin')
                                    <a href="{{ url('/admin/dashboard') }}" class="block px-4 py-2 text-primary font-semibold hover:bg-primary/5 transition">
                                        ⚡ Quản trị Admin
                                    </a>
                                    <hr class="my-1 border-gray-100">
                                @endif
                                <a href="{{ url('/profile') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition">
                                    Hồ sơ tài khoản
                                </a>
                                <a href="{{ url('/orders') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 transition">
                                    Đơn hàng của tôi
                                </a>
                                <hr class="my-1 border-gray-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50 transition">
                                        Đăng xuất
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-800 hover:text-primary transition px-3 py-1.5">
                            Đăng nhập
                        </a>
                        <a href="{{ route('register') }}" class="hidden sm:inline-flex items-center justify-center px-4 py-2 text-xs font-bold text-white bg-primary hover:bg-primary-hover rounded-xl shadow-sm transition">
                            Đăng ký
                        </a>
                    @endauth

                    <!-- Mobile Menu Hamburger Button -->
                    <button 
                        type="button" 
                        @click="mobileMenuOpen = !mobileMenuOpen" 
                        class="md:hidden p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100"
                    >
                        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Expandable Search Bar on Click with Live Skeleton & Instant Suggestions -->
            <div 
                x-show="searchOpen" 
                x-transition 
                class="py-4 border-t border-gray-100 relative" 
                style="display: none;"
                x-data="{
                    query: '',
                    results: [],
                    loading: false,
                    hasSearched: false,
                    debounceTimer: null,
                    handleInput() {
                        clearTimeout(this.debounceTimer);
                        const q = this.query.trim();
                        if (q.length < 2) {
                            this.results = [];
                            this.loading = false;
                            this.hasSearched = false;
                            return;
                        }
                        this.loading = true;
                        this.hasSearched = true;
                        this.debounceTimer = setTimeout(() => {
                            fetch('{{ route('community.search-products') }}?q=' + encodeURIComponent(q))
                                .then(res => res.json())
                                .then(data => {
                                    this.results = data || [];
                                    this.loading = false;
                                })
                                .catch(() => {
                                    this.loading = false;
                                });
                        }, 280);
                    }
                }"
                @click.away="results = []; hasSearched = false"
            >
                <form action="{{ url('/shop') }}" method="GET" class="relative max-w-xl mx-auto">
                    <input 
                        type="text" 
                        name="search" 
                        x-model="query"
                        @input="handleInput()"
                        @keydown.escape="searchOpen = false"
                        placeholder="Tìm kiếm bàn phím cơ, chuột, tai nghe..." 
                        class="w-full rounded-2xl border border-gray-200 bg-[#F4F5F7] pl-11 pr-24 py-3 text-sm text-gray-900 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition outline-none"
                    >
                    <svg class="w-5 h-5 absolute left-3.5 top-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <button type="submit" class="absolute right-2 top-2 px-4 py-1.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-semibold shadow-sm transition">
                        Tìm
                    </button>

                    <!-- Live Instant Dropdown with Skeleton State -->
                    <div 
                        x-show="hasSearched && query.trim().length >= 2" 
                        x-transition
                        class="absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl shadow-2xl border border-gray-200/90 overflow-hidden z-50 text-left"
                        style="display: none;"
                    >
                        <!-- Skeleton Loading State -->
                        <div x-show="loading" class="p-3 space-y-2.5">
                            <div class="px-2 py-1 text-[11px] font-bold text-gray-400 uppercase tracking-wider flex items-center justify-between">
                                <span>Đang tìm kiếm...</span>
                                <span class="w-3.5 h-3.5 border-2 border-primary border-t-transparent rounded-full animate-spin"></span>
                            </div>
                            <div class="flex items-center gap-3 p-2 rounded-xl bg-gray-50 animate-pulse">
                                <div class="w-10 h-10 rounded-lg bg-gray-200 shrink-0"></div>
                                <div class="flex-1 space-y-1.5">
                                    <div class="h-3 w-3/4 bg-gray-200 rounded"></div>
                                    <div class="h-2.5 w-1/3 bg-gray-200 rounded"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-2 rounded-xl bg-gray-50 animate-pulse">
                                <div class="w-10 h-10 rounded-lg bg-gray-200 shrink-0"></div>
                                <div class="flex-1 space-y-1.5">
                                    <div class="h-3 w-1/2 bg-gray-200 rounded"></div>
                                    <div class="h-2.5 w-1/4 bg-gray-200 rounded"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Results List -->
                        <div x-show="!loading && results.length > 0">
                            <div class="p-2 border-b border-gray-100 bg-[#F8FAFC] flex items-center justify-between text-[11px] text-gray-500 font-bold px-3">
                                <span>GỢI Ý SẢN PHẨM KHỚP NHẤT</span>
                                <span x-text="results.length + ' kết quả'"></span>
                            </div>
                            <div class="max-h-72 overflow-y-auto divide-y divide-gray-50 p-1">
                                <template x-for="item in results" :key="item.id">
                                    <a 
                                        :href="'{{ url('/shop') }}/' + item.slug" 
                                        class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-gray-50 transition group"
                                    >
                                        <img 
                                            :src="item.thumbnail" 
                                            :alt="item.name" 
                                            loading="lazy"
                                            decoding="async"
                                            class="w-11 h-11 rounded-lg object-contain bg-white border border-gray-100 p-0.5 shrink-0 group-hover:scale-105 transition"
                                        >
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-xs font-bold text-gray-900 truncate group-hover:text-primary transition" x-text="item.name"></h4>
                                            <p class="text-[11px] text-gray-400 font-mono" x-text="'SKU: ' + item.sku"></p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <span class="text-xs font-black text-primary" x-text="item.formatted_price"></span>
                                        </div>
                                    </a>
                                </template>
                            </div>
                            <div class="p-2 bg-gray-50 border-t border-gray-100 text-center">
                                <button 
                                    type="submit" 
                                    class="text-xs font-bold text-primary hover:underline flex items-center justify-center gap-1 w-full py-1"
                                >
                                    <span>Xem toàn bộ kết quả trong Shop</span>
                                    <span>→</span>
                                </button>
                            </div>
                        </div>

                        <!-- Empty State in Live Search -->
                        <div x-show="!loading && results.length === 0" class="p-6 text-center space-y-2">
                            <div class="text-2xl">🔍</div>
                            <p class="text-xs font-bold text-gray-800">Không tìm thấy sản phẩm nào khớp với "<span x-text="query"></span>"</p>
                            <p class="text-[11px] text-gray-400">Thử tìm kiếm với từ khóa ngắn gọn hơn như "phím", "chuột", "tai nghe"...</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden border-t border-gray-100 bg-white px-4 pt-3 pb-6 space-y-2 shadow-xl" style="display: none;">
            <a href="{{ url('/') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->is('/') ? 'bg-primary/10 text-primary font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                Trang chủ
            </a>
            <a href="{{ url('/shop') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->is('shop*') ? 'bg-primary/10 text-primary font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                Cửa hàng
            </a>
            <a href="{{ route('compare.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-base font-medium {{ request()->is('compare*') ? 'bg-primary/10 text-primary font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <span>So sánh Gear</span>
            </a>
            <a href="{{ route('quiz.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-base font-medium {{ request()->is('quiz*') ? 'bg-primary/10 text-primary font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <span>Tư vấn chọn Gear (Quiz)</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">Quiz</span>
            </a>
            <a href="{{ route('community.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-base font-medium {{ request()->is('community*') ? 'bg-primary/10 text-primary font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                <span>Khoe góc máy</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">Mới</span>
            </a>
            @guest
                <div class="pt-4 border-t border-gray-100 flex gap-2">
                    <a href="{{ route('login') }}" class="w-1/2 text-center py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-800">
                        Đăng nhập
                    </a>
                    <a href="{{ route('register') }}" class="w-1/2 text-center py-2.5 rounded-xl bg-primary text-white text-sm font-semibold">
                        Đăng ký
                    </a>
                </div>
            @endguest
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Footer Matching Figma Designs (Page 1, 4, 5, 6, 10) -->
    <footer class="bg-white border-t border-gray-100 pt-16 pb-12 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 lg:gap-8 pb-12 border-b border-gray-100">
                
                <!-- Col 1: Brand Info -->
                <div class="lg:col-span-2 space-y-4">
                    <a href="{{ url('/') }}" class="inline-block">
                        <span class="text-2xl font-black tracking-tight text-primary">GEAR<span class="text-secondary">HUB</span></span>
                    </a>
                    <p class="text-sm text-gray-500 max-w-sm leading-relaxed">
                        Gaming gear & accessories for everyone. Chuyên cung cấp phụ kiện máy tính, bàn phím cơ, chuột gaming, tai nghe và góc setup công nghệ chính hãng.
                    </p>
                    <div class="flex items-center gap-4 pt-2 text-gray-400">
                        <a href="#" class="hover:text-primary transition" aria-label="Instagram">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="#" class="hover:text-primary transition" aria-label="X / Twitter">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        <a href="#" class="hover:text-primary transition" aria-label="Facebook">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" class="hover:text-primary transition" aria-label="LinkedIn">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Col 2: Quick links -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 tracking-wider mb-4">Liên kết nhanh</h3>
                    <ul class="space-y-2.5 text-sm text-gray-500">
                        <li><a href="{{ url('/') }}" class="hover:text-primary transition">Trang chủ</a></li>
                        <li><a href="{{ url('/shop') }}" class="hover:text-primary transition">Cửa hàng sản phẩm</a></li>
                        <li><a href="{{ route('compare.index') }}" class="hover:text-primary transition">So sánh Gear</a></li>
                        <li><a href="{{ route('quiz.index') }}" class="hover:text-primary transition">Tư vấn chọn Gear</a></li>
                        <li><a href="{{ route('community.index') }}" class="hover:text-primary transition">Diễn đàn Góc máy setup</a></li>
                    </ul>
                </div>

                <!-- Col 3: Support -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 tracking-wider mb-4">Chính sách & Hỗ trợ</h3>
                    <ul class="space-y-2.5 text-sm text-gray-500">
                        <li><a href="{{ url('/orders') }}" class="hover:text-primary transition">Tra cứu đơn hàng</a></li>
                        <li><a href="{{ url('/shop') }}" class="hover:text-primary transition">Bảo hành 12 - 24 tháng</a></li>
                        <li><a href="{{ url('/shop') }}" class="hover:text-primary transition">Đổi trả 1 đổi 1 trong 30 ngày</a></li>
                        <li><a href="{{ url('/shop') }}" class="hover:text-primary transition">Đồng kiểm hàng khi nhận COD</a></li>
                    </ul>
                </div>

                <!-- Col 4: Newsletter -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 tracking-wider mb-2">Đăng ký nhận ưu đãi</h3>
                    <p class="text-xs text-gray-500 mb-4">Nhận voucher độc quyền & thông báo sản phẩm mới nhất.</p>
                    <form action="#" method="POST" class="space-y-2" onsubmit="event.preventDefault(); alert('Cảm ơn bạn đã đăng ký nhận ưu đãi!');">
                        <input 
                            type="email" 
                            placeholder="Nhập email của bạn..." 
                            required 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-[#F4F5F7] text-sm text-gray-900 focus:bg-white focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition"
                        >
                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold shadow-sm transition">
                            Đăng ký nhận tin
                        </button>
                    </form>
                    <div class="pt-4 text-xs text-gray-500 space-y-1">
                        <p><span class="font-medium text-gray-700">Hotline:</span> 1900 6868 (8:00 - 21:30)</p>
                        <p><span class="font-medium text-gray-700">Email:</span> contact@gearhub.vn</p>
                    </div>
                </div>

            </div>

            <!-- Bottom Copyright -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-400 gap-4">
                <p>© 2026 GearHub. All rights reserved.</p>
                <p>Đồ án môn học Web Dev • Nhóm 2 thành viên</p>
            </div>
        </div>
    </footer>

    <!-- Global Sticky Compare Bar (Thanh công cụ So sánh dính đáy) -->
    <x-sticky-compare-bar />

    <!-- Global Unified Toast Notification Stack -->
    <div 
        x-data="globalToastSystem()" 
        class="fixed bottom-6 right-6 z-50 flex flex-col gap-2.5 max-w-sm sm:max-w-md w-full pointer-events-none"
        style="display: none;"
        x-show="toasts.length > 0"
    >
        <template x-for="t in toasts" :key="t.id">
            <div 
                x-show="t.visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-x-4 scale-95"
                class="pointer-events-auto w-full p-4 rounded-2xl shadow-2xl border backdrop-blur-md flex items-start gap-3 transition-all bg-[#18181B] text-white"
                :class="{
                    'border-emerald-500/40 shadow-emerald-950/30': t.type === 'cart' || t.type === 'success',
                    'border-primary/50 shadow-primary/20': t.type === 'compare',
                    'border-rose-500/40 shadow-rose-950/30': t.type === 'community' || t.type === 'like',
                    'border-red-600/50 shadow-red-950/30': t.type === 'error',
                    'border-amber-500/40 shadow-amber-950/30': t.type === 'warning',
                    'border-gray-800 shadow-black/40': !['cart', 'success', 'compare', 'community', 'like', 'error', 'warning'].includes(t.type)
                }"
            >
                <!-- Icon Badge -->
                <div class="shrink-0 mt-0.5">
                    <template x-if="t.type === 'cart' || t.type === 'success'">
                        <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">✓</span>
                    </template>
                    <template x-if="t.type === 'compare'">
                        <span class="w-6 h-6 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold text-xs">⚖</span>
                    </template>
                    <template x-if="t.type === 'community' || t.type === 'like'">
                        <span class="w-6 h-6 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center font-bold text-xs">❤️</span>
                    </template>
                    <template x-if="t.type === 'error'">
                        <span class="w-6 h-6 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center font-bold text-xs">✕</span>
                    </template>
                    <template x-if="t.type === 'warning'">
                        <span class="w-6 h-6 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-xs">⚠</span>
                    </template>
                    <template x-if="!['cart', 'success', 'compare', 'community', 'like', 'error', 'warning'].includes(t.type)">
                        <span class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold text-xs">ℹ</span>
                    </template>
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0 space-y-1">
                    <template x-if="t.title">
                        <h4 class="text-xs font-bold text-gray-200 tracking-tight" x-text="t.title"></h4>
                    </template>
                    <p class="text-xs font-medium text-gray-300 leading-relaxed" x-text="t.message"></p>
                    <template x-if="t.link">
                        <div class="pt-1">
                            <a :href="t.link" class="text-xs font-bold text-primary hover:text-red-400 underline inline-flex items-center gap-1 transition">
                                <span x-text="t.linkText || 'Xem chi tiết'"></span>
                                <span>→</span>
                            </a>
                        </div>
                    </template>
                </div>

                <!-- Dismiss Button -->
                <button 
                    type="button" 
                    @click="remove(t.id)" 
                    class="text-gray-400 hover:text-white transition p-1 rounded-lg hover:bg-white/10 shrink-0"
                    title="Đóng thông báo"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <script>
        function globalToastSystem() {
            return {
                toasts: [],
                init() {
                    window.addEventListener('toast', (e) => this.add(e.detail));
                    window.addEventListener('cart-toast', (e) => {
                        this.add({
                            type: 'cart',
                            title: 'Giỏ hàng GearHub',
                            message: e.detail?.message || 'Đã thêm sản phẩm vào giỏ thành công!',
                            link: '{{ route('cart.index') }}',
                            linkText: 'Xem giỏ hàng'
                        });
                    });
                    window.addEventListener('compare-toast', (e) => {
                        this.add({
                            type: 'compare',
                            title: 'So sánh thông số',
                            message: e.detail?.message || 'Đã cập nhật danh sách so sánh!',
                            link: '{{ route('compare.index') }}',
                            linkText: 'So sánh ngay'
                        });
                    });
                    window.addEventListener('community-toast', (e) => {
                        this.add({
                            type: 'community',
                            title: 'Góc máy GearHub',
                            message: e.detail?.message || 'Đã cập nhật bài viết!'
                        });
                    });

                    // Laravel session flash message support
                    @if (session('success'))
                        this.add({ type: 'success', title: 'Thành công', message: @js(session('success')) });
                    @endif
                    @if (session('error'))
                        this.add({ type: 'error', title: 'Thông báo lỗi', message: @js(session('error')) });
                    @endif
                    @if (session('warning'))
                        this.add({ type: 'warning', title: 'Cảnh báo', message: @js(session('warning')) });
                    @endif
                    @if (session('status'))
                        this.add({ type: 'info', title: 'Thông báo', message: @js(session('status')) });
                    @endif
                },
                add(item) {
                    if (!item || !item.message) return;
                    const id = Date.now() + Math.random().toString(36).substr(2, 4);
                    const toast = {
                        id: id,
                        type: item.type || 'info',
                        title: item.title || '',
                        message: item.message,
                        link: item.link || null,
                        linkText: item.linkText || 'Xem chi tiết',
                        visible: true
                    };
                    this.toasts.push(toast);

                    setTimeout(() => {
                        this.remove(id);
                    }, 3800);
                },
                remove(id) {
                    const index = this.toasts.findIndex(t => t.id === id);
                    if (index !== -1) {
                        this.toasts[index].visible = false;
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(t => t.id !== id);
                        }, 300);
                    }
                }
            };
        }

        window.addEventListener('add-to-cart', function(e) {
            const productId = e.detail?.id;
            if (!productId) return;

            fetch('{{ route('cart.add') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ product_id: productId, quantity: 1 })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.dispatchEvent(new CustomEvent('cart-toast', { detail: { message: data.message } }));
                    const badge = document.querySelector('a[href*="cart"] span.bg-primary');
                    if (badge && data.cartCount !== undefined) {
                        badge.innerText = data.cartCount;
                    }
                }
            })
            .catch(console.error);
        });
    </script>

    @stack('scripts')
</body>
</html>
