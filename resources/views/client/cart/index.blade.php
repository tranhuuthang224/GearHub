@extends('layouts.client')

@section('content')
<div 
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12"
    x-data="{
        couponCode: '',
        appliedCoupon: null,
        couponDiscount: 0,
        couponError: '',
        couponSuccess: '',
        subtotal: {{ $subtotal }},
        shipping: {{ $shipping }},
        applyCoupon() {
            this.couponError = '';
            this.couponSuccess = '';
            const code = this.couponCode.trim().toUpperCase();
            
            if (!code) {
                this.couponError = 'Vui lòng nhập mã giảm giá';
                return;
            }

            if (code === 'GEARHUB10') {
                this.appliedCoupon = 'GEARHUB10';
                this.couponDiscount = Math.round(this.subtotal * 0.1);
                this.couponSuccess = 'Áp dụng mã GEARHUB10 thành công! Giảm 10% giá trị đơn hàng.';
            } else if (code === 'FREESHIP') {
                if (this.shipping === 0) {
                    this.couponError = 'Đơn hàng của bạn đã được Miễn phí vận chuyển!';
                    return;
                }
                this.appliedCoupon = 'FREESHIP';
                this.couponDiscount = this.shipping;
                this.couponSuccess = 'Áp dụng mã FREESHIP thành công! Miễn phí vận chuyển toàn quốc.';
            } else if (code === 'GEARHUB50') {
                if (this.subtotal < 1000000) {
                    this.couponError = 'Mã GEARHUB50 chỉ áp dụng cho đơn hàng từ 1.000.000đ trở lên.';
                    return;
                }
                this.appliedCoupon = 'GEARHUB50';
                this.couponDiscount = 50000;
                this.couponSuccess = 'Áp dụng mã GEARHUB50 thành công! Giảm trực tiếp 50.000đ.';
            } else {
                this.couponError = 'Mã giảm giá không tồn tại hoặc đã hết lượt sử dụng.';
            }
        },
        removeCoupon() {
            this.appliedCoupon = null;
            this.couponDiscount = 0;
            this.couponSuccess = '';
            this.couponError = '';
            this.couponCode = '';
        },
        get finalTotal() {
            const raw = this.subtotal + this.shipping - this.couponDiscount;
            return Math.max(0, raw);
        }
    }"
>

    <!-- 1. BREADCRUMB (Figma Style) -->
    <nav class="flex items-center gap-2 text-xs text-gray-400 mb-6" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="hover:text-primary transition flex items-center gap-1 font-medium">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Trang chủ
        </a>
        <span class="text-gray-300">/</span>
        <a href="{{ url('/shop') }}" class="hover:text-primary transition font-medium">Sản phẩm</a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-900 font-semibold">Giỏ hàng</span>
    </nav>

    <!-- 2. HEADER TIÊU ĐỀ TRANG -->
    <div class="flex items-end justify-between mb-8 pb-5 border-b border-gray-100 flex-wrap gap-4">
        <div>
            <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-primary mb-1">
                <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                Quản lý giỏ hàng
            </div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-gray-900 tracking-tight">
                Giỏ hàng của bạn
            </h1>
        </div>

        @if (!empty($cart) && count($cart) > 0)
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-gray-600 bg-gray-100 px-3 py-1.5 rounded-full">
                    Tổng cộng: <strong class="text-primary">{{ $count }}</strong> sản phẩm
                </span>

                <form action="{{ route('cart.clear') }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa toàn bộ sản phẩm khỏi giỏ hàng?');">
                    @csrf
                    @method('DELETE')
                    <button 
                        type="submit" 
                        class="text-xs text-gray-400 hover:text-rose-600 font-semibold transition flex items-center gap-1.5 p-1.5 rounded-lg hover:bg-rose-50"
                        title="Xóa sạch giỏ hàng"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Xóa tất cả</span>
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- FLASH MESSAGES -->
    @if (session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between text-xs text-emerald-800 font-medium shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-700 flex items-center justify-center font-bold shrink-0">✓</div>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-bold ml-4">✕</button>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center justify-between text-xs text-rose-800 font-medium shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-6 h-6 rounded-full bg-rose-500/20 text-rose-700 flex items-center justify-center font-bold shrink-0">!</div>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-bold ml-4">✕</button>
        </div>
    @endif

    @if (!empty($cart) && count($cart) > 0)
        <!-- GIAO DIỆN CHÍNH: 2 CỘT (8 COLS DANH SÁCH - 4 COLS TÓM TẮT ĐƠN) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
            
            <!-- CỘT TRÁI (8 COLS): BẢNG DANH SÁCH MÓN HÀNG -->
            <div class="lg:col-span-8 space-y-6">
                
                <div class="bg-white rounded-3xl border border-gray-200/80 shadow-sm overflow-hidden">
                    
                    <!-- Header Bảng (Desktop) -->
                    <div class="hidden sm:grid grid-cols-12 gap-4 px-6 py-4 bg-gray-50/80 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <div class="col-span-6">Sản phẩm</div>
                        <div class="col-span-2 text-right">Đơn giá</div>
                        <div class="col-span-2 text-center">Số lượng</div>
                        <div class="col-span-2 text-right">Thành tiền</div>
                    </div>

                    <!-- Danh sách các dòng sản phẩm -->
                    <div class="divide-y divide-gray-100">
                        @foreach ($cart as $id => $item)
                            @php
                                $lineTotal = $item['price'] * $item['quantity'];
                                $hasDiscount = !empty($item['original_price']) && $item['original_price'] > $item['price'];
                                $maxStock = $item['max_stock'] ?? 99;
                            @endphp
                            <div class="p-5 sm:px-6 sm:py-5 flex flex-col sm:grid sm:grid-cols-12 sm:gap-4 sm:items-center hover:bg-gray-50/40 transition">
                                
                                <!-- Cột 1: Thông tin sản phẩm (Ảnh + Tên + SKU + Phân loại) -->
                                <div class="sm:col-span-6 flex items-center gap-4">
                                    <!-- Khung ảnh thumbnail -->
                                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-[#F4F5F7] p-2.5 flex items-center justify-center shrink-0 border border-gray-100 overflow-hidden group">
                                        <img 
                                            src="{{ $item['thumbnail'] }}" 
                                            alt="{{ $item['name'] }}" 
                                            class="max-h-full max-w-full object-contain drop-shadow transition duration-300 group-hover:scale-105"
                                        >
                                    </div>

                                    <!-- Chi tiết text -->
                                    <div class="space-y-1 min-w-0 flex-1">
                                        <!-- Phân loại danh mục -->
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-primary block">
                                            {{ $item['category'] ?? 'Phụ kiện' }}
                                        </span>

                                        <!-- Tên sản phẩm -->
                                        <a 
                                            href="{{ $item['slug'] !== '#' ? url('/shop/' . $item['slug']) : '#' }}" 
                                            class="text-sm font-bold text-gray-900 hover:text-primary transition line-clamp-2 leading-snug"
                                            title="{{ $item['name'] }}"
                                        >
                                            {{ $item['name'] }}
                                        </a>

                                        <!-- Mã SKU & Tồn kho badge -->
                                        <div class="flex items-center gap-2 flex-wrap text-xs pt-0.5">
                                            <span class="font-mono text-[11px] font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded">
                                                SKU: {{ $item['sku'] ?? 'N/A' }}
                                            </span>

                                            <span class="text-[11px] text-emerald-700 font-semibold flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Còn hàng (Kho: {{ $maxStock }})
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cột 2: Đơn giá -->
                                <div class="sm:col-span-2 text-left sm:text-right mt-3 sm:mt-0 flex sm:flex-col items-baseline sm:items-end justify-between sm:justify-center gap-1">
                                    <span class="sm:hidden text-xs text-gray-400 font-medium">Đơn giá:</span>
                                    <div>
                                        <span class="text-sm font-black text-gray-900 block">
                                            {{ number_format($item['price'], 0, ',', '.') }}đ
                                        </span>
                                        @if ($hasDiscount)
                                            <span class="text-xs text-gray-400 line-through block">
                                                {{ number_format($item['original_price'], 0, ',', '.') }}đ
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Cột 3: Bộ tăng / giảm số lượng (+ / -) có kiểm tra tồn kho -->
                                <div class="sm:col-span-2 flex items-center justify-between sm:justify-center mt-3 sm:mt-0">
                                    <span class="sm:hidden text-xs text-gray-400 font-medium">Số lượng:</span>
                                    
                                    <form action="{{ route('cart.update') }}" method="POST" class="inline-flex items-center border border-gray-200 rounded-2xl bg-[#F4F5F7] p-1 shadow-inner">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="product_id" value="{{ $id }}">

                                        <!-- Giảm số lượng -->
                                        <button 
                                            type="submit" 
                                            name="quantity" 
                                            value="{{ $item['quantity'] - 1 }}" 
                                            class="w-7 h-7 rounded-xl flex items-center justify-center text-gray-700 hover:bg-white hover:text-black font-bold text-xs transition shadow-sm active:scale-90"
                                            title="Giảm 1 sản phẩm"
                                        >
                                            –
                                        </button>

                                        <!-- Số lượng hiện tại -->
                                        <input 
                                            type="number" 
                                            value="{{ $item['quantity'] }}" 
                                            readonly 
                                            class="w-10 text-center font-black text-xs text-gray-900 bg-transparent border-none p-0 focus:ring-0 select-none cursor-default"
                                        >

                                        <!-- Tăng số lượng (Chặn nếu đạt max_stock) -->
                                        <button 
                                            type="submit" 
                                            name="quantity" 
                                            value="{{ $item['quantity'] + 1 }}" 
                                            {{ $item['quantity'] >= $maxStock ? 'disabled' : '' }}
                                            class="w-7 h-7 rounded-xl flex items-center justify-center text-gray-700 hover:bg-white hover:text-black font-bold text-xs transition shadow-sm active:scale-90 disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-transparent"
                                            title="{{ $item['quantity'] >= $maxStock ? 'Đã đạt số lượng tồn kho tối đa' : 'Tăng 1 sản phẩm' }}"
                                        >
                                            +
                                        </button>
                                    </form>
                                </div>

                                <!-- Cột 4: Thành tiền từng dòng & Nút xóa món -->
                                <div class="sm:col-span-2 flex items-center justify-between sm:justify-end gap-3 mt-3 sm:mt-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-100">
                                    <span class="sm:hidden text-xs text-gray-400 font-medium">Thành tiền:</span>
                                    
                                    <div class="text-right">
                                        <span class="text-base font-black text-primary block tracking-tight">
                                            {{ number_format($lineTotal, 0, ',', '.') }}đ
                                        </span>
                                    </div>

                                    <!-- Nút xóa món -->
                                    <form action="{{ route('cart.remove', $id) }}" method="POST" onsubmit="return confirm('Bạn muốn xóa sản phẩm này khỏi giỏ hàng?');">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="p-2 text-gray-300 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition duration-200" 
                                            title="Xóa sản phẩm này"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>

                            </div>
                        @endforeach
                    </div>

                    <!-- Footer danh sách: Nút tiếp tục mua sắm -->
                    <div class="p-4 sm:px-6 bg-gray-50/60 border-t border-gray-100 flex items-center justify-between flex-wrap gap-3">
                        <a 
                            href="{{ url('/shop') }}" 
                            class="inline-flex items-center gap-2 text-xs font-bold text-gray-700 hover:text-primary transition group"
                        >
                            <span class="transition transform group-hover:-translate-x-1">←</span>
                            <span>Tiếp tục chọn thêm phụ kiện khác</span>
                        </a>

                        <span class="text-[11px] text-gray-400">
                            Giá sản phẩm đã bao gồm bảo hành chính hãng từ GearHub
                        </span>
                    </div>

                </div>

                <!-- Khối cam kết dịch vụ dưới giỏ hàng -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <div class="p-4 rounded-2xl bg-white border border-gray-100 shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-red-50 text-primary flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-xs text-gray-900">Giao hỏa tốc 2 Giờ</h4>
                            <p class="text-[11px] text-gray-400">Nội thành TP.HCM & Hà Nội</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-white border border-gray-100 shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-xs text-gray-900">100% Chính hãng</h4>
                            <p class="text-[11px] text-gray-400">Đền 200% nếu phát hiện giả</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-white border border-gray-100 shadow-sm flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-xs text-gray-900">Lỗi 1 đổi 1 30N</h4>
                            <p class="text-[11px] text-gray-400">Thủ tục nhanh gọn 15 phút</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- CỘT PHẢI (4 COLS): BOX TÓM TẮT ĐƠN HÀNG (STICKY) -->
            <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-28">
                
                <div class="bg-white rounded-3xl border border-gray-200/80 shadow-sm p-6 sm:p-7 space-y-5">
                    
                    <h3 class="text-lg font-black text-gray-900 tracking-tight pb-3 border-b border-gray-100 flex items-center justify-between">
                        <span>Tóm tắt đơn hàng</span>
                        <span class="text-xs font-bold text-gray-400">{{ count($cart) }} món</span>
                    </h3>

                    <!-- Thanh Tiến độ Miễn phí vận chuyển (Freeship Progress Bar) -->
                    <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-gray-100 space-y-2">
                        @php
                            $freeShippingThreshold = 500000;
                            $diff = $freeShippingThreshold - $subtotal;
                            $percent = min(100, max(0, round(($subtotal / $freeShippingThreshold) * 100)));
                        @endphp

                        <div class="flex items-center justify-between text-xs">
                            @if ($diff > 0)
                                <span class="text-gray-600">
                                    Mua thêm <strong class="text-primary">{{ number_format($diff, 0, ',', '.') }}đ</strong> để được:
                                </span>
                                <span class="font-bold text-primary uppercase text-[11px]">Free Ship</span>
                            @else
                                <span class="font-bold text-emerald-700 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Đã đủ điều kiện Free Ship toàn quốc!
                                </span>
                            @endif
                        </div>

                        <!-- Progress Bar Track -->
                        <div class="w-full h-2 rounded-full bg-gray-200 overflow-hidden">
                            <div 
                                class="h-full rounded-full transition-all duration-500 {{ $diff <= 0 ? 'bg-emerald-500' : 'bg-primary' }}"
                                style="width: {{ $percent }}%;"
                            ></div>
                        </div>

                        <p class="text-[10px] text-gray-400 italic">
                            * Áp dụng tự động cho tất cả đơn hàng từ 500.000đ trở lên.
                        </p>
                    </div>

                    <!-- Ô NHẬP VOUCHER / COUPON GIẢM GIÁ (Đúng yêu cầu) -->
                    <div class="space-y-2.5 pt-1">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Mã giảm giá (Voucher)
                        </label>

                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                </div>
                                <input 
                                    type="text" 
                                    x-model="couponCode"
                                    placeholder="Nhập mã (VD: GEARHUB10)"
                                    @keydown.enter.prevent="applyCoupon()"
                                    class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 bg-[#F4F5F7] text-xs font-mono font-bold uppercase placeholder:text-gray-400 placeholder:font-sans placeholder:normal-case focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition"
                                >
                            </div>

                            <button 
                                type="button" 
                                @click="applyCoupon()"
                                class="py-2.5 px-4 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition shrink-0 active:scale-95 shadow-sm"
                            >
                                Áp dụng
                            </button>
                        </div>

                        <!-- Thông báo lỗi hoặc thành công khi nhập coupon -->
                        <div x-show="couponError" class="text-xs text-rose-600 font-medium flex items-center gap-1.5 pt-1" style="display: none;">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span x-text="couponError"></span>
                        </div>

                        <div x-show="couponSuccess" class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-medium flex items-center justify-between" style="display: none;">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span x-text="couponSuccess"></span>
                            </div>
                            <button type="button" @click="removeCoupon()" class="text-emerald-700 hover:text-rose-600 font-bold ml-2">✕</button>
                        </div>

                        <!-- Gợi ý Voucher cho khách hàng -->
                        <div class="flex items-center gap-2 pt-1 flex-wrap text-[11px] text-gray-500">
                            <span>Mã gợi ý:</span>
                            <button 
                                type="button" 
                                @click="couponCode = 'GEARHUB10'; applyCoupon();" 
                                class="px-2 py-0.5 rounded-md bg-gray-100 hover:bg-red-50 text-gray-700 hover:text-primary font-mono font-bold transition border border-gray-200"
                            >
                                GEARHUB10 (-10%)
                            </button>
                            <button 
                                type="button" 
                                @click="couponCode = 'FREESHIP'; applyCoupon();" 
                                class="px-2 py-0.5 rounded-md bg-gray-100 hover:bg-emerald-50 text-gray-700 hover:text-emerald-700 font-mono font-bold transition border border-gray-200"
                            >
                                FREESHIP
                            </button>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <!-- Các dòng tính toán chi phí -->
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Tạm tính tiền hàng:</span>
                            <span class="font-bold text-gray-900">{{ number_format($subtotal, 0, ',', '.') }}đ</span>
                        </div>

                        <div class="flex items-center justify-between text-gray-600">
                            <span>Phí vận chuyển:</span>
                            @if ($shipping === 0)
                                <span class="font-bold text-emerald-600 flex items-center gap-1">
                                    <span>Miễn phí</span>
                                    <span class="text-[10px] bg-emerald-100 text-emerald-800 px-1.5 py-0.2 rounded font-black">FREE</span>
                                </span>
                            @else
                                <span class="font-bold text-gray-900">{{ number_format($shipping, 0, ',', '.') }}đ</span>
                            @endif
                        </div>

                        <!-- Giảm giá từ Coupon (nếu có) -->
                        <template x-if="couponDiscount > 0">
                            <div class="flex items-center justify-between text-emerald-700 font-medium">
                                <span>Giảm giá khuyến mãi:</span>
                                <span>-<span x-text="couponDiscount.toLocaleString('vi-VN')"></span>đ</span>
                            </div>
                        </template>

                        <!-- Dòng Tổng thanh toán lớn -->
                        <div class="pt-3 border-t border-gray-200/80 flex items-baseline justify-between">
                            <div>
                                <span class="text-sm font-bold text-gray-900 block">Tổng thanh toán:</span>
                                <span class="text-[11px] text-gray-400">Đã bao gồm VAT & bảo hiểm</span>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl sm:text-3xl font-black text-primary tracking-tight">
                                    <template x-if="couponDiscount === 0">
                                        <span>{{ number_format($total, 0, ',', '.') }}đ</span>
                                    </template>
                                    <template x-if="couponDiscount > 0">
                                        <span x-text="finalTotal.toLocaleString('vi-VN') + 'đ'"></span>
                                    </template>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- NÚT LỚN CTA: TIẾN HÀNH ĐẶT HÀNG (Đúng yêu cầu) -->
                    <div class="pt-2 space-y-3">
                        <a 
                            href="{{ url('/shop') }}" 
                            class="w-full py-4 px-6 rounded-2xl bg-primary hover:bg-primary-hover text-white font-black text-sm uppercase tracking-wider text-center shadow-xl shadow-primary/30 transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2.5 group"
                        >
                            <svg class="w-5 h-5 transition transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <span>Tiến hành đặt hàng</span>
                        </a>

                        <div class="pt-2 text-center text-xs text-gray-400 flex items-center justify-center gap-4">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                Bảo mật 256-bit SSL
                            </span>
                            <span>•</span>
                            <span>Đồng kiểm khi nhận</span>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    @else
        <!-- 3. KHỐI GIỎ HÀNG TRỐNG (EMPTY STATE VỚI HÌNH MINH HỌA & NÚT KHÁM PHÁ SP NGAY) -->
        <div class="py-16 sm:py-24 max-w-2xl mx-auto text-center">
            
            <!-- Hình minh họa Giỏ hàng trống đẹp mắt -->
            <div class="relative w-32 h-32 mx-auto mb-6 flex items-center justify-center">
                <div class="absolute inset-0 rounded-full bg-red-50 animate-ping opacity-25"></div>
                <div class="relative w-28 h-28 rounded-3xl bg-gradient-to-tr from-gray-50 to-gray-100 border border-gray-200/80 shadow-inner flex items-center justify-center text-gray-300">
                    <svg class="w-14 h-14 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>

            <!-- Tiêu đề & Lời nhắn nhủ -->
            <h2 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight mb-2">
                Giỏ hàng của bạn đang trống!
            </h2>
            <p class="text-xs sm:text-sm text-gray-500 max-w-md mx-auto mb-8 leading-relaxed">
                Có vẻ như bạn chưa chọn được sản phẩm ưng ý. Hãy khám phá hàng trăm phụ kiện gaming gear chính hãng đỉnh cao tại GearHub ngay hôm nay!
            </p>

            <!-- Nút CTA: Khám phá sản phẩm ngay -->
            <a 
                href="{{ url('/shop') }}" 
                class="inline-flex items-center gap-2.5 py-4 px-8 rounded-2xl bg-primary hover:bg-primary-hover text-white text-sm font-bold shadow-xl shadow-primary/25 transition transform hover:-translate-y-0.5 active:translate-y-0"
            >
                <span>Khám phá sản phẩm ngay</span>
                <span class="text-base">→</span>
            </a>

            <!-- Gợi ý danh mục phụ kiện phổ biến khi giỏ hàng trống -->
            <div class="mt-14 pt-10 border-t border-gray-100">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-4">
                    Gợi ý danh mục phụ kiện được yêu thích nhất
                </span>
                
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <a href="{{ url('/shop?categories%5B%5D=Tai+nghe') }}" class="p-3.5 rounded-2xl bg-gray-50 hover:bg-red-50 hover:border-primary/20 border border-gray-100 text-xs font-bold text-gray-700 hover:text-primary transition text-center">
                        🎧 Tai nghe Gaming
                    </a>
                    <a href="{{ url('/shop?categories%5B%5D=Chu%E1%BB%99t') }}" class="p-3.5 rounded-2xl bg-gray-50 hover:bg-red-50 hover:border-primary/20 border border-gray-100 text-xs font-bold text-gray-700 hover:text-primary transition text-center">
                        🖱️ Chuột Gaming
                    </a>
                    <a href="{{ url('/shop?categories%5B%5D=B%C3%A0n+ph%C3%ADm+c%C6%A1') }}" class="p-3.5 rounded-2xl bg-gray-50 hover:bg-red-50 hover:border-primary/20 border border-gray-100 text-xs font-bold text-gray-700 hover:text-primary transition text-center">
                        ⌨️ Bàn phím cơ
                    </a>
                    <a href="{{ url('/shop?categories%5B%5D=Microphone') }}" class="p-3.5 rounded-2xl bg-gray-50 hover:bg-red-50 hover:border-primary/20 border border-gray-100 text-xs font-bold text-gray-700 hover:text-primary transition text-center">
                        🎙️ Micro & Speaker
                    </a>
                </div>
            </div>

        </div>
    @endif

</div>
@endsection
