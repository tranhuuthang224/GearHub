@extends('layouts.client')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    <!-- BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs text-gray-400 mb-6 flex-wrap" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="hover:text-primary transition flex items-center gap-1 font-medium">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Trang chủ
        </a>
        <span class="text-gray-300">/</span>
        <a href="{{ route('orders.index') }}" class="hover:text-primary transition font-medium">Đơn hàng của tôi</a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-900 font-semibold">#{{ $order->code }}</span>
    </nav>

    <!-- HEADER CHI TIẾT ĐƠN HÀNG -->
    <div class="flex items-center justify-between mb-8 pb-5 border-b border-gray-100 flex-wrap gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('orders.index') }}" class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight flex items-center gap-2">
                    <span>Đơn hàng</span>
                    <span class="font-mono text-primary">#{{ $order->code }}</span>
                </h1>
            </div>
            <p class="text-xs sm:text-sm text-gray-500 mt-1 pl-11">
                Đặt lúc {{ $order->created_at->format('d/m/Y H:i') }} • Cập nhật lần cuối: {{ $order->updated_at->format('d/m/Y H:i') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            @php
                $badgeStyle = match ($order->status) {
                    'pending'   => 'bg-amber-50 text-amber-700 border-amber-200',
                    'shipping'  => 'bg-sky-50 text-sky-700 border-sky-200',
                    'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
                    default     => 'bg-gray-100 text-gray-600 border-gray-200',
                };
            @endphp
            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-wider border {{ $badgeStyle }}">
                <span class="w-2 h-2 rounded-full {{ match($order->status) { 'pending' => 'bg-amber-500', 'shipping' => 'bg-sky-500', 'completed' => 'bg-emerald-500', 'cancelled' => 'bg-rose-500', default => 'bg-gray-400' } }}"></span>
                {{ $order->statusLabel() }}
            </span>

            @can('cancel', $order)
                <form 
                    action="{{ route('orders.cancel', $order->code) }}" 
                    method="POST" 
                    onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');"
                >
                    @csrf
                    <button type="submit" class="px-4 py-1.5 rounded-xl border border-rose-300 text-rose-600 hover:bg-rose-50 text-xs font-bold transition">
                        Hủy đơn
                    </button>
                </form>
            @endcan
        </div>
    </div>

    <!-- FLASH THÔNG BÁO -->
    @if (session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-xs sm:text-sm text-emerald-800 font-medium shadow-sm">
            <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold shrink-0">✓</div>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- CỘT TRÁI (8 COLS): DANH SÁCH SẢN PHẨM & TIMELINE TRẠNG THÁI -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- 1. Danh sách sản phẩm -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-sm space-y-6">
                <h3 class="text-base font-black text-gray-900 tracking-tight pb-3 border-b border-gray-100 flex items-center justify-between">
                    <span>Sản phẩm trong đơn</span>
                    <span class="text-xs font-bold text-gray-400 bg-gray-100 px-2.5 py-0.5 rounded-full">
                        {{ $order->items->count() }} mặt hàng
                    </span>
                </h3>

                <div class="divide-y divide-gray-100">
                    @foreach ($order->items as $item)
                        <div class="py-4 first:pt-0 last:pb-0 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-16 h-16 rounded-2xl bg-[#F4F5F7] p-2 flex items-center justify-center shrink-0 border border-gray-100">
                                    <img 
                                        src="{{ $item->product?->thumbnail ? (str_starts_with($item->product->thumbnail, 'http') ? $item->product->thumbnail : asset('storage/' . $item->product->thumbnail)) : asset('images/hero-headphone.png') }}" 
                                        alt="{{ $item->product_name }}" 
                                        class="max-h-full max-w-full object-contain"
                                        onerror="this.src='{{ asset('images/hero-headphone.png') }}'"
                                    >
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-gray-900 truncate">
                                        @if ($item->product)
                                            <a href="{{ route('shop.show', $item->product->slug) }}" class="hover:text-primary transition">
                                                {{ $item->product_name }}
                                            </a>
                                        @else
                                            {{ $item->product_name }}
                                        @endif
                                    </h4>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        Số lượng: <span class="font-bold text-gray-700">x{{ $item->quantity }}</span>
                                    </p>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <span class="text-xs text-gray-400 block">
                                    {{ number_format($item->unit_price, 0, ',', '.') }}đ / món
                                </span>
                                <span class="text-sm font-black text-gray-900 block mt-0.5">
                                    {{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}đ
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 2. Lịch sử tiến trình đơn hàng (Timeline) -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-sm space-y-5">
                <h3 class="text-base font-black text-gray-900 tracking-tight pb-3 border-b border-gray-100">
                    Lịch sử trạng thái đơn hàng
                </h3>

                @if ($order->histories && $order->histories->isNotEmpty())
                    <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-200">
                        @foreach ($order->histories as $history)
                            <div class="relative flex items-start gap-4">
                                <div class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full border-2 border-white bg-primary shadow"></div>
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-gray-900">
                                            Chuyển sang "{{ \App\Models\Order::STATUS_LABELS[$history->to_status] ?? $history->to_status }}"
                                        </span>
                                        <span class="text-[11px] text-gray-400">
                                            {{ $history->created_at->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        Cập nhật bởi: {{ $history->actor ? $history->actor->name : 'Hệ thống tự động' }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-gray-400">Chưa có thay đổi trạng thái nào khác.</p>
                @endif
            </div>

        </div>

        <!-- CỘT PHẢI (4 COLS): THÔNG TIN GIAO HÀNG & TỔNG TIỀN -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- 1. Thông tin giao nhận -->
            <div class="bg-white rounded-3xl p-6 border border-gray-200/80 shadow-sm space-y-4">
                <h3 class="text-sm font-black text-gray-900 tracking-tight pb-3 border-b border-gray-100 uppercase">
                    Địa chỉ nhận hàng
                </h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-gray-400 block mb-0.5">Người nhận:</span>
                        <span class="font-bold text-gray-900">{{ $order->receiver_name }}</span>
                    </div>

                    <div>
                        <span class="text-gray-400 block mb-0.5">Số điện thoại:</span>
                        <span class="font-bold text-gray-900 font-mono">{{ $order->phone }}</span>
                    </div>

                    <div>
                        <span class="text-gray-400 block mb-0.5">Địa chỉ giao:</span>
                        <span class="font-medium text-gray-800 leading-relaxed">{{ $order->shipping_address }}</span>
                    </div>

                    @if ($order->note)
                        <div class="p-3 rounded-xl bg-[#F8FAFC] border border-gray-100">
                            <span class="text-gray-400 block text-[11px] mb-0.5">Ghi chú cho tài xế:</span>
                            <span class="text-gray-700 italic">"{{ $order->note }}"</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 2. Phương thức thanh toán & Bảng tính tiền -->
            <div class="bg-white rounded-3xl p-6 border border-gray-200/80 shadow-sm space-y-4">
                <h3 class="text-sm font-black text-gray-900 tracking-tight pb-3 border-b border-gray-100 uppercase">
                    Thanh toán
                </h3>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Hình thức:</span>
                        <span class="font-bold text-gray-900">COD (Thanh toán khi nhận)</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Tạm tính tiền hàng:</span>
                        <span class="font-bold text-gray-900">{{ number_format($order->subtotal, 0, ',', '.') }}đ</span>
                    </div>

                    @if ($order->discount > 0)
                        <div class="flex items-center justify-between text-emerald-700 font-bold">
                            <span>Voucher ({{ $order->coupon?->code ?? '' }}):</span>
                            <span>-{{ number_format($order->discount, 0, ',', '.') }}đ</span>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-gray-100 flex items-baseline justify-between text-base">
                        <span class="font-black text-gray-900">Tổng thanh toán:</span>
                        <span class="font-black text-xl text-primary">
                            {{ number_format($order->total, 0, ',', '.') }}đ
                        </span>
                    </div>
                </div>

                <div class="pt-2">
                    <form action="{{ route('orders.reorder', $order->code) }}" method="POST">
                        @csrf
                        <button 
                            type="submit" 
                            class="w-full py-3 px-4 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition shadow-sm text-center"
                        >
                            Mua lại đơn hàng này
                        </button>
                    </form>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
