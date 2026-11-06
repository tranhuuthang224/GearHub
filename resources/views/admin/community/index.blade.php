@extends('layouts.admin', ['title' => 'Kiểm duyệt Diễn đàn Khoe Góc Máy'])

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <form method="GET" action="{{ route('admin.community.index') }}" class="flex flex-wrap gap-2">
            <select name="status" class="w-full sm:w-52 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20">
                <option value="">Tất cả trạng thái</option>
                <option value="published" @selected(request('status') === 'published')>Đã duyệt (Published)</option>
                <option value="pending" @selected(request('status') === 'pending')>Chờ duyệt (Pending)</option>
                <option value="hidden" @selected(request('status') === 'hidden')>Đã ẩn / Vi phạm (Hidden)</option>
            </select>
            <x-button type="submit">Lọc</x-button>
            @if(request('status'))
                <x-button :href="route('admin.community.index')" variant="outline">Xóa lọc</x-button>
            @endif
        </form>

        <a href="{{ route('community.index') }}" target="_blank" class="px-4 py-2.5 rounded-xl border border-gray-200 hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
            Xem trang cộng đồng ↗
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto rounded-2xl bg-white shadow-sm border border-gray-150">
        <div class="border-b border-gray-100 px-4 py-3 text-xs text-gray-500 font-semibold">
            {{ number_format($posts->total(), 0, ',', '.') }} bài đăng góc máy
        </div>

        <table class="min-w-full text-left text-xs">
            <thead class="bg-gray-50 text-gray-500 uppercase font-semibold">
                <tr>
                    <th class="px-4 py-3">Ảnh</th>
                    <th class="px-4 py-3">Tiêu đề bài viết</th>
                    <th class="px-4 py-3">Tác giả</th>
                    <th class="px-4 py-3">Phong cách</th>
                    <th class="px-4 py-3">Trạng thái</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @forelse($posts as $post)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 w-16">
                            <img src="{{ $post->display_image }}" alt="{{ $post->title }}" class="w-12 h-12 object-cover rounded-xl bg-gray-100 border border-gray-200">
                        </td>
                        <td class="px-4 py-3 max-w-xs font-semibold text-gray-900">
                            <a href="{{ route('community.show', $post->slug) }}" target="_blank" class="hover:text-primary transition line-clamp-2">
                                {{ $post->title }}
                            </a>
                            <div class="text-[10px] text-gray-400 mt-0.5">🛒 {{ $post->products->count() }} gear gắn thẻ</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $post->user?->name ?? 'Người dùng ẩn' }}</div>
                            <div class="text-[10px] text-gray-400">{{ $post->created_at->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-700">
                                {{ $post->style_info['name'] ?? $post->style }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($post->status === 'published')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Đã duyệt
                                </span>
                            @elseif($post->status === 'pending')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    Chờ duyệt
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    Đã ẩn
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right space-x-1">
                            @if($post->status !== 'published')
                                <form method="POST" action="{{ route('admin.community.status', $post->id) }}" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="published">
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 text-white font-bold text-[11px] hover:bg-emerald-700">Duyệt</button>
                                </form>
                            @endif

                            @if($post->status !== 'hidden')
                                <form method="POST" action="{{ route('admin.community.status', $post->id) }}" class="inline-block">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="hidden">
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-amber-500 text-white font-bold text-[11px] hover:bg-amber-600">Ẩn</button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('admin.community.destroy', $post->id) }}" class="inline-block" onsubmit="return confirm('Bạn có chắc muốn xóa vĩnh viễn bài đăng này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-red-600 text-white font-bold text-[11px] hover:bg-red-700">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">Không có bài đăng nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($posts->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
@endsection
