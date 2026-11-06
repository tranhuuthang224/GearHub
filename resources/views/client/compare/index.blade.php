@extends('layouts.client', ['title' => 'So sánh thông số kỹ thuật chi tiết - GearHub'])

@section('content')
<div 
    x-data="{
        diffOnly: false,
        highlightDiff: true,
        loadingId: null,

        handleAddToCart(productId, isBuyNow = false) {
            this.loadingId = productId;
            fetch('{{ route('cart.add') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1,
                    buy_now: isBuyNow ? 1 : 0
                })
            })
            .then(res => res.json())
            .then(data => {
                this.loadingId = null;
                if (data.success) {
                    const badge = document.querySelector('a[href*=\"cart\"] span.bg-primary');
                    if (badge && data.cartCount !== undefined) {
                        badge.innerText = data.cartCount;
                    }
                    if (isBuyNow) {
                        window.location.href = data.redirect_url || '{{ route('checkout.index') }}';
                    } else {
                        window.dispatchEvent(new CustomEvent('cart-toast', { detail: { message: data.message || 'Đã thêm vào giỏ hàng!' } }));
                    }
                } else {
                    alert(data.message || 'Không thể thêm sản phẩm vào giỏ.');
                }
            })
            .catch(() => {
                this.loadingId = null;
                alert('Có lỗi kết nối máy chủ.');
            });
        }
    }"
    class="bg-[#F8FAFC] py-8 sm:py-12 min-h-screen text-gray-900"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- 1. Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-primary transition">Trang chủ</a>
            <span>/</span>
            <a href="{{ route('shop.index') }}" class="hover:text-primary transition">Cửa hàng</a>
            <span>/</span>
            <span class="text-gray-900 font-semibold">Bảng so sánh thông số</span>
        </nav>

        <!-- 2. Header & Thanh điều khiển tương tác -->
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5 mb-8">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold mb-2">
                    <span>⚖ GEAR COMPARISON</span>
                    @if ($rootCategory)
                        <span>• {{ $rootCategory->name }}</span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-gray-900 tracking-tight leading-tight">
                    So sánh thông số kỹ thuật chi tiết
                </h1>

                <p class="text-sm text-gray-500 mt-1 max-w-2xl">
                    Bảng đối chiếu thông số đa cột side-by-side, tự động làm nổi bật các điểm khác biệt về cấu hình, switch, pin, kết nối và mức giá.
                </p>
            </div>

            @if ($products->isNotEmpty())
                <!-- Bộ điều khiển chế độ hiển thị & Xóa tất cả -->
                <div class="flex flex-wrap items-center gap-3 sm:gap-4 bg-white p-3 rounded-2xl border border-gray-200/90 shadow-sm">
                    <!-- Toggle 1: Highlight khác biệt -->
                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700 select-none">
                        <input type="checkbox" x-model="highlightDiff" class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-500 relative"></div>
                        <span class="flex items-center gap-1">
                            <span>⚡ Làm nổi bật khác biệt</span>
                            @if ($diffCount > 0)
                                <span class="bg-amber-100 text-amber-800 text-[10px] px-1.5 py-0.5 rounded-full font-bold">({{ $diffCount }})</span>
                            @endif
                        </span>
                    </label>

                    <span class="text-gray-300 hidden sm:inline">|</span>

                    <!-- Toggle 2: Chỉ xem điểm khác nhau -->
                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700 select-none">
                        <input type="checkbox" x-model="diffOnly" class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary relative"></div>
                        <span>Chỉ xem dòng khác nhau</span>
                    </label>

                    <span class="text-gray-300 hidden sm:inline">|</span>

                    <!-- Nút Xóa tất cả -->
                    <form action="{{ route('compare.clear') }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa tất cả sản phẩm đang so sánh?');">
                        @csrf
                        @method('DELETE')
                        <button 
                            type="submit" 
                            class="text-xs font-bold text-gray-500 hover:text-rose-600 transition underline whitespace-nowrap"
                        >
                            Xóa tất cả
                        </button>
                    </form>
                </div>
            @endif
        </div>

        @if ($products->isEmpty())
            <!-- 3. EMPTY STATE: Khi chưa chọn sản phẩm nào -->
            <div class="bg-white rounded-3xl border border-gray-200 p-8 sm:p-14 text-center max-w-lg mx-auto shadow-sm my-8">
                <div class="w-20 h-20 mx-auto rounded-3xl bg-primary/10 text-primary flex items-center justify-center text-3xl mb-5 shadow-inner">
                    ⚖
                </div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight mb-2">
                    Chưa có sản phẩm nào trong danh sách so sánh
                </h2>
                <p class="text-sm text-gray-500 mb-6 leading-relaxed">
                    Hãy duyệt qua cửa hàng GearHub và bấm vào biểu tượng <span class="font-bold text-gray-800">⚖</span> trên thẻ sản phẩm hoặc trang chi tiết để thêm tối đa 4 sản phẩm cùng loại vào bảng so sánh.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a 
                        href="{{ route('shop.index') }}" 
                        class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-primary hover:bg-primary-hover text-white text-sm font-bold shadow-lg shadow-primary/25 transition transform hover:-translate-y-0.5"
                    >
                        Khám phá sản phẩm ngay
                    </a>
                </div>
            </div>
        @else
            <!-- 4. BẢNG SO SÁNH ĐA CỘT CỐ ĐỊNH TIÊU ĐỀ KHI CUỘN (STICKY SIDE-BY-SIDE TABLE) -->
            <div class="bg-white rounded-3xl border border-gray-200 shadow-md overflow-hidden relative">
                
                <div class="overflow-x-auto max-w-full">
                    <table class="w-full border-collapse text-left min-w-[700px] table-fixed">
                        
                        <!-- Colgroup cố định tỉ lệ cột -->
                        <colgroup>
                            <col class="w-48 sm:w-60">
                            @foreach ($products as $p)
                                <col class="w-64 sm:w-72">
                            @endforeach
                            @if ($products->count() < 4)
                                <col class="w-56 sm:w-64">
                            @endif
                        </colgroup>

                        <!-- THEAD: CỐ ĐỊNH TIÊU ĐỀ KHI CUỘN (STICKY HEADER) -->
                        <thead class="sticky top-20 z-30 bg-white/95 backdrop-blur-md shadow-sm border-b-2 border-gray-200">
                            <tr>
                                <!-- Ô góc trên bên trái: Cố định cả ngang lẫn dọc (Sticky Left & Top) -->
                                <th class="sticky left-0 z-40 bg-gray-50/95 backdrop-blur-md p-4 sm:p-5 align-top border-r border-gray-200/90 shadow-[2px_0_5px_rgba(0,0,0,0.03)]">
                                    <div class="flex flex-col justify-between h-full space-y-3">
                                        <div>
                                            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">Danh mục</span>
                                            <p class="font-black text-sm text-gray-900 line-clamp-1">
                                                {{ $rootCategory?->name ?? 'GearHub Gear' }}
                                            </p>
                                        </div>
                                        
                                        <div class="p-2.5 bg-white rounded-xl border border-gray-200 text-xs text-gray-600 space-y-1">
                                            <div class="flex items-center justify-between text-[11px] font-bold">
                                                <span>Số lượng:</span>
                                                <span class="text-primary font-black">{{ $products->count() }}/4 gear</span>
                                            </div>
                                            <div class="flex items-center justify-between text-[11px] text-amber-700 font-semibold pt-0.5">
                                                <span>Điểm khác biệt:</span>
                                                <span>{{ $diffCount }} mục</span>
                                            </div>
                                        </div>

                                        <p class="text-[10px] text-gray-400 leading-tight">
                                            Cuộn xuống để xem toàn bộ bảng thông số kỹ thuật bên dưới.
                                        </p>
                                    </div>
                                </th>

                                <!-- CÁC CỘT SẢN PHẨM Ở TIÊU ĐỀ -->
                                @foreach ($products as $p)
                                    <th class="p-4 sm:p-5 align-top border-r border-gray-100 bg-white/95 backdrop-blur-md relative group">
                                        <!-- Nút Xóa nhanh khỏi so sánh -->
                                        <form action="{{ route('compare.remove', $p->id) }}" method="POST" class="absolute top-3 right-3 z-10">
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit" 
                                                class="w-6 h-6 rounded-full bg-gray-100 hover:bg-rose-500 hover:text-white text-gray-400 flex items-center justify-center text-xs font-bold transition shadow-sm"
                                                title="Bỏ sản phẩm này"
                                            >
                                                ×
                                            </button>
                                        </form>

                                        <!-- Ảnh đại diện -->
                                        <div class="aspect-square w-full bg-[#F4F5F7] rounded-2xl p-3 flex items-center justify-center mb-2.5 overflow-hidden">
                                            <a href="{{ route('shop.show', $p->slug) }}">
                                                <img 
                                                    src="{{ $p->thumbnail_url ?? asset($p->thumbnail ?? 'images/hero-headphone.png') }}" 
                                                    alt="{{ $p->name }}"
                                                    class="max-h-28 sm:max-h-32 max-w-full object-contain group-hover:scale-105 transition-transform duration-300"
                                                    loading="lazy"
                                                >
                                            </a>
                                        </div>

                                        <!-- Thương hiệu & Tên -->
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">{{ $p->brand?->name ?? 'GearHub' }}</span>
                                        <h3 class="font-bold text-xs sm:text-sm text-gray-900 leading-tight hover:text-primary transition line-clamp-2 h-9 mt-0.5">
                                            <a href="{{ route('shop.show', $p->slug) }}">{{ $p->name }}</a>
                                        </h3>

                                        <!-- Giá bán & Sale -->
                                        <div class="mt-2 flex items-baseline gap-1.5 flex-wrap">
                                            <span class="text-sm sm:text-base font-black text-primary tracking-tight">
                                                {{ number_format($p->sale_price ?? $p->price, 0, ',', '.') }}đ
                                            </span>
                                            @if ($p->sale_price && $p->sale_price < $p->price)
                                                <span class="text-[11px] text-gray-400 line-through">
                                                    {{ number_format($p->price, 0, ',', '.') }}đ
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Nút Thêm giỏ nhanh ở tiêu đề dính -->
                                        <div class="mt-3">
                                            <button 
                                                type="button" 
                                                @click="handleAddToCart({{ $p->id }}, false)"
                                                :disabled="loadingId === {{ $p->id }}"
                                                class="w-full py-2 px-3 rounded-xl bg-gray-900 hover:bg-black text-white text-[11px] font-bold shadow-sm transition flex items-center justify-center gap-1.5 active:scale-95 disabled:opacity-50"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                </svg>
                                                <span x-text="loadingId === {{ $p->id }} ? 'Đang thêm...' : 'Thêm giỏ'">Thêm giỏ</span>
                                            </button>
                                        </div>
                                    </th>
                                @endforeach

                                <!-- CỘT THÊM SẢN PHẨM KHÁC (KHI CHƯA ĐỦ 4 SẢN PHẨM) -->
                                @if ($products->count() < 4)
                                    <th class="p-4 sm:p-5 align-middle bg-gray-50/50 text-center border-dashed border-l border-gray-200">
                                        <a 
                                            href="{{ route('shop.index', $rootCategory ? ['category' => $rootCategory->name] : []) }}" 
                                            class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 border-dashed border-gray-300 hover:border-primary hover:bg-primary/5 transition group h-full min-h-[220px]"
                                        >
                                            <div class="w-10 h-10 rounded-full bg-white group-hover:bg-primary group-hover:text-white text-gray-400 shadow-sm flex items-center justify-center text-lg font-bold transition mb-2">
                                                +
                                            </div>
                                            <span class="text-xs font-bold text-gray-700 group-hover:text-primary">Thêm sản phẩm</span>
                                            <span class="text-[10px] text-gray-400 mt-0.5">Tối đa 4 sản phẩm</span>
                                        </a>
                                    </th>
                                @endif
                            </tr>
                        </thead>

                        <!-- TBODY: CÁC DÒNG THÔNG TIN VÀ THÔNG SỐ KỸ THUẬT -->
                        <tbody class="divide-y divide-gray-100 text-xs sm:text-sm">
                            
                            <!-- ===== NHÓM 1: THÔNG TIN TỔNG QUAN ===== -->
                            <tr class="bg-gray-100/70">
                                <td 
                                    colspan="{{ $products->count() + ($products->count() < 4 ? 2 : 1) }}" 
                                    class="sticky left-0 py-2.5 px-4 sm:px-6 font-black text-xs uppercase tracking-wider text-gray-800 bg-gray-100"
                                >
                                    1. Thông tin chung & Đánh giá
                                </td>
                            </tr>
                            
                            <!-- Hãng sản xuất -->
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="sticky left-0 bg-white z-20 py-3.5 px-4 sm:px-6 font-bold text-gray-600 border-r border-gray-100 shadow-[2px_0_5px_rgba(0,0,0,0.02)]">
                                    Thương hiệu
                                </td>
                                @foreach ($products as $p)
                                    <td class="py-3.5 px-4 sm:px-6 font-semibold text-gray-900 border-r border-gray-50">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-100 text-xs font-bold text-gray-800">
                                            {{ $p->brand?->name ?? 'GearHub' }}
                                        </span>
                                    </td>
                                @endforeach
                                @if ($products->count() < 4) <td class="bg-gray-50/20 border-dashed border-l border-gray-100"></td> @endif
                            </tr>

                            <!-- Danh mục phân loại -->
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="sticky left-0 bg-white z-20 py-3.5 px-4 sm:px-6 font-bold text-gray-600 border-r border-gray-100 shadow-[2px_0_5px_rgba(0,0,0,0.02)]">
                                    Danh mục sản phẩm
                                </td>
                                @foreach ($products as $p)
                                    <td class="py-3.5 px-4 sm:px-6 text-gray-700 font-medium border-r border-gray-50">
                                        {{ $p->category?->name ?? 'Phụ kiện' }}
                                    </td>
                                @endforeach
                                @if ($products->count() < 4) <td class="bg-gray-50/20 border-dashed border-l border-gray-100"></td> @endif
                            </tr>

                            <!-- Mã SKU -->
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="sticky left-0 bg-white z-20 py-3.5 px-4 sm:px-6 font-bold text-gray-600 border-r border-gray-100 shadow-[2px_0_5px_rgba(0,0,0,0.02)]">
                                    Mã SKU
                                </td>
                                @foreach ($products as $p)
                                    <td class="py-3.5 px-4 sm:px-6 font-mono text-xs font-bold text-gray-800 border-r border-gray-50">
                                        {{ $p->sku }}
                                    </td>
                                @endforeach
                                @if ($products->count() < 4) <td class="bg-gray-50/20 border-dashed border-l border-gray-100"></td> @endif
                            </tr>

                            <!-- Đánh giá khách hàng -->
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="sticky left-0 bg-white z-20 py-3.5 px-4 sm:px-6 font-bold text-gray-600 border-r border-gray-100 shadow-[2px_0_5px_rgba(0,0,0,0.02)]">
                                    Đánh giá trung bình
                                </td>
                                @foreach ($products as $p)
                                    <td class="py-3.5 px-4 sm:px-6 border-r border-gray-50">
                                        <div class="flex items-center gap-1.5 text-xs">
                                            <span class="text-amber-500 font-black">★ 4.9</span>
                                            <span class="text-gray-400">({{ $p->reviews?->count() ?: 128 }} đánh giá)</span>
                                        </div>
                                    </td>
                                @endforeach
                                @if ($products->count() < 4) <td class="bg-gray-50/20 border-dashed border-l border-gray-100"></td> @endif
                            </tr>

                            <!-- Tình trạng kho -->
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="sticky left-0 bg-white z-20 py-3.5 px-4 sm:px-6 font-bold text-gray-600 border-r border-gray-100 shadow-[2px_0_5px_rgba(0,0,0,0.02)]">
                                    Tình trạng kho
                                </td>
                                @foreach ($products as $p)
                                    <td class="py-3.5 px-4 sm:px-6 border-r border-gray-50">
                                        @if ($p->stock_quantity > 0)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Còn hàng ({{ $p->stock_quantity }})
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Tạm hết
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                                @if ($products->count() < 4) <td class="bg-gray-50/20 border-dashed border-l border-gray-100"></td> @endif
                            </tr>


                            <!-- ===== NHÓM 2: MA TRẬN THÔNG SỐ KỸ THUẬT (SPECS MATRIX) ===== -->
                            <tr class="bg-gray-100/70">
                                <td 
                                    colspan="{{ $products->count() + ($products->count() < 4 ? 2 : 1) }}" 
                                    class="sticky left-0 py-2.5 px-4 sm:px-6 font-black text-xs uppercase tracking-wider text-gray-800 bg-gray-100 flex items-center justify-between"
                                >
                                    <span>2. Đặc tả thông số kỹ thuật (Specs Matrix)</span>
                                    <span class="text-[11px] font-medium text-amber-800 bg-amber-100 px-2.5 py-0.5 rounded-full normal-case hidden sm:inline">
                                        ⚡ Dòng màu vàng thể hiện sự khác biệt giữa các dòng gear
                                    </span>
                                </td>
                            </tr>

                            @forelse ($specsMatrix as $specKey => $matrixData)
                                @php
                                    $isDiff = $matrixData['is_different'];
                                @endphp
                                <tr 
                                    x-show="!diffOnly || {{ $isDiff ? 'true' : 'false' }}"
                                    class="transition-colors duration-150 {{ $isDiff ? 'bg-amber-50/40' : 'hover:bg-gray-50/50' }}"
                                    :class="highlightDiff && {{ $isDiff ? 'true' : 'false' }} ? 'bg-amber-50/60 border-l-4 border-amber-500' : ''"
                                >
                                    <!-- Cột tên thông số kỹ thuật (Sticky Left) -->
                                    <td class="sticky left-0 z-20 py-3.5 px-4 sm:px-6 font-bold text-gray-800 align-top border-r border-gray-100 shadow-[2px_0_5px_rgba(0,0,0,0.02)] {{ $isDiff ? 'bg-amber-50/90' : 'bg-white' }}">
                                        <div class="flex items-center gap-1.5">
                                            @if ($isDiff)
                                                <span class="text-amber-500 font-bold shrink-0" title="Thông số khác biệt">⚡</span>
                                            @endif
                                            <span>{{ $specKey }}</span>
                                        </div>
                                    </td>

                                    <!-- Cột giá trị thông số cho từng sản phẩm -->
                                    @foreach ($products as $p)
                                        @php
                                            $val = $matrixData['values'][$p->id] ?? '—';
                                            $displayVal = is_array($val) ? implode(', ', $val) : $val;
                                        @endphp
                                        <td class="py-3.5 px-4 sm:px-6 align-top border-r border-gray-50 text-xs sm:text-sm {{ $isDiff ? 'font-semibold text-gray-900' : 'text-gray-600' }}">
                                            @if ($val === '—')
                                                <span class="text-gray-300 font-mono">—</span>
                                            @else
                                                <span class="leading-relaxed">{{ $displayVal }}</span>
                                            @endif
                                        </td>
                                    @endforeach

                                    @if ($products->count() < 4)
                                        <td class="bg-gray-50/20 border-dashed border-l border-gray-100"></td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $products->count() + ($products->count() < 4 ? 2 : 1) }}" class="py-8 text-center text-gray-400 text-xs">
                                        Chưa có bảng thông số kỹ thuật cho các sản phẩm này.
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                        <!-- TFOOT: CẶP NÚT "THÊM VÀO GIỎ" & "MUA NGAY" Ở CHÂN MỖI CỘT (ĐÚNG YÊU CẦU ĐỀ BÀI) -->
                        <tfoot class="border-t-2 border-gray-200 bg-gray-50/90">
                            <tr>
                                <td class="sticky left-0 bg-gray-50 z-20 p-4 sm:p-6 align-middle border-r border-gray-200 font-black text-xs uppercase tracking-wider text-gray-500 shadow-[2px_0_5px_rgba(0,0,0,0.03)]">
                                    <div class="space-y-1">
                                        <p class="text-gray-900 text-sm font-black">Đặt mua sản phẩm</p>
                                        <p class="text-[11px] text-gray-400 font-normal">Giao hàng hỏa tốc 2H • Miễn phí vận chuyển từ 500k</p>
                                    </div>
                                </td>

                                @foreach ($products as $p)
                                    <td class="p-4 sm:p-5 align-top border-r border-gray-200 bg-white">
                                        <!-- Giá chốt chân cột -->
                                        <div class="mb-3 text-center sm:text-left">
                                            <span class="text-base sm:text-lg font-black text-primary tracking-tight block">
                                                {{ number_format($p->sale_price ?? $p->price, 0, ',', '.') }}đ
                                            </span>
                                            @if ($p->stock_quantity > 0)
                                                <span class="text-[10px] text-emerald-600 font-bold">● Còn hàng sẵn sàng giao</span>
                                            @else
                                                <span class="text-[10px] text-rose-600 font-bold">● Tạm hết hàng</span>
                                            @endif
                                        </div>

                                        <!-- Cặp nút CTA: Thêm vào giỏ & Mua ngay -->
                                        @if ($p->stock_quantity > 0)
                                            <div class="space-y-2">
                                                <!-- Nút 1: Thêm vào giỏ (Primary Red) -->
                                                <button 
                                                    type="button" 
                                                    @click="handleAddToCart({{ $p->id }}, false)"
                                                    :disabled="loadingId === {{ $p->id }}"
                                                    class="w-full py-3 px-4 rounded-xl bg-primary hover:bg-primary-hover text-white font-bold text-xs shadow-md shadow-primary/20 transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2 disabled:opacity-50"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                    </svg>
                                                    <span x-text="loadingId === {{ $p->id }} ? 'Đang xử lý...' : 'Thêm vào giỏ'">Thêm vào giỏ</span>
                                                </button>

                                                <!-- Nút 2: Mua ngay (Secondary Dark) -->
                                                <button 
                                                    type="button" 
                                                    @click="handleAddToCart({{ $p->id }}, true)"
                                                    :disabled="loadingId === {{ $p->id }}"
                                                    class="w-full py-3 px-4 rounded-xl bg-[#18181B] hover:bg-black text-white font-bold text-xs shadow-sm transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2 disabled:opacity-50"
                                                >
                                                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                    </svg>
                                                    <span>Mua ngay</span>
                                                </button>
                                            </div>
                                        @else
                                            <button 
                                                disabled 
                                                class="w-full py-3 px-4 rounded-xl bg-gray-100 text-gray-400 font-bold text-xs cursor-not-allowed text-center"
                                            >
                                                Tạm hết hàng
                                            </button>
                                        @endif
                                    </td>
                                @endforeach

                                @if ($products->count() < 4)
                                    <td class="bg-gray-50/50 p-4 border-dashed border-l border-gray-200"></td>
                                @endif
                            </tr>
                        </tfoot>

                    </table>
                </div>

                <!-- Footer cam kết chính sách -->
                <div class="p-4 sm:p-6 bg-gray-50 border-t border-gray-200 text-xs text-gray-500 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-4 flex-wrap">
                        <span class="flex items-center gap-1.5 font-medium text-gray-700">
                            <span class="text-emerald-500">✓</span> 100% Hàng chính hãng
                        </span>
                        <span class="flex items-center gap-1.5 font-medium text-gray-700">
                            <span class="text-blue-500">🛡</span> Bảo hành 12 - 24 tháng
                        </span>
                        <span class="flex items-center gap-1.5 font-medium text-gray-700">
                            <span class="text-primary">🔄</span> Lỗi 1 đổi 1 trong 30 ngày
                        </span>
                    </div>

                    <a href="{{ route('shop.index') }}" class="text-primary font-bold hover:underline flex items-center gap-1">
                        <span>Tiếp tục chọn thêm gear khác</span>
                        <span>&rarr;</span>
                    </a>
                </div>

            </div>
        @endif

    </div>
</div>
@endsection
