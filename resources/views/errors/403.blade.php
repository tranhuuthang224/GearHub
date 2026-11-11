@extends('layouts.client', ['title' => '403 - Quyền truy cập bị từ chối | GearHub'])

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4 py-16 bg-[#F8FAFC]">
    <div class="text-center max-w-md">
        <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-sm">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m12-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Truy cập bị từ chối (403)</h1>
        <p class="mt-3 text-sm text-gray-500 leading-relaxed">
            Bạn không có quyền quản trị viên để truy cập khu vực này. Vui lòng quay trở lại cửa hàng.
        </p>
        <div class="mt-8 flex justify-center gap-3">
            <a href="{{ route('home') }}" class="px-5 py-2.5 bg-primary hover:bg-primary-hover text-white text-xs sm:text-sm font-bold rounded-xl transition shadow-md shadow-primary/20">
                Về trang chủ GearHub
            </a>
            <a href="{{ route('shop.index') }}" class="px-5 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs sm:text-sm font-bold rounded-xl transition shadow-xs">
                Khám phá sản phẩm
            </a>
        </div>
    </div>
</div>
@endsection
