@extends('layouts.client')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-16" x-data="{ mobileFilterOpen: false }">

    <!-- Breadcrumb (Figma Page 4) -->
    <nav class="flex items-center gap-2 text-xs text-gray-400 mb-6">
        <a href="{{ url('/') }}" class="hover:text-primary transition font-medium">Home</a>
        <span>/</span>
        <a href="{{ url('/shop') }}" class="hover:text-primary transition font-medium">Shop</a>
        <span>/</span>
        <span class="text-gray-900 font-semibold">All Products</span>
    </nav>

    <!-- Page Title & Mobile Trigger -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight">
                All Products
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Khám phá hệ sinh thái gaming gear và phụ kiện công nghệ chính hãng từ GearHub
            </p>
        </div>

        <!-- Mobile Filter Button Toggle -->
        <button 
            type="button" 
            @click="mobileFilterOpen = !mobileFilterOpen"
            class="lg:hidden inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-700 bg-white shadow-sm hover:border-primary/40 transition"
        >
            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            Bộ lọc
            @php
                $activeFilterCount = count($selectedCategories) + count($selectedBrands) + ($minPrice ? 1 : 0) + ($maxPrice ? 1 : 0) + ($stockStatus ? 1 : 0);
            @endphp
            @if ($activeFilterCount > 0)
                <span class="w-5 h-5 rounded-full bg-primary text-white text-[10px] font-bold flex items-center justify-center">
                    {{ $activeFilterCount }}
                </span>
            @endif
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
                class="w-full max-w-xs lg:max-w-none bg-white p-6 rounded-3xl border border-gray-100 shadow-sm space-y-7 overflow-y-auto max-h-screen lg:max-h-none"
                @click.outside="mobileFilterOpen = false"
            >
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        <h2 class="text-base font-bold text-gray-900">Bộ lọc tìm kiếm</h2>
                    </div>
                    <button type="button" @click="mobileFilterOpen = false" class="lg:hidden text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form id="filter-form" action="{{ url('/shop') }}" method="GET" class="space-y-7">
                    
                    <!-- Giữ lại search và sort nếu có -->
                    @if ($search)
                        <input type="hidden" name="search" value="{{ $search }}">
                    @endif
                    @if ($sort && $sort !== 'newest')
                        <input type="hidden" name="sort" value="{{ $sort }}">
                    @endif

                    <!-- 1. Danh mục (Category Filter) -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Danh mục</h3>
                            @if (!empty($selectedCategories))
                                <span class="text-[11px] font-bold text-primary">{{ count($selectedCategories) }} đã chọn</span>
                            @endif
                        </div>
                        <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                            @foreach ($categories as $cat)
                                <label class="flex items-center justify-between text-sm text-gray-700 cursor-pointer hover:text-primary transition group select-none">
                                    <div class="flex items-center gap-2.5">
                                        <input 
                                            type="checkbox" 
                                            name="categories[]" 
                                            value="{{ $cat['name'] }}"
                                            {{ in_array($cat['name'], $selectedCategories) || in_array($cat['slug'], $selectedCategories) ? 'checked' : '' }}
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

                    <!-- 2. Thương hiệu (Brand Filter) -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Thương hiệu</h3>
                            @if (!empty($selectedBrands))
                                <span class="text-[11px] font-bold text-primary">{{ count($selectedBrands) }} đã chọn</span>
                            @endif
                        </div>
                        <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                            @foreach ($brands as $brand)
                                <label class="flex items-center justify-between text-sm text-gray-700 cursor-pointer hover:text-primary transition group select-none">
                                    <div class="flex items-center gap-2.5">
                                        <input 
                                            type="checkbox" 
                                            name="brands[]" 
                                            value="{{ $brand['name'] }}"
                                            {{ in_array($brand['name'], $selectedBrands) || in_array($brand['slug'], $selectedBrands) ? 'checked' : '' }}
                                            onchange="document.getElementById('filter-form').submit()"
                                            class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary/20 transition cursor-pointer"
                                        >
                                        <span class="group-hover:translate-x-0.5 transition-transform">{{ $brand['name'] }}</span>
                                    </div>
                                    <span class="text-xs text-gray-400 font-mono">({{ $brand['count'] }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- 3. Khoảng giá (Price Range Filter: Min / Max) -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Khoảng giá (VNĐ)</h3>
                            @if ($minPrice || $maxPrice)
                                <a 
                                    href="{{ request()->fullUrlWithQuery(['min_price' => null, 'max_price' => null, 'page' => null]) }}" 
                                    class="text-[11px] font-semibold text-primary hover:underline"
                                >
                                    Xóa giá
                                </a>
                            @endif
                        </div>

                        <!-- 2 ô nhập Min / Max trực quan -->
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-[11px] text-gray-500 font-medium block mb-1">Từ:</label>
                                <div class="relative">
                                    <input 
                                        type="number" 
                                        name="min_price" 
                                        value="{{ $minPrice }}" 
                                        placeholder="0"
                                        min="0"
                                        step="100000"
                                        class="w-full pl-2.5 pr-6 py-1.5 text-xs rounded-xl border border-gray-200 bg-[#F4F5F7] focus:bg-white focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition"
                                    >
                                    <span class="absolute right-2 top-1.5 text-[11px] text-gray-400 font-semibold pointer-events-none">đ</span>
                                </div>
                            </div>
                            <div>
                                <label class="text-[11px] text-gray-500 font-medium block mb-1">Đến:</label>
                                <div class="relative">
                                    <input 
                                        type="number" 
                                        name="max_price" 
                                        value="{{ $maxPrice }}" 
                                        placeholder="Tối đa"
                                        min="0"
                                        step="100000"
                                        class="w-full pl-2.5 pr-6 py-1.5 text-xs rounded-xl border border-gray-200 bg-[#F4F5F7] focus:bg-white focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition"
                                    >
                                    <span class="absolute right-2 top-1.5 text-[11px] text-gray-400 font-semibold pointer-events-none">đ</span>
                                </div>
                            </div>
                        </div>

                        <!-- Nút Áp dụng khoảng giá -->
                        <button 
                            type="submit" 
                            class="w-full py-2 px-3 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold shadow-sm transition transform hover:-translate-y-0.5"
                        >
                            Áp dụng khoảng giá
                        </button>

                        <!-- Các mức giá chọn nhanh (Quick Presets) -->
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            @php
                                $isP1 = empty($minPrice) && $maxPrice == 1000000;
                                $isP2 = $minPrice == 1000000 && $maxPrice == 3000000;
                                $isP3 = $minPrice == 3000000 && $maxPrice == 5000000;
                                $isP4 = $minPrice == 5000000 && empty($maxPrice);
                            @endphp
                            <a 
                                href="{{ request()->fullUrlWithQuery(['min_price' => null, 'max_price' => 1000000, 'page' => null]) }}"
                                class="px-2.5 py-1 text-[11px] rounded-lg border {{ $isP1 ? 'bg-primary text-white border-primary font-bold shadow-sm' : 'bg-gray-50 text-gray-600 border-gray-200 hover:border-gray-300' }} transition"
                            >
                                &lt; 1 triệu
                            </a>
                            <a 
                                href="{{ request()->fullUrlWithQuery(['min_price' => 1000000, 'max_price' => 3000000, 'page' => null]) }}"
                                class="px-2.5 py-1 text-[11px] rounded-lg border {{ $isP2 ? 'bg-primary text-white border-primary font-bold shadow-sm' : 'bg-gray-50 text-gray-600 border-gray-200 hover:border-gray-300' }} transition"
                            >
                                1 - 3 triệu
                            </a>
                            <a 
                                href="{{ request()->fullUrlWithQuery(['min_price' => 3000000, 'max_price' => 5000000, 'page' => null]) }}"
                                class="px-2.5 py-1 text-[11px] rounded-lg border {{ $isP3 ? 'bg-primary text-white border-primary font-bold shadow-sm' : 'bg-gray-50 text-gray-600 border-gray-200 hover:border-gray-300' }} transition"
                            >
                                3 - 5 triệu
                            </a>
                            <a 
                                href="{{ request()->fullUrlWithQuery(['min_price' => 5000000, 'max_price' => null, 'page' => null]) }}"
                                class="px-2.5 py-1 text-[11px] rounded-lg border {{ $isP4 ? 'bg-primary text-white border-primary font-bold shadow-sm' : 'bg-gray-50 text-gray-600 border-gray-200 hover:border-gray-300' }} transition"
                            >
                                &gt; 5 triệu
                            </a>
                        </div>
                    </div>

                    <!-- 4. Tình trạng hàng (Stock Status Filter) -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Tình trạng còn hàng</h3>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2.5 text-sm text-gray-700 cursor-pointer hover:text-primary transition group select-none">
                                <input 
                                    type="radio" 
                                    name="stock_status" 
                                    value="" 
                                    {{ empty($stockStatus) ? 'checked' : '' }}
                                    onchange="document.getElementById('filter-form').submit()"
                                    class="w-4 h-4 border-gray-300 text-primary focus:ring-primary/20 transition cursor-pointer"
                                >
                                <span class="group-hover:translate-x-0.5 transition-transform">Tất cả sản phẩm</span>
                            </label>
                            <label class="flex items-center gap-2.5 text-sm text-gray-700 cursor-pointer hover:text-primary transition group select-none">
                                <input 
                                    type="radio" 
                                    name="stock_status" 
                                    value="in_stock" 
                                    {{ $stockStatus === 'in_stock' ? 'checked' : '' }}
                                    onchange="document.getElementById('filter-form').submit()"
                                    class="w-4 h-4 border-gray-300 text-primary focus:ring-primary/20 transition cursor-pointer"
                                >
                                <span class="group-hover:translate-x-0.5 transition-transform flex items-center gap-1.5 font-medium">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                                    Chỉ hiện còn hàng
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Reset Filters Button -->
                    <div class="pt-2">
                        <a 
                            href="{{ url('/shop') }}" 
                            class="w-full inline-flex items-center justify-center py-2.5 px-4 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:text-primary hover:border-primary/30 hover:bg-primary/5 transition"
                        >
                            Xóa tất cả bộ lọc
                        </a>
                    </div>

                </form>
            </div>
        </aside>

        <!-- RIGHT CATALOG CONTENT -->
        <div class="lg:col-span-3 space-y-6">
            
            <!-- Top Controls Bar: Search + Sort Dropdown + Product Counter (Figma Page 4) -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                
                <!-- Search Input with Query String -->
                <div class="relative w-full sm:w-80">
                    <input 
                        type="text" 
                        name="search" 
                        form="filter-form"
                        value="{{ $search }}"
                        placeholder="Tìm theo tên, SKU, thương hiệu..." 
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
                        <span class="text-xs text-gray-400 font-semibold hidden md:inline">Sắp xếp:</span>
                        <select 
                            name="sort" 
                            form="filter-form"
                            onchange="document.getElementById('filter-form').submit()"
                            class="text-sm font-semibold text-gray-700 bg-[#F4F5F7] border-0 rounded-xl px-3 py-2 focus:ring-1 focus:ring-primary cursor-pointer transition"
                        >
                            <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Mới nhất</option>
                            <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Giá: Thấp đến cao</option>
                            <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Giá: Cao đến thấp</option>
                            <option value="popular" {{ in_array($sort, ['popular', 'best_selling']) ? 'selected' : '' }}>Bán chạy nhất</option>
                        </select>
                    </div>

                    <!-- Total Products Counter -->
                    <span class="text-xs font-semibold text-gray-500 whitespace-nowrap">
                        @if ($totalProducts > 0)
                            Hiển thị <span class="text-gray-900 font-bold">{{ $products->firstItem() }}-{{ $products->lastItem() }}</span> / {{ $totalProducts }} sản phẩm
                        @else
                            0 sản phẩm
                        @endif
                    </span>

                </div>

            </div>

            <!-- Active Filter Badges (Chips) -->
            @php
                $hasActiveFilters = !empty($selectedCategories) || !empty($selectedBrands) || $minPrice || $maxPrice || $stockStatus || $search;
            @endphp
            @if ($hasActiveFilters)
                <div class="flex items-center gap-2 flex-wrap text-xs pt-1">
                    <span class="text-gray-400 font-semibold">Đang lọc theo:</span>

                    @foreach ($selectedCategories as $catVal)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary font-medium">
                            {{ $catVal }}
                            <a href="{{ request()->fullUrlWithQuery(['categories' => array_diff($selectedCategories, [$catVal]), 'page' => null]) }}" class="hover:text-red-700 font-bold">×</a>
                        </span>
                    @endforeach

                    @foreach ($selectedBrands as $brandVal)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gray-100 text-gray-700 font-medium">
                            Hãng: {{ $brandVal }}
                            <a href="{{ request()->fullUrlWithQuery(['brands' => array_diff($selectedBrands, [$brandVal]), 'page' => null]) }}" class="hover:text-red-700 font-bold">×</a>
                        </span>
                    @endforeach

                    @if ($minPrice || $maxPrice)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200/60 font-medium">
                            Giá: {{ $minPrice ? number_format((int)$minPrice).'đ' : '0đ' }} - {{ $maxPrice ? number_format((int)$maxPrice).'đ' : 'Tối đa' }}
                            <a href="{{ request()->fullUrlWithQuery(['min_price' => null, 'max_price' => null, 'page' => null]) }}" class="hover:text-red-700 font-bold">×</a>
                        </span>
                    @endif

                    @if ($stockStatus === 'in_stock')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60 font-medium">
                            ● Còn hàng
                            <a href="{{ request()->fullUrlWithQuery(['stock_status' => null, 'page' => null]) }}" class="hover:text-red-700 font-bold">×</a>
                        </span>
                    @endif

                    @if ($search)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200/60 font-medium">
                            Từ khóa: "{{ $search }}"
                            <a href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => null]) }}" class="hover:text-red-700 font-bold">×</a>
                        </span>
                    @endif

                    <a href="{{ url('/shop') }}" class="text-xs text-red-600 hover:underline font-bold ml-1">
                        Xóa tất cả
                    </a>
                </div>
            @endif

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
            @if ($products->hasPages())
                <div class="pt-8 border-t border-gray-100 flex justify-center">
                    <x-pagination :paginator="$products" />
                </div>
            @endif

        </div>

    </div>

</div>
@endsection
