@extends('layouts.client')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-16" x-data="{ mobileFilterOpen: false }">

    <!-- Breadcrumb (Figma Page 4) -->
    <nav class="flex items-center gap-2 text-xs text-gray-400 mb-6">
        <a href="{{ url('/') }}" class="hover:text-primary transition">Home</a>
        <span>/</span>
        <a href="{{ url('/shop') }}" class="hover:text-primary transition">Shop</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">All Products</span>
    </nav>

    <!-- Page Title -->
    <div class="mb-8 flex items-center justify-between">
        <h1 class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight">
            All Products
        </h1>

        <!-- Mobile Filter Button Toggle -->
        <button 
            type="button" 
            @click="mobileFilterOpen = !mobileFilterOpen"
            class="lg:hidden inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-700 bg-white shadow-sm"
        >
            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            Bộ lọc
        </button>
    </div>

    <!-- Main Layout: Sidebar Filter (Left) + Product Catalog (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 sm:gap-10 items-start">
        
        <!-- SIDEBAR FILTERS (Desktop + Mobile Drawer) -->
        <aside 
            :class="mobileFilterOpen ? 'fixed inset-0 z-50 bg-black/50 flex' : 'hidden lg:block'"
            class="lg:col-span-1"
        >
            <!-- Filter Box Container -->
            <div 
                class="w-full max-w-xs lg:max-w-none bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-8 overflow-y-auto max-h-screen lg:max-h-none"
                @click.outside="mobileFilterOpen = false"
            >
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900">Filters</h2>
                    <button type="button" @click="mobileFilterOpen = false" class="lg:hidden text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form id="filter-form" action="{{ url('/shop') }}" method="GET" class="space-y-8">
                    
                    <!-- 1. Category Filter -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Category</h3>
                        <div class="space-y-2">
                            @foreach ($categories as $cat)
                                <label class="flex items-center justify-between text-sm text-gray-700 cursor-pointer hover:text-primary transition group select-none">
                                    <div class="flex items-center gap-2.5">
                                        <input 
                                            type="checkbox" 
                                            name="categories[]" 
                                            value="{{ $cat['name'] }}"
                                            {{ in_array($cat['name'], $selectedCategories) ? 'checked' : '' }}
                                            onchange="document.getElementById('filter-form').submit()"
                                            class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/20 transition cursor-pointer"
                                        >
                                        <span class="group-hover:translate-x-0.5 transition-transform">{{ $cat['name'] }}</span>
                                    </div>
                                    <span class="text-xs text-gray-400 font-mono">({{ $cat['count'] }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- 2. Price Range Filter -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Price range</h3>
                            <span class="text-xs font-bold text-primary">{{ number_format(request('max_price', 70000000)) }}đ</span>
                        </div>
                        
                        <!-- Visual Range Bar matching Figma -->
                        <div class="space-y-2">
                            <input 
                                type="range" 
                                min="500000" 
                                max="70000000" 
                                step="500000"
                                value="{{ request('max_price', 70000000) }}"
                                name="max_price"
                                class="w-full accent-primary cursor-pointer"
                                onchange="document.getElementById('filter-form').submit()"
                            >
                            <div class="flex items-center justify-between text-xs font-semibold text-gray-500">
                                <span>500.000đ</span>
                                <span>70.000.000đ</span>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Brand Filter -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Brand</h3>
                        <div class="space-y-2">
                            @foreach ($brands as $brand)
                                <label class="flex items-center gap-2.5 text-sm text-gray-700 cursor-pointer hover:text-primary transition group select-none">
                                    <input 
                                        type="checkbox" 
                                        name="brands[]" 
                                        value="{{ $brand['name'] }}"
                                        {{ in_array($brand['name'], $selectedBrands) ? 'checked' : '' }}
                                        onchange="document.getElementById('filter-form').submit()"
                                        class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/20 transition cursor-pointer"
                                    >
                                    <span class="group-hover:translate-x-0.5 transition-transform">{{ $brand['name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- 4. Rating Filter -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Rating</h3>
                        <div class="space-y-2 text-sm text-gray-700">
                            <label class="flex items-center gap-2 cursor-pointer hover:text-primary select-none">
                                <input type="radio" name="rating" value="5" class="text-primary focus:ring-primary/20" onchange="document.getElementById('filter-form').submit()">
                                <span class="text-amber-400">★★★★★</span>
                                <span class="text-xs text-gray-500">& up</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer hover:text-primary select-none">
                                <input type="radio" name="rating" value="4" class="text-primary focus:ring-primary/20" onchange="document.getElementById('filter-form').submit()">
                                <span class="text-amber-400">★★★★☆</span>
                                <span class="text-xs text-gray-500">& up</span>
                            </label>
                        </div>
                    </div>

                    <!-- Reset Filters Button -->
                    <div class="pt-2">
                        <a 
                            href="{{ url('/shop') }}" 
                            class="w-full inline-flex items-center justify-center py-2.5 px-4 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:text-primary hover:border-primary/30 hover:bg-primary/5 transition"
                        >
                            Reset filters
                        </a>
                    </div>

                </form>
            </div>
        </aside>

        <!-- RIGHT CATALOG CONTENT -->
        <div class="lg:col-span-3 space-y-8">
            
            <!-- Top Controls Bar: Search + Sort Dropdown + Product Counter (Figma Page 4) -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                
                <!-- Search Input with Query String -->
                <div class="relative w-full sm:w-72">
                    <input 
                        type="text" 
                        name="search" 
                        form="filter-form"
                        value="{{ $search }}"
                        placeholder="Search products..." 
                        class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-gray-200 bg-[#F4F5F7] focus:bg-white focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition"
                    >
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <!-- Sort & Counter Row -->
                <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-4">
                    
                    <!-- Sorting Dropdown -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400 font-medium hidden md:inline">Sort:</span>
                        <select 
                            name="sort" 
                            form="filter-form"
                            onchange="document.getElementById('filter-form').submit()"
                            class="text-sm font-semibold text-gray-700 bg-[#F4F5F7] border-0 rounded-xl px-3 py-2 focus:ring-1 focus:ring-primary cursor-pointer transition"
                        >
                            <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest</option>
                            <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Best Selling</option>
                        </select>
                    </div>

                    <!-- Total Products Counter (Figma Page 4: 128 products) -->
                    <span class="text-xs font-semibold text-gray-400 whitespace-nowrap">
                        {{ $totalProducts }} products
                    </span>

                </div>

            </div>

            <!-- Products Grid (3 columns on desktop matching Figma Page 4) -->
            @if ($products->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-7">
                    @foreach ($products as $item)
                        <x-product-card :product="$item" />
                    @endforeach
                </div>
            @else
                <div class="text-center py-16 bg-white rounded-3xl border border-gray-100 p-8 space-y-4">
                    <div class="w-16 h-16 mx-auto rounded-full bg-gray-100 flex items-center justify-center text-gray-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Không tìm thấy sản phẩm nào</h3>
                    <p class="text-sm text-gray-500 max-w-sm mx-auto">Vui lòng thử bỏ bớt bộ lọc hoặc tìm kiếm với từ khóa khác.</p>
                    <a href="{{ url('/shop') }}" class="inline-flex px-5 py-2.5 rounded-xl bg-primary text-white text-xs font-bold shadow-sm hover:bg-primary-hover transition">
                        Xem tất cả sản phẩm
                    </a>
                </div>
            @endif

            <!-- Numbered Pagination (1 2 3 4 5 matching Figma Page 4) -->
            <div class="pt-8 border-t border-gray-100 flex justify-center">
                <x-pagination />
            </div>

        </div>

    </div>

</div>
@endsection
