@extends('layouts.admin')

@section('title', 'Danh mục')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Danh mục</h1>
        <a href="{{ route('admin.categories.create') }}"
           class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
            + Thêm danh mục
        </a>
    </div>

    @include('admin.partials.flash')

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Ảnh</th>
                    <th class="px-4 py-3">Tên</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3">Số lượng</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($categories as $root)
                    @foreach (collect([$root])->concat($root->children) as $cat)
                        <tr class="{{ $cat->parent_id ? 'bg-white' : 'bg-slate-50/50' }}">
                            <td class="px-4 py-3">
                                @if ($cat->image_url)
                                    <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}"
                                         class="h-10 w-10 rounded object-cover">
                                @endif
                            </td>
                            <td class="px-4 py-3 {{ $cat->parent_id ? 'pl-8 text-slate-600' : 'font-semibold text-slate-900' }}">
                                {{ $cat->parent_id ? '└ ' : '' }}{{ $cat->name }}
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $cat->slug }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                @if ($cat->parent_id)
                                    {{ $cat->products_count }} sản phẩm
                                @else
                                    {{ $cat->children_count }} danh mục con
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.categories.edit', $cat) }}"
                                       class="font-medium text-blue-600 hover:underline">Sửa</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}"
                                          onsubmit="return confirm('Xóa danh mục «{{ $cat->name }}»?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-red-600 hover:underline">Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">Chưa có danh mục nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $categories->links() }}</div>
@endsection