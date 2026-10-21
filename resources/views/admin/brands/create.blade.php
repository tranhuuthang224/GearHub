@extends('layouts.admin', ['title' => 'Thêm thương hiệu'])

@section('content')
    <form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data"
          class="max-w-xl rounded-2xl bg-white p-6 shadow-sm">
        @csrf
        @include('admin.brands._form')

        <div class="mt-6 flex gap-3">
            <x-button type="submit">Lưu</x-button>
            <x-button :href="route('admin.brands.index')" variant="outline">Hủy</x-button>
        </div>
    </form>
@endsection