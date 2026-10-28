@extends('layouts.admin', ['title' => 'Mã giảm giá'])

@section('content')
    @php
        $fieldClass = 'w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition';
        $badge      = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium';
        $statusClasses = [
            'active'    => 'bg-emerald-50 text-emerald-700',
            'inactive'  => 'bg-gray-100 text-gray-600',
            'scheduled' => 'bg-sky-50 text-sky-700',
            'expired'   => 'bg-rose-50 text-rose-700',
            'exhausted' => 'bg-amber-50 text-amber-700',
        ];
    @endphp

    {{-- Thanh công cụ --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <form method="GET" action="{{ route('admin.coupons.index') }}" class="flex flex-wrap gap-2">
            <select name="status" class="{{ $fieldClass }} sm:w-52" aria-label="Lọc theo kích hoạt">
                <option value="">Tất cả mã</option>
                <option value="on" @selected($status === 'on')>Đang bật</option>
                <option value="off" @selected($status === 'off')>Đang tắt</option>
            </select>
            <x-button type="submit">Lọc</x-button>
            @if ($status)
                <x-button :href="route('admin.coupons.index')" variant="outline">Xóa lọc</x-button>
            @endif
        </form>

        <x-button :href="route('admin.coupons.create')">+ Thêm mã giảm giá</x-button>
    </div>

    {{-- Bảng --}}
    <div class="overflow-x-auto rounded-2xl bg-white shadow-sm">
        <div class="border-b border-gray-100 px-4 py-3 text-sm text-gray-500">
            {{ number_format($coupons->total(), 0, ',', '.') }} mã giảm giá
        </div>

        <table class="min-w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Mã</th>
                    <th class="px-4 py-3">Giảm</th>
                    <th class="px-4 py-3">Đơn tối thiểu</th>
                    <th class="px-4 py-3">Lượt dùng</th>
                    <th class="px-4 py-3">Thời hạn</th>
                    <th class="px-4 py-3">Tình trạng</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($coupons as $coupon)
                    @php $state = $coupon->status(); @endphp
                    <tr>
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $coupon->code }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $coupon->discountLabel() }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $coupon->min_order > 0 ? number_format($coupon->min_order, 0, ',', '.') . ' ₫' : 'Không yêu cầu' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ number_format($coupon->used_count, 0, ',', '.') }}
                            / {{ $coupon->usage_limit !== null ? number_format($coupon->usage_limit, 0, ',', '.') : '∞' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            @if ($coupon->starts_at || $coupon->expires_at)
                                <div>{{ $coupon->starts_at ? $coupon->starts_at->format('d/m/Y H:i') : 'Từ trước' }}</div>
                                <div class="text-xs text-gray-400">
                                    đến {{ $coupon->expires_at ? $coupon->expires_at->format('d/m/Y H:i') : 'không giới hạn' }}
                                </div>
                            @else
                                Không giới hạn
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="{{ $badge }} {{ $statusClasses[$state] }}">{{ $coupon->statusLabel() }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <x-button :href="route('admin.coupons.edit', $coupon)" variant="outline" size="sm">Sửa</x-button>

                                <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}"
                                      onsubmit="return confirm(@js('Xóa mã giảm giá «' . $coupon->code . '»?'))">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="danger" size="sm">Xóa</x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                            {{ $status ? 'Không có mã nào phù hợp với bộ lọc.' : 'Chưa có mã giảm giá nào.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <x-pagination :paginator="$coupons" />
    </div>
@endsection