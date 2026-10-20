@extends('layouts.admin')

@section('title', 'Sửa danh mục')

@section('content')
    <h1 class="mb-6 text-2xl font-bold text-slate-900">Sửa danh mục</h1>

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data"
          class="max-w-xl rounded-xl bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('admin.categories._form')

        <div class="mt-6 flex gap-3">
            <button type="submit"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Cập nhật</button>
            <a href="{{ route('admin.categories.index') }}"
               class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Hủy</a>
        </div>
    </form>
@endsection