@extends('layouts.admin', ['title' => 'Quản lý Khách hàng'])

@section('content')
<div class="space-y-6">

    <!-- Header Tiêu đề -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Danh sách Khách hàng</h1>
            <p class="text-sm text-gray-500 mt-1">Quản lý tài khoản người dùng, trạng thái kích hoạt và theo dõi lịch sử mua sắm.</p>
        </div>
    </div>

    <!-- Bộ lọc & Tìm kiếm -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Tìm theo tên, email hoặc số điện thoại..." 
                    class="w-full text-sm rounded-xl border-gray-200 focus:border-primary focus:ring focus:ring-primary/20"
                >
            </div>
            <div class="flex gap-2">
                <select name="status" class="w-full text-sm rounded-xl border-gray-200 focus:border-primary focus:ring focus:ring-primary/20">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Đang bị khóa</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary/90 transition">
                    Lọc
                </button>
            </div>
        </form>
    </div>

    <!-- Bảng danh sách Khách hàng -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50/75 text-xs uppercase font-semibold text-gray-500">
                    <tr>
                        <th class="px-6 py-3.5">Khách hàng</th>
                        <th class="px-6 py-3.5">Liên hệ</th>
                        <th class="px-6 py-3.5 text-center">Tổng đơn</th>
                        <th class="px-6 py-3.5 text-center">Trạng thái</th>
                        <th class="px-6 py-3.5 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                                <div class="text-xs text-gray-400">Tham gia: {{ $user->created_at->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div>{{ $user->email }}</div>
                                <div class="text-xs text-gray-500">{{ $user->phone ?? 'Chưa cập nhật SĐT' }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                                    {{ $user->orders_count }} đơn
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($user->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                        Hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700">
                                        Đã khóa
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('admin.users.show', $user) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                    Chi tiết
                                </a>

                                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn thay đổi trạng thái tài khoản này?')">
                                    @csrf
                                    @method('PATCH')
                                    @if($user->is_active)
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-rose-700 bg-rose-50 rounded-lg hover:bg-rose-100 transition">
                                            Khóa
                                        </button>
                                    @else
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition">
                                            Mở khóa
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-400">Không tìm thấy khách hàng nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection