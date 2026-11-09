@extends('layouts.client')

@section('content')
<script>
function productDetailOrder(productId, maxStock, cartAddUrl, checkoutUrl, csrfToken) {
    return {
        qty: 1,
        maxStock: maxStock,
        addedToast: false,
        toastMessage: '',
        loading: false,
        addToCart(isBuyNow = false) {
            this.loading = true;
            fetch(cartAddUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: this.qty,
                    buy_now: isBuyNow ? 1 : 0
                })
            })
            .then(res => res.json())
            .then(data => {
                this.loading = false;
                if (data.success) {
                    const badge = document.querySelector("a[href*='cart'] span.bg-primary");
                    if (badge && data.cartCount !== undefined) {
                        badge.innerText = data.cartCount;
                    }
                    if (isBuyNow) {
                        window.location.href = data.redirect_url || checkoutUrl;
                    } else {
                        this.toastMessage = data.message || 'Đã thêm vào giỏ hàng!';
                        this.addedToast = true;
                        setTimeout(() => { this.addedToast = false; }, 3500);
                        window.dispatchEvent(new CustomEvent('cart-toast', { detail: { message: this.toastMessage } }));
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
    };
}

function productSpecsCopy() {
    return {
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
                    setTimeout(() => { this.copied = false; }, 2500);
                });
            }
        }
    };
}

