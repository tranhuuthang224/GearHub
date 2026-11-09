@extends('layouts.client')

@section('title', 'Thanh Toán Đơn Hàng | GearHub')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-24" x-data="checkoutPage()">

    <!-- BREADCRUMB (Chuẩn Figma) -->
    <nav class="flex items-center gap-2 text-xs text-gray-400 mb-6 flex-wrap" aria-label="Breadcrumb">
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

    <!-- STEPPER TIẾN TRÌNH ĐẶT HÀNG -->
    <div class="mb-8 bg-white border border-gray-100 rounded-3xl p-4 sm:p-6 shadow-sm">
        <div class="flex items-center justify-between max-w-2xl mx-auto relative">
            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-gray-100 -z-0">
                <div class="h-full bg-primary transition-all duration-500" style="width: 50%;"></div>
            </div>

            <!-- Bước 1: Giỏ hàng -->
            <a href="{{ route('cart.index') }}" class="relative z-10 flex flex-col items-center group">
                <div class="w-9 h-9 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-sm shadow-md transition group-hover:scale-110">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="text-xs font-bold text-gray-700 mt-2">1. Giỏ hàng</span>
            </a>

            <!-- Bước 2: Thanh toán & Giao hàng (Hiện tại) -->
            <div class="relative z-10 flex flex-col items-center">
                <div class="w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center font-black text-sm ring-4 ring-primary/20 shadow-md">
                    2
                </div>
                <span class="text-xs font-black text-primary mt-2">2. Thanh toán & Giao hàng</span>
            </div>

            <!-- Bước 3: Hoàn tất -->
            <div class="relative z-10 flex flex-col items-center">
                <div class="w-9 h-9 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center font-bold text-sm">
                    3
                </div>
                <span class="text-xs font-medium text-gray-400 mt-2">3. Hoàn tất</span>
            </div>
        </div>
    </div>

    <!-- TIÊU ĐỀ TRANG -->
    <div class="mb-8">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 border border-red-100 text-primary text-xs font-bold uppercase tracking-wider mb-2">
            <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
            Thông tin giao nhận
        </div>
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-gray-900 tracking-tight">
            Thanh toán đơn hàng
        </h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1">
            Vui lòng điền địa chỉ nhận hàng và kiểm tra đơn trước khi xác nhận đặt hàng COD.
        </p>
    </div>

    <!-- THÔNG BÁO LỖI HỆ THỐNG NẾU CÓ -->
    @if (session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3 text-xs sm:text-sm text-rose-800 font-medium shadow-sm">
            <div class="w-7 h-7 rounded-full bg-rose-500/20 text-rose-700 flex items-center justify-center font-bold shrink-0">!</div>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- FORM ĐẶT HÀNG CHÍNH: 2 CỘT (7 COLS FORM GIAO HÀNG - 5 COLS ORDER SUMMARY) -->
    <form action="{{ route('checkout.store') }}" method="POST" id="checkout-form" @submit="submitting = true">
        @csrf
        <input type="hidden" name="coupon_code" :value="appliedCouponCode">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
            
            <!-- CỘT TRÁI (7 COLS): THÔNG TIN GIAO HÀNG & PHƯƠNG THỨC THANH TOÁN -->
            <div class="lg:col-span-7 space-y-6">
                
                <!-- 1. THÔNG TIN NHẬN HÀNG -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-sm space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 flex-wrap gap-2">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-2xl bg-primary/10 text-primary flex items-center justify-center font-black text-sm">
                                1
                            </div>
                            <div>
                                <h2 class="text-base sm:text-lg font-black text-gray-900 tracking-tight">
                                    Thông tin người nhận
                                </h2>
                                <p class="text-xs text-gray-500">Nhân viên sẽ liên hệ và giao hàng tới địa chỉ này</p>
                            </div>
                        </div>

                        @if (isset($addresses) && $addresses->isNotEmpty())
                            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-100">
                                Đã lưu {{ $addresses->count() }} địa chỉ
                            </span>
                        @endif
                    </div>

                    <!-- Gợi ý địa chỉ đã lưu (nếu có) -->
                    @if (isset($addresses) && $addresses->isNotEmpty())
                        <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-gray-100 space-y-2.5">
                            <span class="text-xs font-bold text-gray-700 block uppercase tracking-wider">
                                📍 Chọn nhanh từ sổ địa chỉ của bạn:
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach ($addresses as $addr)
                                    <button 
                                        type="button"
                                        @click="fillAddress('{{ addslashes($addr->receiver_name) }}', '{{ addslashes($addr->phone) }}', '{{ addslashes($addr->address_line) }}')"
                                        class="text-left p-3 rounded-xl border border-gray-200 bg-white hover:border-primary hover:bg-red-50/30 transition text-xs space-y-1 group"
                                    >
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-gray-900 group-hover:text-primary transition">{{ $addr->receiver_name }}</span>
                                            @if ($addr->is_default)
                                                <span class="text-[10px] font-bold text-primary bg-red-100 px-1.5 py-0.5 rounded">Mặc định</span>
                                            @endif
                                        </div>
                                        <p class="text-gray-500 text-[11px]">{{ $addr->phone }}</p>
                                        <p class="text-gray-600 text-[11px] truncate">{{ $addr->address_line }}</p>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="space-y-4 pt-1">
                        <!-- Họ và tên người nhận -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Họ và tên người nhận <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <input 
                                    type="text" 
                                    name="receiver_name" 
                                    x-model="receiverName"
                                    required
                                    placeholder="Ví dụ: Nguyễn Văn A"
                                    style="padding-left: 2.75rem !important;"
                                    class="w-full pl-11 pr-4 py-3 rounded-2xl border @error('receiver_name') border-rose-500 bg-rose-50/20 @else border-gray-200 bg-[#F8FAFC] @enderror text-sm focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition placeholder:text-gray-400 font-medium"
                                >
                            </div>
                            @error('receiver_name')
                                <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Số điện thoại & Email -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Số điện thoại -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Số điện thoại người nhận <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                    </div>
                                    <input 
                                        type="tel" 
                                        name="phone" 
                                        x-model="receiverPhone"
                                        required
                                        placeholder="Ví dụ: 0912345678"
                                        style="padding-left: 2.75rem !important;"
                                        class="w-full pl-11 pr-4 py-3 rounded-2xl border @error('phone') border-rose-500 bg-rose-50/20 @else border-gray-200 bg-[#F8FAFC] @enderror text-sm focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition placeholder:text-gray-400 font-medium"
                                    >
                                </div>
                                @error('phone')
                                    <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email tài khoản (Chỉ đọc) -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                    Email tài khoản đặt hàng
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                        </svg>
                                    </div>
                                    <input 
                                        type="email" 
                                        value="{{ $user->email ?? '' }}" 
                                        disabled
                                        style="padding-left: 2.75rem !important;"
                                        class="w-full pl-11 pr-4 py-3 rounded-2xl border border-gray-200 bg-gray-100/70 text-sm text-gray-500 font-medium cursor-not-allowed select-none"
                                    >
                                </div>
                                <p class="text-[11px] text-gray-400 mt-1">Hóa đơn điện tử sẽ tự động gửi về email này</p>
                            </div>
                        </div>

                        <!-- Địa chỉ giao hàng chi tiết -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Địa chỉ giao hàng chi tiết <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute top-3.5 left-3.5 pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <textarea 
                                    name="shipping_address" 
                                    x-model="shippingAddress"
                                    rows="3" 
                                    required
                                    placeholder="Số nhà, tên tòa nhà/tổ dân phố, tên đường, phường/xã, quận/huyện, tỉnh/thành phố..."
                                    style="padding-left: 2.75rem !important;"
                                    class="w-full pl-11 pr-4 py-3 rounded-2xl border @error('shipping_address') border-rose-500 bg-rose-50/20 @else border-gray-200 bg-[#F8FAFC] @enderror text-sm focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition placeholder:text-gray-400 font-medium leading-relaxed"
                                ></textarea>
                            </div>
                            @error('shipping_address')
                                <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Ghi chú đơn hàng cho tài xế -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Ghi chú đơn hàng (Tùy chọn)
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                    </svg>
                                </div>
                                <input 
                                    type="text" 
                                    name="note" 
                                    value="{{ old('note') }}" 
                                    placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi đến 15 phút..."
                                    style="padding-left: 2.75rem !important;"
                                    class="w-full pl-11 pr-4 py-3 rounded-2xl border border-gray-200 bg-[#F8FAFC] text-sm focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition placeholder:text-gray-400 font-medium"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. PHƯƠNG THỨC THANH TOÁN (COD & CHUYỂN KHOẢN VIETQR) -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-sm space-y-5">
                    <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                        <div class="w-9 h-9 rounded-2xl bg-primary/10 text-primary flex items-center justify-center font-black text-sm">
                            2
                        </div>
                        <div>
                            <h2 class="text-base sm:text-lg font-black text-gray-900 tracking-tight">
                                Phương thức thanh toán
                            </h2>
                            <p class="text-xs text-gray-500">Hỗ trợ nhận hàng thanh toán (COD) hoặc Chuyển khoản QR 24/7</p>
                        </div>
                    </div>

                    <div class="space-y-3 pt-1">
                        <!-- Option 1: COD (Thanh toán khi nhận hàng) -->
                        <label 
                            class="flex items-start gap-4 p-4 sm:p-5 rounded-2xl border-2 cursor-pointer transition relative group shadow-sm"
                            :class="paymentMethod === 'cod' ? 'border-primary bg-primary/[0.03]' : 'border-gray-200 hover:border-gray-300 bg-white'"
                        >
                            <input 
                                type="radio" 
                                name="payment_method" 
                                value="cod" 
                                x-model="paymentMethod"
                                class="mt-1 w-4 h-4 text-primary focus:ring-primary border-gray-300"
                            >
                            <div class="space-y-1.5 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <div class="w-7 h-7 rounded-lg bg-red-100 text-primary flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </div>
                                    <span class="font-black text-sm sm:text-base text-gray-900">
                                        Thanh toán khi nhận hàng (COD)
                                    </span>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">
                                        Phổ biến
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed pl-9">
                                    Quý khách chỉ phải thanh toán tiền mặt cho nhân viên giao hàng sau khi đã trực tiếp đồng kiểm kiện hàng. Miễn phí thu hộ toàn quốc.
                                </p>
                            </div>
                        </label>

                        <!-- Option 2: Chuyển khoản ngân hàng qua mã QR (VietQR) -->
                        <label 
                            class="flex items-start gap-4 p-4 sm:p-5 rounded-2xl border-2 cursor-pointer transition relative group shadow-sm"
                            :class="paymentMethod === 'bank_transfer' ? 'border-primary bg-primary/[0.03]' : 'border-gray-200 hover:border-gray-300 bg-white'"
                        >
                            <input 
                                type="radio" 
                                name="payment_method" 
                                value="bank_transfer" 
                                x-model="paymentMethod"
                                class="mt-1 w-4 h-4 text-primary focus:ring-primary border-gray-300"
                            >
                            <div class="space-y-1.5 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                        </svg>
                                    </div>
                                    <span class="font-black text-sm sm:text-base text-gray-900">
                                        Chuyển khoản ngân hàng qua mã QR (VietQR 24/7)
                                    </span>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-blue-700 bg-blue-100 px-2 py-0.5 rounded-full">
                                        Xác nhận nhanh
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed pl-9">
                                    Quét mã QR bằng ứng dụng ngân hàng hoặc ví điện tử bất kỳ. Mã QR tự động điền sẵn số tiền và mã đơn hàng sẽ được hiển thị ngay sau khi bấm đặt hàng.
                                </p>

                                <!-- Khối tóm tắt thông tin tài khoản ngân hàng -->
                                <div x-show="paymentMethod === 'bank_transfer'" class="mt-4 pt-3 border-t border-gray-100 pl-0 sm:pl-9 space-y-2">
                                    <div class="p-3.5 bg-blue-50/60 rounded-2xl border border-blue-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                        <div class="space-y-1 text-xs">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-gray-900">Ngân hàng VietinBank</span>
                                                <span class="text-[10px] text-gray-500">• CN An Giang - PGD Long Xuyên</span>
                                            </div>
                                            <div class="text-gray-700">
                                                Số tài khoản: <span class="font-mono font-bold text-blue-700 text-sm select-all">103882436377</span>
                                            </div>
                                            <div class="text-gray-700">
                                                Chủ tài khoản: <span class="font-bold text-gray-900">TRAN HUU THANG</span>
                                            </div>
                                        </div>
                                        <div class="shrink-0 flex items-center gap-2">
                                            <img src="{{ asset('images/bank-transfer-qr.jpg') }}" alt="Mã QR VietinBank" class="w-16 h-16 object-cover rounded-xl border border-blue-300 shadow-xs">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

            </div>

            <!-- CỘT PHẢI (5 COLS): TÓM TẮT ĐƠN HÀNG (ORDER SUMMARY) -->
            <div class="lg:col-span-5 space-y-6">
                
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-sm space-y-6 lg:sticky lg:top-28">
                    
                    <!-- Tiêu đề tóm tắt đơn -->
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div>
                            <h3 class="text-base sm:text-lg font-black text-gray-900 tracking-tight">
                                Tóm tắt đơn hàng
                            </h3>
                            <p class="text-xs text-gray-400">Kiểm tra danh sách phụ kiện trong giỏ</p>
                        </div>
                        <a 
                            href="{{ route('cart.index') }}" 
                            class="text-xs font-bold text-primary hover:underline flex items-center gap-1"
                            title="Quay lại giỏ hàng để sửa số lượng"
                        >
                            <span>Sửa giỏ</span>
                            <span class="bg-red-50 text-primary px-2 py-0.5 rounded-full font-mono font-bold">{{ count($cart) }}</span>
                        </a>
                    </div>

                    <!-- DANH SÁCH MÓN TRONG GIỎ -->
                    <div class="space-y-3 max-h-72 overflow-y-auto pr-1 divide-y divide-gray-100 scrollbar-thin">
                        @foreach ($cart as $item)
                            <div class="pt-3 first:pt-0 flex items-center gap-3 group">
                                <!-- Ảnh sản phẩm -->
                                <div class="w-14 h-14 rounded-2xl bg-[#F4F5F7] p-1.5 flex items-center justify-center shrink-0 border border-gray-100 overflow-hidden group-hover:border-primary/30 transition">
                                    <img 
                                        src="{{ $item['thumbnail'] ?? asset('images/hero-headphone.png') }}" 
                                        alt="{{ $item['name'] }}" 
                                        loading="lazy"
                                        decoding="async"
                                        class="max-h-full max-w-full object-contain transition group-hover:scale-105"
                                        onerror="this.src='{{ asset('images/hero-headphone.png') }}'"
                                    >
                                </div>

                                <!-- Tên & Số lượng -->
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-bold text-gray-900 truncate" title="{{ $item['name'] }}">
                                        {{ $item['name'] }}
                                    </h4>
                                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $item['category'] ?? 'Phụ kiện' }}</p>
                                    <div class="flex items-center justify-between text-xs text-gray-500 mt-1">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-gray-100 font-mono font-bold text-[11px] text-gray-700">
                                            SL: x{{ $item['quantity'] }}
                                        </span>
                                        <span class="font-black text-gray-900">
                                            {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}đ
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- KHỐI FREE SHIP TỪ 500K (Progress Bar sinh động) -->
                    <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-gray-100 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold flex items-center gap-1.5 text-gray-800">
                                🚚 <span>Chính sách giao hàng:</span>
                            </span>
                            @if ($subtotal >= 500000)
                                <span class="font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                    Đủ điều kiện Free Ship
                                </span>
                            @else
                                <span class="font-semibold text-gray-500">
                                    Thiếu <strong class="text-primary">{{ number_format(500000 - $subtotal, 0, ',', '.') }}đ</strong>
                                </span>
                            @endif
                        </div>

                        <!-- Thanh tiến độ -->
                        <div class="w-full h-2 rounded-full bg-gray-200 overflow-hidden">
                            <div 
                                class="h-full rounded-full transition-all duration-500 @if ($subtotal >= 500000) bg-emerald-500 @else bg-primary @endif"
                                style="width: {{ min(100, round(($subtotal / 500000) * 100)) }}%;"
                            ></div>
                        </div>

                        <p class="text-[11px] text-gray-500 leading-tight">
                            @if ($subtotal >= 500000)
                                🎉 Chúc mừng! Đơn hàng của bạn được <strong>Miễn phí vận chuyển toàn quốc</strong> (Áp dụng cho đơn từ 500.000đ).
                            @else
                                Mua thêm <strong>{{ number_format(500000 - $subtotal, 0, ',', '.') }}đ</strong> để nhận ngay ưu đãi <strong>Free Ship</strong>!
                            @endif
                        </p>
                    </div>

                    <!-- Ô ÁP DỤNG VOUCHER GIẢM GIÁ (COUPON) -->
                    <div class="space-y-2.5 pt-1 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Mã giảm giá / Voucher
                            </label>
                            <span class="text-[10px] text-gray-400">1 mã / đơn</span>
                        </div>

                        <!-- Input & Nút áp dụng -->
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                    </svg>
                                </div>
                                <input 
                                    type="text" 
                                    x-model="inputCoupon"
                                    placeholder="Nhập mã (VD: GEAR10)"
                                    @keydown.enter.prevent="checkCoupon()"
                                    style="padding-left: 2.5rem !important;"
                                    class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-gray-200 bg-[#F4F5F7] text-xs font-mono font-bold uppercase placeholder:font-sans placeholder:normal-case placeholder:text-gray-400 focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary transition"
                                >
                            </div>

                            <button 
                                type="button" 
                                @click="checkCoupon()"
                                :disabled="checkingCoupon"
                                class="py-2.5 px-4 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition shadow-sm disabled:opacity-50 shrink-0 flex items-center gap-1.5"
                            >
                                <svg x-show="checkingCoupon" class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" style="display: none;">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span x-text="checkingCoupon ? 'Đang kiểm tra' : 'Áp dụng'">Áp dụng</span>
                            </button>
                        </div>

                        <!-- Gợi ý mã demo nhanh -->
                        <div class="flex items-center gap-1.5 flex-wrap pt-0.5">
                            <span class="text-[10px] text-gray-400">Gợi ý:</span>
                            <button 
                                type="button" 
                                @click="quickApply('GEAR10')"
                                class="px-2 py-0.5 rounded-md bg-red-50 hover:bg-red-100 text-primary border border-red-200/60 font-mono text-[10px] font-bold transition"
                            >
                                GEAR10 (-10%)
                            </button>
                            <button 
                                type="button" 
                                @click="quickApply('SAVE50K')"
                                class="px-2 py-0.5 rounded-md bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200/60 font-mono text-[10px] font-bold transition"
                            >
                                SAVE50K (-50K)
                            </button>
                        </div>

                        <!-- Thông báo lỗi Coupon -->
                        <div x-show="couponError" class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-700 font-medium flex items-center gap-2" style="display: none;">
                            <span>⚠️</span>
                            <span x-text="couponError"></span>
                        </div>

                        <!-- Thông báo thành công Coupon -->
                        <div x-show="appliedCouponCode" class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-medium flex items-center justify-between shadow-sm" style="display: none;">
                            <div class="flex items-center gap-2 truncate">
                                <span class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px] font-black shrink-0">✓</span>
                                <div class="truncate">
                                    <span class="font-bold font-mono" x-text="appliedCouponCode"></span>:
                                    <span x-text="couponSuccessMessage"></span>
                                </div>
                            </div>
                            <button 
                                type="button" 
                                @click="removeCoupon()" 
                                class="w-6 h-6 rounded-full hover:bg-rose-100 text-gray-400 hover:text-rose-600 flex items-center justify-center font-bold ml-2 transition shrink-0"
                                title="Gỡ mã giảm giá"
                            >
                                ✕
                            </button>
                        </div>
                    </div>

                    <!-- BẢNG TÍNH TOÁN CHI PHÍ & TỔNG THANH TOÁN -->
                    <div class="space-y-3 pt-3 border-t border-gray-100 text-xs text-gray-600">
                        <!-- Tạm tính -->
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Tạm tính tiền hàng:</span>
                            <span class="font-bold text-gray-900 text-sm">
                                {{ number_format($subtotal, 0, ',', '.') }}đ
                            </span>
                        </div>

                        <!-- Phí giao hàng -->
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Phí giao hàng:</span>
                            @if ($shipping === 0)
                                <div class="flex items-center gap-1.5 text-right">
                                    <span class="text-xs text-gray-400 line-through">30.000đ</span>
                                    <span class="font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                        Miễn phí (Free ship)
                                    </span>
                                </div>
                            @else
                                <span class="font-bold text-gray-900">
                                    {{ number_format($shipping, 0, ',', '.') }}đ
                                </span>
                            @endif
                        </div>

                        <!-- Giảm giá khuyến mãi (nếu có) -->
                        <div x-show="discountAmount > 0" class="flex items-center justify-between text-emerald-700 font-bold" style="display: none;">
                            <span class="flex items-center gap-1">
                                <span>Giảm giá Voucher (</span><span class="font-mono" x-text="appliedCouponCode"></span><span>):</span>
                            </span>
                            <span>-<span x-text="discountAmount.toLocaleString('vi-VN')"></span>đ</span>
                        </div>

                        <!-- TỔNG THANH TOÁN LỚN -->
                        <div class="pt-4 border-t border-gray-200 flex items-baseline justify-between text-base">
                            <div>
                                <span class="font-black text-gray-900 text-base sm:text-lg block" x-text="paymentMethod === 'bank_transfer' ? 'Tổng chuyển khoản VietQR:' : 'Tổng thanh toán COD:'">
                                    Tổng thanh toán:
                                </span>
                                <span class="text-[11px] text-gray-400" x-text="paymentMethod === 'bank_transfer' ? 'Đã bao gồm VAT & Miễn phí vận chuyển' : 'Đã bao gồm VAT & Miễn phí thu hộ'">
                                    Đã bao gồm VAT & Miễn phí thu hộ
                                </span>
                            </div>
                            <div class="text-right">
                                <span 
                                    class="text-2xl sm:text-3xl font-black text-primary tracking-tight" 
                                    x-text="finalTotal.toLocaleString('vi-VN') + 'đ'"
                                >
                                    {{ number_format($total, 0, ',', '.') }}đ
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- NÚT XÁC NHẬN ĐẶT HÀNG (SUBMIT BUTTON) -->
                    <div class="pt-2">
                        <button 
                            type="submit" 
                            :disabled="submitting"
                            class="w-full py-4 px-6 rounded-2xl bg-primary hover:bg-primary-hover text-white font-black text-sm uppercase tracking-wider text-center shadow-xl shadow-primary/30 transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2 group cursor-pointer disabled:opacity-75 disabled:cursor-not-allowed"
                        >
                            <svg x-show="!submitting" class="w-5 h-5 transition transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <svg x-show="submitting" class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" style="display: none;">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span x-text="submitting ? 'Đang xử lý đơn hàng...' : (paymentMethod === 'bank_transfer' ? 'Xác nhận đặt hàng & Lấy mã QR' : 'Xác nhận đặt hàng (COD)')">
                                Xác nhận đặt hàng
                            </span>
                        </button>
                    </div>

                    <!-- CAM KẾT CHÍNH SÁCH BẢO HÀNH GEARHUB -->
                    <div class="pt-3 border-t border-gray-100 space-y-2 text-[11px] text-gray-500">
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span>Kiểm tra hàng trước khi thanh toán (Đồng kiểm với shipper)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span>Bảo hành chính hãng 12-24 tháng theo tiêu chuẩn nhà sản xuất</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-emerald-600 font-bold">✓</span>
                            <span>Lỗi 1 đổi 1 trong 30 ngày nếu phát sinh lỗi kỹ thuật</span>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </form>

