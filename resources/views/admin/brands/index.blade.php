@extends('layouts.admin', ['title' => 'Thương hiệu'])

@section('content')
    <div class="mb-6 flex justify-end">
        <x-button :href="route('admin.brands.create')">+ Thêm thương hiệu</x-button>
    </div>

    <div class="overflow-x-auto rounded-2xl bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Logo</th>
                    <th class="px-4 py-3">Tên</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3">Số sản phẩm</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($brands as $brand)
                    <tr>
                        <td class="px-4 py-3">
                            @if ($brand->logo_url)
                                <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}"
                                     class="h-10 w-10 rounded-lg object-cover">
                            @endif
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $brand->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $brand->slug }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $brand->products_count }} sản phẩm</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <x-button :href="route('admin.brands.edit', $brand)" variant="outline" size="sm">Sửa</x-button>

                                <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}"
                                      onsubmit="return confirm(@js('Xóa thương hiệu «' . $brand->name . '»?'))">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="danger" size="sm">Xóa</x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Chưa có thương hiệu nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <x-pagination :paginator="$brands" />
    </div>
@endsection