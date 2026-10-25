<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'GearHub - Phụ kiện công nghệ cao cấp' }}</title>

    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased bg-white text-gray-900 min-h-full flex flex-col selection:bg-primary selection:text-white" x-data="{ mobileMenuOpen: false, searchOpen: false }">

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
                        Home
                    </a>
                    <a href="{{ url('/shop') }}" class="{{ request()->is('shop*') ? 'text-primary font-bold' : 'text-gray-700 hover:text-primary' }} transition">
                        Shop
                    </a>
                    <a href="{{ url('/about') }}" class="{{ request()->is('about*') ? 'text-primary font-bold' : 'text-gray-700 hover:text-primary' }} transition">
                        About us
                    </a>
                    <a href="{{ url('/blog') }}" class="{{ request()->is('blog*') ? 'text-primary font-bold' : 'text-gray-700 hover:text-primary' }} transition">
                        Blog
                    </a>
                    <a href="{{ url('/contact') }}" class="{{ request()->is('contact*') ? 'text-primary font-bold' : 'text-gray-700 hover:text-primary' }} transition">
                        Contact
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
                        <span class="hidden sm:inline text-xs font-semibold">Cart</span>
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
                                <div class="w-8 h-8 rounded-full bg-secondary text-white flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                                    {{ substr(Auth::user()->name, 0, 2) }}
                                </div>
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
                            Login
                        </a>
                        <a href="{{ route('register') }}" class="hidden sm:inline-flex items-center justify-center px-4 py-2 text-xs font-bold text-white bg-primary hover:bg-primary-hover rounded-xl shadow-sm transition">
                            Register
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

            <!-- Expandable Search Bar on Click -->
            <div x-show="searchOpen" x-transition class="py-4 border-t border-gray-100" style="display: none;">
                <form action="{{ url('/shop') }}" method="GET" class="relative max-w-xl mx-auto">
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Tìm kiếm bàn phím cơ, chuột, tai nghe..." 
                        class="w-full rounded-2xl border border-gray-200 bg-[#F4F5F7] pl-11 pr-24 py-3 text-sm text-gray-900 focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition outline-none"
                    >
                    <svg class="w-5 h-5 absolute left-3.5 top-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <button type="submit" class="absolute right-2 top-2 px-4 py-1.5 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-semibold shadow-sm transition">
                        Tìm
                    </button>
                </form>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div x-show="mobileMenuOpen" x-transition class="md:hidden border-t border-gray-100 bg-white px-4 pt-3 pb-6 space-y-2 shadow-xl" style="display: none;">
            <a href="{{ url('/') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->is('/') ? 'bg-primary/10 text-primary font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                Home
            </a>
            <a href="{{ url('/shop') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->is('shop*') ? 'bg-primary/10 text-primary font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                Shop
            </a>
            <a href="{{ url('/about') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->is('about*') ? 'bg-primary/10 text-primary font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                About us
            </a>
            <a href="{{ url('/blog') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->is('blog*') ? 'bg-primary/10 text-primary font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                Blog
            </a>
            <a href="{{ url('/contact') }}" class="block px-3 py-2 rounded-lg text-base font-medium {{ request()->is('contact*') ? 'bg-primary/10 text-primary font-bold' : 'text-gray-700 hover:bg-gray-50' }}">
                Contact
            </a>
            @guest
                <div class="pt-4 border-t border-gray-100 flex gap-2">
                    <a href="{{ route('login') }}" class="w-1/2 text-center py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-800">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="w-1/2 text-center py-2.5 rounded-xl bg-primary text-white text-sm font-semibold">
                        Register
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
                    <h3 class="text-sm font-bold text-gray-900 tracking-wider mb-4">Quick links</h3>
                    <ul class="space-y-2.5 text-sm text-gray-500">
                        <li><a href="{{ url('/shop') }}" class="hover:text-primary transition">Shop</a></li>
                        <li><a href="{{ url('/about') }}" class="hover:text-primary transition">About</a></li>
                        <li><a href="{{ url('/blog') }}" class="hover:text-primary transition">Blog</a></li>
                        <li><a href="{{ url('/contact') }}" class="hover:text-primary transition">Contact</a></li>
                    </ul>
                </div>

                <!-- Col 3: Support -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 tracking-wider mb-4">Support</h3>
                    <ul class="space-y-2.5 text-sm text-gray-500">
                        <li><a href="#" class="hover:text-primary transition">FAQ</a></li>
                        <li><a href="#" class="hover:text-primary transition">Shipping</a></li>
                        <li><a href="#" class="hover:text-primary transition">Returns</a></li>
                        <li><a href="#" class="hover:text-primary transition">Privacy Policy</a></li>
                    </ul>
                </div>

                <!-- Col 4: Newsletter -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 tracking-wider mb-2">Subscribe for latest news</h3>
                    <p class="text-xs text-gray-500 mb-4">Nhận ưu đãi độc quyền & thông báo sản phẩm mới nhất.</p>
                    <form action="#" method="POST" class="space-y-2" onsubmit="event.preventDefault(); alert('Cảm ơn bạn đã đăng ký!');">
                        <input 
                            type="email" 
                            placeholder="Your email" 
                            required 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-[#F4F5F7] text-sm text-gray-900 focus:bg-white focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition"
                        >
                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold shadow-sm transition">
                            Subscribe
                        </button>
                    </form>
                    <div class="pt-4 text-xs text-gray-500">
                        <span class="font-medium text-gray-700">Hotline:</span> +0918919816
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

    <!-- Global Cart Feedback Toast -->
    <div 
        x-data="{ show: false, message: '' }" 
        x-on:cart-toast.window="message = $event.detail.message; show = true; setTimeout(() => show = false, 3500)"
        x-show="show" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-6 right-6 z-50 bg-[#18181B] text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-gray-800 flex items-center gap-3 text-xs"
        style="display: none;"
    >
        <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">✓</span>
        <span x-text="message" class="font-medium"></span>
        <a href="{{ route('cart.index') }}" class="font-bold underline text-primary hover:text-white ml-2">Xem giỏ hàng</a>
    </div>

    <script>
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
