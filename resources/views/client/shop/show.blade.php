@extends('layouts.client')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-24">

    <!-- 1. BREADCRUMBS (Matching Figma) -->
    <nav class="flex items-center gap-2 text-xs text-gray-400 mb-8 flex-wrap" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="hover:text-primary transition flex items-center gap-1 font-medium">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Trang chủ
        </a>
        <span class="text-gray-300">/</span>
        <a href="{{ url('/shop') }}" class="hover:text-primary transition font-medium">Sản phẩm</a>
        <span class="text-gray-300">/</span>
        <a href="{{ url('/shop?category=' . urlencode($product['category'])) }}" class="hover:text-primary transition font-medium">
            {{ $product['category'] }}
        </a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-900 font-semibold truncate max-w-xs">{{ $product['name'] }}</span>
    </nav>

    <!-- 2. MAIN PRODUCT SECTION: 2 COLUMNS (Figma Page 5) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">
        
        <!-- CỘT TRÁI: THƯ VIỆN ẢNH SẢN PHẨM (ALPINE.JS GALLERY) -->
        <div class="lg:col-span-6 space-y-4" x-data="{ 
            activeImage: '{{ $product['gallery'][0] ?? $product['thumbnail'] }}' 
        }">
            <!-- Khung ảnh chính lớn -->
            <div class="aspect-square w-full bg-[#F4F5F7] rounded-3xl p-8 sm:p-14 flex items-center justify-center relative overflow-hidden border border-gray-100 shadow-sm group">
                <!-- Sale Tag nếu có -->
                @if (!empty($product['sale_price']) && $product['sale_price'] < $product['price'])
                    <span class="absolute top-5 left-5 px-3.5 py-1.5 bg-primary text-white text-xs font-black uppercase tracking-wider rounded-full shadow-lg shadow-primary/25 z-10">
                        -{{ round((($product['price'] - $product['sale_price']) / $product['price']) * 100) }}% OFF
                    </span>
                @endif

                <!-- Ảnh chính với hiệu ứng chuyển đổi mượt mà -->
                <img 
                    :src="activeImage" 
                    alt="{{ $product['name'] }}"
                    class="max-h-full max-w-full object-contain drop-shadow-2xl transition duration-500 transform group-hover:scale-105"
                >
            </div>

            <!-- Dải ảnh thu nhỏ (Thumbnails Strip click đổi ảnh) -->
            @if (!empty($product['gallery']) && count($product['gallery']) > 1)
                <div class="grid grid-cols-4 sm:grid-cols-5 gap-3 pt-1">
                    @foreach ($product['gallery'] as $img)
                        <button 
                            type="button" 
                            @click="activeImage = '{{ $img }}'"
                            :class="activeImage === '{{ $img }}' ? 'border-primary ring-2 ring-primary/20 opacity-100 scale-100' : 'border-gray-200 opacity-60 hover:opacity-100 hover:border-gray-300'"
                            class="aspect-square bg-[#F4F5F7] rounded-2xl p-2.5 flex items-center justify-center border-2 transition duration-200 cursor-pointer overflow-hidden focus:outline-none"
                            title="Xem ảnh"
                        >
                            <img src="{{ $img }}" alt="Thumbnail" class="max-h-full max-w-full object-contain">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- CỘT PHẢI: THÔNG TIN CHI TIẾT & BỘ CHỌN MUA (Figma Page 5) -->
        <div class="lg:col-span-6 space-y-5">
            
            <!-- Category & Brand -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="text-xs font-bold uppercase tracking-wider text-primary">
                    {{ $product['category'] }}
                </span>
                <span class="text-gray-300">•</span>
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    Thương hiệu: <span class="text-gray-900 font-bold">{{ $product['brand'] }}</span>
                </span>
            </div>

            <!-- Product Name -->
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-gray-900 tracking-tight leading-tight">
                {{ $product['name'] }}
            </h1>

            <!-- Rating, Review Count & SKU Code -->
            <div class="flex items-center gap-4 text-sm flex-wrap pt-0.5">
                <!-- Stars -->
                <div class="flex items-center gap-1.5 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200/60">
                    <span class="text-amber-500 font-bold text-xs flex items-center gap-1">
                        ★ {{ $product['rating'] }}
                    </span>
                    <span class="text-gray-400 text-xs">({{ $product['reviews_count'] }} đánh giá)</span>
                </div>

                <!-- SKU Code (Đúng yêu cầu) -->
                <div class="text-xs text-gray-500">
                    Mã SKU: <span class="font-mono font-bold text-gray-900 bg-gray-100 px-2 py-0.5 rounded">{{ $product['sku'] }}</span>
                </div>
            </div>

            <!-- Giá gốc & Giá sale -->
            <div class="p-4 sm:p-5 bg-[#F8FAFC] rounded-2xl border border-gray-100 space-y-1">
                <div class="flex items-baseline gap-3 flex-wrap">
                    <span class="text-3xl sm:text-4xl font-black text-primary tracking-tight">
                        {{ number_format($product['sale_price'] ?? $product['price'], 0, ',', '.') }}đ
                    </span>

                    @if (!empty($product['sale_price']) && $product['sale_price'] < $product['price'])
                        <span class="text-lg text-gray-400 line-through font-semibold">
                            {{ number_format($product['price'], 0, ',', '.') }}đ
                        </span>

                        <span class="text-xs font-bold text-emerald-700 bg-emerald-100/70 px-2.5 py-1 rounded-full">
                            Tiết kiệm {{ number_format($product['price'] - $product['sale_price'], 0, ',', '.') }}đ
                        </span>
                    @endif
                </div>
                <p class="text-[11px] text-gray-400">Đã bao gồm thuế VAT và bảo hiểm vận chuyển toàn quốc</p>
            </div>

            <!-- Badge Tình trạng kho (Đúng yêu cầu) -->
            <div class="flex items-center gap-3">
                @if ($product['stock_quantity'] > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Còn hàng ({{ $product['stock_quantity'] }} sản phẩm sẵn có)
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        Tạm hết hàng
                    </span>
                @endif
            </div>

            <!-- Mô tả tóm tắt -->
            <p class="text-sm text-gray-600 leading-relaxed pt-1">
                {{ $product['description'] }}
            </p>

            <hr class="border-gray-100">

            <!-- BỘ CHỌN SỐ LƯỢNG & NÚT CTA (Alpine.js) -->
            <div class="space-y-5 pt-2" x-data="{ 
                qty: 1, 
                maxStock: {{ $product['stock_quantity'] > 0 ? $product['stock_quantity'] : 1 }},
                addedToast: false,
                addToCart() {
                    this.addedToast = true;
                    setTimeout(() => this.addedToast = false, 3000);
                }
            }">
                
                <!-- Bộ chọn số lượng (+ / -) -->
                @if ($product['stock_quantity'] > 0)
                    <div class="flex items-center gap-4">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-700">Số lượng:</span>
                        
                        <div class="inline-flex items-center border border-gray-200 rounded-2xl bg-[#F4F5F7] p-1 shadow-inner">
                            <button 
                                type="button" 
                                @click="if (qty > 1) qty--" 
                                :disabled="qty <= 1"
                                class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-700 hover:bg-white hover:text-black font-bold transition disabled:opacity-40 disabled:hover:bg-transparent"
                                aria-label="Giảm số lượng"
                            >
                                –
                            </button>
                            
                            <input 
                                type="number" 
                                x-model.number="qty" 
                                min="1" 
                                :max="maxStock" 
                                class="w-14 text-center font-black text-sm text-gray-900 bg-transparent border-none focus:ring-0 p-0"
                                @change="if (qty < 1) qty = 1; if (qty > maxStock) qty = maxStock;"
                            >

                            <button 
                                type="button" 
                                @click="if (qty < maxStock) qty++" 
                                :disabled="qty >= maxStock"
                                class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-700 hover:bg-white hover:text-black font-bold transition disabled:opacity-40 disabled:hover:bg-transparent"
                                aria-label="Tăng số lượng"
                            >
                                +
                            </button>
                        </div>

                        <span class="text-xs text-gray-400">Tối đa {{ $product['stock_quantity'] }}</span>
                    </div>
                @endif

                <!-- Cặp nút CTA: Thêm vào giỏ & Mua ngay (Đúng yêu cầu) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-1">
                    @if ($product['stock_quantity'] > 0)
                        <!-- Nút 1: Thêm vào giỏ (Primary Red) -->
                        <button 
                            type="button" 
                            @click="addToCart()"
                            class="py-4 px-6 rounded-2xl bg-primary hover:bg-primary-hover text-white font-bold text-sm shadow-xl shadow-primary/25 transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2 group"
                        >
                            <svg class="w-5 h-5 transition transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <span>Thêm vào giỏ</span>
                        </button>

                        <!-- Nút 2: Mua ngay (Secondary Dark) -->
                        <a 
                            href="{{ url('/shop') }}"
                            class="py-4 px-6 rounded-2xl bg-[#18181B] hover:bg-black text-white font-bold text-sm text-center shadow-lg transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span>Mua ngay</span>
                        </a>
                    @else
                        <!-- Trạng thái Hết hàng -->
                        <button 
                            disabled 
                            class="col-span-2 py-4 px-6 rounded-2xl bg-gray-200 text-gray-400 font-bold text-sm text-center cursor-not-allowed"
                        >
                            Sản phẩm hiện đang tạm hết hàng
                        </button>
                    @endif
                </div>

                <!-- Toast feedback khi thêm giỏ hàng thành công -->
                <div 
                    x-show="addedToast" 
                    x-transition 
                    class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between text-xs text-emerald-800"
                    style="display: none;"
                >
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Đã thêm <strong x-text="qty"></strong> sản phẩm <strong>{{ $product['name'] }}</strong> vào giỏ!</span>
                    </div>
                    <a href="{{ url('/shop') }}" class="font-bold underline text-emerald-900 hover:text-emerald-700">Xem giỏ</a>
                </div>

            </div>

            <!-- 3 Khối cam kết chính hãng (Figma Page 5) -->
            <div class="pt-5 grid grid-cols-1 sm:grid-cols-3 gap-3 border-t border-gray-100 text-xs text-gray-600">
                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-gray-50/80">
                    <svg class="w-4 h-4 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>100% Chính hãng</span>
                </div>
                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-gray-50/80">
                    <svg class="w-4 h-4 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Bảo hành 12 - 24T</span>
                </div>
                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-gray-50/80">
                    <svg class="w-4 h-4 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Đổi trả 7 ngày</span>
                </div>
            </div>

        </div>

    </div>

    <!-- 3. TABS: MÔ TẢ, THÔNG SỐ KỸ THUẬT & ĐÁNH GIÁ (Figma Page 5) -->
    <div class="mt-20 pt-10 border-t border-gray-100" x-data="{ activeTab: 'specs' }">
        
        <!-- Tab Navigation Buttons -->
        <div class="flex items-center gap-8 border-b border-gray-200 pb-4 text-sm font-bold flex-wrap">
            <button 
                type="button"
                @click="activeTab = 'specs'" 
                :class="activeTab === 'specs' ? 'text-primary border-b-2 border-primary -mb-4 pb-4' : 'text-gray-400 hover:text-gray-700'"
                class="transition focus:outline-none"
            >
                Thông số kỹ thuật
            </button>

            <button 
                type="button"
                @click="activeTab = 'desc'" 
                :class="activeTab === 'desc' ? 'text-primary border-b-2 border-primary -mb-4 pb-4' : 'text-gray-400 hover:text-gray-700'"
                class="transition focus:outline-none"
            >
                Mô tả chi tiết
            </button>

            <button 
                type="button"
                @click="activeTab = 'reviews'" 
                :class="activeTab === 'reviews' ? 'text-primary border-b-2 border-primary -mb-4 pb-4' : 'text-gray-400 hover:text-gray-700'"
                class="transition focus:outline-none"
            >
                Đánh giá ({{ $product['reviews_count'] }})
            </button>
        </div>

        <!-- Tab 1: Specifications (JSON Specs Table) -->
        <div x-show="activeTab === 'specs'" class="pt-8">
            <div class="max-w-3xl overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                @if (!empty($product['specs']) && is_array($product['specs']))
                    <dl class="divide-y divide-gray-100 text-sm">
                        @foreach ($product['specs'] as $key => $val)
                            <div class="py-3.5 px-5 grid grid-cols-1 sm:grid-cols-3 odd:bg-gray-50/50">
                                <dt class="font-bold text-gray-900">{{ $key }}</dt>
                                <dd class="sm:col-span-2 text-gray-600 mt-1 sm:mt-0 font-medium">{{ is_array($val) ? implode(', ', $val) : $val }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <p class="p-6 text-sm text-gray-400 italic">Đang cập nhật thông số kỹ thuật cho sản phẩm này.</p>
                @endif
            </div>
        </div>

        <!-- Tab 2: Description Content -->
        <div x-show="activeTab === 'desc'" style="display: none;" class="pt-8 max-w-3xl text-sm text-gray-600 leading-relaxed space-y-4">
            <p>
                {{ $product['description'] }}
            </p>
            <p>
                Tất cả sản phẩm bán ra tại <strong>GearHub</strong> đều được cam kết là hàng mới 100% nguyên hộp, bảo hành chính hãng từ nhà sản xuất với đội ngũ hỗ trợ kỹ thuật tận tâm 24/7.
            </p>
        </div>

        <!-- Tab 3: Reviews Preview -->
        <div x-show="activeTab === 'reviews'" style="display: none;" class="pt-8 max-w-3xl space-y-4">
            <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="font-bold text-gray-900">Trần Hữu Thắng (Đã mua tại GearHub)</span>
                    <span class="text-amber-500 font-bold">★★★★★ 5.0</span>
                </div>
                <p class="text-xs text-gray-600 leading-relaxed">
                    Đóng gói rất cẩn thận, giao hàng hỏa tốc trong 2 tiếng tại TP.HCM. Tai nghe đeo êm, chất âm chi tiết đúng như mô tả!
                </p>
            </div>
            <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="font-bold text-gray-900">Nguyễn Văn An (Đã mua tại GearHub)</span>
                    <span class="text-amber-500 font-bold">★★★★★ 5.0</span>
                </div>
                <p class="text-xs text-gray-600 leading-relaxed">
                    Sản phẩm chất lượng cao, build cứng cáp, pin trâu dùng cả tuần chưa cần sạc. 10/10 điểm!
                </p>
            </div>
        </div>

    </div>

    <!-- 4. KHỐI SẢN PHẨM LIÊN QUAN / CÙNG DANH MỤC (Figma Page 5) -->
    @if (!empty($relatedProducts) && count($relatedProducts) > 0)
        <div class="mt-24 pt-12 border-t border-gray-100">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-primary block mb-1">Khám phá thêm</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Sản phẩm liên quan</h2>
                </div>
                <a href="{{ url('/shop') }}" class="text-xs font-bold text-primary hover:text-primary-hover flex items-center gap-1 group">
                    <span>Xem tất cả</span>
                    <span class="transition transform group-hover:translate-x-1">→</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($relatedProducts as $rel)
                    <x-product-card :product="$rel" />
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
