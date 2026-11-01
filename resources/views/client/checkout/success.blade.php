@extends('layouts.client')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-24 text-center">

    <!-- Icon Thành công -->
    <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-6 shadow-sm">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
        </svg>
    </div>

    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold uppercase tracking-wider mb-3 border border-emerald-200">
        Đặt hàng thành công
    </div>

    <h1 class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight">
        Cảm ơn bạn đã đặt hàng!
    </h1>
    <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">
        Đơn hàng của bạn đã được ghi nhận trên hệ thống GearHub và đang được chuẩn bị để đóng gói chuyển đi.
    </p>

    <!-- Khung thông tin đơn hàng -->
    <div class="mt-8 bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-sm text-left space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-gray-100 flex-wrap gap-2">
            <div>
                <span class="text-xs text-gray-400 font-semibold block">Mã đơn hàng</span>
                <span class="font-mono text-base sm:text-lg font-black text-primary">{{ $order->code }}</span>
            </div>
            <div class="text-right">
                <span class="text-xs text-gray-400 font-semibold block">Trạng thái</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                    {{ $order->status_label }}
                </span>
            </div>
        </div>

        <!-- Thông tin giao hàng -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-gray-400 font-semibold block mb-0.5">Người nhận:</span>
                <span class="font-bold text-gray-900">{{ $order->receiver_name }} ({{ $order->phone }})</span>
            </div>
            <div>
                <span class="text-gray-400 font-semibold block mb-0.5">Hình thức thanh toán:</span>
                <span class="font-bold text-gray-900">COD (Thanh toán tiền mặt khi nhận hàng)</span>
            </div>
            <div class="sm:col-span-2">
                <span class="text-gray-400 font-semibold block mb-0.5">Địa chỉ giao nhận:</span>
                <span class="font-bold text-gray-900">{{ $order->shipping_address }}</span>
            </div>
            @if ($order->note)
                <div class="sm:col-span-2">
                    <span class="text-gray-400 font-semibold block mb-0.5">Ghi chú:</span>
                    <span class="text-gray-700 italic">"{{ $order->note }}"</span>
                </div>
            @endif
        </div>

        <!-- Danh sách món hàng -->
        <div class="pt-4 border-t border-gray-100 space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Chi tiết sản phẩm</h3>
            <div class="divide-y divide-gray-100 text-xs">
                @foreach ($order->items as $item)
                    <div class="py-2.5 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <span class="font-bold text-gray-900 block truncate">{{ $item->product_name }}</span>
                            <span class="text-gray-400">Số lượng: x{{ $item->quantity }} • Đơn giá: {{ number_format($item->unit_price, 0, ',', '.') }}đ</span>
                        </div>
                        <span class="font-bold text-gray-900 shrink-0">
                            {{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}đ
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Chi phí thanh toán -->
        <div class="pt-4 border-t border-gray-100 space-y-2 text-xs">
            <div class="flex items-center justify-between text-gray-600">
                <span>Tạm tính tiền hàng:</span>
                <span class="font-bold text-gray-900">{{ number_format($order->subtotal, 0, ',', '.') }}đ</span>
            </div>
            @if ($order->discount > 0)
                <div class="flex items-center justify-between text-emerald-700 font-bold">
                    <span>Mã giảm giá ({{ $order->coupon?->code }}):</span>
                    <span>-{{ number_format($order->discount, 0, ',', '.') }}đ</span>
                </div>
            @endif
            <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-sm">
                <span class="font-black text-gray-900">Tổng thanh toán COD:</span>
                <span class="font-black text-xl text-primary">{{ number_format($order->total, 0, ',', '.') }}đ</span>
            </div>
        </div>
    </div>

    <!-- Nút hành động -->
    <div class="mt-8 flex items-center justify-center gap-4 flex-wrap">
        <a 
            href="{{ url('/shop') }}" 
            class="px-6 py-3 rounded-2xl bg-primary hover:bg-primary-hover text-white text-xs font-bold transition shadow-lg shadow-primary/25"
        >
            Tiếp tục mua sắm
        </a>
        <a 
            href="{{ route('dashboard') }}" 
            class="px-6 py-3 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition"
        >
            Về trang cá nhân
        </a>
    </div>

</div>
@endsection
