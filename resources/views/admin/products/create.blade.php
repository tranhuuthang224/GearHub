@extends('layouts.admin', ['title' => 'Thêm sản phẩm'])

@section('content')
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data"
          class="max-w-3xl rounded-2xl bg-white p-6 shadow-sm">
        @csrf
        @include('admin.products._form')

        <div class="mt-8 flex gap-3">
            <x-button type="submit">Lưu sản phẩm</x-button>
            <x-button :href="route('admin.products.index')" variant="outline">Hủy</x-button>
        </div>
    </form>
@endsection