</div>

<!-- LOGIC ALPINE.JS CHO CHECKOUT PAGE -->
<script>
function checkoutPage() {
    return {
        baseSubtotal: {{ (float) $subtotal }},
        shippingFee: {{ (float) $shipping }},
        inputCoupon: @js(old('coupon_code', '')),
        appliedCouponCode: @js(old('coupon_code', '')),
        discountAmount: 0,
        couponError: '',
        couponSuccessMessage: '',
        checkingCoupon: false,
        submitting: false,
        paymentMethod: @js(old('payment_method', 'cod')),

        // Form Fields (Hỗ trợ nạp nhanh từ địa chỉ đã lưu)
        receiverName: @js(old('receiver_name', $defaultAddress?->receiver_name ?? $user->name ?? '')),
        receiverPhone: @js(old('phone', $defaultAddress?->phone ?? $user->phone ?? '')),
        shippingAddress: @js(old('shipping_address', $defaultAddress?->address_line ?? '')),

        init() {
            if (this.inputCoupon) {
                this.checkCoupon();
            }
        },

        get finalTotal() {
            const calculated = this.baseSubtotal + this.shippingFee - this.discountAmount;
            return calculated > 0 ? calculated : 0;
        },

        fillAddress(name, phone, address) {
            this.receiverName = name;
            this.receiverPhone = phone;
            this.shippingAddress = address;
        },

        quickApply(code) {
            this.inputCoupon = code;
            this.checkCoupon();
        },

        checkCoupon() {
            const code = this.inputCoupon.trim();
            if (!code) {
                this.couponError = 'Vui lòng nhập mã giảm giá trước khi bấm áp dụng.';
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
            .then(res => {
                return res.json().then(data => ({
                    ok: res.ok,
                    status: res.status,
                    data: data
                }));
            })
            .then(result => {
                this.checkingCoupon = false;
                if (result.ok && result.data.valid) {
                    this.appliedCouponCode = result.data.code;
                    this.discountAmount = result.data.discount;
                    this.couponSuccessMessage = result.data.message;
                    this.couponError = '';
                } else {
                    this.couponError = result.data.message || 'Mã giảm giá không hợp lệ hoặc đã hết hạn.';
                    this.removeCoupon();
                }
            })
            .catch(() => {
                this.checkingCoupon = false;
                this.couponError = 'Không thể kết nối đến máy chủ để kiểm tra mã.';
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
