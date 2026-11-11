@extends('layouts.client', ['title' => '404 - Không tìm thấy trang | GearHub'])

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4 py-16 bg-[#F8FAFC]">
    <div class="text-center max-w-md">
        <div class="w-20 h-20 bg-rose-100 text-primary rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-sm">
            <span class="text-3xl font-black">404</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight">Trang không tồn tại</h1>
        <p class="mt-3 text-sm text-gray-500 leading-relaxed">
            Liên kết bạn truy cập không tồn tại, đã bị gỡ bỏ hoặc đã được chuyển sang địa chỉ khác.
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
