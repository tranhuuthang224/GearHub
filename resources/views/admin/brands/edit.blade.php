@extends('layouts.admin', ['title' => 'Sửa thương hiệu'])

@section('content')
    <form method="POST" action="{{ route('admin.brands.update', $brand) }}" enctype="multipart/form-data"
          class="max-w-xl rounded-2xl bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('admin.brands._form')

        <div class="mt-6 flex gap-3">
            <x-button type="submit">Cập nhật</x-button>
            <x-button :href="route('admin.brands.index')" variant="outline">Hủy</x-button>
        </div>
    </form>
@endsection