<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin Portal' }} - GearHub</title>

    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans antialiased bg-[#F8FAFC] text-gray-900 h-full" x-data="{ sidebarOpen: false }">

    <div class="min-h-full flex">
        
        <!-- Mobile Sidebar Overlay Backdrop -->
        <div 
            x-show="sidebarOpen" 
            @click="sidebarOpen = false" 
            x-transition:enter="transition-opacity ease-linear duration-300" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            x-transition:leave="transition-opacity ease-linear duration-300" 
            x-transition:leave-start="opacity-100" 
            x-transition:leave-end="opacity-0" 
            class="fixed inset-0 bg-black/60 z-40 lg:hidden"
            style="display: none;"
        ></div>

        <!-- Sidebar Navigation (Matching Figma Page 14-20: Dark Background #18181B) -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" 
            class="fixed inset-y-0 left-0 z-50 w-64 bg-[#18181B] text-white flex flex-col transition-transform duration-300 ease-in-out lg:static lg:translate-x-0"
        >
            <!-- Sidebar Header & Logo -->
            <div class="h-20 flex flex-col justify-center px-6 border-b border-white/5">
                <a href="{{ url('/admin/dashboard') }}" class="flex items-center gap-1.5">
                    <span class="text-2xl font-black tracking-tight text-primary">GEAR<span class="text-white">HUB</span></span>
                </a>
                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mt-0.5">Admin Portal</span>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                
                @php
                    $navItems = [
                        ['name' => 'Dashboard', 'url' => url('/admin/dashboard'), 'pattern' => 'admin/dashboard*', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                        ['name' => 'Products', 'url' => url('/admin/products'), 'pattern' => 'admin/products*', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                        ['name' => 'Categories', 'url' => url('/admin/categories'), 'pattern' => 'admin/categories*', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
                        ['name' => 'Brands', 'url' => url('/admin/brands'), 'pattern' => 'admin/brands*', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                        ['name' => 'Inventory', 'url' => url('/admin/inventory'), 'pattern' => 'admin/inventory*', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                        ['name' => 'Orders', 'url' => url('/admin/orders'), 'pattern' => 'admin/orders*', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                        ['name' => 'Trash', 'url' => url('/admin/trash'), 'pattern' => 'admin/trash*', 'icon' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'],
                    ];
                @endphp

                @foreach ($navItems as $item)
                    @php
                        $isActive = request()->is($item['pattern']);
                    @endphp
                    <a 
                        href="{{ $item['url'] }}" 
                        class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-semibold transition {{ $isActive ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-gray-400 hover:text-white hover:bg-white/5' }}"
                    >
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                        </svg>
                        <span>{{ $item['name'] }}</span>
                    </a>
                @endforeach
            </nav>

            <!-- Sidebar Footer & Back to Storefront -->
            <div class="p-4 border-t border-white/5 space-y-2">
                <a 
                    href="{{ url('/') }}" 
                    target="_blank"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-medium text-gray-400 hover:text-white hover:bg-white/5 transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    <span>Xem cửa hàng</span>
                </a>
            </div>
        </aside>

        <!-- Main Column: Topbar + Page Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <!-- Topbar (Matching Figma Page 14-20: Clean & Minimal) -->
            <header class="h-20 bg-white border-b border-gray-100 flex items-center justify-between px-6 sm:px-8 z-10">
                
                <!-- Left: Mobile Menu Button & Page Title -->
                <div class="flex items-center gap-4">
                    <button 
                        type="button" 
                        @click="sidebarOpen = true" 
                        class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100"
                        aria-label="Mở Menu"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">
                        {{ $heading ?? $title ?? 'Dashboard' }}
                    </h1>
                </div>

                <!-- Right: Admin Profile & Actions -->
                <div class="flex items-center gap-4">
                    <div class="relative" x-data="{ open: false }">
                        <button 
                            @click="open = !open" 
                            class="flex items-center gap-3 p-1.5 rounded-xl hover:bg-gray-50 transition focus:outline-none"
                        >
                            <!-- AD Circle Badge matching Figma -->
                            <div class="w-10 h-10 rounded-full bg-gray-200 text-gray-800 font-bold text-xs flex items-center justify-center border border-gray-300 shadow-sm">
                                AD
                            </div>
                            <div class="hidden sm:block text-left text-xs">
                                <p class="font-semibold text-gray-900">{{ Auth::user()->name ?? 'Administrator' }}</p>
                                <p class="text-gray-400">admin@gearhub.vn</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div 
                            x-show="open" 
                            @click.outside="open = false" 
                            x-transition 
                            class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 z-50 text-sm"
                            style="display: none;"
                        >
                            <a href="{{ url('/') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">Về Storefront</a>
                            <hr class="my-1 border-gray-100">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50 font-medium">
                                    Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Body Slot -->
            <main class="flex-1 overflow-y-auto p-6 sm:p-8 bg-[#F8FAFC]">
                
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mb-6">
                        <x-alert type="success" :message="session('success')" />
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6">
                        <x-alert type="error" :message="session('error')" />
                    </div>
                @endif

                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
