@extends('layouts.admin', ['title' => 'Sản phẩm'])

@section('content')
    @php
        $fieldClass = 'w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition';
        $badge      = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium';
    @endphp

    {{-- Thanh công cụ --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <form method="GET" action="{{ route('admin.products.index') }}"
              class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <x-input name="q" placeholder="Tìm theo tên hoặc SKU..." :value="$filters['q']" />

            <select name="category" class="{{ $fieldClass }}" aria-label="Lọc theo danh mục">
                <option value="">Tất cả danh mục</option>
                @foreach ($categoryGroups as $group)
                    <optgroup label="{{ $group->name }}">
                        <option value="{{ $group->id }}" @selected($filters['category'] == $group->id)>Tất cả {{ $group->name }}</option>
                        @foreach ($group->children as $child)
                            <option value="{{ $child->id }}" @selected($filters['category'] == $child->id)>{{ $child->name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>

            <select name="brand" class="{{ $fieldClass }}" aria-label="Lọc theo thương hiệu">
                <option value="">Tất cả thương hiệu</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}" @selected($filters['brand'] == $brand->id)>{{ $brand->name }}</option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <x-button type="submit">Lọc</x-button>
                @if ($hasFilters)
                    <x-button :href="route('admin.products.index')" variant="outline">Xóa lọc</x-button>
                @endif
            </div>
        </form>

        <x-button :href="route('admin.products.create')">+ Thêm sản phẩm</x-button>
    </div>

    {{-- Bảng --}}
    <div class="overflow-x-auto rounded-2xl bg-white shadow-sm">
        <div class="border-b border-gray-100 px-4 py-3 text-sm text-gray-500">
            {{ number_format($products->total(), 0, ',', '.') }} sản phẩm
        </div>

        <table class="min-w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Ảnh</th>
                    <th class="px-4 py-3">Tên</th>
                    <th class="px-4 py-3">SKU</th>
                    <th class="px-4 py-3">Danh mục</th>
                    <th class="px-4 py-3">Thương hiệu</th>
                    <th class="px-4 py-3">Giá bán</th>
                    <th class="px-4 py-3">Tồn kho</th>
                    <th class="px-4 py-3">Hiển thị</th>
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
                        <td class="px-4 py-3">
                            @if ($product->sale_price !== null)
                                <div class="font-semibold text-gray-900">{{ number_format($product->sale_price, 0, ',', '.') }} ₫</div>
                                <div class="text-xs text-gray-400 line-through">{{ number_format($product->price, 0, ',', '.') }} ₫</div>
                            @else
                                <div class="font-semibold text-gray-900">{{ number_format($product->price, 0, ',', '.') }} ₫</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ number_format($product->stock_quantity, 0, ',', '.') }}</div>
                            @if ($product->isOutOfStock())
                                <span class="{{ $badge }} bg-rose-50 text-rose-700">Hết hàng</span>
                            @elseif ($product->isLowStock())
                                <span class="{{ $badge }} bg-amber-50 text-amber-700">Sắp hết</span>
                            @else
                                <span class="{{ $badge }} bg-emerald-50 text-emerald-700">Còn hàng</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($product->is_active)
                                <span class="{{ $badge }} bg-emerald-50 text-emerald-700">Hiển thị</span>
                            @else
                                <span class="{{ $badge }} bg-gray-100 text-gray-600">Ẩn</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <x-button :href="route('admin.products.edit', $product)" variant="outline" size="sm">Sửa</x-button>
                                {{-- Chủ nhật thêm nút Xóa --}}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-gray-500">
                            @if ($hasFilters)
                                Không tìm thấy sản phẩm phù hợp với bộ lọc.
                            @else
                                Chưa có sản phẩm nào.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <x-pagination :paginator="$products" />
    </div>
@endsection