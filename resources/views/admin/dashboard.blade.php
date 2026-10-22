@extends('layouts.admin')

@section('title', 'Bảng điều khiển')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="p-6 sm:p-8 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">
                Chào mừng trở lại, {{ Auth::user()->name ?? 'Quản trị viên' }}! 👋
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Hệ thống quản trị cửa hàng phụ kiện công nghệ GearHub.
            </p>
        </div>
        <a href="{{ url('/') }}" target="_blank" class="px-5 py-2.5 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs font-bold transition flex items-center gap-1.5">
            <span>Xem Storefront</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
        </a>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="p-5 bg-white rounded-2xl shadow-sm border border-gray-100">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Danh mục</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-3xl font-black text-gray-900">{{ \App\Models\Category::count() }}</span>
                <a href="{{ route('admin.categories.index') }}" class="text-xs font-bold text-primary hover:underline">Quản lý →</a>
            </div>
        </div>

        <div class="p-5 bg-white rounded-2xl shadow-sm border border-gray-100">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Thương hiệu</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-3xl font-black text-gray-900">{{ \App\Models\Brand::count() }}</span>
                <a href="{{ route('admin.brands.index') }}" class="text-xs font-bold text-primary hover:underline">Quản lý →</a>
            </div>
        </div>

        <div class="p-5 bg-white rounded-2xl shadow-sm border border-gray-100">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Sản phẩm</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-3xl font-black text-gray-900">{{ \App\Models\Product::count() }}</span>
                <span class="text-xs font-semibold text-gray-400">Kho hàng</span>
            </div>
        </div>

        <div class="p-5 bg-white rounded-2xl shadow-sm border border-gray-100">
            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Khách hàng</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-3xl font-black text-gray-900">{{ \App\Models\User::where('role', 'customer')->count() }}</span>
                <span class="text-xs font-semibold text-emerald-600">Hoạt động</span>
            </div>
        </div>
    </div>
</div>
@endsection
