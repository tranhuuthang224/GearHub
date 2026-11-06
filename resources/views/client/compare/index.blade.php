@extends('layouts.client', ['title' => 'So sánh thông số kỹ thuật - GearHub'])

@section('content')
<div class="bg-[#F8FAFC] py-8 sm:py-12 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-primary transition">Trang chủ</a>
            <span>/</span>
            <a href="{{ route('shop.index') }}" class="hover:text-primary transition">Cửa hàng</a>
            <span>/</span>
            <span class="text-gray-900 font-semibold">So sánh sản phẩm</span>
        </nav>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-primary text-white flex items-center justify-center text-sm shadow-md">⚖</span>
                    <span>So sánh thông số kỹ thuật</span>
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Đối chiếu trực quan thông số, cấu hình và mức giá giữa các dòng gear hàng đầu.
                </p>
            </div>

            @if ($products->isNotEmpty())
                <div class="flex items-center gap-3">
                    <form action="{{ route('compare.clear') }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa tất cả sản phẩm đang so sánh?');">
                        @csrf
                        @method('DELETE')
                        <button 
                            type="submit" 
                            class="px-4 py-2 text-xs font-bold text-gray-600 hover:text-rose-600 bg-white border border-gray-200 rounded-xl hover:border-rose-200 shadow-sm transition"
                        >
                            Xóa tất cả
                        </button>
                    </form>

                    <a 
                        href="{{ route('shop.index') }}" 
                        class="px-4 py-2 text-xs font-bold text-white bg-[#18181B] hover:bg-black rounded-xl shadow-md transition flex items-center gap-1.5"
                    >
                        <span>+ Thêm sản phẩm</span>
                    </a>
                </div>
            @endif
        </div>

        @if ($products->isEmpty())
            <!-- EMPTY STATE -->
            <div class="bg-white rounded-3xl border border-gray-200 p-12 text-center max-w-lg mx-auto shadow-sm my-8">
                <div class="w-20 h-20 mx-auto rounded-3xl bg-primary/10 text-primary flex items-center justify-center text-3xl mb-5 shadow-inner">
                    ⚖
                </div>
                <h2 class="text-lg sm:text-xl font-black text-gray-900 tracking-tight mb-2">
                    Chưa có sản phẩm nào trong danh sách so sánh
                </h2>
                <p class="text-sm text-gray-500 mb-6 leading-relaxed">
                    Hãy duyệt qua cửa hàng và bấm vào biểu tượng <span class="font-bold text-gray-800">⚖</span> trên thẻ sản phẩm hoặc trang chi tiết để thêm tối đa 4 sản phẩm cùng loại vào bảng so sánh.
                </p>
                <a 
                    href="{{ route('shop.index') }}" 
                    class="inline-flex items-center justify-center px-6 py-3 rounded-2xl bg-primary hover:bg-primary-hover text-white text-sm font-bold shadow-lg shadow-primary/25 transition transform hover:-translate-y-0.5"
                >
                    Khám phá sản phẩm ngay
                </a>
            </div>
        @else
            <!-- BẢNG SO SÁNH ĐA CỘT (SIDE-BY-SIDE COMPARE MATRIX) -->
            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
                
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        
                        <!-- HEADER ROW: ẢNH & THÔNG TIN CƠ BẢN CỦA SẢN PHẨM -->
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50/60">
                                <th class="p-4 sm:p-6 w-44 sm:w-56 align-top font-bold text-xs uppercase tracking-wider text-gray-400">
                                    Tiêu chí so sánh
                                </th>
                                @foreach ($products as $p)
                                    <th class="p-4 sm:p-6 min-w-[220px] max-w-[280px] align-top relative group">
                                        <!-- Nút Xóa món này -->
                                        <form action="{{ route('compare.remove', $p->id) }}" method="POST" class="absolute top-3 right-3 z-10">
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit" 
                                                class="w-7 h-7 rounded-full bg-gray-100 hover:bg-rose-500 hover:text-white text-gray-400 flex items-center justify-center text-xs font-bold transition shadow-sm"
                                                title="Bỏ khỏi bảng so sánh"
                                            >
                                                ×
                                            </button>
                                        </form>

                                        <!-- Ảnh đại diện -->
                                        <div class="aspect-square w-full bg-white rounded-2xl border border-gray-100 p-4 flex items-center justify-center mb-3 shadow-inner">
                                            <a href="{{ route('shop.show', $p->slug) }}">
                                                <img 
                                                    src="{{ $p->thumbnail_url ?? asset($p->thumbnail ?? 'images/hero-headphone.png') }}" 
                                                    alt="{{ $p->name }}"
                                                    class="max-h-36 max-w-full object-contain hover:scale-105 transition-transform duration-300"
                                                >
                                            </a>
                                        </div>

                                        <!-- Thương hiệu & Tên sản phẩm -->
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ $p->brand?->name ?? 'GearHub' }}</span>
                                        <h3 class="font-bold text-sm text-gray-900 leading-snug hover:text-primary transition line-clamp-2 mt-0.5">
                                            <a href="{{ route('shop.show', $p->slug) }}">{{ $p->name }}</a>
                                        </h3>

                                        <!-- Giá bán & Sale -->
                                        <div class="mt-2.5">
                                            <span class="text-base sm:text-lg font-black text-primary tracking-tight">
                                                {{ number_format($p->sale_price ?? $p->price, 0, ',', '.') }}đ
                                            </span>
                                            @if ($p->sale_price && $p->sale_price < $p->price)
                                                <span class="text-xs text-gray-400 line-through block">
                                                    {{ number_format($p->price, 0, ',', '.') }}đ
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Nút Mua / Thêm giỏ nhanh -->
                                        <div class="mt-4 flex flex-col gap-2">
                                            <button 
                                                type="button" 
                                                onclick="window.dispatchEvent(new CustomEvent('add-to-cart', { detail: { id: {{ $p->id }} } }))"
                                                class="w-full py-2 px-3 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold shadow-sm transition flex items-center justify-center gap-1.5"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                </svg>
                                                <span>Thêm vào giỏ</span>
                                            </button>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 text-xs sm:text-sm">
                            
                            <!-- NHÓM 1: THÔNG TIN TỔNG QUAN -->
                            <tr class="bg-gray-100/50">
                                <td colspan="{{ count($products) + 1 }}" class="py-2.5 px-4 sm:px-6 font-black text-[11px] uppercase tracking-wider text-gray-700">
                                    1. Thông tin chung
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="py-3 px-4 sm:px-6 font-bold text-gray-500 bg-gray-50/30">Mã SKU</td>
                                @foreach ($products as $p)
                                    <td class="py-3 px-4 sm:px-6 font-mono font-medium text-gray-800">{{ $p->sku }}</td>
                                @endforeach
                            </tr>

                            <tr>
                                <td class="py-3 px-4 sm:px-6 font-bold text-gray-500 bg-gray-50/30">Danh mục</td>
                                @foreach ($products as $p)
                                    <td class="py-3 px-4 sm:px-6 font-medium text-gray-800">{{ $p->category?->name ?? 'Phụ kiện' }}</td>
                                @endforeach
                            </tr>

                            <tr>
                                <td class="py-3 px-4 sm:px-6 font-bold text-gray-500 bg-gray-50/30">Tình trạng kho</td>
                                @foreach ($products as $p)
                                    <td class="py-3 px-4 sm:px-6">
                                        @if ($p->stock_quantity > 0)
                                            <span class="inline-flex items-center gap-1 text-emerald-700 font-bold text-xs bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Còn hàng ({{ $p->stock_quantity }})
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-rose-700 font-bold text-xs bg-rose-50 px-2.5 py-1 rounded-full border border-rose-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Tạm hết
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            <!-- NHÓM 2: MA TRẬN THÔNG SỐ KỸ THUẬT (SPECS MATRIX) -->
                            <tr class="bg-gray-100/50">
                                <td colspan="{{ count($products) + 1 }}" class="py-2.5 px-4 sm:px-6 font-black text-[11px] uppercase tracking-wider text-gray-700 flex items-center justify-between">
                                    <span>2. Đặc tả thông số kỹ thuật chi tiết</span>
                                    <span class="text-[10px] font-medium text-amber-700 bg-amber-100/70 px-2 py-0.5 rounded-full normal-case">
                                        ★ Các dòng có màu vàng thể hiện điểm khác biệt
                                    </span>
                                </td>
                            </tr>

                            @forelse ($specsMatrix as $specKey => $matrixData)
                                @php
                                    $isDiff = $matrixData['is_different'];
                                @endphp
                                <tr class="{{ $isDiff ? 'bg-amber-50/40' : 'hover:bg-gray-50/40' }} transition">
                                    <td class="py-3 px-4 sm:px-6 font-bold text-gray-700 align-top flex items-center gap-1.5">
                                        @if ($isDiff)
                                            <span class="text-amber-500 font-bold" title="Thông số khác biệt">⚡</span>
                                        @endif
                                        <span>{{ $specKey }}</span>
                                    </td>
                                    @foreach ($products as $p)
                                        @php
                                            $val = $matrixData['values'][$p->id] ?? '—';
                                        @endphp
                                        <td class="py-3 px-4 sm:px-6 align-top {{ $isDiff ? 'font-semibold text-gray-900' : 'text-gray-600' }}">
                                            @if ($val === '—')
                                                <span class="text-gray-300">—</span>
                                            @else
                                                <span>{{ $val }}</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($products) + 1 }}" class="py-6 text-center text-gray-400 text-xs">
                                        Chưa có bảng thông số kỹ thuật chi tiết cho các sản phẩm này.
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>

                <!-- Footer lưu ý -->
                <div class="p-4 sm:p-6 bg-gray-50 border-t border-gray-200 text-xs text-gray-500 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p>💡 Thông số kỹ thuật được trích xuất từ dữ liệu công bố chính thức của nhà sản xuất.</p>
                    <a href="{{ route('shop.index') }}" class="text-primary font-bold hover:underline">Tiếp tục duyệt sản phẩm &rarr;</a>
                </div>

            </div>
        @endif

    </div>
</div>
@endsection
