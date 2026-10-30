@extends('layouts.admin', ['title' => 'Đơn ' . $order->code])

@section('content')
    @php
        $badge = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium';
        $statusClasses = [
            'pending'   => 'bg-amber-50 text-amber-700',
            'shipping'  => 'bg-sky-50 text-sky-700',
            'completed' => 'bg-emerald-50 text-emerald-700',
            'cancelled' => 'bg-gray-100 text-gray-600',
        ];
        $money = fn ($amount) => number_format($amount, 0, ',', '.') . ' ₫';

        // Bước kế tiếp theo luồng pending → shipping → completed
        $next = [
            'pending'  => ['status' => 'shipping',  'label' => 'Duyệt giao hàng'],
            'shipping' => ['status' => 'completed', 'label' => 'Hoàn thành đơn'],
        ][$order->status] ?? null;
    @endphp

    @if ($errors->has('status'))
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            {{ $errors->first('status') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <x-button :href="route('admin.orders.index')" variant="outline" size="sm">← Danh sách</x-button>
            <span class="{{ $badge }} {{ $statusClasses[$order->status] ?? 'bg-gray-100 text-gray-600' }}">
                {{ $order->statusLabel() }}
            </span>
            <span class="text-sm text-gray-500">Đặt lúc {{ $order->created_at->format('d/m/Y H:i') }}</span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($next)
                <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $next['status'] }}">
                    <x-button type="submit">{{ $next['label'] }}</x-button>
                </form>
            @endif

            @can('cancel', $order)
                <form method="POST" action="{{ route('admin.orders.status', $order) }}"
                      onsubmit="return confirm(@js('Hủy đơn «' . $order->code . '»? Tồn kho và lượt dùng mã giảm giá sẽ được hoàn lại.'))">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="cancelled">
                    <x-button type="submit" variant="danger">Hủy đơn</x-button>
                </form>
            @endcan

            @if (! $next && ! $order->canTransitionTo('cancelled'))
                <span class="text-sm text-gray-500">Đơn đã kết thúc, không thể đổi trạng thái.</span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Danh sách món --}}
            <div class="overflow-x-auto rounded-2xl bg-white shadow-sm">
                <div class="border-b border-gray-100 px-4 py-3 text-sm font-semibold text-gray-900">Sản phẩm trong đơn</div>
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Sản phẩm</th>
                            <th class="px-4 py-3 text-right">Đơn giá lúc mua</th>
                            <th class="px-4 py-3 text-right">SL</th>
                            <th class="px-4 py-3 text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ $item->product_name }}
                                    @if ($item->product?->trashed())
                                        <span class="{{ $badge }} ml-2 bg-gray-100 text-gray-600">Đã xóa</span>
                                    @elseif ($item->product)
                                        <a href="{{ route('admin.products.edit', $item->product) }}"
                                           class="ml-2 text-xs font-normal text-primary hover:underline">Xem sản phẩm</a>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $money($item->unit_price) }}</td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $money($item->unit_price * $item->quantity) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <dl class="space-y-2 border-t border-gray-100 px-4 py-4 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <dt>Tạm tính</dt>
                        <dd>{{ $money($order->subtotal) }}</dd>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <dt>Giảm giá @if ($order->coupon) <span class="text-xs text-gray-400">({{ $order->coupon->code }})</span> @endif</dt>
                        <dd>− {{ $money($order->discount) }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 pt-2 text-base font-bold text-gray-900">
                        <dt>Tổng thanh toán (COD)</dt>
                        <dd>{{ $money($order->total) }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Dòng thời gian trạng thái --}}
            <div class="rounded-2xl bg-white p-4 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-gray-900">Lịch sử trạng thái</h2>

                <ol class="space-y-4">
                    @forelse ($order->histories as $history)
                        <li class="flex gap-3">
                            <span class="mt-1.5 h-2.5 w-2.5 flex-shrink-0 rounded-full {{ $loop->last ? 'bg-primary' : 'bg-gray-300' }}"></span>
                            <div class="text-sm">
                                <div class="font-medium text-gray-900">
                                    {{ $history->from_status === null ? 'Đặt hàng' : 'Chuyển sang' }}
                                    <span class="{{ $badge }} ml-1 {{ $statusClasses[$history->to_status] ?? 'bg-gray-100 text-gray-600' }}">{{ $history->toLabel() }}</span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $history->created_at->format('d/m/Y H:i') }}
                                    · {{ $history->actor?->name ?? 'Hệ thống' }}
                                </div>
                            </div>
                        </li>
                    @empty
                        {{-- Đơn cũ tạo trước khi có nhật ký --}}
                        <li class="text-sm text-gray-500">
                            Đặt hàng lúc {{ $order->created_at->format('d/m/Y H:i') }} (chưa có nhật ký chi tiết).
                        </li>
                    @endforelse
                </ol>
            </div>
        </div>

        {{-- Thông tin người nhận --}}
        <div class="space-y-6">
            <div class="rounded-2xl bg-white p-4 shadow-sm">
                <h2 class="mb-3 text-sm font-semibold text-gray-900">Người nhận</h2>
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-xs text-gray-400">Họ tên</dt><dd class="text-gray-900">{{ $order->receiver_name }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Số điện thoại</dt><dd class="text-gray-900">{{ $order->phone }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Địa chỉ giao hàng</dt><dd class="text-gray-900">{{ $order->shipping_address }}</dd></div>
                    <div>
                        <dt class="text-xs text-gray-400">Ghi chú</dt>
                        <dd class="whitespace-pre-line text-gray-900">{{ $order->note ?: 'Không có' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl bg-white p-4 shadow-sm">
                <h2 class="mb-3 text-sm font-semibold text-gray-900">Tài khoản đặt hàng</h2>
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-xs text-gray-400">Tên</dt><dd class="text-gray-900">{{ $order->user?->name ?? 'Không còn' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Email</dt><dd class="text-gray-900">{{ $order->user?->email ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-gray-400">Thanh toán</dt><dd class="text-gray-900">Thanh toán khi nhận hàng (COD)</dd></div>
                </dl>
            </div>
        </div>
    </div>
@endsection