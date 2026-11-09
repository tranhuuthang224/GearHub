@extends('layouts.admin', ['title' => 'Chi tiết Khách hàng - ' . $user->name])

@section('content')
<div class="space-y-6">

    <!-- Header điều hướng -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-primary hover:underline">&larr; Quay lại danh sách khách hàng</a>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 mt-2">{{ $user->name }}</h1>
        </div>
        <div>
            <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn đổi trạng thái?')">
                @csrf
                @method('PATCH')
                @if($user->is_active)
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-rose-600 rounded-xl hover:bg-rose-700 transition">
                        Khóa tài khoản
                    </button>
                @else
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition">
                        Mở khóa tài khoản
                    </button>
                @endif
            </form>
        </div>
    </div>

    <!-- Thông tin tổng quan & Thống kê chi tiêu -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase">Thông tin liên hệ</p>
            <div class="mt-3 space-y-1.5 text-sm">
                <p><span class="text-gray-400">Email:</span> <span class="font-medium text-gray-900">{{ $user->email }}</span></p>
                <p><span class="text-gray-400">SĐT:</span> <span class="font-medium text-gray-900">{{ $user->phone ?? 'Chưa cập nhật' }}</span></p>
                <p><span class="text-gray-400">Ngày tham gia:</span> <span class="font-medium text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</span></p>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase">Tổng chi tiêu thành công</p>
            <h3 class="text-2xl font-extrabold text-primary mt-2">{{ number_format($totalSpent, 0, ',', '.') }} ₫</h3>
            <p class="text-xs text-gray-500 mt-1">Từ {{ $completedOrdersCount }} đơn hàng đã hoàn tất</p>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold text-gray-500 uppercase">Trạng thái tài khoản</p>
            <div class="mt-3">
                @if($user->is_active)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Đang hoạt động bình thường
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Tài khoản đang bị khóa
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Lịch sử các đơn hàng đã đặt -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="font-bold text-gray-900">Lịch sử đơn hàng</h3>
            <p class="text-xs text-gray-500 mt-0.5">Danh sách các đơn hàng khách đã đặt trên GearHub</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50/75 text-xs uppercase font-semibold text-gray-500">
                    <tr>
                        <th class="px-6 py-3.5">Mã đơn</th>
                        <th class="px-6 py-3.5">Thời gian</th>
                        <th class="px-6 py-3.5">Số mặt hàng</th>
                        <th class="px-6 py-3.5">Tổng tiền</th>
                        <th class="px-6 py-3.5">Trạng thái</th>
                        <th class="px-6 py-3.5 text-right">Xem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 font-bold text-gray-900">
                                #{{ $order->code }}
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-xs">
                                {{ $order->items->count() }} sản phẩm
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                {{ number_format($order->total, 0, ',', '.') }} ₫
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                    @if($order->status === 'completed') bg-emerald-50 text-emerald-700
                                    @elseif($order->status === 'cancelled') bg-rose-50 text-rose-700
                                    @elseif($order->status === 'shipping') bg-blue-50 text-blue-700
                                    @else bg-amber-50 text-amber-700 @endif
                                ">
                                    {{ $order->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-xs font-semibold text-primary hover:underline">
                                    Chi tiết &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">Khách hàng chưa đặt đơn hàng nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection