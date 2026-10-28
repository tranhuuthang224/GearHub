@extends('layouts.admin', ['title' => 'Thêm mã giảm giá'])

@section('content')
    <form method="POST" action="{{ route('admin.coupons.store') }}"
          class="max-w-2xl rounded-2xl bg-white p-6 shadow-sm">
        @csrf
        @include('admin.coupons._form')

        <div class="mt-8 flex gap-3">
            <x-button type="submit">Lưu mã</x-button>
            <x-button :href="route('admin.coupons.index')" variant="outline">Hủy</x-button>
        </div>
    </form>
@endsection