@extends('layouts.admin', ['title' => 'Thùng rác'])

@section('content')
    @php
        $badge = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium';
    @endphp

    <p class="mb-4 text-sm text-gray-500">
        Sản phẩm đã xóa được ẩn khỏi cửa hàng. Khôi phục để bán lại, hoặc xóa vĩnh viễn để giải phóng dung lượng.
    </p>

    <div class="overflow-x-auto rounded-2xl bg-white shadow-sm">
        <div class="border-b border-gray-100 px-4 py-3 text-sm text-gray-500">
            {{ number_format($products->total(), 0, ',', '.') }} sản phẩm trong Thùng rác
        </div>

        <table class="min-w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Ảnh</th>
                    <th class="px-4 py-3">Tên</th>
                    <th class="px-4 py-3">SKU</th>
                    <th class="px-4 py-3">Danh mục</th>
                    <th class="px-4 py-3">Thương hiệu</th>
                    <th class="px-4 py-3">Ngày xóa</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($products as $product)
                    <tr>
                        <td class="px-4 py-3">
                            @if ($product->thumbnail_url)
                                <img src="{{ $product->thumbnail_url }}" alt="{{ $product->name }}"
                                     class="h-12 w-12 rounded-lg object-cover">
                            @else
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 text-[10px] text-gray-400">
                                    Không ảnh
                                </div>
                            @endif
                        </td>
                        <td class="max-w-xs px-4 py-3 font-semibold text-gray-900">{{ $product->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $product->sku }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            @if ($product->category?->parent)
                                <div class="text-xs text-gray-400">{{ $product->category->parent->name }}</div>
                            @endif
                            {{ $product->category?->name }}
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $product->brand?->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $product->deleted_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <form method="POST" action="{{ route('admin.trash.restore', $product) }}">
                                    @csrf
                                    <x-button type="submit" variant="outline" size="sm">Khôi phục</x-button>
                                </form>

                                @if ($product->order_items_count > 0)
                                    <span class="{{ $badge }} bg-amber-50 text-amber-700"
                                          title="Sản phẩm đã có trong đơn hàng nên không thể xóa vĩnh viễn">
                                        Có trong {{ $product->order_items_count }} đơn
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('admin.trash.destroy', $product) }}"
                                          onsubmit="return confirm(@js('Xóa vĩnh viễn «' . $product->name . '»? Sản phẩm và toàn bộ ảnh sẽ bị xóa, không thể hoàn tác.'))">
                                        @csrf
                                        @method('DELETE')
                                        <x-button type="submit" variant="danger" size="sm">Xóa vĩnh viễn</x-button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-500">Thùng rác trống.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <x-pagination :paginator="$products" />
    </div>
@endsection