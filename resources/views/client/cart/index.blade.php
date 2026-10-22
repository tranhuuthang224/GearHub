@extends('layouts.client')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-gray-400 mb-8" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="hover:text-primary transition font-medium">Trang chủ</a>
        <span class="text-gray-300">/</span>
        <span class="text-gray-900 font-semibold">Giỏ hàng của bạn</span>
    </nav>

    <!-- Page Title -->
    <div class="flex items-baseline justify-between mb-8 pb-4 border-b border-gray-100 flex-wrap gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Giỏ hàng của bạn</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                Quản lý các sản phẩm phụ kiện công nghệ bạn đã chọn
            </p>
        </div>
        <span class="text-xs font-bold text-gray-500 bg-gray-100 px-3 py-1.5 rounded-full">
            {{ $count }} sản phẩm trong giỏ
        </span>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-xs text-emerald-800 font-medium">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center gap-3 text-xs text-rose-800 font-medium">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if (!empty($cart) && count($cart) > 0)
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Cột trái: Danh sách sản phẩm trong giỏ (8 cols) -->
            <div class="lg:col-span-8 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-100 flex items-center justify-between">
                    <span class="text-sm font-bold text-gray-900">Danh sách phụ kiện ({{ count($cart) }})</span>
                    <form action="{{ route('cart.clear') }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa tất cả sản phẩm khỏi giỏ hàng?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs text-gray-400 hover:text-rose-600 font-semibold transition">
                            Xóa tất cả
                        </button>
                    </form>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach ($cart as $id => $item)
                        <div class="p-4 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <!-- Ảnh & Tên -->
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-20 h-20 rounded-xl bg-[#F4F5F7] p-2 flex items-center justify-center shrink-0 border border-gray-100">
                                    <img src="{{ $item['thumbnail'] }}" alt="{{ $item['name'] }}" class="max-h-full max-w-full object-contain">
                                </div>
                                <div class="space-y-1">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-primary block">
                                        {{ $item['category'] ?? 'Phụ kiện' }}
                                    </span>
                                    <a href="{{ $item['slug'] !== '#' ? url('/shop/' . $item['slug']) : '#' }}" class="text-sm font-bold text-gray-900 hover:text-primary transition line-clamp-2">
                                        {{ $item['name'] }}
                                    </a>
                                    <div class="text-xs text-gray-400 font-mono">
                                        SKU: {{ $item['sku'] ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>

                            <!-- Đơn giá, Tăng giảm số lượng & Thành tiền -->
                            <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-50">
                                <div class="text-right">
                                    <span class="text-sm font-black text-gray-900 block">
                                        {{ number_format($item['price'], 0, ',', '.') }}đ
                                    </span>
                                    @if (!empty($item['original_price']) && $item['original_price'] > $item['price'])
                                        <span class="text-xs text-gray-400 line-through">
                                            {{ number_format($item['original_price'], 0, ',', '.') }}đ
                                        </span>
                                    @endif
                                </div>

                                <!-- Form cập nhật số lượng -->
                                <form action="{{ route('cart.update') }}" method="POST" class="inline-flex items-center border border-gray-200 rounded-xl bg-[#F4F5F7] p-0.5 shadow-inner">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="product_id" value="{{ $id }}">
                                    
                                    <button 
                                        type="submit" 
                                        name="quantity" 
                                        value="{{ $item['quantity'] - 1 }}" 
                                        class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-700 hover:bg-white hover:text-black font-bold text-xs transition"
                                        title="Giảm 1"
                                    >
                                        –
                                    </button>

                                    <span class="w-9 text-center font-bold text-xs text-gray-900">
                                        {{ $item['quantity'] }}
                                    </span>

                                    <button 
                                        type="submit" 
                                        name="quantity" 
                                        value="{{ $item['quantity'] + 1 }}" 
                                        {{ $item['quantity'] >= ($item['max_stock'] ?? 99) ? 'disabled' : '' }}
                                        class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-700 hover:bg-white hover:text-black font-bold text-xs transition disabled:opacity-40"
                                        title="Tăng 1"
                                    >
                                        +
                                    </button>
                                </form>

                                <!-- Nút xóa món -->
                                <form action="{{ route('cart.remove', $id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 transition" title="Xóa món">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Cột phải: Box Tóm tắt đơn hàng (Order Summary - 4 cols) -->
            <div class="lg:col-span-4 bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5 sticky top-28">
                <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3">Tóm tắt đơn hàng</h3>
                
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between text-gray-600">
                        <span>Tạm tính hàng:</span>
                        <span class="font-bold text-gray-900">{{ number_format($subtotal, 0, ',', '.') }}đ</span>
                    </div>

                    <div class="flex items-center justify-between text-gray-600">
                        <span>Phí vận chuyển:</span>
                        @if ($shipping === 0)
                            <span class="font-bold text-emerald-600">Miễn phí (Free)</span>
                        @else
                            <span class="font-bold text-gray-900">{{ number_format($shipping, 0, ',', '.') }}đ</span>
                        @endif
                    </div>

                    @if ($subtotal < 500000)
                        <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200/60 text-xs text-amber-800">
                            Mua thêm <strong>{{ number_format(500000 - $subtotal, 0, ',', '.') }}đ</strong> để được <strong>Miễn phí vận chuyển toàn quốc</strong>!
                        </div>
                    @endif

                    <div class="pt-3 border-t border-gray-100 flex items-baseline justify-between">
                        <span class="text-base font-bold text-gray-900">Tổng thanh toán:</span>
                        <span class="text-2xl font-black text-primary">{{ number_format($total, 0, ',', '.') }}đ</span>
                    </div>
                    <p class="text-[11px] text-gray-400">Đã bao gồm thuế GTGT (VAT) nếu có</p>
                </div>

                <div class="pt-2 space-y-3">
                    <a 
                        href="{{ url('/shop') }}" 
                        class="w-full py-4 px-6 rounded-2xl bg-primary hover:bg-primary-hover text-white font-bold text-sm text-center shadow-xl shadow-primary/25 transition block"
                    >
                        Tiến hành đặt hàng
                    </a>

                    <a 
                        href="{{ url('/shop') }}" 
                        class="w-full py-3 px-4 rounded-2xl bg-gray-50 hover:bg-gray-100 text-gray-700 font-semibold text-xs text-center transition block"
                    >
                        ← Tiếp tục chọn thêm sản phẩm
                    </a>
                </div>
            </div>

        </div>
    @else
        <!-- Empty Cart State -->
        <div class="py-16 text-center max-w-md mx-auto">
            <div class="w-20 h-20 rounded-3xl bg-gray-50 border border-gray-100 flex items-center justify-center mx-auto mb-5 text-gray-400 shadow-inner">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <h2 class="text-xl font-black text-gray-900 mb-2">Giỏ hàng của bạn đang trống</h2>
            <p class="text-xs text-gray-500 mb-6 leading-relaxed">
                Bạn chưa thêm bất kỳ sản phẩm phụ kiện nào vào giỏ hàng. Hãy khám phá hàng trăm gaming gear chất lượng tại GearHub ngay hôm nay!
            </p>
            <a 
                href="{{ url('/shop') }}" 
                class="inline-flex items-center gap-2 py-3 px-6 rounded-2xl bg-primary hover:bg-primary-hover text-white text-xs font-bold shadow-lg shadow-primary/20 transition transform hover:-translate-y-0.5"
            >
                <span>Khám phá sản phẩm ngay</span>
                <span>→</span>
            </a>
        </div>
    @endif

</div>
@endsection
