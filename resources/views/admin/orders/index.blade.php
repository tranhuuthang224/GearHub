@extends('layouts.admin', ['title' => 'Đơn hàng'])

@section('content')
    @php
        $badge = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium';
        $statusClasses = [
            'pending'   => 'bg-amber-50 text-amber-700',
            'shipping'  => 'bg-sky-50 text-sky-700',
            'completed' => 'bg-emerald-50 text-emerald-700',
            'cancelled' => 'bg-gray-100 text-gray-600',
        ];
        $tab = 'inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition';
        $money = fn ($amount) => number_format($amount, 0, ',', '.') . ' ₫';
    @endphp

    {{-- Tab trạng thái kèm số đơn --}}
    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('admin.orders.index', array_filter(['q' => $search])) }}"
           class="{{ $tab }} {{ $status === null ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
            Tất cả <span class="text-xs opacity-80">{{ number_format((int) $counts->sum(), 0, ',', '.') }}</span>
        </a>
        @foreach (\App\Models\Order::STATUS_LABELS as $key => $label)
            <a href="{{ route('admin.orders.index', array_filter(['status' => $key, 'q' => $search])) }}"
               class="{{ $tab }} {{ $status === $key ? 'bg-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                {{ $label }} <span class="text-xs opacity-80">{{ number_format((int) ($counts[$key] ?? 0), 0, ',', '.') }}</span>
            </a>
        @endforeach
    </div>

    {{-- Tìm kiếm --}}
    <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-6 flex flex-wrap gap-2">
        @if ($status)
            <input type="hidden" name="status" value="{{ $status }}">
        @endif
        <div class="w-full sm:w-80">
            <x-input name="q" placeholder="Tìm theo mã đơn, tên hoặc số điện thoại..." :value="$search" />
        </div>
        <x-button type="submit">Tìm</x-button>
        @if ($search !== '')
            <x-button :href="route('admin.orders.index', array_filter(['status' => $status]))" variant="outline">Xóa tìm kiếm</x-button>
        @endif
    </form>

    {{-- Bảng --}}
    <div class="overflow-x-auto rounded-2xl bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Mã đơn</th>
                    <th class="px-4 py-3">Người nhận</th>
                    <th class="px-4 py-3">Số món</th>
                    <th class="px-4 py-3">Tổng tiền</th>
                    <th class="px-4 py-3">Trạng thái</th>
                    <th class="px-4 py-3">Ngày đặt</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($orders as $order)
                    <tr>
                        <td class="px-4 py-3 font-semibold text-gray-900">
                            <a href="{{ route('admin.orders.show', $order) }}" class="hover:underline">{{ $order->code }}</a>
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            <div>{{ $order->receiver_name }}</div>
                            <div class="text-xs text-gray-400">{{ $order->phone }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $order->items_count }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $money($order->total) }}</td>
                        <td class="px-4 py-3">
                            <span class="{{ $badge }} {{ $statusClasses[$order->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $order->statusLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end">
                                <x-button :href="route('admin.orders.show', $order)" variant="outline" size="sm">Xem</x-button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                            {{ ($status || $search !== '') ? 'Không có đơn hàng nào phù hợp.' : 'Chưa có đơn hàng nào.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <x-pagination :paginator="$orders" />
    </div>
@endsection