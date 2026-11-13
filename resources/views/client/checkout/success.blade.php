@extends('layouts.client')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-24 text-center" x-data="orderSuccessPage()">

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
        @if ($order->isBankTransfer())
            Đơn hàng đã được tạo thành công. Vui lòng quét mã VietQR bên dưới để thanh toán, hệ thống sẽ xác nhận ngay khi nhận được chuyển khoản.
        @else
            Đơn hàng của bạn đã được ghi nhận trên hệ thống GearHub và đang được chuẩn bị để đóng gói chuyển đi.
        @endif
    </p>

    @if ($order->isBankTransfer())
        <!-- KHỐI QUÉT MÃ VIETQR THANH TOÁN CHUYỂN KHOẢN -->
        <div class="mt-8 bg-gradient-to-br from-blue-50/90 via-indigo-50/50 to-white rounded-3xl p-6 sm:p-8 border-2 border-blue-200 shadow-md text-left space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-blue-100 flex-wrap gap-2">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-black shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base sm:text-lg font-black text-blue-950 tracking-tight">
                            Quét mã VietQR chuyển khoản
                        </h2>
                        <p class="text-xs text-blue-600 font-medium">Hỗ trợ tất cả ứng dụng ngân hàng và ví điện tử 24/7</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-amber-800 bg-amber-100 px-3 py-1 rounded-full border border-amber-200 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Chờ chuyển khoản
                    </span>
                </div>
            </div>

            <!-- Nội dung QR và Thông tin số tài khoản -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                <!-- Cột trái: Ảnh QR Code (Tự động điền số tiền + mã đơn) -->
                <div class="md:col-span-5 flex flex-col items-center justify-center p-4 bg-white rounded-2xl border border-blue-200/80 shadow-xs space-y-3">
                    <div class="relative group">
                        <!-- VietQR tự động sinh số tiền và nội dung chuyển khoản -->
                        <img 
                            src="https://img.vietqr.io/image/{{ config('bank.bank_bin') }}-{{ config('bank.account_number') }}-compact2.png?amount={{ (int) $order->total }}&addInfo=GEARHUB%20{{ $order->code }}&accountName={{ urlencode(config('bank.account_name')) }}" 
                            alt="VietQR thanh toán {{ config('bank.bank_short_name') }}" 
                            class="w-56 h-auto rounded-xl shadow-xs border border-gray-100"
                        >
                    </div>
                    <div class="text-center">
                        <span class="text-[11px] font-bold text-gray-500 block">Mở app Ngân hàng để quét mã</span>
                        <span class="text-[10px] text-blue-600 font-medium">Tự động điền đúng số tiền & mã đơn</span>
                    </div>
                </div>

                <!-- Cột phải: Chi tiết tài khoản nhận tiền kèm nút Copy 1 chạm -->
                <div class="md:col-span-7 space-y-3.5 text-xs">
                    <!-- Ngân hàng -->
                    <div class="p-3 bg-white rounded-xl border border-blue-100 shadow-2xs space-y-0.5">
                        <span class="text-[11px] text-gray-400 font-semibold block">Ngân hàng thụ hưởng:</span>
                        <div class="font-bold text-gray-900 text-sm flex items-center justify-between">
                            <span>{{ config('bank.bank_name') }}</span>
                        </div>
                        <span class="text-[11px] text-gray-500 block">Chi nhánh: {{ config('bank.bank_branch') }}</span>
                    </div>

                    <!-- Chủ tài khoản -->
                    <div class="p-3 bg-white rounded-xl border border-blue-100 shadow-2xs space-y-0.5">
                        <span class="text-[11px] text-gray-400 font-semibold block">Tên chủ tài khoản:</span>
                        <span class="font-bold text-gray-900 text-sm uppercase">{{ config('bank.account_name') }}</span>
                    </div>

                    <!-- Số tài khoản có nút copy -->
                    <div class="p-3 bg-white rounded-xl border border-blue-100 shadow-2xs flex items-center justify-between gap-3">
                        <div>
                            <span class="text-[11px] text-gray-400 font-semibold block">Số tài khoản:</span>
                            <span class="font-mono font-black text-base text-blue-700 tracking-wider">{{ config('bank.account_number') }}</span>
                        </div>
                        <button 
                            type="button" 
                            @click="copyText('{{ config('bank.account_number') }}', 'stk')"
                            class="px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs transition border border-blue-200 shrink-0 flex items-center gap-1 cursor-pointer"
                        >
                            <span x-text="copiedField === 'stk' ? '✓ Đã chép' : '📋 Sao chép'">📋 Sao chép</span>
                        </button>
                    </div>

                    <!-- Số tiền cần chuyển có nút copy -->
                    <div class="p-3 bg-white rounded-xl border border-blue-100 shadow-2xs flex items-center justify-between gap-3">
                        <div>
                            <span class="text-[11px] text-gray-400 font-semibold block">Số tiền thanh toán:</span>
                            <span class="font-black text-base text-primary">{{ number_format($order->total, 0, ',', '.') }}đ</span>
                        </div>
                        <button 
                            type="button" 
                            @click="copyText('{{ (int) $order->total }}', 'amount')"
                            class="px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-primary font-bold text-xs transition border border-red-200 shrink-0 flex items-center gap-1 cursor-pointer"
                        >
                            <span x-text="copiedField === 'amount' ? '✓ Đã chép' : '📋 Sao chép'">📋 Sao chép</span>
                        </button>
                    </div>

                    <!-- Nội dung chuyển khoản bắt buộc có nút copy -->
                    <div class="p-3 bg-amber-50/70 rounded-xl border border-amber-200 shadow-2xs flex items-center justify-between gap-3">
                        <div>
                            <span class="text-[11px] text-amber-800 font-bold block">Nội dung chuyển khoản (bắt buộc):</span>
                            <span class="font-mono font-black text-base text-amber-950">GEARHUB {{ $order->code }}</span>
                        </div>
                        <button 
                            type="button" 
                            @click="copyText('GEARHUB {{ $order->code }}', 'memo')"
                            class="px-3 py-1.5 rounded-lg bg-amber-200 hover:bg-amber-300 text-amber-900 font-bold text-xs transition border border-amber-300 shrink-0 flex items-center gap-1 cursor-pointer"
                        >
                            <span x-text="copiedField === 'memo' ? '✓ Đã chép' : '📋 Sao chép'">📋 Sao chép</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-start gap-2.5">
                    <span class="text-amber-600 text-sm">⏰</span>
                    <span class="leading-relaxed">
                        <strong>Thời hạn thanh toán:</strong> Quý khách vui lòng hoàn tất chuyển khoản trong vòng <strong>24 giờ</strong>. Quá thời hạn này, hệ thống sẽ tự động hủy đơn và hoàn trả sản phẩm về kho để phục vụ khách hàng khác.
                    </span>
                </div>
                <div class="p-3 rounded-xl bg-blue-100/60 border border-blue-200 text-xs text-blue-900 flex items-start gap-2.5">
                    <span class="text-blue-700 text-sm">💡</span>
                    <span class="leading-relaxed">
                        Sau khi quý khách hoàn tất chuyển khoản, đơn hàng sẽ được bộ phận vận hành xác nhận và tiến hành đóng gói trong vòng <strong>15 - 30 phút</strong>.
                    </span>
                </div>
            </div>
        </div>
    @endif

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
                <span class="font-bold text-gray-900">{{ $order->payment_method_label }}</span>
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
                <span class="font-black text-gray-900">
                    {{ $order->isBankTransfer() ? 'Tổng thanh toán chuyển khoản:' : 'Tổng thanh toán COD:' }}
                </span>
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
            href="{{ route('orders.show', $order->code) }}" 
            class="px-6 py-3 rounded-2xl bg-gray-900 hover:bg-black text-white text-xs font-bold transition shadow-sm"
        >
            Xem chi tiết đơn hàng
        </a>
        <a 
            href="{{ route('dashboard') }}" 
            class="px-6 py-3 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition"
        >
            Về trang cá nhân
        </a>
    </div>

</div>

<script>
function orderSuccessPage() {
    return {
        copiedField: null,
        copyText(text, fieldName) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    this.copiedField = fieldName;
                    setTimeout(() => { this.copiedField = null; }, 2500);
                }).catch(() => {
                    this.fallbackCopy(text, fieldName);
                });
            } else {
                this.fallbackCopy(text, fieldName);
            }
        },
        fallbackCopy(text, fieldName) {
            const ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            this.copiedField = fieldName;
            setTimeout(() => { this.copiedField = null; }, 2500);
        }
    };
}
</script>
@endsection
