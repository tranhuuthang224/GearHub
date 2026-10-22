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

            <!-- BỘ CHỌN SỐ LƯỢNG & NÚT CTA (Alpine.js kết nối Cart Backend) -->
            <div class="space-y-5 pt-2" x-data="{ 
                qty: 1, 
                maxStock: {{ $product['stock_quantity'] > 0 ? $product['stock_quantity'] : 1 }},
                addedToast: false,
                toastMessage: '',
                loading: false,
                addToCart(isBuyNow = false) {
                    this.loading = true;
                    fetch('{{ route('cart.add') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            product_id: {{ $product['id'] }},
                            quantity: this.qty
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.loading = false;
                        if (data.success) {
                            if (isBuyNow) {
                                window.location.href = '{{ route('cart.index') }}';
                            } else {
                                this.toastMessage = data.message || 'Đã thêm vào giỏ hàng!';
                                this.addedToast = true;
                                setTimeout(() => this.addedToast = false, 3500);
                            }
                        } else {
                            alert(data.message || 'Không thể thêm sản phẩm vào giỏ.');
                        }
                    })
                    .catch(() => {
                        this.loading = false;
                        alert('Có lỗi xảy ra khi kết nối máy chủ.');
                    });
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
                                :disabled="qty <= 1 || loading"
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
                                :disabled="qty >= maxStock || loading"
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
                            @click="addToCart(false)"
                            :disabled="loading"
                            class="py-4 px-6 rounded-2xl bg-primary hover:bg-primary-hover text-white font-bold text-sm shadow-xl shadow-primary/25 transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2 group disabled:opacity-75"
                        >
                            <svg class="w-5 h-5 transition transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <span x-text="loading ? 'Đang thêm...' : 'Thêm vào giỏ'">Thêm vào giỏ</span>
                        </button>

                        <!-- Nút 2: Mua ngay (Secondary Dark) -->
                        <button 
                            type="button"
                            @click="addToCart(true)"
                            :disabled="loading"
                            class="py-4 px-6 rounded-2xl bg-[#18181B] hover:bg-black text-white font-bold text-sm text-center shadow-lg transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2 disabled:opacity-75"
                        >
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span>Mua ngay</span>
                        </button>
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
                        <span x-text="toastMessage"></span>
                    </div>
                    <a href="{{ route('cart.index') }}" class="font-bold underline text-emerald-900 hover:text-emerald-700">Xem giỏ hàng</a>
                </div>

            </div>

            <!-- 3 Khối cam kết nhanh dưới nút mua -->
            <div class="pt-5 grid grid-cols-1 sm:grid-cols-3 gap-3 border-t border-gray-100 text-xs text-gray-600">
                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-gray-50/80 hover:bg-red-50/50 transition">
                    <svg class="w-4 h-4 text-primary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="font-medium text-gray-800">100% Chính hãng</span>
                </div>
                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-gray-50/80 hover:bg-blue-50/50 transition">
                    <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium text-gray-800">Bảo hành 12 - 24T</span>
                </div>
                <div class="flex items-center gap-2 p-2.5 rounded-xl bg-gray-50/80 hover:bg-emerald-50/50 transition">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span class="font-medium text-gray-800">Lỗi 1 đổi 1 trong 30N</span>
                </div>
            </div>

        </div>

    </div>

    <!-- 3. KHỐI CHÍNH SÁCH ĐỔI TRẢ / BẢO HÀNH / CAM KẾT CHÍNH HÃNG (Figma Page 5) -->
    <section class="mt-16 bg-gradient-to-br from-[#FAFAFA] via-[#F8FAFC] to-[#F1F5F9] rounded-3xl p-6 sm:p-10 border border-gray-200/70 shadow-sm" aria-label="Chính sách bảo hành và cam kết">
        <div class="max-w-3xl mb-8">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 border border-red-100 text-primary text-xs font-bold uppercase tracking-wider mb-2">
                <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                GearHub Service & Warranty
            </div>
            <h2 class="text-xl sm:text-2xl lg:text-3xl font-black text-gray-900 tracking-tight">
                Chính sách bảo hành & Cam kết chất lượng
            </h2>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Tất cả sản phẩm tại GearHub đều tuân thủ tiêu chuẩn phân phối chính hãng nghiêm ngặt cùng dịch vụ hậu mãi số 1 cho game thủ.
            </p>
        </div>

        <!-- 4 Cột Cam kết cốt lõi -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- 1. Cam kết chính hãng 100% -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition duration-300 flex flex-col justify-between group">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-red-50 text-primary flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-primary group-hover:text-white transition duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm mb-1.5">100% Hàng chính hãng</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Phân phối chính ngạch từ HyperX, Razer, Logitech, Keychron. Nguyên seal, tem chống giả & hóa đơn VAT minh bạch.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100">
                    <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full inline-block">
                        Bồi hoàn 200% nếu phát hiện hàng giả
                    </span>
                </div>
            </div>

            <!-- 2. Bảo hành 12 - 24 tháng chính hãng -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition duration-300 flex flex-col justify-between group">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm mb-1.5">Bảo hành 12 – 24 Tháng</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Kích hoạt bảo hành điện tử theo Serial/IMEI. Tiếp nhận và gửi bảo hành nhanh chóng tại hệ thống TTBH ủy quyền toàn quốc.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100">
                    <span class="text-[11px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full inline-block">
                        Xử lý trả bảo hành siêu tốc 7 ngày
                    </span>
                </div>
            </div>

            <!-- 3. Lỗi 1 đổi 1 trong 30 ngày -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition duration-300 flex flex-col justify-between group">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-amber-600 group-hover:text-white transition duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm mb-1.5">Lỗi 1 đổi 1 trong 30 ngày</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Yên tâm trải nghiệm thiết bị mới. Đổi ngay sản phẩm mới 100% nếu phát sinh lỗi phần cứng từ nhà sản xuất trong tháng đầu.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100">
                    <span class="text-[11px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full inline-block">
                        Thẩm định kỹ thuật trong 15 phút
                    </span>
                </div>
            </div>

            <!-- 4. Giao hàng hỏa tốc 2 giờ -->
            <div class="bg-white p-5 sm:p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition duration-300 flex flex-col justify-between group">
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center mb-4 group-hover:scale-110 group-hover:bg-purple-600 group-hover:text-white transition duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm mb-1.5">Giao hỏa tốc 2 Giờ</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Nhận hàng siêu tốc trong 2H tại nội thành TP.HCM & Hà Nội. Miễn phí vận chuyển toàn quốc cho tất cả đơn hàng từ 500K.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100">
                    <span class="text-[11px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-full inline-block">
                        Đồng kiểm & thử hàng khi nhận
                    </span>
                </div>
            </div>
        </div>

        <!-- Thanh dịch vụ hỗ trợ khách hàng bổ sung -->
        <div class="mt-6 pt-5 border-t border-gray-200/60 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs text-gray-600">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-red-100/70 text-primary flex items-center justify-center font-bold text-sm shrink-0">📞</span>
                <div>
                    <span class="font-bold text-gray-900">Hotline bảo hành & kỹ thuật:</span>
                    <a href="tel:19006868" class="text-primary font-bold hover:underline ml-1">1900 6868</a>
                    <span class="text-[11px] text-gray-400 block sm:inline">(8:30 - 21:30)</span>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-blue-100/70 text-blue-700 flex items-center justify-center font-bold text-sm shrink-0">🛠️</span>
                <div>
                    <span class="font-bold text-gray-900">Vệ sinh & Mod Gear:</span>
                    <span class="text-gray-600">Miễn phí vệ sinh switch, tai nghe trọn đời</span>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-emerald-100/70 text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">🔄</span>
                <div>
                    <span class="font-bold text-gray-900">Đổi hàng theo nhu cầu:</span>
                    <span class="text-gray-600">Hỗ trợ đổi mẫu mới trong 7 ngày (nguyên seal)</span>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. TABS: THÔNG SỐ KỸ THUẬT, MÔ TẢ & ĐÁNH GIÁ (Figma Page 5) -->
    <div class="mt-20 pt-10 border-t border-gray-100" x-data="{ activeTab: 'specs' }">
        
        <!-- Tab Navigation Buttons -->
        <div class="flex items-center gap-8 border-b border-gray-200 pb-4 text-sm font-bold flex-wrap">
            <button 
                type="button"
                @click="activeTab = 'specs'" 
                :class="activeTab === 'specs' ? 'text-primary border-b-2 border-primary -mb-4 pb-4' : 'text-gray-400 hover:text-gray-700'"
                class="transition focus:outline-none flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                </svg>
                <span>Thông số kỹ thuật</span>
            </button>

            <button 
                type="button"
                @click="activeTab = 'desc'" 
                :class="activeTab === 'desc' ? 'text-primary border-b-2 border-primary -mb-4 pb-4' : 'text-gray-400 hover:text-gray-700'"
                class="transition focus:outline-none flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
                <span>Mô tả chi tiết</span>
            </button>

            <button 
                type="button"
                @click="activeTab = 'reviews'" 
                :class="activeTab === 'reviews' ? 'text-primary border-b-2 border-primary -mb-4 pb-4' : 'text-gray-400 hover:text-gray-700'"
                class="transition focus:outline-none flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                <span>Đánh giá ({{ $product['reviews_count'] }})</span>
            </button>
        </div>

        <!-- Tab 1: Specifications (JSON Specs Table) -->
        <div x-show="activeTab === 'specs'" class="pt-8" x-data="{ 
            copied: false,
            copySpecs() {
                const rows = Array.from(document.querySelectorAll('#specs-table [data-spec-row]'));
                const specText = rows.map(r => {
                    const k = r.querySelector('[data-spec-key]')?.innerText.trim() || '';
                    const v = r.querySelector('[data-spec-val]')?.innerText.trim() || '';
                    return `${k}: ${v}`;
                }).join('\n');

                if (navigator.clipboard) {
                    navigator.clipboard.writeText(specText).then(() => {
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2500);
                    });
                }
            }
        }">
            @php
                $rawSpecs = $product['specs'] ?? [];
                $parsedSpecs = is_array($rawSpecs) ? $rawSpecs : (json_decode($rawSpecs, true) ?? []);
            @endphp

            <div class="max-w-4xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <!-- Bảng Header & Nút copy -->
                <div class="px-6 py-4 bg-gray-50/80 border-b border-gray-200 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm">Bảng đặc tả thông số kỹ thuật</h3>
                            <p class="text-[11px] text-gray-500">Dữ liệu công bố chính thức từ nhà sản xuất {{ $product['brand'] }}</p>
                        </div>
                    </div>

                    @if (!empty($parsedSpecs) && count($parsedSpecs) > 0)
                        <button 
                            type="button" 
                            @click="copySpecs()" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-semibold text-gray-700 transition shadow-sm active:scale-95"
                            title="Sao chép toàn bộ thông số"
                        >
                            <svg x-show="!copied" class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <svg x-show="copied" class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span x-text="copied ? 'Đã chép thông số!' : 'Sao chép thông số'">Sao chép thông số</span>
                        </button>
                    @endif
                </div>

                @if (!empty($parsedSpecs) && count($parsedSpecs) > 0)
                    <div id="specs-table" class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($parsedSpecs as $key => $val)
                                    @php
                                        $displayVal = is_array($val) ? implode(', ', $val) : $val;
                                        $keyLower = \Illuminate\Support\Str::lower($key);
                                        $isWarranty = \Illuminate\Support\Str::contains($keyLower, ['bảo hành', 'warranty']);
                                        $isBattery = \Illuminate\Support\Str::contains($keyLower, ['pin', 'battery']);
                                        $isConnectivity = \Illuminate\Support\Str::contains($keyLower, ['kết nối', 'chuẩn kết nối', 'connectivity']);
                                        $isDriverOrSensor = \Illuminate\Support\Str::contains($keyLower, ['driver', 'cảm biến', 'sensor', 'dpi']);
                                    @endphp
                                    <tr data-spec-row class="hover:bg-primary/5 transition-colors odd:bg-white even:bg-gray-50/60">
                                        <td data-spec-key class="py-3.5 px-6 font-bold text-gray-800 w-1/3 text-xs sm:text-sm align-middle whitespace-nowrap">
                                            <span class="inline-flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 rounded-full bg-primary/60"></span>
                                                {{ $key }}
                                            </span>
                                        </td>
                                        <td data-spec-val class="py-3.5 px-6 text-gray-700 text-xs sm:text-sm font-medium align-middle">
                                            @if ($isWarranty)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                    </svg>
                                                    {{ $displayVal }}
                                                </span>
                                            @elseif ($isBattery)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                    </svg>
                                                    {{ $displayVal }}
                                                </span>
                                            @elseif ($isConnectivity)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                                                    </svg>
                                                    {{ $displayVal }}
                                                </span>
                                            @elseif ($isDriverOrSensor)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                                                    </svg>
                                                    {{ $displayVal }}
                                                </span>
                                            @else
                                                <span class="text-gray-900 font-semibold">{{ $displayVal }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center gap-2 text-[11px] text-gray-500">
                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Thông số kỹ thuật được trích xuất từ dữ liệu công bố của <strong>{{ $product['brand'] }}</strong>. Sản phẩm thực tế có thể thay đổi nhẹ tùy theo đợt sản xuất và thị trường phân phối.</span>
                    </div>
                @else
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h4 class="font-bold text-gray-800 text-sm mb-1">Đang cập nhật bảng thông số kỹ thuật</h4>
                        <p class="text-xs text-gray-500 max-w-sm mx-auto mb-4">Dữ liệu chi tiết cho sản phẩm này đang được đội ngũ kỹ thuật GearHub tổng hợp.</p>
                        <a href="{{ url('/contact') }}" class="text-xs font-bold text-primary hover:underline">Liên hệ để được tư vấn thông số &rarr;</a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Tab 2: Description Content -->
        <div x-show="activeTab === 'desc'" style="display: none;" class="pt-8 max-w-4xl text-sm text-gray-600 leading-relaxed space-y-6">
            <div class="p-6 bg-gray-50/70 rounded-2xl border border-gray-100 space-y-3">
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                    Tổng quan sản phẩm {{ $product['name'] }}
                </h3>
                <p>
                    {{ $product['description'] }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-5 bg-white rounded-2xl border border-gray-200 space-y-2">
                    <h4 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                        <span class="text-primary font-black">✓</span>
                        Ưu điểm nổi bật
                    </h4>
                    <ul class="text-xs text-gray-600 space-y-2 list-inside list-disc">
                        <li>Thiết kế công thái học cao cấp, đeo thoải mái trong thời gian dài.</li>
                        <li>Chất âm tinh chỉnh chuyên sâu, tối ưu cho thi đấu Esports và giải trí đa phương tiện.</li>
                        <li>Kết nối ổn định, độ trễ cực thấp với công nghệ truyền dẫn không dây thế hệ mới.</li>
                        <li>Khung kim loại gia cố bền bỉ, chống va đập và hao mòn theo thời gian.</li>
                    </ul>
                </div>

                <div class="p-5 bg-white rounded-2xl border border-gray-200 space-y-2">
                    <h4 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                        <span class="text-primary font-black">📦</span>
                        Bộ sản phẩm nguyên hộp bao gồm
                    </h4>
                    <ul class="text-xs text-gray-600 space-y-2 list-inside list-disc">
                        <li>01 x Thiết bị chính hãng <strong>{{ $product['name'] }}</strong></li>
                        <li>01 x Cáp sạc & truyền dữ liệu bọc dù cao cấp</li>
                        <li>01 x USB Dongle thu phát sóng không dây (nếu có)</li>
                        <li>01 x Sách hướng dẫn sử dụng & Thẻ bảo hành điện tử GearHub</li>
                    </ul>
                </div>
            </div>

            <p class="text-xs text-gray-500 pt-2">
                Tất cả sản phẩm bán ra tại <strong>GearHub</strong> đều được cam kết là hàng mới 100% nguyên hộp, bảo hành chính hãng từ nhà sản xuất với đội ngũ hỗ trợ kỹ thuật tận tâm 24/7.
            </p>
        </div>

        <!-- Tab 3: Reviews Preview -->
        <div x-show="activeTab === 'reviews'" style="display: none;" class="pt-8 max-w-4xl space-y-6">
            <!-- Điểm đánh giá tổng quan -->
            <div class="p-6 bg-gray-50/80 rounded-2xl border border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="text-center sm:text-left">
                    <span class="text-4xl font-black text-gray-900 tracking-tight">{{ $product['rating'] }}</span>
                    <span class="text-gray-400 text-sm">/ 5.0</span>
                    <div class="flex items-center justify-center sm:justify-start gap-1 text-amber-500 text-base mt-1">
                        ★★★★★
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Dựa trên {{ $product['reviews_count'] }} đánh giá thực tế từ khách hàng</p>
                </div>

                <div class="flex items-center gap-4 text-xs">
                    <div class="text-center px-4 py-2 bg-white rounded-xl border border-gray-200">
                        <span class="font-bold text-emerald-600 text-sm block">98%</span>
                        <span class="text-gray-500">Khách hàng hài lòng</span>
                    </div>
                    <div class="text-center px-4 py-2 bg-white rounded-xl border border-gray-200">
                        <span class="font-bold text-primary text-sm block">100%</span>
                        <span class="text-gray-500">Đã mua tại GearHub</span>
                    </div>
                </div>
            </div>

            <!-- Danh sách đánh giá mẫu -->
            <div class="space-y-3">
                <div class="p-5 bg-white rounded-2xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between text-xs mb-2">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-gray-900">Trần Hữu Thắng</span>
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                ✓ Đã mua hàng tại GearHub
                            </span>
                        </div>
                        <span class="text-amber-500 font-bold">★★★★★ 5.0</span>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Đóng gói rất cẩn thận bằng hộp xốp chống sốc, nhân viên giao hỏa tốc chỉ mất hơn 1 tiếng là có hàng tại TP.HCM. Thiết bị build chắc chắn, chất âm chi tiết đúng như mô tả của shop!
                    </p>
                    <span class="text-[11px] text-gray-400 mt-2 block">Đánh giá vào 2 ngày trước</span>
                </div>

                <div class="p-5 bg-white rounded-2xl border border-gray-100 shadow-sm">
                    <div class="flex items-center justify-between text-xs mb-2">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-gray-900">Nguyễn Văn An</span>
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                ✓ Đã mua hàng tại GearHub
                            </span>
                        </div>
                        <span class="text-amber-500 font-bold">★★★★★ 5.0</span>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed">
                        Sản phẩm chất lượng cực kỳ cao, pin trâu dùng liên tục cả tuần chưa phải sạc lại. Rất hài lòng về thái độ tư vấn nhiệt tình của shop. 10/10 điểm!
                    </p>
                    <span class="text-[11px] text-gray-400 mt-2 block">Đánh giá vào 1 tuần trước</span>
                </div>
            </div>
        </div>

    </div>

    <!-- 5. KHỐI SẢN PHẨM LIÊN QUAN / CÙNG DANH MỤC (Figma Page 5) -->
    @if (!empty($relatedProducts) && count($relatedProducts) > 0)
        <section class="mt-24 pt-12 border-t border-gray-200/80" aria-label="Sản phẩm liên quan">
            <div class="flex items-end justify-between mb-8 flex-wrap gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-primary mb-1">
                        <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                        Khám phá thêm
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">
                        Sản phẩm cùng danh mục <span class="text-primary">{{ $product['category'] }}</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">
                        Các thiết bị gaming gear được quan tâm nhiều nhất trong cùng phân khúc
                    </p>
                </div>
                
                <a 
                    href="{{ url('/shop?categories%5B%5D=' . urlencode($product['category'])) }}" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-50 hover:bg-red-50 border border-gray-200 hover:border-primary/30 text-xs font-bold text-gray-700 hover:text-primary transition group shadow-sm"
                >
                    <span>Xem tất cả {{ $product['category'] }}</span>
                    <span class="transition transform group-hover:translate-x-1">→</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($relatedProducts as $rel)
                    <x-product-card :product="$rel" />
                @endforeach
            </div>
        </section>
    @endif

</div>
@endsection