function productReviewForm(initialRating, initialComment) {
    return {
        rating: initialRating,
        hoverRating: 0,
        comment: initialComment,
        previews: [],
        submitting: false,
        labels: {
            1: '1 sao: Thất vọng - Rất tệ',
            2: '2 sao: Chưa ưng ý - Cần cải thiện',
            3: '3 sao: Bình thường - Đủ dùng',
            4: '4 sao: Hài lòng - Chất lượng tốt',
            5: '5 sao: Tuyệt vời - Rất đáng tiền'
        },
        handleFileChange(event) {
            const files = Array.from(event.target.files).slice(0, 3);
            this.previews = files.map(function(file) { return URL.createObjectURL(file); });
        },
        removePreview(index) {
            this.previews.splice(index, 1);
            const input = document.getElementById('review-photos-input');
            if (input) input.value = '';
        }
    };
}
</script>

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
                <button 
                    type="button"
                    @click="$dispatch('switch-tab', 'reviews'); document.getElementById('tabs-section').scrollIntoView({ behavior: 'smooth' })"
                    class="flex items-center gap-1.5 bg-amber-50 hover:bg-amber-100/80 transition px-2.5 py-1 rounded-lg border border-amber-200/60 cursor-pointer text-left group"
                    title="Xem chi tiết đánh giá từ khách hàng"
                >
                    @if ($product['reviews_count'] > 0)
                        <span class="text-amber-500 font-bold text-xs flex items-center gap-1">
                            ★ {{ $product['rating'] }}
                        </span>
                        <span class="text-gray-500 group-hover:text-primary transition text-xs underline decoration-dotted underline-offset-2">({{ $product['reviews_count'] }} đánh giá)</span>
                    @else
                        <span class="text-amber-500 font-bold text-xs">★</span>
                        <span class="text-gray-500 group-hover:text-primary transition text-xs underline decoration-dotted underline-offset-2">Chưa có đánh giá (Gửi đầu tiên)</span>
                    @endif
                </button>

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
            <div 
                class="space-y-5 pt-2" 
                x-data="productDetailOrder({{ $product['id'] }}, {{ $product['stock_quantity'] > 0 ? $product['stock_quantity'] : 1 }}, '{{ route('cart.add') }}', '{{ route('checkout.index') }}', '{{ csrf_token() }}')"
            >
                
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

                <!-- Nút Thêm vào so sánh thông số kỹ thuật -->
                <div 
                    x-data="{ isCompared: false }" 
                    x-on:compare-active-ids.window="isCompared = ($event.detail.ids || []).includes({{ $product['id'] }})"
                    class="pt-1"
                >
                    <button
                        type="button"
                        @click="window.dispatchEvent(new CustomEvent('toggle-compare', { detail: { id: {{ $product['id'] }} } }))"
                        :class="isCompared ? 'bg-primary/10 text-primary border-primary/40' : 'bg-gray-50 hover:bg-gray-100 text-gray-700 border-gray-200'"
                        class="w-full py-3 px-5 rounded-2xl border font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition active:scale-[0.99]"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                        <span x-text="isCompared ? '✓ Đang trong danh sách so sánh (Bấm để bỏ)' : '⚖ Thêm vào so sánh thông số'"></span>
                    </button>
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
    <div id="tabs-section" class="mt-20 pt-10 border-t border-gray-100" x-data="{ activeTab: window.location.hash === '#reviews' ? 'reviews' : 'specs' }" x-on:switch-tab.window="activeTab = $event.detail">
        
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
        <div x-show="activeTab === 'specs'" class="pt-8" x-data="productSpecsCopy()">
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
                        <div class="flex items-center gap-2 flex-wrap">
                            <!-- Nút So sánh gear này -->
                            <button 
                                type="button" 
                                onclick="window.dispatchEvent(new CustomEvent('toggle-compare', { detail: { id: {{ $product['id'] }} } }))"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-semibold text-gray-700 transition shadow-sm active:scale-95"
                                title="So sánh với sản phẩm khác"
                            >
                                <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                </svg>
                                <span>So sánh gear này</span>
                            </button>

                            <!-- Nút Sao chép thông số -->
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
                        </div>
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

        <!-- Tab 3: Reviews & Star Ratings (Figma Spec) -->
        <div 
            x-show="activeTab === 'reviews'" 
            style="display: none;" 
            class="pt-8 max-w-5xl space-y-8"
            x-data="{
                filterRating: 'all',
                activePhotoModal: null,
                showReviewForm: {{ $currentUserReview || $errors->any() ? 'true' : 'false' }}
            }"
        >
            <!-- 1. Flash Notifications -->
            @if (session('review_success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between gap-3 text-emerald-800 text-sm font-semibold shadow-sm animate-fade-in">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('review_success') }}</span>
                    </div>
                    <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-xs font-bold">Đóng</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 rounded-2xl text-red-800 text-sm font-medium space-y-1.5 shadow-sm">
                    <div class="font-bold flex items-center gap-2 text-red-900">
                        <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span>Không thể gửi đánh giá. Vui lòng kiểm tra lại:</span>
                    </div>
                    <ul class="list-disc list-inside pl-1 text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- 2. Khối tổng quan: Điểm trung bình, Thanh tỷ lệ số sao & Nút viết đánh giá -->
            <div class="p-6 sm:p-8 bg-gradient-to-br from-[#F8FAFC] to-white rounded-3xl border border-gray-200 shadow-sm">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    
                    <!-- Cột 1: Điểm số trung bình & Nút viết đánh giá (Rộng rãi, không bị co ép) -->
                    <div class="lg:col-span-5 flex flex-col items-center lg:items-start text-center lg:text-left space-y-4 lg:pr-6 lg:border-r lg:border-gray-200/80">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 border border-amber-200/80 text-amber-800 text-xs font-bold uppercase tracking-wider">
                            <span>★ Đánh giá tổng hợp</span>
                        </div>

                        @if ($reviewsCount > 0)
                            <div class="flex items-baseline gap-2">
                                <span class="text-5xl sm:text-6xl font-black text-gray-900 tracking-tight">{{ $averageRating }}</span>
                                <span class="text-gray-400 text-lg font-bold">/ 5.0</span>
                            </div>

                            <!-- 5 Ngôi sao vàng lớn -->
                            <div class="flex items-center gap-1 text-amber-400">
                                @for ($s = 1; $s <= 5; $s++)
                                    @if ($s <= round((float)$averageRating))
                                        <svg class="w-6 h-6 fill-current drop-shadow-xs" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6 text-gray-200 fill-current" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endif
                                @endfor
                            </div>

                            <p class="text-xs text-gray-500 font-medium">
                                Dựa trên <strong class="text-gray-900 font-bold">{{ $reviewsCount }}</strong> đánh giá thực tế từ khách hàng mua sắm
                            </p>
                        @else
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl sm:text-5xl font-black text-gray-400 tracking-tight">0.0</span>
                                <span class="text-gray-400 text-base font-bold">/ 5.0</span>
                            </div>

                            <div class="flex items-center gap-1 text-gray-200">
                                @for ($s = 1; $s <= 5; $s++)
                                    <svg class="w-6 h-6 fill-current" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>

                            <p class="text-xs text-gray-500 font-medium">
                                Sản phẩm chưa có lượt đánh giá nào từ khách hàng
                            </p>
                        @endif

                        <!-- Nút Viết đánh giá: Rộng rãi, chữ nằm ngang thoải mái, không bao giờ bị rớt dòng -->
                        <button 
                            type="button" 
                            @click="showReviewForm = !showReviewForm; $nextTick(() => document.getElementById('review-form-anchor')?.scrollIntoView({ behavior: 'smooth' }))"
                            class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-primary hover:bg-primary/90 text-white font-bold text-xs shadow-md shadow-primary/20 transition flex items-center justify-center gap-2 whitespace-nowrap active:scale-98"
                        >
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span x-text="showReviewForm ? 'Đóng khung đánh giá' : '{{ $reviewsCount > 0 ? 'Viết đánh giá của bạn' : 'Viết đánh giá đầu tiên' }}'">
                                {{ $reviewsCount > 0 ? 'Viết đánh giá của bạn' : 'Viết đánh giá đầu tiên' }}
                            </span>
                        </button>
                    </div>

                    <!-- Cột 2: Thanh tỷ lệ số sao & Hàng cam kết niềm tin (lg:col-span-7) -->
                    <div class="lg:col-span-7 space-y-5">
                        
                        <!-- 5 Dòng thanh tỷ lệ số sao -->
                        <div class="space-y-2.5">
                            @foreach ([5, 4, 3, 2, 1] as $star)
                                <div class="flex items-center gap-3 text-xs">
                                    <span class="w-10 font-bold text-gray-700 flex items-center justify-end gap-1 shrink-0">
                                        {{ $star }} <span class="text-amber-500">★</span>
                                    </span>

                                    <div class="flex-1 bg-gray-100 rounded-full h-3 overflow-hidden p-0.5 border border-gray-200/60 min-w-[120px]">
                                        <div 
                                            class="h-full bg-gradient-to-r from-amber-400 to-amber-500 rounded-full transition-all duration-700"
                                            style="width: {{ $starDistribution[$star]['percent'] }}%"
                                        ></div>
                                    </div>

                                    <div class="w-20 text-right font-medium text-gray-500 shrink-0">
                                        <span class="font-bold text-gray-900">{{ $starDistribution[$star]['count'] }}</span>
                                        <span class="text-[11px] text-gray-400">({{ $starDistribution[$star]['percent'] }}%)</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Hàng cam kết niềm tin (Nằm ngang rộng rãi, không bị co ép) -->
                        <div class="pt-4 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div class="flex items-center gap-3 p-3 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 text-emerald-800">
                                <span class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-xs">
                                    ✓
                                </span>
                                <div>
                                    <span class="font-bold block text-emerald-950 text-xs">100% Đánh giá xác thực</span>
                                    <span class="text-[11px] text-emerald-700">Chỉ từ khách hàng đã mua tại GearHub</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 p-3 rounded-2xl bg-gray-50 border border-gray-200/80 text-gray-800">
                                <span class="w-8 h-8 rounded-xl bg-primary text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-xs">
                                    🛡️
                                </span>
                                <div>
                                    <span class="font-bold block text-gray-900 text-xs">Bảo hành chính hãng</span>
                                    <span class="text-[11px] text-gray-500">Bảo hành 12 - 24 tháng, 1 đổi 1 trong 30 ngày</span>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <!-- Anchor cuộn tới form -->
            <div id="review-form-anchor"></div>

            <!-- 3. KHUNG CHẤM 1–5 SAO, Ô NHẬP NHẬN XÉT, NÚT TẢI ẢNH GÓC CHỤP THẬT -->
            <div 
                x-show="showReviewForm" 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="p-6 sm:p-8 bg-white rounded-3xl border border-gray-200 shadow-lg space-y-6"
                id="review-form-card"
            >
                @auth
                    <div class="flex items-center justify-between flex-wrap gap-4 border-b border-gray-100 pb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-primary to-orange-500 text-white font-black flex items-center justify-center text-sm shadow-sm">
                                {{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                    {{ auth()->user()->name }}
                                    @if ($isVerifiedBuyer)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                            <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Đã mua hàng tại GearHub
                                        </span>
                                    @endif
                                </h3>
                                <p class="text-xs text-gray-500">
                                    {{ $currentUserReview ? 'Bạn đã đánh giá sản phẩm này. Bạn có thể cập nhật nhận xét và ảnh bên dưới.' : 'Chia sẻ góc chụp thật và đánh giá trung thực để nhận điểm thưởng GearHub.' }}
                                </p>
                            </div>
                        </div>

                        @if ($currentUserReview)
                            <form 
                                action="{{ route('shop.reviews.destroy', $product['slug']) }}" 
                                method="POST" 
                                onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài đánh giá này không?')"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-bold text-red-500 hover:text-red-700 transition flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Xóa đánh giá cũ
                                </button>
                            </form>
                        @endif
                    </div>

                    <!-- Alpine Interactive Form Component -->
                    <form 
                        action="{{ route('shop.reviews.store', $product['slug']) }}" 
                        method="POST" 
                        enctype="multipart/form-data"
                        x-data="productReviewForm({{ (int) old('rating', $currentUserReview->rating ?? 5) }}, @js(old('comment', $currentUserReview->comment ?? '')))"
                        @submit="submitting = true"
                        class="space-y-6"
                    >
                        @csrf
                        <input type="hidden" name="rating" :value="rating">

                        <!-- KHUNG CHẤM 1–5 SAO (Hover đổi màu sao vàng hổ phách) -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                                1. Mức độ hài lòng của bạn <span class="text-red-500">*</span>
                            </label>

                            <div class="flex items-center gap-4 flex-wrap bg-gray-50/80 p-4 rounded-2xl border border-gray-200">
                                <div class="flex items-center gap-1.5" @mouseleave="hoverRating = 0">
                                    <template x-for="star in 5" :key="star">
                                        <button 
                                            type="button"
                                            @click="rating = star"
                                            @mouseenter="hoverRating = star"
                                            class="p-1 focus:outline-none transition-transform hover:scale-125 duration-150 cursor-pointer"
                                            :title="star + ' sao'"
                                        >
                                            <svg 
                                                class="w-8 h-8 sm:w-9 sm:h-9 transition-colors duration-150" 
                                                :class="(hoverRating ? star <= hoverRating : star <= rating) ? 'text-amber-400 fill-amber-400 drop-shadow-sm' : 'text-gray-200 fill-gray-200'"
                                                viewBox="0 0 20 20"
                                            >
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        </button>
                                    </template>
                                </div>

                                <span 
                                    class="text-xs sm:text-sm font-bold px-3 py-1.5 rounded-xl bg-white border border-gray-200 text-amber-600 shadow-xs"
                                    x-text="labels[hoverRating || rating]"
                                ></span>
                            </div>
                        </div>

                        <!-- Ô NHẬP NHẬN XÉT -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label for="comment-input" class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                                    2. Nhận xét chi tiết <span class="text-red-500">*</span>
                                </label>
                                <span class="text-xs text-gray-400 font-mono">
                                    <span x-text="comment.length">0</span>/2000 ký tự
                                </span>
                            </div>

                            <textarea 
                                id="comment-input"
                                name="comment" 
                                rows="4" 
                                x-model="comment"
                                required
                                minlength="5"
                                maxlength="2000"
                                placeholder="Hãy chia sẻ trải nghiệm sử dụng thực tế: cảm giác gõ phím/cầm chuột/âm thanh, chất lượng hoàn thiện, thời lượng pin, độ nhạy hay quy cách đóng gói..."
                                class="w-full px-4 py-3.5 text-sm text-gray-900 placeholder-gray-400 bg-white border border-gray-300 rounded-2xl focus:ring-2 focus:ring-primary/20 focus:border-primary transition outline-none resize-y"
                            ></textarea>
                        </div>

                        <!-- NÚT TẢI ẢNH GÓC CHỤP THẬT (Góc setup / Unbox) -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700">
                                    3. Ảnh góc chụp thật (Setup / Unbox thực tế)
                                </label>
                                <span class="text-xs text-gray-400 font-medium">Tối đa 3 ảnh (JPG, PNG, WebP)</span>
                            </div>

                            <div class="flex items-center gap-4 flex-wrap">
                                <label 
                                    for="review-photos-input"
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl border-2 border-dashed border-gray-300 hover:border-primary hover:bg-red-50/40 text-xs font-bold text-gray-700 hover:text-primary transition cursor-pointer group"
                                >
                                    <svg class="w-5 h-5 text-gray-400 group-hover:text-primary transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>Tải ảnh góc chụp thật</span>
                                </label>
                                <input 
                                    type="file" 
                                    id="review-photos-input" 
                                    name="photos[]" 
                                    multiple 
                                    accept="image/jpeg,image/png,image/webp" 
                                    class="hidden"
                                    @change="handleFileChange($event)"
                                >

                                <p class="text-xs text-gray-400">
                                    Khuyên dùng: Ảnh chụp góc máy làm việc hoặc góc unbox để giúp cộng đồng tham khảo trực quan!
                                </p>
                            </div>

                            <!-- Ảnh xem trước (Previews) -->
                            <div class="flex items-center gap-3 flex-wrap pt-1" x-show="previews.length > 0">
                                <template x-for="(preview, idx) in previews" :key="idx">
                                    <div class="relative w-20 h-20 rounded-2xl overflow-hidden border border-gray-200 shadow-xs group">
                                        <img :src="preview" class="w-full h-full object-cover">
                                        <button 
                                            type="button" 
                                            @click="removePreview(idx)" 
                                            class="absolute top-1 right-1 w-5 h-5 bg-red-600 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-700 transition"
                                            title="Xóa ảnh"
                                        >
                                            ×
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <!-- Hiển thị ảnh cũ đã lưu nếu đang sửa review -->
                            @if ($currentUserReview && !empty($currentUserReview->photos))
                                <div class="pt-1">
                                    <span class="text-xs font-bold text-gray-500 block mb-1.5">Ảnh hiện tại:</span>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        @foreach ($currentUserReview->photos as $ph)
                                            <div class="w-16 h-16 rounded-xl overflow-hidden border border-gray-200">
                                                <img 
                                                    src="{{ \Illuminate\Support\Str::startsWith($ph, ['http', 'images/']) ? asset($ph) : asset('storage/' . $ph) }}" 
                                                    class="w-full h-full object-cover"
                                                >
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Submit Buttons -->
                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                            <button 
                                type="button" 
                                @click="showReviewForm = false"
                                class="px-5 py-3 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition"
                            >
                                Hủy bỏ
                            </button>

                            <button 
                                type="submit" 
                                :disabled="submitting"
                                class="px-6 py-3 rounded-2xl bg-primary hover:bg-primary/90 text-white font-bold text-xs shadow-md shadow-primary/20 transition flex items-center gap-2 disabled:opacity-50"
                            >
                                <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span x-text="submitting ? 'Đang gửi...' : '{{ $currentUserReview ? 'Cập nhật đánh giá' : 'Gửi đánh giá & Ảnh' }}'">
                                    {{ $currentUserReview ? 'Cập nhật đánh giá' : 'Gửi đánh giá & Ảnh' }}
                                </span>
                            </button>
                        </div>
                    </form>
                @else
                    <!-- Khách chưa đăng nhập -->
                    <div class="p-8 text-center space-y-4">
                        <div class="w-14 h-14 mx-auto rounded-3xl bg-amber-50 text-amber-500 flex items-center justify-center border border-amber-200 shadow-sm">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <h4 class="text-base font-bold text-gray-900">Đăng nhập để gửi đánh giá sản phẩm</h4>
                        <p class="text-xs text-gray-500 max-w-md mx-auto">
                            Chỉ những tài khoản đã đăng nhập mới có thể gửi đánh giá, chấm sao và đính kèm hình ảnh góc máy thực tế tại GearHub.
                        </p>
                        <div class="pt-2">
                            <a 
                                href="{{ route('login') }}?redirect={{ urlencode(url()->current() . '#reviews') }}"
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-primary hover:bg-primary/90 text-white font-bold text-xs shadow-md shadow-primary/20 transition"
                            >
                                <span>Đăng nhập ngay</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endauth
            </div>

            <!-- 4. DANH SÁCH REVIEWS KÈM NHÃN "ĐÃ MUA HÀNG TẠI GEARHUB" (Verified Purchase) -->
            <div class="space-y-4">
                
                <!-- Thanh công cụ lọc đánh giá (Filter Pills) -->
                <div class="flex items-center justify-between flex-wrap gap-3 border-b border-gray-100 pb-3">
                    <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                        <span>Đánh giá từ khách hàng</span>
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-600 font-bold font-mono">
                            {{ $reviewsCount }}
                        </span>
                    </h3>

                    <!-- Filter buttons -->
                    <div class="flex items-center gap-1.5 flex-wrap text-xs font-bold">
                        <button 
                            type="button" 
                            @click="filterRating = 'all'" 
                            :class="filterRating === 'all' ? 'bg-gray-900 text-white shadow-xs' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 border border-gray-200'"
                            class="px-3 py-1.5 rounded-xl transition"
                        >
                            Tất cả ({{ $reviewsCount }})
                        </button>
                        
                        <button 
                            type="button" 
                            @click="filterRating = 'photo'" 
                            :class="filterRating === 'photo' ? 'bg-gray-900 text-white shadow-xs' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 border border-gray-200'"
                            class="px-3 py-1.5 rounded-xl transition flex items-center gap-1"
                        >
                            <span>📸 Có ảnh</span>
                        </button>

                        @foreach ([5, 4, 3] as $st)
                            @if ($starDistribution[$st]['count'] > 0)
                                <button 
                                    type="button" 
                                    @click="filterRating = '{{ $st }}'" 
                                    :class="filterRating === '{{ $st }}' ? 'bg-amber-500 text-white shadow-xs' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 border border-gray-200'"
                                    class="px-3 py-1.5 rounded-xl transition"
                                >
                                    {{ $st }} ★ ({{ $starDistribution[$st]['count'] }})
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Danh sách từng review -->
                @if ($reviews->isNotEmpty())
                    <div class="space-y-3.5">
                        @foreach ($reviews as $rev)
                            @php
                                $isVerified = in_array($rev->user_id, $verifiedUserIds, true);
                                $hasPhotos = !empty($rev->photos) && is_array($rev->photos) && count($rev->photos) > 0;
                            @endphp

                            <div 
                                class="p-6 bg-white rounded-3xl border border-gray-100 hover:border-gray-200 shadow-xs hover:shadow-md transition-all duration-200 space-y-3"
                                x-show="filterRating === 'all' || (filterRating === 'photo' && {{ $hasPhotos ? 'true' : 'false' }}) || filterRating === '{{ $rev->rating }}'"
                                x-transition
                            >
                                <div class="flex items-start justify-between flex-wrap gap-3">
                                    
                                    <!-- Người đánh giá & Nhãn Verified Purchase -->
                                    <div class="flex items-center gap-3">
                                        <!-- Avatar Initials Gradient -->
                                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-gray-700 to-gray-900 text-white font-bold flex items-center justify-center text-sm shadow-xs flex-shrink-0">
                                            {{ mb_substr($rev->user?->name ?? 'G', 0, 1) }}
                                        </div>

                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-bold text-gray-900 text-sm">
                                                    {{ $rev->user?->name ?? 'Khách hàng GearHub' }}
                                                </span>

                                                <!-- Nhãn "Đã mua hàng tại GearHub" (Verified Purchase) -->
                                                @if ($isVerified)
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200/80 shadow-xs">
                                                        <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                        </svg>
                                                        Đã mua hàng tại GearHub
                                                    </span>
                                                @endif
                                            </div>

                                            <span class="text-[11px] text-gray-400 font-medium">
                                                Đánh giá vào {{ $rev->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Stars badge -->
                                    <div class="flex items-center gap-1 text-amber-500 font-bold text-xs bg-amber-50 px-2.5 py-1 rounded-xl border border-amber-200/60">
                                        <div class="flex items-center text-amber-400">
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= $rev->rating)
                                                    ★
                                                @else
                                                    <span class="text-gray-300">★</span>
                                                @endif
                                            @endfor
                                        </div>
                                        <span class="font-mono">{{ number_format($rev->rating, 1) }}</span>
                                    </div>

                                </div>

                                <!-- Nội dung nhận xét -->
                                <p class="text-xs sm:text-sm text-gray-700 leading-relaxed pl-0 sm:pl-13">
                                    {{ $rev->comment }}
                                </p>

                                <!-- Gallery ảnh góc chụp thật (Setup / Unbox) -->
                                @if ($hasPhotos)
                                    <div class="pl-0 sm:pl-13 pt-1">
                                        <div class="flex items-center gap-2.5 flex-wrap">
                                            @foreach ($rev->photos as $photo)
                                                @php
                                                    $photoUrl = \Illuminate\Support\Str::startsWith($photo, ['http', 'images/']) 
                                                        ? asset($photo) 
                                                        : asset('storage/' . $photo);
                                                @endphp
                                                <button 
                                                    type="button"
                                                    @click="activePhotoModal = '{{ $photoUrl }}'"
                                                    class="relative w-20 h-20 rounded-2xl overflow-hidden border border-gray-200 hover:border-primary shadow-xs hover:shadow-md transition group focus:outline-none"
                                                    title="Bấm để xem ảnh phóng to"
                                                >
                                                    <img src="{{ $photoUrl }}" alt="Ảnh thực tế" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                                    <div class="absolute inset-0 bg-black/10 group-hover:bg-black/0 transition"></div>
                                                    <div class="absolute bottom-1 right-1 p-1 bg-black/60 rounded-md text-white text-[10px] opacity-0 group-hover:opacity-100 transition">
                                                        🔍
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Tương tác hữu ích -->
                                <div class="pl-0 sm:pl-13 pt-2 flex items-center justify-between text-xs text-gray-400 border-t border-gray-50" x-data="{ liked: false, count: {{ rand(2, 9) }} }">
                                    <button 
                                        type="button" 
                                        @click="liked = !liked; liked ? count++ : count--"
                                        :class="liked ? 'text-primary font-bold' : 'hover:text-gray-600'"
                                        class="inline-flex items-center gap-1.5 transition"
                                    >
                                        <svg class="w-3.5 h-3.5" :fill="liked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                                        </svg>
                                        <span>Hữu ích (<span x-text="count"></span>)</span>
                                    </button>

                                    <span class="text-[11px] text-gray-400">Đã kiểm duyệt bởi GearHub</span>
                                </div>

                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Trạng thái trống (Empty Reviews) -->
                    <div class="p-10 text-center bg-gray-50/70 rounded-3xl border border-dashed border-gray-200 space-y-3">
                        <div class="w-12 h-12 mx-auto rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center border border-amber-200 text-xl">
                            ★
                        </div>
                        <h4 class="text-sm font-bold text-gray-900">Chưa có đánh giá nào cho sản phẩm này</h4>
                        <p class="text-xs text-gray-500 max-w-sm mx-auto">
                            Hãy là khách hàng đầu tiên chia sẻ cảm nhận và hình ảnh thực tế để giúp cộng đồng GearHub lựa chọn dễ dàng hơn!
                        </p>
                        <button 
                            type="button" 
                            @click="showReviewForm = true; $nextTick(() => document.getElementById('review-form-anchor')?.scrollIntoView({ behavior: 'smooth' }))"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white border border-gray-200 hover:border-primary text-xs font-bold text-gray-700 hover:text-primary transition shadow-xs"
                        >
                            <span>✍️ Viết đánh giá đầu tiên</span>
                        </button>
                    </div>
                @endif

            </div>

            <!-- MODAL PHÓNG TO ẢNH THỰC TẾ (Lightbox) -->
            <div 
                x-show="activePhotoModal !== null" 
                style="display: none;"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-xs p-4"
                @click.self="activePhotoModal = null"
                @keydown.escape.window="activePhotoModal = null"
            >
                <div class="relative max-w-3xl max-h-[90vh] bg-white rounded-3xl overflow-hidden shadow-2xl p-2 animate-scale-up">
                    <button 
                        type="button" 
                        @click="activePhotoModal = null" 
                        class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-black/70 hover:bg-black text-white flex items-center justify-center text-sm font-bold transition"
                    >
                        ✕
                    </button>
                    <img :src="activePhotoModal" class="max-w-full max-h-[82vh] object-contain rounded-2xl">
                    <p class="text-center text-xs text-gray-500 py-2 font-medium">Ảnh góc chụp thật từ khách hàng đã mua tại GearHub</p>
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
