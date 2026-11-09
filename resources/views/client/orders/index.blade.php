@extends('layouts.client')

@section('title', 'Đơn Hàng Của Tôi | GearHub')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    <!-- BREADCRUMB -->
    <nav class="flex items-center gap-2 text-xs text-gray-400 mb-6 flex-wrap" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="hover:text-primary transition flex items-center gap-1 font-medium">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Trang chủ
        </a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-900 font-semibold">Đơn hàng của tôi</span>
    </nav>

    <!-- HEADER TIÊU ĐỀ TRANG -->
    <div class="flex items-end justify-between mb-8 pb-5 border-b border-gray-100 flex-wrap gap-4">
        <div>
            <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-primary mb-1">
                <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                Lịch sử mua sắm
            </div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-gray-900 tracking-tight">
                Đơn hàng của tôi
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Theo dõi tiến độ giao hàng và xem lại các phụ kiện công nghệ bạn đã đặt mua
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-xs font-bold text-gray-600 bg-gray-100 px-3.5 py-1.5 rounded-full">
                Tổng cộng: <strong class="text-primary">{{ $counts['all'] }}</strong> đơn hàng
            </span>
        </div>
    </div>

    <!-- FLASH THÔNG BÁO THÀNH CÔNG HOẶC LỖI -->
    @if (session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-xs sm:text-sm text-emerald-800 font-medium shadow-sm">
            <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold shrink-0">✓</div>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3 text-xs sm:text-sm text-rose-800 font-medium shadow-sm">
            <div class="w-6 h-6 rounded-full bg-rose-500 text-white flex items-center justify-center font-bold shrink-0">!</div>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- TABS LỌC TRẠNG THÁI ĐƠN HÀNG (Chuẩn sàn E-Commerce) -->
    <div class="mb-8 overflow-x-auto pb-2 scrollbar-none">
        <div class="inline-flex items-center gap-2 p-1.5 bg-[#F4F5F7] rounded-2xl border border-gray-200/60 min-w-full sm:min-w-0">
            <!-- Tab Tất cả -->
            <a 
                href="{{ route('orders.index') }}" 
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-2 {{ $status === 'all' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}"
            >
                <span>Tất cả</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'all' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600' }}">
                    {{ $counts['all'] }}
                </span>
            </a>

            <!-- Tab Chờ xử lý -->
            <a 
                href="{{ route('orders.index', ['status' => 'pending']) }}" 
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-2 {{ $status === 'pending' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}"
            >
                <span>Chờ xử lý</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'pending' ? 'bg-amber-500 text-white' : 'bg-gray-200 text-gray-600' }}">
                    {{ $counts['pending'] }}
                </span>
            </a>

            <!-- Tab Đang giao -->
            <a 
                href="{{ route('orders.index', ['status' => 'shipping']) }}" 
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-2 {{ $status === 'shipping' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}"
            >
                <span>Đang giao</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'shipping' ? 'bg-sky-500 text-white' : 'bg-gray-200 text-gray-600' }}">
                    {{ $counts['shipping'] }}
                </span>
            </a>

            <!-- Tab Hoàn thành -->
            <a 
                href="{{ route('orders.index', ['status' => 'completed']) }}" 
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-2 {{ $status === 'completed' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}"
            >
                <span>Hoàn thành</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'completed' ? 'bg-emerald-500 text-white' : 'bg-gray-200 text-gray-600' }}">
                    {{ $counts['completed'] }}
                </span>
            </a>

            <!-- Tab Đã hủy -->
            <a 
                href="{{ route('orders.index', ['status' => 'cancelled']) }}" 
                class="px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap flex items-center gap-2 {{ $status === 'cancelled' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-900' }}"
            >
                <span>Đã hủy</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'cancelled' ? 'bg-rose-500 text-white' : 'bg-gray-200 text-gray-600' }}">
                    {{ $counts['cancelled'] }}
                </span>
            </a>
        </div>
    </div>

    <!-- DANH SÁCH ĐƠN HÀNG HOẶC EMPTY STATE -->
    @if ($orders->isEmpty())
        <x-empty-state 
            icon="📦"
            title="Chưa có đơn hàng nào"
            description="{{ $status !== 'all' ? 'Không tìm thấy đơn hàng nào ở trạng thái ' . (\App\Models\Order::STATUS_LABELS[$status] ?? $status) . '.' : 'Bạn chưa đặt mua sản phẩm nào tại GearHub. Hãy lướt xem các phụ kiện gaming và setup góc làm việc ngay nhé!' }}"
            action-text="Khám phá sản phẩm ngay"
            action-url="{{ route('shop.index') }}"
            class="my-8 py-14"
        />
    @else
        <div class="space-y-6">
            @foreach ($orders as $order)
                <div class="bg-white rounded-3xl border border-gray-200/80 shadow-sm hover:shadow-md transition overflow-hidden">
                    
                    <!-- 1. Header Đơn Hàng -->
                    <div class="p-5 sm:p-6 bg-[#F8FAFC] border-b border-gray-100 flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-3 flex-wrap">
                            <span class="text-xs text-gray-400 font-medium">Mã đơn:</span>
                            <span class="font-mono text-sm sm:text-base font-black text-primary">
                                #{{ $order->code }}
                            </span>
                            <span class="text-gray-300">|</span>
                            <span class="text-xs text-gray-500">
                                📅 {{ $order->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>

                        <!-- Badge Trạng thái -->
                        <div>
                            @php
                                $badgeStyle = match ($order->status) {
                                    'pending'   => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'shipping'  => 'bg-sky-50 text-sky-700 border-sky-200',
                                    'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    default     => 'bg-gray-100 text-gray-600 border-gray-200',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider border {{ $badgeStyle }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ match($order->status) { 'pending' => 'bg-amber-500', 'shipping' => 'bg-sky-500', 'completed' => 'bg-emerald-500', 'cancelled' => 'bg-rose-500', default => 'bg-gray-400' } }}"></span>
                                {{ $order->statusLabel() }}
                            </span>
                        </div>
                    </div>

                    <!-- 2. Danh Sách Sản Phẩm Trong Đơn -->
                    <div class="p-5 sm:p-6 divide-y divide-gray-100">
                        @foreach ($order->items as $item)
                            <div class="py-4 first:pt-0 last:pb-0 flex items-center justify-between gap-4 flex-wrap sm:flex-nowrap">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <div class="w-16 h-16 rounded-2xl bg-[#F4F5F7] p-2 flex items-center justify-center shrink-0 border border-gray-100">
                                        <img 
                                            src="{{ $item->product?->thumbnail ? (str_starts_with($item->product->thumbnail, 'http') ? $item->product->thumbnail : asset('storage/' . $item->product->thumbnail)) : asset('images/hero-headphone.png') }}" 
                                            alt="{{ $item->product_name }}" 
                                            loading="lazy"
                                            decoding="async"
                                            class="max-h-full max-w-full object-contain"
                                            onerror="this.src='{{ asset('images/hero-headphone.png') }}'"
                                        >
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-bold text-gray-900 truncate" title="{{ $item->product_name }}">
                                            @if ($item->product)
                                                <a href="{{ route('shop.show', $item->product->slug) }}" class="hover:text-primary transition">
                                                    {{ $item->product_name }}
                                                </a>
                                            @else
                                                {{ $item->product_name }}
                                            @endif
                                        </h4>
                                        <p class="text-xs text-gray-400 mt-0.5">
                                            Số lượng: <span class="font-mono font-bold text-gray-700">x{{ $item->quantity }}</span>
                                        </p>
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <span class="text-xs text-gray-400 block">
                                        {{ number_format($item->unit_price, 0, ',', '.') }}đ / sp
                                    </span>
                                    <span class="text-sm font-black text-gray-900 block mt-0.5">
                                        {{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}đ
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- 3. Footer Đơn Hàng: Tóm tắt chi phí & Nút hành động -->
                    <div class="p-5 sm:p-6 bg-[#F8FAFC]/50 border-t border-gray-100 flex items-center justify-between flex-wrap gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500">Thanh toán (COD):</span>
                                <span class="text-lg sm:text-xl font-black text-primary">
                                    {{ number_format($order->total, 0, ',', '.') }}đ
                                </span>
                            </div>
                            @if ($order->discount > 0)
                                <p class="text-[11px] text-emerald-700 font-semibold">
                                    ✓ Đã áp dụng mã giảm giá {{ $order->coupon?->code ?? '' }} (-{{ number_format($order->discount, 0, ',', '.') }}đ)
                                </p>
                            @endif
                        </div>

                        <!-- Các nút tương tác -->
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <!-- Nút Hủy đơn (Nếu đang ở trạng thái pending) -->
                            @can('cancel', $order)
                                <form 
                                    action="{{ route('orders.cancel', $order->code) }}" 
                                    method="POST" 
                                    onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng #{{ $order->code }}? Số lượng sản phẩm sẽ được hoàn lại kho.');"
                                >
                                    @csrf
                                    <button 
                                        type="submit" 
                                        class="px-4 py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition"
                                    >
                                        Hủy đơn hàng
                                    </button>
                                </form>
                            @endcan

                            <!-- Nút Mua lại -->
                            <form action="{{ route('orders.reorder', $order->code) }}" method="POST">
                                @csrf
                                <button 
                                    type="submit" 
                                    class="px-4 py-2 rounded-xl border border-gray-200 bg-white hover:border-gray-900 text-gray-800 text-xs font-bold transition shadow-sm"
                                >
                                    Mua lại
                                </button>
                            </form>

                            <!-- Nút Xem chi tiết -->
                            <a 
                                href="{{ route('orders.show', $order->code) }}" 
                                class="px-5 py-2 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5"
                            >
                                <span>Chi tiết</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        <!-- PHÂN TRANG -->
        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    @endif

</div>
@endsection
