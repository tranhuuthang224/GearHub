@extends('layouts.client')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-24" x-data="checkoutPage()">

    <!-- BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs text-gray-400 mb-8 flex-wrap" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="hover:text-primary transition flex items-center gap-1 font-medium">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Trang chủ
        </a>
        <span class="text-gray-300">/</span>
        <a href="{{ route('cart.index') }}" class="hover:text-primary transition font-medium">Giỏ hàng</a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-900 font-semibold">Thanh toán</span>
    </nav>

    <!-- TIÊU ĐỀ TRANG -->
    <div class="mb-8">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 border border-red-100 text-primary text-xs font-bold uppercase tracking-wider mb-2">
            <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
            Hoàn tất đơn hàng
        </div>
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-gray-900 tracking-tight">
            Thanh toán & Giao hàng
        </h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1">
            Vui lòng kiểm tra kỹ thông tin người nhận và đơn hàng trước khi tiến hành thanh toán COD.
        </p>
    </div>

    <!-- THÔNG BÁO LỖI NẾU CÓ -->
    @if (session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3 text-xs text-rose-800 font-medium shadow-sm">
            <div class="w-6 h-6 rounded-full bg-rose-500/20 text-rose-700 flex items-center justify-center font-bold shrink-0">!</div>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800 font-medium space-y-1">
            <div class="font-bold">Vui lòng kiểm tra lại thông tin:</div>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- KHUNG CHÍNH: 2 CỘT (FORM GIAO HÀNG TRÁI - TÓM TẮT ĐƠN PHẢI) -->
    <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form">
        @csrf
        <input type="hidden" name="coupon_code" :value="appliedCouponCode">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
            
            <!-- CỘT TRÁI (7 COLS): THÔNG TIN GIAO HÀNG & PHƯƠNG THỨC THANH TOÁN -->
            <div class="lg:col-span-7 space-y-6">
                
                <!-- 1. Thông tin giao nhận -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-sm space-y-5">
                    <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                        <div class="w-9 h-9 rounded-2xl bg-primary/10 text-primary flex items-center justify-center font-bold">
                            1
                        </div>
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-gray-900">Địa chỉ nhận hàng</h2>
                            <p class="text-xs text-gray-500">Đơn hàng sẽ được nhân viên giao tận nơi theo địa chỉ này</p>
                        </div>
                    </div>

                    <div class="space-y-4 pt-1">
                        <!-- Họ và tên -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Họ và tên người nhận <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="receiver_name" 
                                value="{{ old('receiver_name', $user->name ?? '') }}" 
                                required
                                placeholder="Ví dụ: Nguyễn Văn A"
                                class="w-full px-4 py-3 rounded-2xl border border-gray-200 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 transition placeholder:text-gray-400"
                            >
                        </div>

                        <!-- Số điện thoại & Email -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Số điện thoại <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    type="tel" 
                                    name="phone" 
                                    value="{{ old('phone') }}" 
                                    required
                                    placeholder="Ví dụ: 0912345678"
                                    class="w-full px-4 py-3 rounded-2xl border border-gray-200 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 transition placeholder:text-gray-400"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Email tài khoản
                                </label>
                                <input 
                                    type="email" 
                                    value="{{ $user->email ?? '' }}" 
                                    disabled
                                    class="w-full px-4 py-3 rounded-2xl border border-gray-200 bg-gray-50 text-sm text-gray-500 cursor-not-allowed"
                                >
                            </div>
                        </div>

                        <!-- Địa chỉ giao hàng chi tiết -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Địa chỉ giao hàng chi tiết <span class="text-rose-500">*</span>
                            </label>
                            <textarea 
                                name="shipping_address" 
                                rows="3" 
                                required
                                placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố..."
                                class="w-full px-4 py-3 rounded-2xl border border-gray-200 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 transition placeholder:text-gray-400"
                            >{{ old('shipping_address') }}</textarea>
                        </div>

                        <!-- Ghi chú cho tài xế -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Ghi chú đơn hàng (Tùy chọn)
                            </label>
                            <input 
                                type="text" 
                                name="note" 
                                value="{{ old('note') }}" 
                                placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi đến..."
                                class="w-full px-4 py-3 rounded-2xl border border-gray-200 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 transition placeholder:text-gray-400"
                            >
                        </div>
                    </div>
                </div>

                <!-- 2. Phương thức thanh toán (COD) -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-sm space-y-4">
                    <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                        <div class="w-9 h-9 rounded-2xl bg-primary/10 text-primary flex items-center justify-center font-bold">
                            2
                        </div>
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-gray-900">Phương thức thanh toán</h2>
                            <p class="text-xs text-gray-500">Lựa chọn hình thức thanh toán thuận tiện nhất</p>
                        </div>
                    </div>

                    <div class="space-y-3 pt-1">
                        <label class="flex items-start gap-3.5 p-4 rounded-2xl border-2 border-primary bg-primary/5 cursor-pointer transition">
                            <input type="radio" name="payment_method" value="cod" checked class="mt-1 text-primary focus:ring-primary/20">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-gray-900">Thanh toán khi nhận hàng (COD)</span>
                                    <span class="text-[11px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">Khuyên dùng</span>
                                </div>
                                <p class="text-xs text-gray-500 leading-relaxed">
                                    Quý khách chỉ phải thanh toán tiền mặt cho shipper sau khi nhận và kiểm tra kiện hàng.
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

            </div>

            <!-- CỘT PHẢI (5 COLS): TÓM TẮT ĐƠN HÀNG & MÃ GIẢM GIÁ -->
            <div class="lg:col-span-5 space-y-6">
                
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-sm space-y-6 sticky top-28">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <h3 class="text-base sm:text-lg font-black text-gray-900 tracking-tight">
                            Đơn hàng của bạn
                        </h3>
                        <span class="text-xs font-bold text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full">
                            {{ count($cart) }} sản phẩm
                        </span>
                    </div>

                    <!-- Danh sách sản phẩm thu gọn -->
                    <div class="space-y-3 max-h-72 overflow-y-auto pr-1 divide-y divide-gray-100">
                        @foreach ($cart as $item)
                            <div class="pt-3 first:pt-0 flex items-center gap-3">
                                <div class="w-14 h-14 rounded-2xl bg-[#F4F5F7] p-1.5 flex items-center justify-center shrink-0 border border-gray-100 overflow-hidden">
                                    <img src="{{ $item['thumbnail'] ?? asset('images/hero-headphone.png') }}" alt="{{ $item['name'] }}" class="max-h-full max-w-full object-contain">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-bold text-gray-900 truncate" title="{{ $item['name'] }}">
                                        {{ $item['name'] }}
                                    </h4>
                                    <div class="flex items-center justify-between text-xs text-gray-500 mt-1">
                                        <span>SL: x{{ $item['quantity'] }}</span>
                                        <span class="font-bold text-gray-900">
                                            {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}đ
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- MÃ GIẢM GIÁ (COUPON PREVIEW) -->
                    <div class="space-y-2 pt-2 border-t border-gray-100">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Mã giảm giá (Coupon)
                        </label>
                        <div class="flex items-center gap-2">
                            <input 
                                type="text" 
                                x-model="inputCoupon"
                                placeholder="Nhập mã (VD: GEAR10)"
                                @keydown.enter.prevent="checkCoupon()"
                                class="flex-1 px-3.5 py-2.5 rounded-xl border border-gray-200 bg-[#F4F5F7] text-xs font-mono font-bold uppercase placeholder:font-sans placeholder:normal-case placeholder:text-gray-400 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary transition"
                            >
                            <button 
                                type="button" 
                                @click="checkCoupon()"
                                :disabled="checkingCoupon"
                                class="py-2.5 px-4 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition shadow-sm disabled:opacity-50"
                            >
                                <span x-text="checkingCoupon ? '...' : 'Áp dụng'">Áp dụng</span>
                            </button>
                        </div>

                        <!-- Thông báo Coupon -->
                        <div x-show="couponError" class="text-xs text-rose-600 font-medium pt-1" style="display: none;">
                            <span x-text="couponError"></span>
                        </div>

                        <div x-show="appliedCouponCode" class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-medium flex items-center justify-between" style="display: none;">
                            <div class="flex items-center gap-1.5 truncate">
                                <span>✓</span>
                                <span x-text="couponSuccessMessage"></span>
                            </div>
                            <button type="button" @click="removeCoupon()" class="text-emerald-700 hover:text-rose-600 font-bold ml-2">✕</button>
                        </div>
                    </div>

                    <!-- TÍNH TOÁN CHI PHÍ -->
                    <div class="space-y-2.5 pt-2 border-t border-gray-100 text-xs text-gray-600">
                        <div class="flex items-center justify-between">
                            <span>Tạm tính tiền hàng:</span>
                            <span class="font-bold text-gray-900">{{ number_format($subtotal, 0, ',', '.') }}đ</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span>Phí giao hàng:</span>
                            @if ($shipping === 0)
                                <span class="font-bold text-emerald-600">Miễn phí toàn quốc</span>
                            @else
                                <span class="font-bold text-gray-900">{{ number_format($shipping, 0, ',', '.') }}đ</span>
                            @endif
                        </div>

                        <!-- Giảm giá khuyến mãi nếu có -->
                        <template x-if="discountAmount > 0">
                            <div class="flex items-center justify-between text-emerald-700 font-bold">
                                <span>Giảm giá (<span x-text="appliedCouponCode"></span>):</span>
                                <span>-<span x-text="discountAmount.toLocaleString('vi-VN')"></span>đ</span>
                            </div>
                        </template>

                        <!-- Tổng thanh toán lớn -->
                        <div class="pt-3 border-t border-gray-200 flex items-baseline justify-between text-base">
                            <div>
                                <span class="font-black text-gray-900 block">Tổng thanh toán:</span>
                                <span class="text-[11px] text-gray-400">Đã bao gồm VAT & vận chuyển</span>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl sm:text-3xl font-black text-primary tracking-tight" x-text="finalTotal.toLocaleString('vi-VN') + 'đ'">
                                    {{ number_format($total, 0, ',', '.') }}đ
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- NÚT XÁC NHẬN ĐẶT HÀNG -->
                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="w-full py-4 px-6 rounded-2xl bg-primary hover:bg-primary-hover text-white font-black text-sm uppercase tracking-wider text-center shadow-xl shadow-primary/30 transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Xác nhận đặt hàng (COD)</span>
                        </button>
                    </div>

                    <!-- Cam kết nhanh -->
                    <div class="pt-2 text-center text-[11px] text-gray-400 space-y-1">
                        <p>🛡️ Kiểm tra hàng thoải mái trước khi thanh toán</p>
                        <p>🔄 Lỗi 1 đổi 1 trong 30 ngày nếu phát sinh lỗi từ nhà sản xuất</p>
                    </div>

                </div>

            </div>

        </div>
    </form>

</div>

<script>
function checkoutPage() {
    return {
        baseSubtotal: {{ $subtotal }},
        shippingFee: {{ $shipping }},
        inputCoupon: '',
        appliedCouponCode: '',
        discountAmount: 0,
        couponError: '',
        couponSuccessMessage: '',
        checkingCoupon: false,

        get finalTotal() {
            const calculated = this.baseSubtotal + this.shippingFee - this.discountAmount;
            return calculated > 0 ? calculated : 0;
        },

        checkCoupon() {
            const code = this.inputCoupon.trim();
            if (!code) {
                this.couponError = 'Vui lòng nhập mã giảm giá.';
                return;
            }

            this.checkingCoupon = true;
            this.couponError = '';

            fetch('{{ route('checkout.coupon') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    code: code,
                    subtotal: this.baseSubtotal
                })
            })
            .then(res => res.json())
            .then(data => {
                this.checkingCoupon = false;
                if (data.valid) {
                    this.appliedCouponCode = data.code;
                    this.discountAmount = data.discount;
                    this.couponSuccessMessage = data.message;
                    this.couponError = '';
                } else {
                    this.couponError = data.message || 'Mã giảm giá không hợp lệ.';
                    this.removeCoupon();
                }
            })
            .catch(() => {
                this.checkingCoupon = false;
                this.couponError = 'Không thể kiểm tra mã giảm giá lúc này.';
            });
        },

        removeCoupon() {
            this.appliedCouponCode = '';
            this.discountAmount = 0;
            this.couponSuccessMessage = '';
        }
    }
}
</script>
@endsection
