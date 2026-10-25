@extends('layouts.admin', ['title' => 'Sửa sản phẩm'])

@section('content')
    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data"
          class="max-w-3xl rounded-2xl bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('admin.products._form')

        <div class="mt-8 flex gap-3">
            <x-button type="submit">Cập nhật</x-button>
            <x-button :href="route('admin.products.index')" variant="outline">Hủy</x-button>
        </div>
    </form>
@endsection