@extends('layouts.admin', ['title' => 'Sửa mã giảm giá'])

@section('content')
    <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}"
          class="max-w-2xl rounded-2xl bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('admin.coupons._form')

        <div class="mt-8 flex gap-3">
            <x-button type="submit">Cập nhật</x-button>
            <x-button :href="route('admin.coupons.index')" variant="outline">Hủy</x-button>
        </div>
    </form>
@endsection